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
        // 1. Tambah tipe_nota dan biaya_adm di tabel invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('tipe_nota', 20)->default('pernama')->after('nomor_nota'); // pernama, pecahan
            $table->decimal('biaya_adm', 15, 2)->default(0)->after('subtotal_lembar_kerja');
        });

        // 2. Tambah kolom origin pada tabel worksheets
        if (!Schema::hasColumn('worksheets', 'origin')) {
            Schema::table('worksheets', function (Blueprint $table) {
                $table->string('origin', 20)->default('lop')->after('nomor_smu');
            });
        }

        // 3. Tambah kolom origin pada tabel worksheet_items
        if (!Schema::hasColumn('worksheet_items', 'origin')) {
            Schema::table('worksheet_items', function (Blueprint $table) {
                $table->string('origin', 20)->default('lop')->after('marking');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worksheet_items', function (Blueprint $table) {
            $table->dropColumn('origin');
        });

        Schema::table('worksheets', function (Blueprint $table) {
            $table->dropColumn('origin');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['tipe_nota', 'biaya_adm']);
        });
    }
};
