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
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('tarif_pecahan', 15, 2)->nullable()->default(2000)->after('tarif_default');
            $table->decimal('tarif_pernama', 15, 2)->nullable()->default(1980)->after('tarif_pecahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['tarif_pecahan', 'tarif_pernama']);
        });
    }
};
