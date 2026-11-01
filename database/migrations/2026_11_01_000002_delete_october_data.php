<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menghapus seluruh data operasional dan keuangan pada bulan Oktober 2026.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Hapus Payment Allocations terkait data Oktober
        DB::table('payment_allocations')
            ->whereIn('payment_id', function ($q) {
                $q->select('id')->from('payments')
                    ->whereBetween('tanggal', ['2026-10-01', '2026-10-31'])
                    ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59']);
            })
            ->orWhereIn('invoice_id', function ($q) {
                $q->select('id')->from('invoices')
                    ->whereBetween('tanggal', ['2026-10-01', '2026-10-31'])
                    ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59']);
            })
            ->delete();

        // 2. Hapus Payments (Pembayaran Piutang) bulan Oktober
        DB::table('payments')
            ->whereBetween('tanggal', ['2026-10-01', '2026-10-31'])
            ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])
            ->delete();

        // 3. Hapus IAS Payment Invoices dan IAS Payments bulan Oktober
        DB::table('ias_payment_invoices')
            ->whereIn('ias_payment_id', function ($q) {
                $q->select('id')->from('ias_payments')
                    ->whereBetween('tanggal', ['2026-10-01', '2026-10-31'])
                    ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59']);
            })
            ->delete();

        DB::table('ias_payments')
            ->whereBetween('tanggal', ['2026-10-01', '2026-10-31'])
            ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])
            ->delete();

        // 4. Hapus Worksheet Items terkait Lembar Kerja bulan Oktober
        DB::table('worksheet_items')
            ->whereIn('worksheet_id', function ($q) {
                $q->select('id')->from('worksheets')
                    ->whereBetween('tanggal', ['2026-10-01', '2026-10-31'])
                    ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59']);
            })
            ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])
            ->delete();

        // 5. Hapus Worksheets (Lembar Kerja SMU) bulan Oktober
        DB::table('worksheets')
            ->whereBetween('tanggal', ['2026-10-01', '2026-10-31'])
            ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])
            ->delete();

        // 6. Hapus Invoices (Nota Penagihan) bulan Oktober
        DB::table('invoices')
            ->whereBetween('tanggal', ['2026-10-01', '2026-10-31'])
            ->orWhereBetween('created_at', ['2026-10-01 00:00:00', '2026-10-31 23:59:59'])
            ->delete();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data yang telah dihapus tidak dapat dipulihkan secara otomatis.
    }
};
