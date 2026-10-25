<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_payment_create_page_only_offers_transfer_bank_bca_and_cash(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($user)->get(route('payments.create'));

        $response->assertStatus(200);
        $response->assertSee('Transfer Bank BCA');
        $response->assertSee('Cash');
        $response->assertDontSee('Bilyet Giro');
        $response->assertDontSee('QRIS / E-Wallet');
    }

    public function test_store_payment_with_transfer_bank_bca(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = Customer::create([
            'kode_customer' => 'CUST-BCA',
            'nama' => 'Customer BCA',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'nomor_nota' => 'AMG/CB/1001',
            'tanggal' => now(),
            'customer_id' => $customer->id,
            'tipe_nota' => 'pecahan',
            'subtotal_lembar_kerja' => 500000,
            'biaya_adm' => 3000,
            'total_nota' => 503000,
            'total_tagihan_kumulatif' => 503000,
            'jumlah_dibayar' => 0,
            'sisa_piutang' => 503000,
            'status_pembayaran' => 'belum_lunas',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'customer_id' => $customer->id,
            'tanggal' => now()->toDateString(),
            'jumlah' => 503000,
            'metode' => 'transfer',
            'catatan' => 'Transfer BCA ref #123456',
        ]);

        $response->assertRedirect();

        $payment = Payment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('transfer', $payment->metode);
        $this->assertEquals(503000, (float) $payment->jumlah);

        // Verify invoice was marked as lunas
        $invoice->refresh();
        $this->assertEquals('lunas', $invoice->status_pembayaran);
        $this->assertEquals(0, (float) $invoice->sisa_piutang);
    }

    public function test_store_payment_with_cash_metode(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = Customer::create([
            'kode_customer' => 'CUST-CASH',
            'nama' => 'Customer Cash',
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'nomor_nota' => 'AMG/CB/1002',
            'tanggal' => now(),
            'customer_id' => $customer->id,
            'tipe_nota' => 'pecahan',
            'subtotal_lembar_kerja' => 200000,
            'biaya_adm' => 3000,
            'total_nota' => 203000,
            'total_tagihan_kumulatif' => 203000,
            'jumlah_dibayar' => 0,
            'sisa_piutang' => 203000,
            'status_pembayaran' => 'belum_lunas',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'customer_id' => $customer->id,
            'tanggal' => now()->toDateString(),
            'jumlah' => 203000,
            'metode' => 'tunai',
            'catatan' => 'Cash diterima di kasir',
        ]);

        $response->assertRedirect();

        $payment = Payment::where('customer_id', $customer->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('tunai', $payment->metode);

        // Verify index view displays 'Cash'
        $indexResponse = $this->actingAs($user)->get(route('payments.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Cash');
    }
}
