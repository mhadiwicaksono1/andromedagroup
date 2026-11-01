<?php

namespace App\Http\Controllers;

use App\Models\IasPayment;
use App\Models\Invoice;
use App\Models\Worksheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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
                  ->orWhere('no_kuitansi', 'like', "%{$search}%")
                  ->orWhere('no_btb', 'like', "%{$search}%")
                  ->orWhere('nomor_smu', 'like', "%{$search}%")
                  ->orWhere('airline', 'like', "%{$search}%")
                  ->orWhere('shipper', 'like', "%{$search}%")
                  ->orWhere('consignee', 'like', "%{$search}%")
                  ->orWhere('komoditi', 'like', "%{$search}%")
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
        
        $selectedSmu = $request->query('nomor_smu');
        $selectedWorksheetId = $request->query('worksheet_id');

        $worksheets = Worksheet::with(['items.customer', 'items.invoice'])
            ->latest('tanggal')
            ->latest('id')
            ->get();

        return view('ias_payments.create', compact(
            'today',
            'autoNomor',
            'worksheets',
            'selectedSmu',
            'selectedWorksheetId'
        ));
    }

    public function store(Request $request)
    {
        if (!$request->filled('nomor_smu') && $request->filled('invoice_ids')) {
            $firstInv = Invoice::with('worksheetItems.worksheet')->find($request->invoice_ids[0]);
            $smu = $firstInv?->worksheetItems->first()?->worksheet?->nomor_smu ?? 'SMU-AUTO';
            $request->merge(['nomor_smu' => $smu]);
        }

        $validated = $request->validate([
            'nomor_transaksi_ias' => 'required|string|max:80|unique:ias_payments,nomor_transaksi_ias',
            'tanggal' => 'required|date',
            'worksheet_id' => 'nullable|exists:worksheets,id',
            'nomor_smu' => 'required|string|max:80',
            'nomor_nota' => 'nullable|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'cashback' => 'nullable|numeric|min:0',
            'metode_bayar' => 'required|string|max:40',
            'bank_pengirim' => 'nullable|string|max:50',
            'nomor_rekening_tujuan' => 'nullable|string|max:80',
            'status' => 'required|in:berhasil,pending,gagal',
            'catatan' => 'nullable|string',
            'invoice_ids' => 'nullable|array',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $validated['cashback'] = $validated['cashback'] ?? 0;
        if ($request->filled('nomor_nota')) {
            $validated['nomor_nota'] = $request->nomor_nota;
        }

        $ws = null;
        if (!empty($validated['worksheet_id'])) {
            $ws = Worksheet::with('items.invoice')->find($validated['worksheet_id']);
        } elseif (!empty($validated['nomor_smu'])) {
            $firstSmu = trim(explode(',', $validated['nomor_smu'])[0]);
            $ws = Worksheet::with('items.invoice')->where('nomor_smu', $firstSmu)->first();
            if ($ws) {
                $validated['worksheet_id'] = $ws->id;
            }
        }

        if ($ws) {
            $validated['koli'] = $ws->display_koli_smu;
            $validated['berat_kg'] = $ws->display_berat_smu;
        }

        $invoices = collect();
        if ($request->filled('invoice_ids')) {
            $invoices = Invoice::whereIn('id', $request->invoice_ids)->get();
            if ($invoices->isNotEmpty()) {
                $validated['nomor_nota'] = $invoices->pluck('nomor_nota')->implode(', ');
                $validated['invoice_id'] = $invoices->first()?->id;
            }
        } elseif ($ws) {
            $invoices = $ws->items->map(fn($it) => $it->invoice)->filter()->unique('id')->values();
            if ($invoices->isNotEmpty() && empty($validated['nomor_nota'])) {
                $validated['nomor_nota'] = $invoices->pluck('nomor_nota')->implode(', ');
                $validated['invoice_id'] = $invoices->first()?->id;
            }
        }

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
            'file_import' => 'required|file|max:10240',
        ]);

        $file = $request->file('file_import');
        $filename = $file->getClientOriginalName();
        $path = $file->getRealPath();
        $ext = strtolower($file->getClientOriginalExtension());

        $rows = [];
        if (in_array($ext, ['xlsx', 'xls']) && class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, false);
        } else {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $rows = array_map(function ($line) {
                $delimiter = strpos($line, ';') !== false ? ';' : ',';
                return str_getcsv($line, $delimiter);
            }, $lines);
        }

        if (count($rows) < 2) {
            return redirect()->back()->with('error', 'File kosong atau tidak memiliki baris data.');
        }

        // Dynamically find header row in the first 15 rows
        $headerRowIndex = 0;
        for ($r = 0; $r < min(15, count($rows)); $r++) {
            $normalizedRow = array_map(function ($col) {
                return trim(strtolower(str_replace([' ', '-', '.', '/', '(', ')'], '_', (string) $col)));
            }, $rows[$r]);

            $hasAwb = in_array('awb', $normalizedRow) || in_array('nomor_smu', $normalizedRow) || in_array('awb_nomor_smu', $normalizedRow) || in_array('no_smu', $normalizedRow) || in_array('smu', $normalizedRow);
            $hasKuitansi = in_array('nomor_kuitansi', $normalizedRow) || in_array('no_kuitansi', $normalizedRow) || in_array('kuitansi', $normalizedRow);
            $hasTotalOrHandling = in_array('total', $normalizedRow) || in_array('handling', $normalizedRow) || in_array('nominal', $normalizedRow);

            if (($hasAwb && ($hasKuitansi || $hasTotalOrHandling)) || ($hasKuitansi && $hasTotalOrHandling)) {
                $headerRowIndex = $r;
                break;
            }
        }

        $rawHeader = $rows[$headerRowIndex];
        $headerMap = [];
        foreach ($rawHeader as $idx => $colName) {
            $normalized = trim(strtolower(str_replace([' ', '-', '.', '/', '(', ')'], '_', (string) $colName)));
            $normalized = preg_replace('/_+/', '_', $normalized);
            $normalized = trim($normalized, '_');
            $headerMap[$idx] = $normalized;
        }

        $importedCount = 0;
        $failedCount = 0;
        $skippedSubtotalCount = 0;

        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                continue;
            }

            $data = [];
            foreach ($headerMap as $colIdx => $colKey) {
                $data[$colKey] = isset($row[$colIdx]) ? trim((string) $row[$colIdx]) : null;
            }

            // Identify subtotal / summary row (e.g. green row "Total Kuitansi BTHPID2610000092")
            $firstFewCols = implode(' ', array_slice(array_filter(array_map('strval', $row)), 0, 4));
            $isSubtotalRow = false;
            if (preg_match('/total\s+kuitansi|total\s+transaksi|total\s+bayar|subtotal/i', $firstFewCols)) {
                $isSubtotalRow = true;
            }

            $noKuitansiRaw = $data['nomor_kuitansi'] ?? $data['no_kuitansi'] ?? $data['kuitansi'] ?? $data['nomor_transaksi_ias'] ?? $data['no_transaksi'] ?? null;
            if ($noKuitansiRaw && preg_match('/^total/i', trim($noKuitansiRaw))) {
                $isSubtotalRow = true;
            }

            if ($isSubtotalRow) {
                $skippedSubtotalCount++;
                continue;
            }

            $nomorSmu = $data['awb'] ?? $data['awb_nomor_smu'] ?? $data['no_awb'] ?? $data['nomor_smu'] ?? $data['no_smu'] ?? $data['smu'] ?? null;

            // If both AWB and kuitansi are empty or empty row
            if (empty($nomorSmu) && empty($noKuitansiRaw)) {
                continue;
            }

            $tanggalRaw = $data['tanggal'] ?? $data['tgl'] ?? $data['date'] ?? null;
            $tanggal = $this->parseExcelDate($tanggalRaw);

            $noBtb = $data['no_btb_ttb'] ?? $data['no_btb'] ?? $data['btb_ttb'] ?? $data['btb'] ?? $data['ttb'] ?? null;
            $airline = $data['airline'] ?? $data['maskapai'] ?? null;
            $shipper = $data['shipper'] ?? $data['sender'] ?? null;
            $consignee = $data['consignee'] ?? $data['receiver'] ?? null;
            $ori = $data['ori'] ?? $data['origin'] ?? null;
            $dst = $data['dst'] ?? $data['dest'] ?? $data['destination'] ?? null;
            $komoditi = $data['komoditi'] ?? $data['commodity'] ?? $data['goods'] ?? $data['barang'] ?? null;

            $koli = (int) ($this->parseIndonesianNumber($data['qty'] ?? $data['koli'] ?? $data['pieces'] ?? 0));
            $chw = $this->parseIndonesianNumber($data['chw'] ?? $data['chargeable_weight'] ?? $data['berat_kg'] ?? $data['berat'] ?? null);
            $gw = $this->parseIndonesianNumber($data['gw'] ?? $data['gross_weight'] ?? null);
            $beratKg = $chw > 0 ? $chw : ($gw > 0 ? $gw : 0.0);

            $handling = $this->parseIndonesianNumber($data['handling'] ?? 0);
            $admFee = $this->parseIndonesianNumber($data['adm_fee'] ?? $data['admin_fee'] ?? $data['biaya_admin'] ?? 0);
            $pndp = $this->parseIndonesianNumber($data['pndp'] ?? 0);

            $total = $this->parseIndonesianNumber($data['total'] ?? $data['nominal'] ?? $data['jumlah'] ?? $data['subtotal'] ?? 0);
            if ($total <= 0 && $handling > 0) {
                $total = $handling + $admFee + $pndp;
            }

            if ($total <= 0) {
                $failedCount++;
                continue;
            }

            // Cashback calculation: default Rp 200/kg standard IAS cashback if not provided
            $explicitCashback = isset($data['cashback']) || isset($data['cash_back']);
            if ($explicitCashback) {
                $cashback = $this->parseIndonesianNumber($data['cashback'] ?? $data['cash_back'] ?? 0);
            } else {
                $cashback = $beratKg > 0 ? round($beratKg * 200, 2) : 0.0;
            }

            // Clean nomor kuitansi and ensure a unique nomor_transaksi_ias (handles 2x pengiriman 1 SMU)
            $noKuitansi = $noKuitansiRaw ? trim($noKuitansiRaw) : null;
            $baseNomorTransaksi = $noKuitansi ?: ($nomorSmu ? 'IAS-' . str_replace(['-', ' '], '', $nomorSmu) : 'IAS-' . date('Ymd') . '-' . strtoupper(Str::random(4)));

            $nomorTransaksi = $baseNomorTransaksi;
            $counter = 1;
            while (IasPayment::where('nomor_transaksi_ias', $nomorTransaksi)->exists()) {
                $counter++;
                $nomorTransaksi = $baseNomorTransaksi . '-' . $counter;
            }

            // Link Worksheet by AWB / SMU
            $worksheetId = null;
            $matchedWs = null;
            if ($nomorSmu) {
                $cleanSmu = str_replace(['-', ' '], '', $nomorSmu);
                $matchedWs = Worksheet::with('items.invoice')->where('nomor_smu', $nomorSmu)
                    ->orWhere('nomor_smu', $cleanSmu)
                    ->orWhere('nomor_smu', 'like', "%{$cleanSmu}%")
                    ->first();
                if ($matchedWs) {
                    $worksheetId = $matchedWs->id;
                }
            }

            // Link Invoices
            $nomorNota = $data['nomor_nota'] ?? $data['no_nota'] ?? $data['nota'] ?? $data['invoice'] ?? null;
            $invoices = collect();
            $firstInvoiceId = null;

            if ($nomorNota) {
                $notasList = array_filter(array_map('trim', explode(',', $nomorNota)));
                $invoices = Invoice::whereIn('nomor_nota', $notasList)->get();
                $firstInvoiceId = $invoices->first()?->id;
            } elseif ($matchedWs) {
                $invoices = $matchedWs->items->map(fn($it) => $it->invoice)->filter()->unique('id')->values();
                if ($invoices->isNotEmpty()) {
                    $nomorNota = $invoices->pluck('nomor_nota')->implode(', ');
                    $firstInvoiceId = $invoices->first()?->id;
                }
            }

            // Catatan deskriptif
            $customCatatan = $data['catatan'] ?? $data['keterangan'] ?? null;
            if (!$customCatatan) {
                $parts = [];
                if ($airline) $parts[] = $airline;
                if ($nomorSmu) $parts[] = "AWB: {$nomorSmu}";
                if ($ori && $dst) $parts[] = "{$ori} -> {$dst}";
                if ($komoditi) $parts[] = "Komoditi: {$komoditi}";
                if ($koli > 0 || $beratKg > 0) $parts[] = "({$koli} koli / {$beratKg} kg)";
                $customCatatan = !empty($parts) ? implode(' | ', $parts) : "Import pembayaran IAS {$noKuitansi}";
            }

            $metode = $data['metode_bayar'] ?? $data['metode'] ?? 'transfer';
            $bank = $data['bank_pengirim'] ?? $data['bank'] ?? 'BCA';

            $iasPayment = IasPayment::create([
                'nomor_transaksi_ias' => $nomorTransaksi,
                'no_kuitansi' => $noKuitansi,
                'no_btb' => $noBtb,
                'nomor_smu' => $nomorSmu,
                'airline' => $airline,
                'tanggal' => $tanggal,
                'shipper' => $shipper,
                'consignee' => $consignee,
                'ori' => $ori,
                'dst' => $dst,
                'komoditi' => $komoditi,
                'koli' => $koli,
                'berat_kg' => $beratKg,
                'handling' => $handling,
                'adm_fee' => $admFee,
                'pndp' => $pndp,
                'nominal' => $total,
                'cashback' => $cashback,
                'metode_bayar' => $metode,
                'bank_pengirim' => $bank,
                'status' => 'berhasil',
                'catatan' => $customCatatan,
                'file_referensi' => $filename,
                'user_id' => Auth::id(),
                'worksheet_id' => $worksheetId,
                'nomor_nota' => $nomorNota,
                'invoice_id' => $firstInvoiceId,
            ]);

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

        $msg = "Berhasil mengimpor {$importedCount} transaksi pembayaran ke IAS dari file {$filename}.";
        if ($skippedSubtotalCount > 0) {
            $msg .= " ({$skippedSubtotalCount} baris total kuitansi dilewati).";
        }
        if ($failedCount > 0) {
            $msg .= " ({$failedCount} baris diabaikan karena nominal tidak valid).";
        }

        return redirect()->route('ias.payments.index')->with('success', $msg);
    }

    protected function parseExcelDate($value): string
    {
        if (empty($value)) {
            return now()->toDateString();
        }

        if (is_numeric($value) && (float)$value > 20000 && class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        $valStr = trim((string)$value);

        try {
            $carbon = \Carbon\Carbon::parse($valStr);
            if ($carbon && $carbon->year > 2000) {
                return $carbon->format('Y-m-d');
            }
        } catch (\Throwable $e) {}

        $ts = strtotime($valStr);
        if ($ts && date('Y', $ts) > 2000) {
            return date('Y-m-d', $ts);
        }

        return now()->toDateString();
    }

    protected function parseIndonesianNumber($val): float
    {
        if ($val === null || $val === '') {
            return 0.0;
        }
        if (is_numeric($val)) {
            return (float)$val;
        }

        $clean = trim((string)$val);
        if (strpos($clean, ',') !== false) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $clean)) {
            $clean = str_replace('.', '', $clean);
        }

        $clean = preg_replace('/[^0-9.-]/', '', $clean);
        return (float)$clean;
    }

    public function downloadTemplate(Request $request)
    {
        $format = strtolower((string) $request->query('format', 'xlsx'));

        if ($format === 'csv') {
            return $this->downloadCsvTemplate();
        }

        if (class_exists(Spreadsheet::class)) {
            return $this->downloadExcelTemplate();
        }

        return $this->downloadCsvTemplate();
    }

    protected function downloadExcelTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Pembayaran IAS');

        $headers = [
            'No',
            'Nomor Kuitansi',
            'No. BTB/TTB',
            'AWB',
            'Airline',
            'Tanggal',
            'Customer',
            'Sender',
            'Receiver',
            'Shipper',
            'Consignee',
            'ORI',
            'DST',
            'Komoditi',
            'Goods',
            'Qty',
            'GW',
            'CHW',
            'Handling',
            'Timbun',
            'SGMA',
            'Print SMU',
            'Biaya Lainnya',
            'Adm Fee',
            'Subtotal',
            'PPn',
            'PNDP',
            'Materai',
            'Total',
            'nomor_nota',
        ];

        $rows = [
            [
                1,
                'BTHPID2610000092',
                'BTHCID26090016857',
                '126-05166770',
                '(GA) Garuda Indonesia',
                '2026-10-01',
                'PT. JASAMITRA EKSPRES',
                '',
                '',
                '-',
                'JDE/INDRA',
                'CGK',
                'BTH',
                'General',
                'GENCO',
                1,
                14,
                14,
                21000,
                0,
                0,
                0,
                7000,
                3000,
                31000,
                0,
                840,
                0,
                31840,
                '',
            ],
            [
                '',
                'Total Kuitansi BTHPID2610000092',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                1,
                14,
                14,
                21000,
                0,
                0,
                0,
                7000,
                3000,
                31000,
                0,
                840,
                0,
                31840,
                '',
            ],
            [
                2,
                'BTHPID2610000248',
                'BTHCID26100000201',
                '273-06943882',
                '(RI) Rimbun Air',
                '2026-10-01',
                'PT. JASAMITRA EKSPRES',
                '',
                '',
                'LOGITRANS / BRK',
                'DIPO - BTH',
                'CGK',
                'BTH',
                'Strongly Smelling Goods',
                'DURIAN',
                1,
                28,
                28,
                42000,
                0,
                0,
                0,
                14000,
                3000,
                59000,
                0,
                1680,
                0,
                60680,
                '',
            ],
            [
                '',
                'Total Kuitansi BTHPID2610000248',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                1,
                28,
                28,
                42000,
                0,
                0,
                0,
                14000,
                3000,
                59000,
                0,
                1680,
                0,
                60680,
                '',
            ],
            [
                3,
                'BTHPID2610000412',
                'BTHCID26100000370',
                '920-02197322',
                '(IU) Super Air Jet',
                '2026-10-01',
                'PT. JASAMITRA EKSPRES',
                '',
                '',
                '-',
                'ANDROMEDA GROUP',
                'YIA',
                'BTH',
                'Perishable',
                'CABAI',
                14,
                382,
                382,
                573000,
                0,
                0,
                0,
                191000,
                3000,
                767000,
                0,
                22920,
                0,
                789920,
                '',
            ],
            [
                '',
                'Total Kuitansi BTHPID2610000412',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                14,
                382,
                382,
                573000,
                0,
                0,
                0,
                191000,
                3000,
                767000,
                0,
                22920,
                0,
                789920,
                '',
            ],
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $highestCol = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        // Header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '366092']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '244061']]],
        ];
        $sheet->getStyle("A1:{$highestCol}1")->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Subtotal row style
        $subtotalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A9D08E']],
        ];

        for ($r = 2; $r <= $highestRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(20);
            $val = (string) $sheet->getCell("B{$r}")->getValue();
            if (str_starts_with(strtolower($val), 'total kuitansi')) {
                $sheet->getStyle("A{$r}:{$highestCol}{$r}")->applyFromArray($subtotalStyle);
            }
        }

        // Borders and vertical alignment for data
        $sheet->getStyle("A2:{$highestCol}{$highestRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Number format
        $sheet->getStyle("P2:R{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("S2:AC{$highestRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Auto size columns
        foreach (range('A', $highestCol) as $colId) {
            $sheet->getColumnDimension($colId)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template_import_pembayaran_ias.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected function downloadCsvTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_pembayaran_ias.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Nomor Kuitansi',
                'No. BTB/TTB',
                'AWB (nomor_smu)',
                'Airline',
                'Tanggal',
                'nomor_nota',
                'Customer',
                'Sender',
                'Receiver',
                'Shipper',
                'Consignee',
                'ORI',
                'DST',
                'Komoditi',
                'Goods',
                'Qty',
                'GW',
                'CHW',
                'Handling',
                'Timbun',
                'SGMA',
                'Print SMU',
                'Biaya Lainnya',
                'Adm Fee',
                'Subtotal',
                'PPn',
                'PNDP',
                'Materai',
                'Total',
            ]);

            // Sample rows matching user's image exactly
            fputcsv($handle, [
                '1',
                'BTHPID2610000092',
                'BTHCID26090016857',
                '126-05166770',
                '(GA) Garuda Indonesia',
                '01-Oct-26',
                '',
                'PT. JASAMITRA EKSPRES',
                '',
                '',
                '-',
                'JDE/INDRA',
                'CGK',
                'BTH',
                'General',
                'GENCO',
                '1',
                '14',
                '14',
                '21.000,00',
                '0',
                '0',
                '0',
                '7.000,00',
                '3.000,00',
                '31.000,00',
                '0',
                '840',
                '0',
                '31.840,00',
            ]);

            fputcsv($handle, [
                '',
                'Total Kuitansi BTHPID2610000092',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '1',
                '14',
                '14',
                '21.000,00',
                '0',
                '0',
                '0',
                '7.000,00',
                '3.000,00',
                '31.000,00',
                '0',
                '840',
                '0',
                '31.840,00',
            ]);

            fputcsv($handle, [
                '2',
                'BTHPID2610000248',
                'BTHCID26100000201',
                '273-06943882',
                '(RI) Rimbun Air',
                '01-Oct-26',
                '',
                'PT. JASAMITRA EKSPRES',
                '',
                '',
                'LOGITRANS / BRK',
                'DIPO - BTH',
                'CGK',
                'BTH',
                'Strongly Smelling Goods',
                'DURIAN',
                '1',
                '28',
                '28',
                '42.000,00',
                '0',
                '0',
                '0',
                '14.000,00',
                '3.000,00',
                '59.000,00',
                '0',
                '1.680,00',
                '0',
                '60.680,00',
            ]);

            fputcsv($handle, [
                '3',
                'BTHPID2610000412',
                'BTHCID26100000370',
                '920-02197322',
                '(IU) Super Air Jet',
                '01-Oct-26',
                '',
                'PT. JASAMITRA EKSPRES',
                '',
                '',
                '-',
                'ANDROMEDA GROUP',
                'YIA',
                'BTH',
                'Perishable',
                'CABAI',
                '14',
                '382',
                '382',
                '573.000,00',
                '0',
                '0',
                '0',
                '191.000,00',
                '3.000,00',
                '767.000,00',
                '0',
                '22.920,00',
                '0',
                '789.920,00',
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
