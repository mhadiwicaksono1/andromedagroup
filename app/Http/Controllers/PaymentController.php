<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $customerId = $request->input('customer_id');
        $metode = $request->input('metode');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Payment::with(['customer', 'user', 'allocations.invoice'])
            ->latest('tanggal')
            ->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pembayaran', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('nama', 'like', "%{$search}%")
                         ->orWhere('kode_customer', 'like', "%{$search}%");
                  });
            });
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($metode) {
            $query->where('metode', $metode);
        }

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $payments = $query->paginate(15)->withQueryString();
        $customers = Customer::where('status', 'active')->orderBy('nama')->get();

        // Summary stats
        $totalBulanIni = Payment::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->sum('jumlah');

        $totalSemua = Payment::sum('jumlah');

        return view('payments.index', compact(
            'payments',
            'customers',
            'search',
            'customerId',
            'metode',
            'startDate',
            'endDate',
            'totalBulanIni',
            'totalSemua'
        ));
    }

    public function create(Request $request)
    {
        $selectedCustomerId = $request->input('customer_id');
        $customers = Customer::where('status', 'active')->orderBy('nama')->get();
        $nextNumber = Payment::generateNumber();
        $today = now()->format('Y-m-d');

        return view('payments.create', compact('customers', 'selectedCustomerId', 'nextNumber', 'today'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'metode' => 'required|string|in:transfer,tunai,giro,qris',
            'catatan' => 'nullable|string|max:500',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);

        return DB::transaction(function () use ($validated, $customer) {
            $payment = Payment::create([
                'nomor_pembayaran' => Payment::generateNumber(),
                'tanggal' => $validated['tanggal'],
                'customer_id' => $customer->id,
                'invoice_id' => null, // Pembayaran tingkat customer
                'jumlah' => $validated['jumlah'],
                'metode' => $validated['metode'],
                'catatan' => $validated['catatan'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Auto-alokasi FIFO ke nota-nota yang belum lunas
            $unpaidInvoices = Invoice::where('customer_id', $customer->id)
                ->where('status_pembayaran', '!=', 'lunas')
                ->orderBy('tanggal', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $remaining = (float) $validated['jumlah'];

            foreach ($unpaidInvoices as $inv) {
                if ($remaining <= 0) break;

                $needed = (float) $inv->sisa_piutang;
                $allocated = min($remaining, $needed);

                $inv->jumlah_dibayar += $allocated;
                $inv->sisa_piutang -= $allocated;
                $inv->status_pembayaran = ($inv->sisa_piutang <= 0) ? 'lunas' : 'sebagian';
                $inv->save();

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $inv->id,
                    'jumlah_alokasi' => $allocated,
                ]);

                $remaining -= $allocated;
            }

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Pembayaran sebesar Rp ' . number_format($validated['jumlah'], 0, ',', '.') . ' untuk customer ' . $customer->nama . ' berhasil dicatat dan dialokasikan!');
        });
    }

    public function show(Payment $payment)
    {
        $payment->load(['customer', 'user', 'allocations.invoice']);
        return view('payments.show', compact('payment'));
    }

    public function print(Payment $payment)
    {
        $payment->load(['customer', 'user', 'allocations.invoice']);
        return view('payments.print', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $nomor = $payment->nomor_pembayaran;

        DB::transaction(function () use ($payment) {
            // Rollback alokasi ke tiap nota
            foreach ($payment->allocations as $alloc) {
                $invoice = $alloc->invoice;
                if ($invoice) {
                    $invoice->jumlah_dibayar = max(0, $invoice->jumlah_dibayar - $alloc->jumlah_alokasi);
                    $invoice->sisa_piutang += $alloc->jumlah_alokasi;
                    $invoice->status_pembayaran = ($invoice->jumlah_dibayar <= 0) ? 'belum_lunas' : 'sebagian';
                    $invoice->save();
                }
                $alloc->delete();
            }

            $payment->delete();
        });

        return redirect()->route('payments.index')
            ->with('success', "Pembayaran {$nomor} berhasil dibatalkan (Soft Delete) dan alokasi piutang nota telah dipulihkan.");
    }

    public function storeForInvoice(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
            'metode' => 'required|string|in:transfer,tunai,giro,qris',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($invoice->sisa_piutang <= 0) {
            return redirect()->back()->with('error', 'Nota ini sudah lunas.');
        }

        if ($validated['jumlah'] > $invoice->sisa_piutang) {
            return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa piutang (Maks: Rp ' . number_format($invoice->sisa_piutang, 0, ',', '.') . ').');
        }

        DB::transaction(function () use ($validated, $invoice) {
            $payment = Payment::create([
                'nomor_pembayaran' => Payment::generateNumber(),
                'tanggal' => $validated['tanggal'],
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'jumlah' => $validated['jumlah'],
                'metode' => $validated['metode'],
                'catatan' => $validated['catatan'] ?? null,
                'user_id' => auth()->id(),
            ]);

            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'jumlah_alokasi' => $validated['jumlah'],
            ]);

            $totalDibayarBaru = $invoice->jumlah_dibayar + $validated['jumlah'];
            $sisaPiutangBaru = max(0, $invoice->total_nota - $totalDibayarBaru);
            $statusPembayaran = ($sisaPiutangBaru <= 0) ? 'lunas' : 'sebagian';

            $invoice->update([
                'jumlah_dibayar' => $totalDibayarBaru,
                'sisa_piutang' => $sisaPiutangBaru,
                'status_pembayaran' => $statusPembayaran,
            ]);
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Pembayaran sebesar Rp ' . number_format($validated['jumlah'], 0, ',', '.') . ' untuk nota ' . $invoice->nomor_nota . ' berhasil dicatat!');
    }
}
