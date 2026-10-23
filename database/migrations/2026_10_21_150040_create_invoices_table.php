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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota', 50)->unique();
            $table->date('tanggal');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->decimal('sisa_hutang_sebelumnya', 15, 2)->default(0);
            $table->decimal('subtotal_lembar_kerja', 15, 2)->default(0);
            $table->decimal('penyesuaian_biaya', 15, 2)->default(0);
            $table->decimal('total_nota', 15, 2)->default(0);
            $table->decimal('total_tagihan_kumulatif', 15, 2)->default(0);
            $table->decimal('jumlah_dibayar', 15, 2)->default(0);
            $table->decimal('sisa_piutang', 15, 2)->default(0);
            $table->string('status_pembayaran', 30)->default('belum_lunas'); // belum_lunas, sebagian, lunas
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
