<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Worksheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorksheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_worksheet_page_has_koli_and_kilo_smu_inputs(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($user)->get(route('worksheets.create'));

        $response->assertStatus(200);
        $response->assertSee('name="koli_smu"', false);
        $response->assertSee('name="berat_smu"', false);
        $response->assertSee('Koli SMU', false);
        $response->assertSee('Kilo SMU (kg)', false);
    }

    public function test_store_and_update_worksheet_with_koli_and_berat_smu(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = Customer::create([
            'kode_customer' => 'CUST-TEST',
            'nama' => 'Customer Test',
            'kategori' => 'standar',
            'tarif_default' => 2100,
            'status' => 'active',
        ]);

        $payload = [
            'nomor_lembar_kerja' => 'LK-202610-9999',
            'tanggal' => '2026-10-23',
            'nomor_smu' => 'GA-99887766',
            'koli_smu' => 25,
            'berat_smu' => 350.50,
            'origin' => 'lop',
            'tipe' => 'pecahan',
            'status' => 'selesai',
            'items' => [
                [
                    'customer_id' => $customer->id,
                    'marking' => 'TEST-01',
                    'jumlah_koli' => 25,
                    'berat_kg' => 350.50,
                    'tarif_per_kg' => 2100,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post(route('worksheets.store'), $payload);

        $worksheet = Worksheet::where('nomor_lembar_kerja', 'LK-202610-9999')->first();
        $this->assertNotNull($worksheet);
        $this->assertEquals(25, $worksheet->koli_smu);
        $this->assertEquals(350.50, $worksheet->berat_smu);
        $this->assertEquals(25, $worksheet->display_koli_smu);
        $this->assertEquals(350.50, $worksheet->display_berat_smu);

        $response->assertRedirect(route('worksheets.show', $worksheet));

        // Test Show
        $showResponse = $this->actingAs($user)->get(route('worksheets.show', $worksheet));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('25 Koli');
        $showResponse->assertSee('350.50 kg');

        // Test Update
        $updatePayload = $payload;
        $updatePayload['koli_smu'] = 30;
        $updatePayload['berat_smu'] = 400.00;
        $updatePayload['items'][0]['jumlah_koli'] = 30;
        $updatePayload['items'][0]['berat_kg'] = 400.00;

        $updateResponse = $this->actingAs($user)->put(route('worksheets.update', $worksheet), $updatePayload);
        $updateResponse->assertRedirect(route('worksheets.show', $worksheet));

        $worksheet->refresh();
        $this->assertEquals(30, $worksheet->koli_smu);
        $this->assertEquals(400.00, $worksheet->berat_smu);

        // Test Index
        $indexResponse = $this->actingAs($user)->get(route('worksheets.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('30/30 koli', false);
        $indexResponse->assertSee('400/400 kg', false);
    }
}

