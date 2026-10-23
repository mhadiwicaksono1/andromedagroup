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
        Schema::create('ias_payment_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ias_payment_id')->constrained('ias_payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('nomor_nota', 100)->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('ias_payments', function (Blueprint $table) {
                $table->text('nomor_nota')->nullable()->change();
                $table->text('nomor_smu')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ias_payment_invoices');
    }
};
