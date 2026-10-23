<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CargoDashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\IasPaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorksheetController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Cargo & ERP Routes
Route::middleware(['auth'])->group(function () {
    // Tahap 1: Dashboard
    Route::get('/', [CargoDashboardController::class, 'index'])->name('cargo.dashboard');

    // Tahap 2: Master Customer
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::get('/{customer}/statement', [CustomerController::class, 'statement'])->name('statement');
        Route::patch('/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('toggleStatus');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    // API endpoint for dynamic Invoice creation & Payment allocation (fetch debt & unbilled worksheets)
    Route::get('/api/customers/{customer}/debt', [CustomerController::class, 'apiDebt'])->name('api.customers.debt');

    // Tahap 3: Lembar Kerja (Job Order SMU)
    Route::prefix('worksheets')->name('worksheets.')->group(function () {
        Route::get('/', [WorksheetController::class, 'index'])->name('index');

        // Tindakan operasional kargo (Create & Store) - Berbasis Izin Dinamis
        Route::middleware(['permission:worksheets.create'])->group(function () {
            Route::get('/create', [WorksheetController::class, 'create'])->name('create');
            Route::post('/', [WorksheetController::class, 'store'])->name('store');
        });

        Route::get('/{worksheet}', [WorksheetController::class, 'show'])->name('show');

        // Tindakan edit, update status, dan hapus lembar kerja - Berbasis Izin Dinamis
        Route::middleware(['permission:worksheets.edit,worksheets.status,worksheets.delete'])->group(function () {
            Route::get('/{worksheet}/edit', [WorksheetController::class, 'edit'])->name('edit');
            Route::put('/{worksheet}', [WorksheetController::class, 'update'])->name('update');
            Route::patch('/{worksheet}/status', [WorksheetController::class, 'updateStatus'])->name('updateStatus');
            Route::delete('/{worksheet}', [WorksheetController::class, 'destroy'])->name('destroy');
        });
    });

    // Tahap 4: Pembuatan Nota & Piutang (Invoice) - Berbasis Izin Dinamis
    Route::middleware(['permission:invoices.view'])->prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/print', [InvoiceController::class, 'print'])->name('print');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/{invoice}/payments', [PaymentController::class, 'storeForInvoice'])->name('payments.store');
    });

    // Tahap 4.5: Penerimaan Pembayaran Customer (Kas & Bank Masuk) - Berbasis Izin Dinamis
    Route::middleware(['permission:payments.view'])->prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/create', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('/{payment}/print', [PaymentController::class, 'print'])->name('print');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
    });

    // Tahap 4.8: Pembayaran ke IAS & Import CSV - Berbasis Izin Dinamis
    Route::middleware(['permission:ias_payments.view'])->prefix('ias-payments')->name('ias.payments.')->group(function () {
        Route::get('/', [IasPaymentController::class, 'index'])->name('index');
        Route::get('/create', [IasPaymentController::class, 'create'])->name('create');
        Route::post('/', [IasPaymentController::class, 'store'])->name('store');
        Route::post('/import', [IasPaymentController::class, 'import'])->name('import');
        Route::get('/template', [IasPaymentController::class, 'downloadTemplate'])->name('template');
        Route::delete('/{iasPayment}', [IasPaymentController::class, 'destroy'])->name('destroy');
    });

    // Tahap 5: Laporan & Monitoring
    Route::prefix('reports')->name('reports.')->group(function () {
        // Laporan Lembar Kerja (Bisa diakses pengguna dengan izin laporan lembar kerja)
        Route::get('/worksheets', [ReportController::class, 'worksheets'])->name('worksheets');
        Route::get('/worksheets/print', [ReportController::class, 'printWorksheets'])->name('worksheets.print');

        // Laporan Keuangan, Piutang & Laba Rugi - Berbasis Izin Dinamis
        Route::middleware(['permission:reports.invoices,reports.receivables,reports.profit_loss'])->group(function () {
            Route::get('/invoices', [ReportController::class, 'invoices'])->name('invoices');
            Route::get('/invoices/print', [ReportController::class, 'printInvoices'])->name('invoices.print');
            Route::get('/receivables', [ReportController::class, 'receivables'])->name('receivables');
            Route::get('/receivables/print', [ReportController::class, 'printReceivables'])->name('receivables.print');
            Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit_loss');
            Route::get('/profit-loss/print', [ReportController::class, 'printProfitLoss'])->name('profit_loss.print');
            Route::get('/daily-performance', [ReportController::class, 'dailyPerformance'])->name('daily_performance');
            Route::get('/daily-performance/print', [ReportController::class, 'printDailyPerformance'])->name('daily_performance.print');
            Route::get('/daily-performance/export', [ReportController::class, 'exportDailyPerformance'])->name('daily_performance.export');
        });
    });

    // Tahap 6: Pengaturan & Sistem (Manajemen Pengguna & Hak Akses Role)
    Route::middleware(['permission:users.manage'])->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware(['permission:roles.manage'])->group(function () {
        Route::resource('roles', RoleController::class);
    });
});
