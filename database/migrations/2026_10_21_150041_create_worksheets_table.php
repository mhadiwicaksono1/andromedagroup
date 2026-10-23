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
        Schema::create('worksheets', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_lembar_kerja', 50)->unique();
            $table->date('tanggal');
            $table->string('nomor_smu', 50);
            $table->integer('total_koli')->default(0);
            $table->decimal('total_berat_kg', 10, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('draft'); // draft, selesai, dinotakan
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worksheets');
    }
};
