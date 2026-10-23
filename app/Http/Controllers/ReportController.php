<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\IasPayment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Worksheet;
use App\Models\WorksheetItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function worksheets(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $customerId = $request->query('customer_id');
        $status = $request->query('status');

        $query = WorksheetItem::with(['worksheet', 'customer'])
            ->whereHas('worksheet', function ($w) use ($startDate, $endDate) {
                $w->whereBetween('tanggal', [$startDate, $endDate]);
            });

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $items = $query->latest('id')->get();

        $summary = [
            'total_items' => $items->count(),
            'total_koli' => $items->sum('jumlah_koli'),
            'total_berat' => $items->sum('berat_kg'),
            'total_biaya' => $items->sum('total_biaya'),
        ];

        $customers = Customer::orderBy('nama')->get();

        return view('reports.worksheets', compact('items', 'summary', 'startDate', 'endDate', 'customerId', 'status', 'customers'));
    }

    public function invoices(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $customerId = $request->query('customer_id');
        $status = $request->query('status');

        $query = Invoice::with('customer')
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status_pembayaran', $status);
        }

        $invoices = $query->orderBy('tanggal', 'desc')->get();

        $summary = [
            'total_nota_count' => $invoices->count(),
            'total_omzet' => $invoices->sum('total_nota'),
            'total_dibayar' => $invoices->sum('jumlah_dibayar'),
            'total_sisa_piutang' => $invoices->sum('sisa_piutang'),
        ];

        $customers = Customer::orderBy('nama')->get();

        return view('reports.invoices', compact('invoices', 'summary', 'startDate', 'endDate', 'customerId', 'status', 'customers'));
    }

    public function receivables(Request $request)
    {
        $customerId = $request->query('customer_id');

        $query = Customer::whereHas('invoices', function ($q) {
            $q->where('status_pembayaran', '!=', 'lunas');
        })->with(['invoices' => function ($q) {
            $q->where('status_pembayaran', '!=', 'lunas')->orderBy('tanggal', 'asc');
        }]);

        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->get();
        $allCustomers = Customer::orderBy('nama')->get();

        $grandTotalPiutang = $customers->sum(function ($c) {
            return $c->invoices->sum('sisa_piutang');
        });

        return view('reports.receivables', compact('customers', 'allCustomers', 'customerId', 'grandTotalPiutang'));
    }

    /**
     * Laporan Laba Rugi Operasional Kargo
     * Menghitung Pendapatan Penjualan Jasa Kargo dikurangi Beban Pokok Maskapai/IAS per SMU
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $customerId = $request->query('customer_id');
        $modalRatePerKg = (float) $request->query('modal_rate', 1500); // Default modal maskapai & IAS per kg

        $query = Worksheet::with(['items.customer'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('status', '!=', 'draft');

        if ($customerId) {
            $query->whereHas('items', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            });
        }

        $worksheets = $query->orderBy('tanggal', 'desc')->get();

        // Calculate P&L metrics per worksheet
        $pnlRows = $worksheets->map(function ($ws) use ($modalRatePerKg, $customerId) {
            $items = $customerId ? $ws->items->where('customer_id', $customerId) : $ws->items;
            $beratKg = (float) $items->sum('berat_kg');
            $pendapatan = (float) $items->sum('total_biaya');
            $bebanPokok = $beratKg * $modalRatePerKg;
            $labaKotor = $pendapatan - $bebanPokok;
            $marginPersen = $pendapatan > 0 ? ($labaKotor / $pendapatan) * 100 : 0;

            return [
                'worksheet' => $ws,
                'items_count' => $items->count(),
                'total_koli' => (int) $items->sum('jumlah_koli'),
                'total_berat_kg' => $beratKg,
                'pendapatan' => $pendapatan,
                'beban_pokok' => $bebanPokok,
                'laba_kotor' => $labaKotor,
                'margin_persen' => $marginPersen,
                'customers' => $items->pluck('customer.nama')->filter()->unique()->values()->all(),
            ];
        });

        $totalPendapatan = $pnlRows->sum('pendapatan');
        $totalBeban = $pnlRows->sum('beban_pokok');
        $totalLabaKotor = $totalPendapatan - $totalBeban;
        $totalBerat = $pnlRows->sum('total_berat_kg');
        $totalKoli = $pnlRows->sum('total_koli');
        $overallMargin = $totalPendapatan > 0 ? ($totalLabaKotor / $totalPendapatan) * 100 : 0;

        $summary = [
            'total_pendapatan' => $totalPendapatan,
            'total_beban' => $totalBeban,
            'total_laba_kotor' => $totalLabaKotor,
            'overall_margin' => $overallMargin,
            'total_berat_kg' => $totalBerat,
            'total_koli' => $totalKoli,
            'total_smu' => $pnlRows->count(),
        ];

        $customers = Customer::orderBy('nama')->get();

        return view('reports.profit_loss', compact(
            'pnlRows',
            'summary',
            'startDate',
            'endDate',
            'customerId',
            'modalRatePerKg',
            'customers'
        ));
    }

    public function printWorksheets(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $customerId = $request->query('customer_id');
        $status = $request->query('status');

        $query = WorksheetItem::with(['worksheet', 'customer'])
            ->whereHas('worksheet', function ($w) use ($startDate, $endDate) {
                $w->whereBetween('tanggal', [$startDate, $endDate]);
            });

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $items = $query->latest('id')->get();

        $summary = [
            'total_items' => $items->count(),
            'total_koli' => $items->sum('jumlah_koli'),
            'total_berat' => $items->sum('berat_kg'),
            'total_biaya' => $items->sum('total_biaya'),
        ];

        $selectedCustomer = $customerId ? Customer::find($customerId) : null;

        return view('reports.print_worksheets', compact('items', 'summary', 'startDate', 'endDate', 'customerId', 'status', 'selectedCustomer'));
    }

    public function printInvoices(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $customerId = $request->query('customer_id');
        $status = $request->query('status');

        $query = Invoice::with('customer')
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        if ($status) {
            $query->where('status_pembayaran', $status);
        }

        $invoices = $query->orderBy('tanggal', 'desc')->get();

        $summary = [
            'total_nota_count' => $invoices->count(),
            'total_omzet' => $invoices->sum('total_nota'),
            'total_dibayar' => $invoices->sum('jumlah_dibayar'),
            'total_sisa_piutang' => $invoices->sum('sisa_piutang'),
        ];

        $selectedCustomer = $customerId ? Customer::find($customerId) : null;

        return view('reports.print_invoices', compact('invoices', 'summary', 'startDate', 'endDate', 'customerId', 'status', 'selectedCustomer'));
    }

    public function printReceivables(Request $request)
    {
        $customerId = $request->query('customer_id');

        $query = Customer::whereHas('invoices', function ($q) {
            $q->where('status_pembayaran', '!=', 'lunas');
        })->with(['invoices' => function ($q) {
            $q->where('status_pembayaran', '!=', 'lunas')->orderBy('tanggal', 'asc');
        }]);

        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->get();
        $selectedCustomer = $customerId ? Customer::find($customerId) : null;

        $grandTotalPiutang = $customers->sum(function ($c) {
            return $c->invoices->sum('sisa_piutang');
        });

        return view('reports.print_receivables', compact('customers', 'customerId', 'grandTotalPiutang', 'selectedCustomer'));
    }

    public function printProfitLoss(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $customerId = $request->query('customer_id');
        $modalRatePerKg = (float) $request->query('modal_rate', 1500);

        $query = Worksheet::with(['items.customer'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('status', '!=', 'draft');

        if ($customerId) {
            $query->whereHas('items', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            });
        }

        $worksheets = $query->orderBy('tanggal', 'desc')->get();

        $pnlRows = $worksheets->map(function ($ws) use ($modalRatePerKg, $customerId) {
            $items = $customerId ? $ws->items->where('customer_id', $customerId) : $ws->items;
            $beratKg = (float) $items->sum('berat_kg');
            $pendapatan = (float) $items->sum('total_biaya');
            $bebanPokok = $beratKg * $modalRatePerKg;
            $labaKotor = $pendapatan - $bebanPokok;
            $marginPersen = $pendapatan > 0 ? ($labaKotor / $pendapatan) * 100 : 0;

            return [
                'worksheet' => $ws,
                'items_count' => $items->count(),
                'total_koli' => (int) $items->sum('jumlah_koli'),
                'total_berat_kg' => $beratKg,
                'pendapatan' => $pendapatan,
                'beban_pokok' => $bebanPokok,
                'laba_kotor' => $labaKotor,
                'margin_persen' => $marginPersen,
                'customers' => $items->pluck('customer.nama')->filter()->unique()->values()->all(),
            ];
        });

        $totalPendapatan = $pnlRows->sum('pendapatan');
        $totalBeban = $pnlRows->sum('beban_pokok');
        $totalLabaKotor = $totalPendapatan - $totalBeban;
        $totalBerat = $pnlRows->sum('total_berat_kg');
        $totalKoli = $pnlRows->sum('total_koli');
        $overallMargin = $totalPendapatan > 0 ? ($totalLabaKotor / $totalPendapatan) * 100 : 0;

        $summary = [
            'total_pendapatan' => $totalPendapatan,
            'total_beban' => $totalBeban,
            'total_laba_kotor' => $totalLabaKotor,
            'overall_margin' => $overallMargin,
            'total_berat_kg' => $totalBerat,
            'total_koli' => $totalKoli,
            'total_smu' => $pnlRows->count(),
        ];

        $selectedCustomer = $customerId ? Customer::find($customerId) : null;

        return view('reports.print_profit_loss', compact(
            'pnlRows',
            'summary',
            'startDate',
            'endDate',
            'customerId',
            'modalRatePerKg',
            'selectedCustomer'
        ));
    }

    /**
     * Laporan Rekapitulasi Mutasi Kas, IAS & Profit Bersih Harian
     * Rumus: Profit = Total Nota Customer - (Pembayaran ke IAS - Cash Back)
     */
    public function dailyPerformance(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $cashbackRate = (float) $request->query('cashback_rate', 200); // Default Rp 200 per kg SMU

        $data = $this->calculateDailyPerformanceData($startDate, $endDate, $cashbackRate);

        return view('reports.daily_performance', [
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'cashbackRate' => $cashbackRate,
        ]);
    }

    /**
     * Cetak Laporan Rekapitulasi Mutasi Kas & Profit Harian (Tab Baru, Standalone)
     */
    public function printDailyPerformance(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $cashbackRate = (float) $request->query('cashback_rate', 200);

        $data = $this->calculateDailyPerformanceData($startDate, $endDate, $cashbackRate);

        return view('reports.print_daily_performance', [
            'rows' => $data['rows'],
            'summary' => $data['summary'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'cashbackRate' => $cashbackRate,
        ]);
    }

    /**
     * Export Rekapitulasi Kas & Profit Harian ke CSV / Excel
     */
    public function exportDailyPerformance(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $cashbackRate = (float) $request->query('cashback_rate', 200);

        $data = $this->calculateDailyPerformanceData($startDate, $endDate, $cashbackRate);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"rekap_kas_profit_harian_{$startDate}_{$endDate}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            // Header Row 1
            fputcsv($handle, ['TANGGAL', 'MUTASI - UANG MASUK', 'MUTASI - PEMBAYARAN KE IAS', 'CASH BACK', 'TOTAL NOTA KE CUSTOMER', 'PROFIT']);

            foreach ($data['rows'] as $r) {
                fputcsv($handle, [
                    $r['tanggal_formatted'],
                    $r['uang_masuk'],
                    $r['pembayaran_ias'],
                    $r['cashback'],
                    $r['total_nota'],
                    $r['profit'],
                ]);
            }

            // Summary Row
            fputcsv($handle, [
                'TOTAL',
                $data['summary']['total_uang_masuk'],
                $data['summary']['total_pembayaran_ias'],
                $data['summary']['total_cashback'],
                $data['summary']['total_nota'],
                $data['summary']['total_profit'],
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper kalkulasi data harian
     */
    private function calculateDailyPerformanceData(string $startDate, string $endDate, float $cashbackRate = 200): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        // Ambil semua transaksi pada rentang tanggal
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $payments = Payment::whereBetween('tanggal', [$startDateTime, $endDateTime])->get();
        $iasPayments = IasPayment::whereBetween('tanggal', [$startDateTime, $endDateTime])->get();
        $invoices = Invoice::whereBetween('tanggal', [$startDateTime, $endDateTime])->get();
        $worksheets = Worksheet::whereBetween('tanggal', [$startDateTime, $endDateTime])->get();

        // Kelompokkan per tanggal
        $paymentsGrouped = $payments->groupBy(fn($p) => Carbon::parse($p->tanggal)->toDateString());
        $iasPaymentsGrouped = $iasPayments->groupBy(fn($p) => Carbon::parse($p->tanggal)->toDateString());
        $invoicesGrouped = $invoices->groupBy(fn($p) => Carbon::parse($p->tanggal)->toDateString());
        $worksheetsGrouped = $worksheets->groupBy(fn($w) => Carbon::parse($w->tanggal)->toDateString());

        $rows = [];
        foreach ($period as $dateObj) {
            $dateStr = $dateObj->toDateString();

            $pList = $paymentsGrouped->get($dateStr, collect());
            $iasList = $iasPaymentsGrouped->get($dateStr, collect());
            $invList = $invoicesGrouped->get($dateStr, collect());
            $wsList = $worksheetsGrouped->get($dateStr, collect());

            $uangMasuk = (float) ($pList->sum('jumlah') ?: $pList->sum('nominal'));
            $pembayaranIas = (float) $iasList->sum('nominal');
            $totalNota = (float) $invList->sum('total_nota');
            $tonaseKg = (float) $wsList->sum('total_berat_kg');

            // Cashback: Ambil dari pembayaran IAS jika ada; jika 0, otomatis hitung dari tonase SMU hari tersebut x Rp 200
            $cashback = (float) $iasList->sum('cashback');
            if ($cashback <= 0 && $tonaseKg > 0) {
                $cashback = $tonaseKg * $cashbackRate;
            }

            // Rumus: Beban Bersih IAS = Pembayaran IAS - Cashback
            // Profit = Total Nota - Beban Bersih IAS
            $bebanBersihIas = max(0, $pembayaranIas - $cashback);
            $profit = $totalNota - $bebanBersihIas;

            $hasActivity = ($uangMasuk > 0 || $pembayaranIas > 0 || $cashback > 0 || $totalNota > 0 || $tonaseKg > 0);

            $rows[] = [
                'tanggal' => $dateObj,
                'tanggal_str' => $dateStr,
                'tanggal_formatted' => $dateObj->format('d-m-Y'),
                'uang_masuk' => $uangMasuk,
                'pembayaran_ias' => $pembayaranIas,
                'cashback' => $cashback,
                'tonase_kg' => $tonaseKg,
                'total_nota' => $totalNota,
                'beban_bersih_ias' => $bebanBersihIas,
                'profit' => $profit,
                'has_activity' => $hasActivity,
            ];
        }

        $totalUangMasuk = array_sum(array_column($rows, 'uang_masuk'));
        $totalPembayaranIas = array_sum(array_column($rows, 'pembayaran_ias'));
        $totalCashback = array_sum(array_column($rows, 'cashback'));
        $totalTonaseKg = array_sum(array_column($rows, 'tonase_kg'));
        $totalNota = array_sum(array_column($rows, 'total_nota'));
        $totalBebanBersihIas = array_sum(array_column($rows, 'beban_bersih_ias'));
        $totalProfit = array_sum(array_column($rows, 'profit'));
        $marginProfitPersen = $totalNota > 0 ? ($totalProfit / $totalNota) * 100 : 0;

        $summary = [
            'total_uang_masuk' => $totalUangMasuk,
            'total_pembayaran_ias' => $totalPembayaranIas,
            'total_cashback' => $totalCashback,
            'total_tonase_kg' => $totalTonaseKg,
            'cashback_rate' => $cashbackRate,
            'total_nota' => $totalNota,
            'total_beban_bersih_ias' => $totalBebanBersihIas,
            'total_profit' => $totalProfit,
            'margin_profit_persen' => $marginProfitPersen,
        ];

        return [
            'rows' => $rows,
            'summary' => $summary,
        ];
    }
}
