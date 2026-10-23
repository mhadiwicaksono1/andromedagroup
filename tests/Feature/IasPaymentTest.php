<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Worksheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IasPaymentTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_create_page_loads_successfully()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($user)->get(route('users.create'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Pengguna Baru');
        $response->assertDontSee('max-w-2xl mx-auto');
    }

    public function test_ias_payment_create_page_loads_with_invoices_and_worksheets()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($user)->get(route('ias.payments.create'));
        $response->assertStatus(200);
        $response->assertSee('Catat Pembayaran ke IAS');
        $response->assertSee('Bisa Lebih dari 1');
        $response->assertDontSee('max-w-2xl mx-auto');
        $response->assertSee('Kembali');
    }

    public function test_store_ias_payment_with_nota_and_smu()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $invoice = Invoice::first();
        $worksheet = Worksheet::first();

        $payload = [
            'nomor_transaksi_ias' => 'TEST-IAS-' . rand(1000, 9999),
            'tanggal' => now()->toDateString(),
            'invoice_id' => $invoice ? $invoice->id : null,
            'nomor_nota' => $invoice ? $invoice->nomor_nota : 'INV/202610/9999',
            'worksheet_id' => $worksheet ? $worksheet->id : null,
            'nomor_smu' => $worksheet ? $worksheet->nomor_smu : 'SMU-TEST-999',
            'nominal' => 2500000,
            'metode_bayar' => 'transfer',
            'bank_pengirim' => 'BCA',
            'status' => 'berhasil',
            'catatan' => 'Test pembayaran terminal handling IAS',
        ];

        $response = $this->actingAs($user)->post(route('ias.payments.store'), $payload);
        $response->assertRedirect(route('ias.payments.index'));

        $this->assertDatabaseHas('ias_payments', [
            'nomor_transaksi_ias' => $payload['nomor_transaksi_ias'],
            'nomor_nota' => $payload['nomor_nota'],
            'nomor_smu' => $payload['nomor_smu'],
            'nominal' => 2500000.00,
        ]);
    }

    public function test_back_button_has_icon_in_create_ias_payment_and_create_user()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $resIas = $this->actingAs($user)->get(route('ias.payments.create'));
        $resIas->assertStatus(200);
        $resIas->assertSee('<svg class="w-3.5 h-3.5"', false);
        $resIas->assertSee('Kembali');

        $resUser = $this->actingAs($user)->get(route('users.create'));
        $resUser->assertStatus(200);
        $resUser->assertSee('<svg class="w-3.5 h-3.5"', false);
        $resUser->assertSee('Kembali');
    }

    public function test_csv_template_includes_nomor_nota()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($user)->get(route('ias.payments.template'));
        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('nomor_nota', $content);
        $this->assertStringContainsString('nomor_smu', $content);
    }

    public function test_ias_payments_index_shows_dasar_nota_and_dasar_smu_columns()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($user)->get(route('ias.payments.index'));
        $response->assertStatus(200);
        $response->assertSee('Dasar Nota');
        $response->assertSee('Dasar Master SMU');
    }

    public function test_store_ias_payment_with_multiple_invoices()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $customer = \App\Models\Customer::create([
            'kode_customer' => 'CUST-TEST-01',
            'nama' => 'PT Test Logistik Multi',
            'status' => 'active',
        ]);

        $inv1 = Invoice::create([
            'nomor_nota' => 'INV/TEST/0001',
            'tanggal' => now()->toDateString(),
            'customer_id' => $customer->id,
            'subtotal' => 1000000,
            'total_nota' => 1000000,
            'total_tagihan_kumulatif' => 1000000,
            'sisa_piutang' => 1000000,
            'status_pembayaran' => 'belum_lunas',
            'user_id' => $user->id,
        ]);

        $inv2 = Invoice::create([
            'nomor_nota' => 'INV/TEST/0002',
            'tanggal' => now()->toDateString(),
            'customer_id' => $customer->id,
            'subtotal' => 1500000,
            'total_nota' => 1500000,
            'total_tagihan_kumulatif' => 1500000,
            'sisa_piutang' => 1500000,
            'status_pembayaran' => 'belum_lunas',
            'user_id' => $user->id,
        ]);

        $payload = [
            'nomor_transaksi_ias' => 'TEST-MULTI-' . rand(1000, 9999),
            'tanggal' => now()->toDateString(),
            'invoice_ids' => [$inv1->id, $inv2->id],
            'nominal' => 2500000,
            'metode_bayar' => 'transfer',
            'bank_pengirim' => 'BCA',
            'status' => 'berhasil',
            'catatan' => 'Pembayaran multi-nota ke IAS',
        ];

        $response = $this->actingAs($user)->post(route('ias.payments.store'), $payload);
        $response->assertRedirect(route('ias.payments.index'));

        $payment = \App\Models\IasPayment::where('nomor_transaksi_ias', $payload['nomor_transaksi_ias'])->first();
        $this->assertNotNull($payment);
        $this->assertStringContainsString('INV/TEST/0001', $payment->nomor_nota);
        $this->assertStringContainsString('INV/TEST/0002', $payment->nomor_nota);
        $this->assertCount(2, $payment->invoices);
    }

    public function test_report_print_views_are_standalone_without_navigation_menus()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $printRoutes = [
            route('reports.worksheets.print'),
            route('reports.invoices.print'),
            route('reports.receivables.print'),
            route('reports.profit_loss.print'),
        ];

        foreach ($printRoutes as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertStatus(200);
            $response->assertSee('Tutup Tab');
            $response->assertSee('Cetak / Download PDF');
            $response->assertDontSee('app-sidebar');
            $response->assertDontSee('mobile-menu-btn');
        }
    }

    public function test_report_index_pages_have_target_blank_print_links()
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $reportPages = [
            route('reports.worksheets'),
            route('reports.invoices'),
            route('reports.receivables'),
            route('reports.profit_loss'),
        ];

        foreach ($reportPages as $page) {
            $response = $this->actingAs($user)->get($page);
            $response->assertStatus(200);
            $response->assertSee('target="_blank"', false);
        }
    }
}
