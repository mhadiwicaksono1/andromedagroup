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
        Schema::create('ias_payments', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi_ias', 80)->unique();
            $table->date('tanggal');
            $table->string('nomor_smu', 50)->nullable()->index();
            $table->decimal('nominal', 15, 2);
            $table->string('metode_bayar', 40)->default('transfer');
            $table->string('bank_pengirim', 50)->nullable();
            $table->string('nomor_rekening_tujuan', 80)->nullable();
            $table->string('status', 30)->default('berhasil'); // berhasil, pending, gagal
            $table->text('catatan')->nullable();
            $table->string('file_referensi', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ias_payments');
    }
};
