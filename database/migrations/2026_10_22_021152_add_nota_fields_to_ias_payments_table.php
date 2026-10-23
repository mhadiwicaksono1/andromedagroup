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
            $table->string('nomor_nota', 50)->nullable()->index()->after('tanggal');
            $table->foreignId('invoice_id')->nullable()->after('nomor_nota')->constrained('invoices')->nullOnDelete();
            $table->foreignId('worksheet_id')->nullable()->after('nomor_smu')->constrained('worksheets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('ias_payments', function (Blueprint $table) {
                $table->dropForeign(['invoice_id']);
                $table->dropForeign(['worksheet_id']);
                $table->dropIndex(['nomor_nota']);
                $table->dropColumn(['nomor_nota', 'invoice_id', 'worksheet_id']);
            });
        }
    }
};
