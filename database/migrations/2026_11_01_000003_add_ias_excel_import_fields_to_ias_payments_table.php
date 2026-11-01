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
        Schema::table('ias_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('ias_payments', 'no_kuitansi')) {
                $table->string('no_kuitansi', 80)->nullable()->index()->after('nomor_transaksi_ias');
            }
            if (!Schema::hasColumn('ias_payments', 'no_btb')) {
                $table->string('no_btb', 80)->nullable()->after('no_kuitansi');
            }
            if (!Schema::hasColumn('ias_payments', 'airline')) {
                $table->string('airline', 80)->nullable()->after('nomor_smu');
            }
            if (!Schema::hasColumn('ias_payments', 'shipper')) {
                $table->string('shipper', 100)->nullable()->after('airline');
            }
            if (!Schema::hasColumn('ias_payments', 'consignee')) {
                $table->string('consignee', 100)->nullable()->after('shipper');
            }
            if (!Schema::hasColumn('ias_payments', 'ori')) {
                $table->string('ori', 15)->nullable()->after('consignee');
            }
            if (!Schema::hasColumn('ias_payments', 'dst')) {
                $table->string('dst', 15)->nullable()->after('ori');
            }
            if (!Schema::hasColumn('ias_payments', 'komoditi')) {
                $table->string('komoditi', 100)->nullable()->after('dst');
            }
            if (!Schema::hasColumn('ias_payments', 'koli')) {
                $table->integer('koli')->default(0)->after('komoditi');
            }
            if (!Schema::hasColumn('ias_payments', 'berat_kg')) {
                $table->decimal('berat_kg', 10, 2)->default(0)->after('koli');
            }
            if (!Schema::hasColumn('ias_payments', 'handling')) {
                $table->decimal('handling', 15, 2)->default(0)->after('berat_kg');
            }
            if (!Schema::hasColumn('ias_payments', 'adm_fee')) {
                $table->decimal('adm_fee', 15, 2)->default(0)->after('handling');
            }
            if (!Schema::hasColumn('ias_payments', 'pndp')) {
                $table->decimal('pndp', 15, 2)->default(0)->after('adm_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ias_payments', function (Blueprint $table) {
            $table->dropColumn([
                'no_kuitansi',
                'no_btb',
                'airline',
                'shipper',
                'consignee',
                'ori',
                'dst',
                'komoditi',
                'koli',
                'berat_kg',
                'handling',
                'adm_fee',
                'pndp',
            ]);
        });
    }
};
