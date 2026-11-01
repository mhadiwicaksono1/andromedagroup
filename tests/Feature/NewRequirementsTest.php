<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Worksheet;
use App\Models\WorksheetItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewRequirementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->customer = Customer::create([
            'kode_customer' => 'CUST-TEST',
            'nama' => 'PT Test Kargo Indonesia',
            'telepon' => '081234567890',
            'status' => 'active',
            'kategori' => 'reguler',
            'tarif_default' => 2000,
        ]);
    }

    /**
     * Test Menu Belum Ternota (Point 4)
     */
    public function test_unbilled_worksheets_page_displays_customer_with_unbilled_items(): void
    {
        $worksheet = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-TEST-001',
            'nomor_smu' => 'SMU-TEST-001',
            'tanggal' => now()->toDateString(),
            'tipe' => 'pecahan',
            'status' => 'selesai',
            'koli_smu' => 5,
            'berat_smu' => 50,
            'user_id' => $this->admin->id,
        ]);

        WorksheetItem::create([
            'worksheet_id' => $worksheet->id,
            'customer_id' => $this->customer->id,
            'jumlah_koli' => 5,
            'berat_kg' => 50,
            'tarif_per_kg' => 2000,
            'total_biaya' => 100000,
            'status' => 'selesai',
            'invoice_id' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('worksheets.unbilled'));
        $response->assertStatus(200);
        $response->assertSee('Kargo Belum Ternota');
        $response->assertSee('PT Test Kargo Indonesia');
        $response->assertSee('SMU-TEST-001');
        $response->assertSee('Buat Nota Langsung');
    }

    /**
     * Test Hak Akses Role untuk Menu Belum Ternota (worksheets.unbilled)
     */
    public function test_unbilled_worksheets_permission_access_and_role_matrix(): void
    {
        // 1. Verifikasi permission worksheets.unbilled terdaftar di database
        $permission = \App\Models\Permission::where('slug', 'worksheets.unbilled')->first();
        $this->assertNotNull($permission);
        $this->assertEquals('Menu Belum Ternota (Lihat & Buat Nota)', $permission->name);
        $this->assertEquals('Belum Ternota', $permission->group);

        // 2. Buat role kustom tanpa izin worksheets.unbilled
        $roleRestricted = \App\Models\Role::create([
            'name' => 'Staf Gudang Terbatas',
            'slug' => 'staf-gudang-terbatas',
            'is_system' => false,
        ]);
        // Hanya beri izin worksheets.view
        $viewPerm = \App\Models\Permission::where('slug', 'worksheets.view')->first();
        $roleRestricted->permissions()->attach($viewPerm->id);

        $userRestricted = User::factory()->create([
            'role' => $roleRestricted->slug,
            'role_id' => $roleRestricted->id,
            'status' => 'active',
        ]);

        // Coba akses route /worksheets/unbilled -> Harus ditolak (403)
        $responseForbidden = $this->actingAs($userRestricted)->get(route('worksheets.unbilled'));
        $responseForbidden->assertStatus(403);

        // Di dashboard/sidebar, menu 'Belum Ternota' tidak boleh tampil untuk user ini
        $dashResponse = $this->actingAs($userRestricted)->get(route('cargo.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertDontSee('href="' . route('worksheets.unbilled') . '"', false);

        // 3. Sekarang berikan izin worksheets.unbilled ke role tersebut
        $roleRestricted->permissions()->attach($permission->id);
        $roleRestricted->unsetRelation('permissions');
        $userRestricted->unsetRelation('roleModel');

        // Akses route /worksheets/unbilled -> Harus berhasil (200)
        $responseAllowed = $this->actingAs($userRestricted)->get(route('worksheets.unbilled'));
        $responseAllowed->assertStatus(200);
        $responseAllowed->assertSee('Kargo Belum Ternota');

        // Di sidebar, menu 'Belum Ternota' sekarang harus tampil
        $dashResponseAllowed = $this->actingAs($userRestricted)->get(route('cargo.dashboard'));
        $dashResponseAllowed->assertStatus(200);
        $dashResponseAllowed->assertSee('href="' . route('worksheets.unbilled') . '"', false);

        // 4. Verifikasi bahwa halaman edit hak akses role menampilkan checklist 'Menu Belum Ternota'
        $roleEditResponse = $this->actingAs($this->admin)->get(route('roles.edit', $roleRestricted->id));
        $roleEditResponse->assertStatus(200);
        $roleEditResponse->assertSee('Menu Belum Ternota');
        $roleEditResponse->assertSee('Belum Ternota');
        $roleEditResponse->assertSee('worksheets.unbilled');
    }

    /**
     * Test Revisi Biaya Admin: Pecahan Flat 3000, Pernama total LK x 3000 (Point 7)
     */
    public function test_admin_fee_calculation_for_mixed_pecahan_and_pernama(): void
    {
        // 2 Lembar Kerja Pecahan
        $wsPecahan1 = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-PEC-01',
            'nomor_smu' => 'SMU-PEC-01',
            'tanggal' => now()->toDateString(),
            'tipe' => 'pecahan',
            'status' => 'selesai',
            'koli_smu' => 2,
            'berat_smu' => 20,
            'user_id' => $this->admin->id,
        ]);
        $itemPecahan1 = WorksheetItem::create([
            'worksheet_id' => $wsPecahan1->id,
            'customer_id' => $this->customer->id,
            'jumlah_koli' => 2,
            'berat_kg' => 20,
            'tarif_per_kg' => 2000,
            'total_biaya' => 40000,
            'status' => 'selesai',
            'invoice_id' => null,
        ]);

        $wsPecahan2 = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-PEC-02',
            'nomor_smu' => 'SMU-PEC-02',
            'tanggal' => now()->toDateString(),
            'tipe' => 'pecahan',
            'status' => 'selesai',
            'koli_smu' => 3,
            'berat_smu' => 30,
            'user_id' => $this->admin->id,
        ]);
        $itemPecahan2 = WorksheetItem::create([
            'worksheet_id' => $wsPecahan2->id,
            'customer_id' => $this->customer->id,
            'jumlah_koli' => 3,
            'berat_kg' => 30,
            'tarif_per_kg' => 2000,
            'total_biaya' => 60000,
            'status' => 'selesai',
            'invoice_id' => null,
        ]);

        // 2 Lembar Kerja Pernama
        $wsPernama1 = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-PER-01',
            'nomor_smu' => 'SMU-PER-01',
            'tanggal' => now()->toDateString(),
            'tipe' => 'pernama',
            'status' => 'selesai',
            'koli_smu' => 4,
            'berat_smu' => 40,
            'user_id' => $this->admin->id,
        ]);
        $itemPernama1 = WorksheetItem::create([
            'worksheet_id' => $wsPernama1->id,
            'customer_id' => $this->customer->id,
            'jumlah_koli' => 4,
            'berat_kg' => 40,
            'tarif_per_kg' => 2000,
            'total_biaya' => 80000,
            'status' => 'selesai',
            'invoice_id' => null,
        ]);

        $wsPernama2 = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-PER-02',
            'nomor_smu' => 'SMU-PER-02',
            'tanggal' => now()->toDateString(),
            'tipe' => 'pernama',
            'status' => 'selesai',
            'koli_smu' => 5,
            'berat_smu' => 50,
            'user_id' => $this->admin->id,
        ]);
        $itemPernama2 = WorksheetItem::create([
            'worksheet_id' => $wsPernama2->id,
            'customer_id' => $this->customer->id,
            'jumlah_koli' => 5,
            'berat_kg' => 50,
            'tarif_per_kg' => 2000,
            'total_biaya' => 100000,
            'status' => 'selesai',
            'invoice_id' => null,
        ]);

        // Buat Nota dengan 2 Pecahan + 2 Pernama
        // Biaya ADM Pecahan: 1 x 3000 = 3000 (meski ada 2 pecahan)
        // Biaya ADM Pernama: 2 x 3000 = 6000
        // Total Biaya ADM: 9000
        $postData = [
            'nomor_nota' => 'INV-TEST-ADM',
            'tanggal' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'worksheet_ids' => [$itemPecahan1->id, $itemPecahan2->id, $itemPernama1->id, $itemPernama2->id],
            'sisa_hutang_sebelumnya' => 0,
            'penyesuaian_biaya' => 0,
            'jumlah_dibayar' => 0,
        ];

        $response = $this->actingAs($this->admin)->post(route('invoices.store'), $postData);
        $response->assertSessionHasNoErrors();

        $invoice = Invoice::where('nomor_nota', 'INV-TEST-ADM')->first();
        $this->assertNotNull($invoice);

        $this->assertEquals(3000, (float) $invoice->biaya_adm_pecahan);
        $this->assertEquals(6000, (float) $invoice->biaya_adm_pernama);
        $this->assertEquals(9000, (float) $invoice->biaya_adm);
        $this->assertEquals(280000, (float) $invoice->subtotal_lembar_kerja); // 40k + 60k + 80k + 100k
        $this->assertEquals(289000, (float) $invoice->total_nota); // 280k + 9k
    }

    /**
     * Test Batal Nota: Snapshot tersimpan, LK open kembali, Piutang berkurang (Point 5)
     */
    public function test_cancel_invoice_preserves_snapshot_reopens_lk_and_affects_debt(): void
    {
        $worksheet = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-BATAL-01',
            'nomor_smu' => 'SMU-BATAL-01',
            'tanggal' => now()->toDateString(),
            'tipe' => 'pecahan',
            'status' => 'selesai',
            'koli_smu' => 2,
            'berat_smu' => 20,
            'user_id' => $this->admin->id,
        ]);
        $item = WorksheetItem::create([
            'worksheet_id' => $worksheet->id,
            'customer_id' => $this->customer->id,
            'jumlah_koli' => 2,
            'berat_kg' => 20,
            'tarif_per_kg' => 2000,
            'total_biaya' => 40000,
            'status' => 'selesai',
            'invoice_id' => null,
        ]);

        // Buat Nota
        $this->actingAs($this->admin)->post(route('invoices.store'), [
            'nomor_nota' => 'INV-BATAL-TEST',
            'tanggal' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'worksheet_ids' => [$item->id],
            'sisa_hutang_sebelumnya' => 0,
            'penyesuaian_biaya' => 0,
            'jumlah_dibayar' => 0,
        ]);

        $invoice = Invoice::where('nomor_nota', 'INV-BATAL-TEST')->first();
        $this->assertNotNull($invoice);

        // Sebelum dibatalkan: hutang customer adalah 43.000 (40.000 + 3.000 adm)
        $this->customer->refresh();
        $this->assertEquals(43000, $this->customer->sisa_hutang);

        // Batalkan Nota
        $response = $this->actingAs($this->admin)->delete(route('invoices.destroy', $invoice));
        $response->assertRedirect(route('invoices.index'));

        // 1. Verifikasi snapshot nota lama tersimpan
        $invoiceWithTrashed = Invoice::withTrashed()->find($invoice->id);
        $this->assertEquals('dibatalkan', $invoiceWithTrashed->status_pembayaran);
        $this->assertEquals(0, (float) $invoiceWithTrashed->sisa_piutang);
        $this->assertNotEmpty($invoiceWithTrashed->items_snapshot);
        $this->assertEquals('SMU-BATAL-01', $invoiceWithTrashed->items_snapshot[0]['nomor_smu']);
        $this->assertEquals('LK-BATAL-01', $invoiceWithTrashed->items_snapshot[0]['nomor_lembar_kerja']);
        $this->assertEquals(40000, (float) $invoiceWithTrashed->items_snapshot[0]['total_biaya']);

        // 2. Verifikasi display_items accessor mengembalikan snapshot dengan lengkap
        $this->assertCount(1, $invoiceWithTrashed->display_items);
        $this->assertEquals('SMU-BATAL-01', $invoiceWithTrashed->display_items[0]->nomor_smu);

        // 3. Verifikasi LK item telah dibuka kembali (invoice_id = null, status = selesai)
        $item->refresh();
        $this->assertNull($item->invoice_id);
        $this->assertEquals('selesai', $item->status);

        // 4. Verifikasi sisa hutang customer kembali menjadi 0 (tidak lagi dibebani nota batal)
        $this->customer->refresh();
        $this->assertEquals(0, $this->customer->sisa_hutang);

        // 5. Verifikasi nota batal dapat dilihat di show page dengan snapshot historis
        $showResponse = $this->actingAs($this->admin)->get(route('invoices.show', $invoice->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('DIBATALKAN');
        $showResponse->assertSee('SMU-BATAL-01');
    }

    /**
     * Test Import Format Resmi Laporan IAS (Point format import excel)
     */
    public function test_ias_official_excel_format_import_with_subtotals_and_duplicate_smus(): void
    {
        // 1. Siapkan worksheet dan item yang cocok dengan salah satu SMU
        $ws = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-IAS-MATCH',
            'nomor_smu' => '920-02197322',
            'tanggal' => '2026-10-01',
            'tipe' => 'pecahan',
            'status' => 'selesai',
            'koli_smu' => 14,
            'berat_smu' => 382,
            'user_id' => $this->admin->id,
        ]);

        $csvContent = implode("\n", [
            'No,Nomor Kuitansi,No. BTB/TTB,AWB,Airline,Tanggal,Customer,Sender,Receiver,Shipper,Consignee,ORI,DST,Komoditi,Goods,Qty,GW,CHW,Handling,Timbun,SGMA,Print SMU,Biaya Lainnya,Adm Fee,Subtotal,PPn,PNDP,Materai,Total',
            '1,BTHPID2610000092,BTHCID26090016857,126-05166770,(GA) Garuda Indonesia,01-Oct-26,PT. JASAMITRA EKSPRES,,,- ,JDE/INDRA,CGK,BTH,General,GENCO,1,14,14,"21.000,00",0,0,0,"7.000,00","3.000,00","31.000,00",0,840,0,"31.840,00"',
            ',,Total Kuitansi BTHPID2610000092,,,,,,,,,,,,1,14,14,"21.000,00",0,0,0,"7.000,00","3.000,00","31.000,00",0,840,0,"31.840,00"',
            '2,BTHPID2610000248,BTHCID26100000201,273-06943882,(RI) Rimbun Air,01-Oct-26,PT. JASAMITRA EKSPRES,,,LOGITRANS / BRK,DIPO - BTH,CGK,BTH,Strongly Smelling Goods,DURIAN,1,28,28,"42.000,00",0,0,0,"14.000,00","3.000,00","59.000,00",0,"1.680,00",0,"60.680,00"',
            ',,Total Kuitansi BTHPID2610000248,,,,,,,,,,,,1,28,28,"42.000,00",0,0,0,"14.000,00","3.000,00","59.000,00",0,"1.680,00",0,"60.680,00"',
            '3,BTHPID2610000412,BTHCID26100000370,920-02197322,(IU) Super Air Jet,01-Oct-26,PT. JASAMITRA EKSPRES,,,- ,ANDROMEDA GROUP,YIA,BTH,Perishable,CABAI,14,382,382,"573.000,00",0,0,0,"191.000,00","3.000,00","767.000,00",0,"22.920,00",0,"789.920,00"',
            ',,Total Kuitansi BTHPID2610000412,,,,,,,,,,,,14,382,382,"573.000,00",0,0,0,"191.000,00","3.000,00","767.000,00",0,"22.920,00",0,"789.920,00"',
            '4,BTHPID2610000412-TRIP2,BTHCID26100000371,920-02197322,(IU) Super Air Jet,01-Oct-26,PT. JASAMITRA EKSPRES,,,- ,ANDROMEDA GROUP,YIA,BTH,Perishable,CABAI PART 2,5,100,100,"150.000,00",0,0,0,"50.000,00","3.000,00","200.000,00",0,"6.000,00",0,"206.000,00"',
        ]);

        $uploadedFile = \Illuminate\Http\UploadedFile::fake()->createWithContent('laporan_ias_oktober.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('ias.payments.import'), [
            'file_import' => $uploadedFile,
        ]);

        $response->assertRedirect(route('ias.payments.index'));
        $response->assertSessionHas('success');

        // Pastikan hanya 4 baris data yang terimpor (3 baris subtotal hijau dilewati)
        $this->assertDatabaseCount('ias_payments', 4);

        // Record 1: Garuda Indonesia
        $p1 = \App\Models\IasPayment::where('no_kuitansi', 'BTHPID2610000092')->first();
        $this->assertNotNull($p1);
        $this->assertEquals('126-05166770', $p1->nomor_smu);
        $this->assertEquals('(GA) Garuda Indonesia', $p1->airline);
        $this->assertEquals('2026-10-01', $p1->tanggal->format('Y-m-d'));
        $this->assertEquals(14, $p1->berat_kg);
        $this->assertEquals(21000, (float) $p1->handling);
        $this->assertEquals(3000, (float) $p1->adm_fee);
        $this->assertEquals(840, (float) $p1->pndp);
        $this->assertEquals(31840, (float) $p1->nominal);
        // Cashback otomatis Rp 200/kg: 14 kg * 200 = 2.800
        $this->assertEquals(2800, (float) $p1->cashback);

        // Record 2: Rimbun Air
        $p2 = \App\Models\IasPayment::where('no_kuitansi', 'BTHPID2610000248')->first();
        $this->assertNotNull($p2);
        $this->assertEquals('273-06943882', $p2->nomor_smu);
        $this->assertEquals(60680, (float) $p2->nominal);
        $this->assertEquals(5600, (float) $p2->cashback); // 28 * 200

        // Record 3: Super Air Jet Trip 1 (cocok dengan Worksheet)
        $p3 = \App\Models\IasPayment::where('no_kuitansi', 'BTHPID2610000412')->first();
        $this->assertNotNull($p3);
        $this->assertEquals('920-02197322', $p3->nomor_smu);
        $this->assertEquals(789920, (float) $p3->nominal);
        $this->assertEquals(76400, (float) $p3->cashback); // 382 * 200
        $this->assertEquals($ws->id, $p3->worksheet_id);

        // Record 4: Super Air Jet Trip 2 (SMU sama 920-02197322, tetap tersimpan!)
        $p4 = \App\Models\IasPayment::where('no_kuitansi', 'BTHPID2610000412-TRIP2')->first();
        $this->assertNotNull($p4);
        $this->assertEquals('920-02197322', $p4->nomor_smu);
        $this->assertEquals(206000, (float) $p4->nominal);
        $this->assertEquals(20000, (float) $p4->cashback); // 100 * 200
    }
}

