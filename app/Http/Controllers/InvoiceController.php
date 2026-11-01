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

        $query = Invoice::withTrashed()->with(['customer', 'worksheetItems.worksheet', 'payments']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_nota', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($c) use ($search) {
                      $c->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_customer', 'like', "%{$search}%");
                  });
            });
        }

        if ($status && in_array($status, ['belum_lunas', 'sebagian', 'lunas', 'dibatalkan'])) {
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
            $items = WorksheetItem::with(['worksheet', 'customer'])
                ->whereIn('id', $request->worksheet_ids)
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

            // Hitung Biaya Administrasi (ADM):
            // Point 7: untuk pecahan biaya admin nya cuma 3000 untuk semua pecahan ( 1 x 3000 ), 
            // sedangkan untuk pernama biaya admin nya total lk x 3000

            // Hitung komposisi tipe LK
            $pecahanCount = $items->filter(fn($i) => ($i->worksheet->tipe ?? 'pecahan') === 'pecahan')->count();
            $pernamaCount = $items->filter(fn($i) => ($i->worksheet->tipe ?? 'pecahan') === 'pernama')->count();

            $biayaAdmPecahan = ($pecahanCount > 0) ? 3000.0 : 0.0;
            $biayaAdmPernama = (float) ($pernamaCount * 3000);
            $calculatedBiayaAdm = $biayaAdmPecahan + $biayaAdmPernama;

            $biayaAdm = $request->filled('biaya_adm') ? (float) $request->biaya_adm : $calculatedBiayaAdm;

            // Tentukan tipe nota
            $countSmu = $items->count();
            $tipeNota = $request->tipe_nota ?: ($pernamaCount > 0 ? 'pernama' : 'pecahan');

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
                'biaya_adm_pecahan' => $biayaAdmPecahan,
                'biaya_adm_pernama' => $biayaAdmPernama,
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
        if ($invoice->status_pembayaran === 'dibatalkan') {
            return redirect()->back()->with('error', 'Nota ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->load(['worksheetItems.worksheet', 'worksheetItems.customer', 'allocations.payment']);

            // 1. Simpan snapshot detail lembar kerja sebelum dilepas agar nota lama tetap utuh untuk perbandingan
            $snapshot = $invoice->worksheetItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'worksheet_id' => $item->worksheet_id,
                    'nomor_lembar_kerja' => $item->worksheet->nomor_lembar_kerja ?? '-',
                    'nomor_smu' => $item->worksheet->nomor_smu ?? '-',
                    'tipe_lembar_kerja' => $item->worksheet->tipe ?? 'pecahan',
                    'tanggal' => $item->worksheet ? $item->worksheet->tanggal->format('Y-m-d') : null,
                    'customer_id' => $item->customer_id,
                    'customer_nama' => $item->customer->nama ?? '-',
                    'jumlah_koli' => (int) $item->jumlah_koli,
                    'berat_kg' => (float) $item->berat_kg,
                    'tarif_per_kg' => (float) $item->tarif_per_kg,
                    'total_biaya' => (float) $item->total_biaya,
                    'keterangan' => $item->keterangan,
                    'origin' => $item->origin ?? ($item->worksheet->origin ?? 'lop'),
                    'marking' => $item->marking ?? '-',
                ];
            })->toArray();

            $parentIds = $invoice->worksheetItems->pluck('worksheet_id')->unique();

            // 2. Kembalikan worksheet items ke status 'selesai' dan kosongkan invoice_id agar bisa diedit & dinotakan ulang
            WorksheetItem::where('invoice_id', $invoice->id)->update([
                'invoice_id' => null,
                'status' => 'selesai',
            ]);

            // 3. Recalculate parent worksheets status
            foreach ($parentIds as $wId) {
                $parent = \App\Models\Worksheet::find($wId);
                if ($parent) {
                    $parent->status = 'selesai';
                    $parent->recalculateTotals();
                }
            }

            // 4. Batalkan alokasi pembayaran terkait
            foreach ($invoice->allocations as $alloc) {
                $payment = $alloc->payment;
                $alloc->delete();
                if ($payment) {
                    $payment->sisa_lebih_bayar = (float) $payment->sisa_lebih_bayar + (float) $alloc->jumlah_alokasi;
                    $payment->save();
                }
            }

            // Hapus pembayaran awal langsung yang dibuat saat penerbitan nota ini
            Payment::where('invoice_id', $invoice->id)->delete();

            // 5. Update status nota ke 'dibatalkan', simpan snapshot, nolkan sisa piutang, dan soft delete
            $invoice->items_snapshot = $snapshot;
            $invoice->status_pembayaran = 'dibatalkan';
            $invoice->sisa_piutang = 0;
            $invoice->batal_at = now();
            $invoice->save();
            $invoice->delete();
        });

        return redirect()->route('invoices.index')
            ->with('success', "Nota {$invoice->nomor_nota} berhasil dibatalkan. Seluruh lembar kerja telah dibuka kembali untuk diedit atau dinotakan ulang, dan arsip data nota lama tetap tersimpan.");
    }
}
