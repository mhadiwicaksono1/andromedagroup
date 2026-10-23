<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Worksheet;
use App\Models\WorksheetItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_invoice_generates_amg_cb_number_format(): void
    {
        $number = Invoice::generateNumber();
        $this->assertStringStartsWith('AMG/CB/', $number);
        $this->assertEquals('AMG/CB/1101', $number);
    }

    public function test_worksheet_calculates_total_biaya_with_ceil_to_nearest_thousand(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = Customer::create([
            'kode_customer' => 'CUST-TEST',
            'nama' => 'Test Customer',
            'telepon' => '0812345678',
            'status' => 'active',
        ]);

        // Testing: 374 kg * 1.960 = 733.040 -> ceil to thousand is 734.000 (as shown in user image 1)
        $response = $this->actingAs($user)->post(route('worksheets.store'), [
            'nomor_lembar_kerja' => Worksheet::generateNumber(),
            'tanggal' => now()->toDateString(),
            'nomor_smu' => '5304',
            'origin' => 'lop',
            'status' => 'selesai',
            'tipe' => 'pecahan',
            'items' => [
                [
                    'customer_id' => $customer->id,
                    'marking' => 'ALUN BR',
                    'origin' => 'lop',
                    'jumlah_koli' => 12,
                    'berat_kg' => 374,
                    'tarif_per_kg' => 1960,
                ],
            ],
        ]);

        $response->assertRedirect();

        $item = WorksheetItem::where('marking', 'ALUN BR')->first();
        $this->assertNotNull($item);
        $this->assertEquals(734000, (float) $item->total_biaya);
        $this->assertEquals('lop', $item->origin);
    }

    public function test_create_and_store_invoice_pernama_with_adm_calculation(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = Customer::create([
            'kode_customer' => 'CUST-ALUN',
            'nama' => 'ALUN',
            'telepon' => '0812345678',
            'kategori' => 'standar',
            'tarif_default' => 1960,
        ]);

        $worksheet = Worksheet::create([
            'nomor_lembar_kerja' => Worksheet::generateNumber(),
            'nomor_smu' => '5304',
            'origin' => 'lop',
            'tanggal' => now(),
            'status' => 'selesai',
            'tipe' => 'pernama',
            'user_id' => $user->id,
        ]);

        // Item 1: 374 kg * 1960 = 734.000
        $item1 = WorksheetItem::create([
            'worksheet_id' => $worksheet->id,
            'customer_id' => $customer->id,
            'marking' => 'ALUN BR',
            'origin' => 'lop',
            'jumlah_koli' => 12,
            'berat_kg' => 374,
            'tarif_per_kg' => 1960,
            'total_biaya' => 734000,
            'status' => 'selesai',
        ]);

        // Item 2: 219 kg * 1960 = 429.240 -> 430.000
        $item2 = WorksheetItem::create([
            'worksheet_id' => $worksheet->id,
            'customer_id' => $customer->id,
            'marking' => 'ALUN BR',
            'origin' => 'lop',
            'jumlah_koli' => 7,
            'berat_kg' => 219,
            'tarif_per_kg' => 1960,
            'total_biaya' => 430000,
            'status' => 'selesai',
        ]);

        $nomorNota = Invoice::generateNumber();

        $response = $this->actingAs($user)->post(route('invoices.store'), [
            'nomor_nota' => $nomorNota,
            'tipe_nota' => 'pernama',
            'tanggal' => now()->toDateString(),
            'customer_id' => $customer->id,
            'worksheet_ids' => [$item1->id, $item2->id],
            'sisa_hutang_sebelumnya' => 0,
            'penyesuaian_biaya' => 0,
            'jumlah_dibayar' => 0,
        ]);

        $response->assertRedirect();

        $invoice = Invoice::where('nomor_nota', $nomorNota)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('pernama', $invoice->tipe_nota);

        // Subtotal = 734.000 + 430.000 = 1.164.000
        $this->assertEquals(1164000, (float) $invoice->subtotal_lembar_kerja);

        // Biaya ADM = 2 SMU * 3.000 = 6.000
        $this->assertEquals(6000, (float) $invoice->biaya_adm);

        // Total Nota = 1.164.000 + 6.000 = 1.170.000
        $this->assertEquals(1170000, (float) $invoice->total_nota);
    }

    public function test_print_view_renders_exact_details_from_images(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = Customer::create([
            'kode_customer' => 'CUST-DENI',
            'nama' => 'DENI',
            'telepon' => '0812999999',
            'kategori' => 'standar',
            'tarif_default' => 2100,
        ]);

        $worksheet = Worksheet::create([
            'nomor_lembar_kerja' => Worksheet::generateNumber(),
            'nomor_smu' => '7411',
            'origin' => 'lop',
            'tanggal' => now(),
            'status' => 'selesai',
            'tipe' => 'pecahan',
            'user_id' => $user->id,
        ]);

        // 102 kg * 2100 = 214.200 -> ceil is 215.000 (Image 2)
        $item = WorksheetItem::create([
            'worksheet_id' => $worksheet->id,
            'customer_id' => $customer->id,
            'marking' => 'DENI',
            'origin' => 'lop',
            'jumlah_koli' => 4,
            'berat_kg' => 102,
            'tarif_per_kg' => 2100,
            'total_biaya' => 215000,
            'status' => 'selesai',
        ]);

        $invoice = Invoice::create([
            'nomor_nota' => 'AMG/CB/001',
            'tipe_nota' => 'pecahan',
            'tanggal' => now(),
            'customer_id' => $customer->id,
            'sisa_hutang_sebelumnya' => 0,
            'subtotal_lembar_kerja' => 215000,
            'biaya_adm' => 3000, // 1 SMU @ 3.000
            'penyesuaian_biaya' => 0,
            'total_nota' => 218000, // 215.000 + 3.000 = 218.000
            'total_tagihan_kumulatif' => 218000,
            'jumlah_dibayar' => 0,
            'sisa_piutang' => 218000,
            'status_pembayaran' => 'belum_lunas',
            'user_id' => $user->id,
        ]);

        $item->update(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($user)->get(route('invoices.print', $invoice));

        $response->assertStatus(200);
        $response->assertSee('NOTA PECAHAN');
        $response->assertSee('AMG/CB/001');
        $response->assertSee('DENI');
        $response->assertSee('7411');
        $response->assertSee('lop');
        $response->assertSee('4 KOLI');
        $response->assertSee('102');
        $response->assertSee('Rp 215.000');
        $response->assertSee('JUMLAH ADM');
        $response->assertSee('Rp 3.000');
        $response->assertSee('Rp 218.000');
        $response->assertSee('BCA 7195091927 An ABDUL SATAR');
    }
}
