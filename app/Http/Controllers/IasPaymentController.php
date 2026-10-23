<?php

namespace App\Http\Controllers;

use App\Models\IasPayment;
use App\Models\Invoice;
use App\Models\Worksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class IasPaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');
        $bank = $request->query('bank');

        $query = IasPayment::with(['user', 'worksheet', 'invoices.customer']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi_ias', 'like', "%{$search}%")
                  ->orWhere('nomor_smu', 'like', "%{$search}%")
                  ->orWhere('nomor_nota', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%")
                  ->orWhereHas('invoices.customer', function ($cq) use ($search) {
                      $cq->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_customer', 'like', "%{$search}%");
                  });
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($bank) {
            $query->where('bank_pengirim', $bank);
        }

        $payments = $query->latest('tanggal')->latest('id')->paginate(15)->withQueryString();

        // Calculate KPI summaries
        $allIasPayments = IasPayment::all();
        $summary = [
            'total_nominal' => $allIasPayments->sum('nominal'),
            'total_cashback' => $allIasPayments->sum('cashback'),
            'total_transaksi' => $allIasPayments->count(),
            'bulan_ini_nominal' => $allIasPayments->where('tanggal', '>=', now()->startOfMonth()->toDateString())->sum('nominal'),
            'bulan_ini_cashback' => $allIasPayments->where('tanggal', '>=', now()->startOfMonth()->toDateString())->sum('cashback'),
            'total_berhasil' => $allIasPayments->where('status', 'berhasil')->count(),
        ];

        return view('ias_payments.index', compact(
            'payments',
            'summary',
            'search',
            'startDate',
            'endDate',
            'status',
            'bank'
        ));
    }

    public function create(Request $request)
    {
        $today = now()->toDateString();
        $autoNomor = 'IAS-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        
        $rawInvoiceIds = $request->query('invoice_ids', []);
        if (!is_array($rawInvoiceIds)) {
            $rawInvoiceIds = explode(',', $rawInvoiceIds);
        }
        if ($request->query('invoice_id')) {
            $rawInvoiceIds[] = $request->query('invoice_id');
        }
        $selectedInvoiceIds = array_values(array_filter(array_map('intval', $rawInvoiceIds)));
        $selectedSmu = $request->query('nomor_smu');

        $invoices = Invoice::with(['customer', 'worksheetItems.worksheet'])
            ->latest('tanggal')
            ->latest('id')
            ->get();

        $worksheets = collect();

        return view('ias_payments.create', compact(
            'today',
            'autoNomor',
            'invoices',
            'worksheets',
            'selectedInvoiceIds',
            'selectedSmu'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_transaksi_ias' => 'required|string|max:80|unique:ias_payments,nomor_transaksi_ias',
            'tanggal' => 'required|date',
            'invoice_ids' => 'nullable|array',
            'invoice_ids.*' => 'exists:invoices,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'nomor_nota' => 'nullable|string',
            'nomor_smu' => 'nullable|string',
            'worksheet_id' => 'nullable|exists:worksheets,id',
            'nominal' => 'required|numeric|min:1',
            'cashback' => 'nullable|numeric|min:0',
            'metode_bayar' => 'required|string|max:40',
            'bank_pengirim' => 'nullable|string|max:50',
            'nomor_rekening_tujuan' => 'nullable|string|max:80',
            'status' => 'required|in:berhasil,pending,gagal',
            'catatan' => 'nullable|string',
        ]);

        $validated['cashback'] = $validated['cashback'] ?? 0;

        $invoiceIds = $validated['invoice_ids'] ?? [];
        if (!empty($validated['invoice_id']) && !in_array($validated['invoice_id'], $invoiceIds)) {
            $invoiceIds[] = $validated['invoice_id'];
        }

        $invoices = collect();
        if (!empty($invoiceIds)) {
            $invoices = Invoice::with(['customer', 'worksheetItems.worksheet'])->whereIn('id', $invoiceIds)->get();
            if (empty($validated['nomor_nota'])) {
                $validated['nomor_nota'] = $invoices->pluck('nomor_nota')->implode(', ');
            }
            if (empty($validated['nomor_smu'])) {
                $allSmus = $invoices->flatMap(function ($inv) {
                    return $inv->worksheetItems->map(fn($it) => $it->worksheet?->nomor_smu);
                })->filter()->unique()->values()->implode(', ');
                if (!empty($allSmus)) {
                    $validated['nomor_smu'] = $allSmus;
                }
            }
            $validated['invoice_id'] = $invoices->first()?->id;
        } elseif (!empty($validated['nomor_nota'])) {
            $notas = array_filter(array_map('trim', explode(',', $validated['nomor_nota'])));
            $invoices = Invoice::with(['customer', 'worksheetItems.worksheet'])->whereIn('nomor_nota', $notas)->get();
            $validated['invoice_id'] = $invoices->first()?->id;
        }

        if (!empty($validated['worksheet_id'])) {
            $ws = Worksheet::find($validated['worksheet_id']);
            if ($ws && empty($validated['nomor_smu'])) {
                $validated['nomor_smu'] = $ws->nomor_smu;
            }
        } elseif (!empty($validated['nomor_smu'])) {
            $firstSmu = trim(explode(',', $validated['nomor_smu'])[0]);
            $ws = Worksheet::where('nomor_smu', $firstSmu)->first();
            if ($ws) {
                $validated['worksheet_id'] = $ws->id;
            }
        }

        unset($validated['invoice_ids']);
        $validated['user_id'] = Auth::id();

        $iasPayment = IasPayment::create($validated);

        if ($invoices->isNotEmpty()) {
            foreach ($invoices as $inv) {
                $iasPayment->invoices()->syncWithoutDetaching([
                    $inv->id => [
                        'nomor_nota' => $inv->nomor_nota,
                        'nominal' => $inv->total_nota,
                    ]
                ]);
            }
        }

        return redirect()->route('ias.payments.index')
            ->with('success', "Pembayaran ke IAS [{$validated['nomor_transaksi_ias']}] berhasil dicatat.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_import' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file_import');
        $filename = $file->getClientOriginalName();
        $path = $file->getRealPath();

        $rows = array_map(function ($line) {
            // Support both comma and semicolon delimiters
            $delimiter = strpos($line, ';') !== false ? ';' : ',';
            return str_getcsv($line, $delimiter);
        }, file($path));

        if (count($rows) < 2) {
            return redirect()->back()->with('error', 'File CSV kosong atau tidak memiliki baris data.');
        }

        $header = array_map('trim', array_map('strtolower', $rows[0]));
        $importedCount = 0;
        $failedCount = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                continue;
            }

            // Combine with header
            $data = [];
            foreach ($header as $colIdx => $colName) {
                $data[$colName] = isset($row[$colIdx]) ? trim($row[$colIdx]) : null;
            }

            $nomorTransaksi = $data['nomor_transaksi_ias'] ?? $data['no_transaksi'] ?? $data['kode_transaksi'] ?? ('IAS-' . date('Ymd') . '-' . strtoupper(Str::random(5)));
            $tanggal = $data['tanggal'] ?? now()->toDateString();
            $nomorNota = $data['nomor_nota'] ?? $data['no_nota'] ?? $data['nota'] ?? null;
            $nomorSmu = $data['nomor_smu'] ?? $data['smu'] ?? null;
            $nominalRaw = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $data['nominal'] ?? $data['jumlah'] ?? '0'));
            $nominal = (float) $nominalRaw;
            $cashbackRaw = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $data['cashback'] ?? $data['cash_back'] ?? '0'));
            $cashback = (float) $cashbackRaw;

            if ($nominal <= 0) {
                $failedCount++;
                continue;
            }

            $invoices = collect();
            $firstInvoiceId = null;
            if ($nomorNota) {
                $notasList = array_filter(array_map('trim', explode(',', $nomorNota)));
                $invoices = Invoice::whereIn('nomor_nota', $notasList)->get();
                $firstInvoiceId = $invoices->first()?->id;
            }

            $worksheetId = null;
            if ($nomorSmu) {
                $firstSmu = trim(explode(',', $nomorSmu)[0]);
                $ws = Worksheet::where('nomor_smu', $firstSmu)->first();
                if ($ws) {
                    $worksheetId = $ws->id;
                }
            }

            $metode = $data['metode_bayar'] ?? $data['metode'] ?? 'transfer';
            $bank = $data['bank_pengirim'] ?? $data['bank'] ?? 'BCA';
            $catatan = $data['catatan'] ?? $data['keterangan'] ?? 'Import pembayaran ke IAS';

            // Check if already exists, update or insert
            $iasPayment = IasPayment::updateOrCreate(
                ['nomor_transaksi_ias' => $nomorTransaksi],
                [
                    'tanggal' => $tanggal,
                    'nomor_nota' => $nomorNota,
                    'invoice_id' => $firstInvoiceId,
                    'nomor_smu' => $nomorSmu,
                    'worksheet_id' => $worksheetId,
                    'nominal' => $nominal,
                    'cashback' => $cashback,
                    'metode_bayar' => $metode,
                    'bank_pengirim' => $bank,
                    'status' => 'berhasil',
                    'catatan' => $catatan,
                    'file_referensi' => $filename,
                    'user_id' => Auth::id(),
                ]
            );

            if ($invoices->isNotEmpty()) {
                foreach ($invoices as $inv) {
                    $iasPayment->invoices()->syncWithoutDetaching([
                        $inv->id => [
                            'nomor_nota' => $inv->nomor_nota,
                            'nominal' => $inv->total_nota,
                        ]
                    ]);
                }
            }

            $importedCount++;
        }

        return redirect()->route('ias.payments.index')
            ->with('success', "Berhasil mengimpor {$importedCount} transaksi pembayaran ke IAS dari file {$filename}." . ($failedCount > 0 ? " ({$failedCount} baris diabaikan karena nominal tidak valid)" : ""));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_pembayaran_ias.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'tanggal',
                'nomor_nota',
                'nomor_smu',
                'nomor_transaksi_ias',
                'nominal',
                'cashback',
                'metode_bayar',
                'bank_pengirim',
                'catatan',
            ]);

            // Sample rows
            fputcsv($handle, [
                date('Y-m-d'),
                'INV/202610/0001',
                '126-98127361',
                'IAS-202610-0001',
                '2500000',
                '300000',
                'transfer',
                'BCA',
                'Pembayaran Terminal Handling SMU CGK-SUB (Cashback Rp 200/kg)',
            ]);
            fputcsv($handle, [
                date('Y-m-d'),
                'INV/202610/0002',
                '126-98127362',
                'IAS-202610-0002',
                '1850000',
                '0',
                'transfer',
                'Mandiri',
                'Pergudangan & Regulated Agent IAS',
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(IasPayment $iasPayment)
    {
        $nomor = $iasPayment->nomor_transaksi_ias;
        $iasPayment->delete();

        return redirect()->route('ias.payments.index')
            ->with('success', "Catatan pembayaran IAS {$nomor} berhasil dihapus.");
    }
}
