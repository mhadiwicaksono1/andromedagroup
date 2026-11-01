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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'biaya_adm_pecahan')) {
                $table->decimal('biaya_adm_pecahan', 15, 2)->default(0)->after('biaya_adm');
            }
            if (!Schema::hasColumn('invoices', 'biaya_adm_pernama')) {
                $table->decimal('biaya_adm_pernama', 15, 2)->default(0)->after('biaya_adm_pecahan');
            }
            if (!Schema::hasColumn('invoices', 'items_snapshot')) {
                $table->json('items_snapshot')->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('invoices', 'batal_at')) {
                $table->timestamp('batal_at')->nullable()->after('items_snapshot');
            }
            if (!Schema::hasColumn('invoices', 'alasan_batal')) {
                $table->string('alasan_batal')->nullable()->after('batal_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'biaya_adm_pecahan',
                'biaya_adm_pernama',
                'items_snapshot',
                'batal_at',
                'alasan_batal',
            ]);
        });
    }
};
