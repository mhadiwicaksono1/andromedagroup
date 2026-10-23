<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Customers: Kategori (standar/prioritas), Tarif Default, and Soft Deletes
        Schema::table('customers', function (Blueprint $table) {
            $table->string('kategori', 20)->default('standar')->after('status'); // standar, prioritas
            $table->decimal('tarif_default', 15, 2)->default(2100)->after('kategori'); // standar: 2100, prioritas: 2000 / 1980
            $table->softDeletes();
        });

        // 2. Worksheets: Tipe (pernama / pecahan), and Soft Deletes
        Schema::table('worksheets', function (Blueprint $table) {
            $table->string('tipe', 20)->default('pecahan')->after('nomor_smu'); // pecahan, pernama
            $table->softDeletes();
        });

        // 3. Worksheet Items: Marking (free text), and Soft Deletes
        Schema::table('worksheet_items', function (Blueprint $table) {
            $table->string('marking', 100)->nullable()->after('customer_id'); // Free text marking
            $table->softDeletes();
        });

        // 4. Invoices: Soft Deletes
        Schema::table('invoices', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 5. Payments: Soft Deletes
        Schema::table('payments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('worksheet_items', function (Blueprint $table) {
            $table->dropColumn('marking');
            $table->dropSoftDeletes();
        });

        Schema::table('worksheets', function (Blueprint $table) {
            $table->dropColumn('tipe');
            $table->dropSoftDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'tarif_default']);
            $table->dropSoftDeletes();
        });
    }
};
