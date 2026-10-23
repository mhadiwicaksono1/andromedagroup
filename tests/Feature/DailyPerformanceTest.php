<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\IasPayment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_performance_report_calculates_profit_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = Customer::create([
            'kode_customer' => 'CUST-001',
            'nama' => 'PT Test Cargo',
            'telepon' => '08123456789',
            'status' => 'active',
        ]);

        $testDate = '2026-10-01';

        // 1. Uang Masuk = 25.131.000
        Payment::create([
            'nomor_pembayaran' => 'PAY-TEST-001',
            'customer_id' => $customer->id,
            'tanggal' => $testDate,
            'jumlah' => 25131000,
            'metode' => 'transfer',
            'user_id' => $admin->id,
        ]);

        // 2. Pembayaran ke IAS = 40.344.540, Cashback = 3.901.800
        IasPayment::create([
            'nomor_transaksi_ias' => 'IAS-TEST-001',
            'tanggal' => $testDate,
            'nominal' => 40344540,
            'cashback' => 3901800,
            'metode_bayar' => 'transfer',
            'status' => 'berhasil',
            'user_id' => $admin->id,
        ]);

        // 3. Total Nota ke Customer = 40.434.000
        Invoice::create([
            'nomor_nota' => 'INV-TEST-001',
            'customer_id' => $customer->id,
            'tanggal' => $testDate,
            'total_nota' => 40434000,
            'jumlah_dibayar' => 25131000,
            'sisa_piutang' => 15303000,
            'status_pembayaran' => 'sebagian',
            'user_id' => $admin->id,
        ]);

        // Expected Profit: 40.434.000 - (40.344.540 - 3.901.800) = 3.991.260
        $response = $this->actingAs($admin)->get(route('reports.daily_performance', [
            'start_date' => $testDate,
            'end_date' => $testDate,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rekap Mutasi Kas &amp; Profit Harian', false);
        $response->assertSee('Rp 25.131.000');
        $response->assertSee('Rp 40.344.540');
        $response->assertSee('Rp 3.901.800');
        $response->assertSee('Rp 40.434.000');
        $response->assertSee('Rp 3.991.260'); // Exactly matches the user's spreadsheet!

        // Print page test (standalone)
        $printResponse = $this->actingAs($admin)->get(route('reports.daily_performance.print', [
            'start_date' => $testDate,
            'end_date' => $testDate,
        ]));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('REKAP MUTASI &amp; PROFIT HARIAN', false);
        $printResponse->assertDontSee('app-sidebar');

        // CSV export test
        $exportResponse = $this->actingAs($admin)->get(route('reports.daily_performance.export', [
            'start_date' => $testDate,
            'end_date' => $testDate,
        ]));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_daily_performance_auto_calculates_cashback_at_200_per_kg_smu_when_not_manually_entered()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $customer = Customer::create([
            'kode_customer' => 'CUST-002',
            'nama' => 'PT Ekspedisi Udara',
            'telepon' => '08129876543',
            'status' => 'active',
        ]);

        $testDate = '2026-10-07';

        // Worksheet with 21.745,5 kg tonase SMU
        \App\Models\Worksheet::create([
            'nomor_lembar_kerja' => 'WS-202610-0007',
            'nomor_smu' => '126-77889900',
            'tipe' => 'outbound',
            'tanggal' => $testDate,
            'total_koli' => 120,
            'total_berat_kg' => 21745.5,
            'status' => 'selesai',
            'user_id' => $admin->id,
        ]);

        // IAS Payment with cashback = 0
        IasPayment::create([
            'nomor_transaksi_ias' => 'IAS-202610-0007',
            'tanggal' => $testDate,
            'nominal' => 45000000,
            'cashback' => 0, // Not explicitly set
            'metode_bayar' => 'transfer',
            'status' => 'berhasil',
            'user_id' => $admin->id,
        ]);

        // Invoice
        Invoice::create([
            'nomor_nota' => 'INV-202610-0007',
            'customer_id' => $customer->id,
            'tanggal' => $testDate,
            'total_nota' => 50000000,
            'jumlah_dibayar' => 50000000,
            'sisa_piutang' => 0,
            'status_pembayaran' => 'lunas',
            'user_id' => $admin->id,
        ]);

        // Expected Cashback: 21.745,5 kg * 200 = 4.349.100 (Row 7 in user spreadsheet)
        // Expected Beban Bersih IAS: 45.000.000 - 4.349.100 = 40.650.900
        // Expected Profit: 50.000.000 - 40.650.900 = 9.349.100
        $response = $this->actingAs($admin)->get(route('reports.daily_performance', [
            'start_date' => $testDate,
            'end_date' => $testDate,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rp 4.349.100'); // Auto-calculated cashback @ Rp 200/kg
        $response->assertSee('Rp 9.349.100'); // Calculated profit
        $response->assertSee('21.745,5 kg SMU'); // Monthly accumulated SMU weight
    }
}
