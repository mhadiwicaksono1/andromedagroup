<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Worksheet;
use App\Models\WorksheetItem;
use Illuminate\Http\Request;

class CargoDashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::where('status', 'active')->count();

        $worksheetsCount = [
            'total' => Worksheet::count(),
            'draft' => Worksheet::where('status', 'draft')->count(),
            'selesai' => WorksheetItem::where('status', 'selesai')->whereNull('invoice_id')->count(),
            'dinotakan' => WorksheetItem::where('status', 'dinotakan')->count(),
        ];

        // Total omzet dari nota bulan ini
        $omzetBulanIni = Invoice::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('total_nota');

        // Total piutang yang belum lunas
        $totalPiutang = Invoice::where('status_pembayaran', '!=', 'lunas')->sum('sisa_piutang');

        // Lembar kerja terbaru
        $recentWorksheets = Worksheet::with('items.customer')
            ->latest('tanggal')
            ->latest('id')
            ->take(5)
            ->get();

        // Nota penagihan terbaru
        $recentInvoices = Invoice::with('customer')
            ->latest('tanggal')
            ->latest('id')
            ->take(5)
            ->get();

        // Customer dengan piutang tertinggi
        $customersWithDebt = Customer::whereHas('invoices', function ($q) {
            $q->where('status_pembayaran', '!=', 'lunas');
        })->withSum(['invoices as total_piutang_aktif' => function ($q) {
            $q->where('status_pembayaran', '!=', 'lunas');
        }], 'sisa_piutang')
        ->orderByDesc('total_piutang_aktif')
        ->take(5)
        ->get();

        return view('cargo.dashboard', compact(
            'totalCustomers',
            'worksheetsCount',
            'omzetBulanIni',
            'totalPiutang',
            'recentWorksheets',
            'recentInvoices',
            'customersWithDebt'
        ));
    }
}
