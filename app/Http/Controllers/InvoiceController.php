<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WorksheetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $customerId = $request->query('customer_id');

        $query = Invoice::with(['customer', 'worksheetItems.worksheet', 'payments']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_nota', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($c) use ($search) {
                      $c->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_customer', 'like', "%{$search}%");
                  });
            });
        }

        if ($status && in_array($status, ['belum_lunas', 'sebagian', 'lunas'])) {
            $query->where('status_pembayaran', $status);
        }

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $invoices = $query->latest('tanggal')->latest('id')->paginate(10)->withQueryString();
        $customers = Customer::where('status', 'active')->orderBy('nama')->get();

        return view('invoices.index', compact('invoices', 'customers', 'search', 'status', 'customerId'));
    }

    public function create(Request $request)
    {
        $nextNumber = Invoice::generateNumber();
        $selectedCustomerId = $request->query('customer_id');
        
        $customers = Customer::where('status', 'active')
            ->where(function ($query) use ($selectedCustomerId) {
                $query->whereHas('worksheetItems', function ($q) {
                    $q->where('status', 'selesai')
                      ->whereNull('invoice_id');
                });
                if ($selectedCustomerId) {
                    $query->orWhere('id', $selectedCustomerId);
                }
            })
            ->orderBy('nama')
            ->get();

        $today = now()->toDateString();

        return view('invoices.create', compact('nextNumber', 'customers', 'selectedCustomerId', 'today'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor_nota' => 'required|string|max:50|unique:invoices,nomor_nota',
            'tipe_nota' => 'nullable|string|in:pernama,pecahan',
            'tanggal' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'worksheet_ids' => 'required|array|min:1',
            'worksheet_ids.*' => 'exists:worksheet_items,id',
            'sisa_hutang_sebelumnya' => 'required|numeric|min:0',
            'biaya_adm' => 'nullable|numeric|min:0',
            'penyesuaian_biaya' => 'nullable|numeric',
            'jumlah_dibayar' => 'nullable|numeric|min:0',
            'metode_pembayaran' => 'nullable|string|in:transfer,tunai,cash,giro,qris',
            'catatan' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $items = WorksheetItem::whereIn('id', $request->worksheet_ids)
                ->where('customer_id', $request->customer_id)
                ->where('status', 'selesai')
                ->whereNull('invoice_id')
                ->get();

            if ($items->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Tidak ada lembar kerja kargo valid yang dipilih.');
            }

            $subtotal = (float) $items->sum('total_biaya');
            $sisaHutangLama = (float) $request->sisa_hutang_sebelumnya;
            $penyesuaian = (float) ($request->penyesuaian_biaya ?? 0);

            // Tentukan tipe nota
            $countSmu = $items->count();
            $tipeNota = $request->tipe_nota ?: ($countSmu > 1 ? 'pernama' : 'pecahan');

            // Hitung Biaya Administrasi (ADM):
            // Pernama: (Jumlah LK / SMU) x Rp 3.000 (misal 4 LK -> 4 x 3.000 = 12.000)
            // Pecahan: Berapapun LK tetap dihitung Rp 3.000 (1 x Rp 3.000)
            if ($request->filled('biaya_adm')) {
                $biayaAdm = (float) $request->biaya_adm;
            } else {
                $biayaAdm = ($tipeNota === 'pernama') ? ($countSmu * 3000) : ($countSmu > 0 ? 3000 : 0);
            }

            $totalNota = $subtotal + $biayaAdm + $penyesuaian;
            $totalTagihanKumulatif = $sisaHutangLama + $totalNota;

            $jumlahDibayar = (float) ($request->jumlah_dibayar ?? 0);
            $sisaPiutang = max(0, $totalNota - $jumlahDibayar);

            if ($sisaPiutang <= 0) {
                $statusPembayaran = 'lunas';
            } elseif ($jumlahDibayar > 0) {
                $statusPembayaran = 'sebagian';
            } else {
                $statusPembayaran = 'belum_lunas';
            }

            $invoice = Invoice::create([
                'nomor_nota' => $request->nomor_nota,
                'tipe_nota' => $tipeNota,
                'tanggal' => $request->tanggal,
                'customer_id' => $request->customer_id,
                'sisa_hutang_sebelumnya' => $sisaHutangLama,
                'subtotal_lembar_kerja' => $subtotal,
                'biaya_adm' => $biayaAdm,
                'penyesuaian_biaya' => $penyesuaian,
                'total_nota' => $totalNota,
                'total_tagihan_kumulatif' => $totalTagihanKumulatif,
                'jumlah_dibayar' => $jumlahDibayar,
                'sisa_piutang' => $sisaPiutang,
                'status_pembayaran' => $statusPembayaran,
                'catatan' => $request->catatan,
                'user_id' => auth()->id(),
            ]);

            // Mark worksheet items as dinotakan
            WorksheetItem::whereIn('id', $items->pluck('id'))->update([
                'invoice_id' => $invoice->id,
                'status' => 'dinotakan',
            ]);

            // Trigger recalculation on parent worksheets
            $parentWorksheetIds = $items->pluck('worksheet_id')->unique();
            foreach ($parentWorksheetIds as $wId) {
                $parent = \App\Models\Worksheet::find($wId);
                $parent?->recalculateTotals();
            }

            // If initial payment was made, record payment and allocation
            if ($jumlahDibayar > 0) {
                $pay = Payment::create([
                    'nomor_pembayaran' => Payment::generateNumber(),
                    'tanggal' => $request->tanggal,
                    'invoice_id' => $invoice->id,
                    'customer_id' => $request->customer_id,
                    'jumlah' => $jumlahDibayar,
                    'metode' => $request->metode_pembayaran === 'cash' ? 'tunai' : ($request->metode_pembayaran ?? 'transfer'),
                    'catatan' => 'Pembayaran awal saat nota diterbitkan',
                    'user_id' => auth()->id(),
                ]);

                \App\Models\PaymentAllocation::create([
                    'payment_id' => $pay->id,
                    'invoice_id' => $invoice->id,
                    'jumlah_alokasi' => $jumlahDibayar,
                ]);
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Nota penagihan ' . $invoice->nomor_nota . ' berhasil diterbitkan!');
        });
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'worksheetItems.worksheet', 'payments.user', 'user']);
        return view('invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'worksheetItems.worksheet', 'payments']);
        return view('invoices.print', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            $parentIds = $invoice->worksheetItems->pluck('worksheet_id')->unique();

            // Revert worksheet items back to 'selesai'
            WorksheetItem::where('invoice_id', $invoice->id)->update([
                'invoice_id' => null,
                'status' => 'selesai',
            ]);

            // Delete associated payments
            $invoice->payments()->delete();

            $nomor = $invoice->nomor_nota;
            $invoice->delete();

            // Recalculate parent worksheets
            foreach ($parentIds as $wId) {
                $parent = \App\Models\Worksheet::find($wId);
                if ($parent) {
                    $parent->status = 'selesai';
                    $parent->recalculateTotals();
                }
            }

            return redirect()->route('invoices.index')
                ->with('success', "Nota {$nomor} berhasil dibatalkan dan item lembar kerja dikembalikan ke status Selesai.");
        });

        return redirect()->route('invoices.index');
    }
}
