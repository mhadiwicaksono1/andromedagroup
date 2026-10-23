<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Worksheet;
use App\Models\WorksheetItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@andromeda.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $ops = User::updateOrCreate(
            ['email' => 'operasional@andromeda.com'],
            [
                'name' => 'Staf Operasional',
                'password' => Hash::make('password'),
                'role' => 'operasional',
                'status' => 'active',
            ]
        );

        $fin = User::updateOrCreate(
            ['email' => 'keuangan@andromeda.com'],
            [
                'name' => 'Staf Keuangan',
                'password' => Hash::make('password'),
                'role' => 'keuangan',
                'status' => 'active',
            ]
        );

        // 2. Customers
        $c1 = Customer::create([
            'kode_customer' => 'CUST-0001',
            'nama' => 'PT. Samudera Logistik Jaya',
            'telepon' => '0812-3456-7890',
            'email' => 'finance@samuderalogistik.com',
            'alamat' => 'Kawasan Pergudangan Soewarna Blok B No. 12, Bandara Soekarno-Hatta, Tangerang',
            'status' => 'active',
        ]);

        $c2 = Customer::create([
            'kode_customer' => 'CUST-0002',
            'nama' => 'CV. Berkah Kargo Nusantara',
            'telepon' => '0821-9876-5432',
            'email' => 'berkahkargo@gmail.com',
            'alamat' => 'Jl. Margomulyo Indah No. 45, Surabaya',
            'status' => 'active',
        ]);

        $c3 = Customer::create([
            'kode_customer' => 'CUST-0003',
            'nama' => 'PT. Angkasa Express Mandiri',
            'telepon' => '0856-1122-3344',
            'email' => 'ops@angkasaexpress.co.id',
            'alamat' => 'Jl. Halim Perdanakusuma No. 8, Jakarta Timur',
            'status' => 'active',
        ]);

        $c4 = Customer::create([
            'kode_customer' => 'CUST-0004',
            'nama' => 'Toko Mitra Cemerlang (Medan)',
            'telepon' => '0878-5566-7788',
            'email' => 'mitracemerlang@yahoo.com',
            'alamat' => 'Jl. Gatot Subroto No. 102, Medan',
            'status' => 'active',
        ]);

        // 3. Master Worksheets (1 SMU has multiple customers)
        // SMU 1: GA-88291038 (Garuda Jakarta - Surabaya) -> Customers: PT. Samudera & CV. Berkah
        $ws1 = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-202610-0001',
            'tanggal' => now()->subDays(10)->toDateString(),
            'nomor_smu' => 'GA-88291038',
            'keterangan' => 'Penerbangan GA-312 CGK - SUB (Konsolidasi)',
            'status' => 'selesai',
            'user_id' => $ops->id,
        ]);

        $item1_1 = WorksheetItem::create([
            'worksheet_id' => $ws1->id,
            'customer_id' => $c1->id,
            'jumlah_koli' => 12,
            'berat_kg' => 240.50,
            'tarif_per_kg' => 15000,
            'total_biaya' => 240.50 * 15000, // 3.607.500
            'keterangan' => 'Sparepart Mesin',
            'status' => 'dinotakan',
        ]);

        $item1_2 = WorksheetItem::create([
            'worksheet_id' => $ws1->id,
            'customer_id' => $c2->id,
            'jumlah_koli' => 8,
            'berat_kg' => 150.00,
            'tarif_per_kg' => 14000,
            'total_biaya' => 150.00 * 14000, // 2.100.000
            'keterangan' => 'Barang Elektronik & Aksesoris',
            'status' => 'selesai',
        ]);

        $ws1->recalculateTotals();

        // SMU 2: JT-44910214 (Lion Jakarta - Denpasar) -> Customers: PT. Samudera & PT. Angkasa
        $ws2 = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-202610-0002',
            'tanggal' => now()->subDays(8)->toDateString(),
            'nomor_smu' => 'JT-44910214',
            'keterangan' => 'Penerbangan JT-024 CGK - DPS',
            'status' => 'selesai',
            'user_id' => $ops->id,
        ]);

        $item2_1 = WorksheetItem::create([
            'worksheet_id' => $ws2->id,
            'customer_id' => $c1->id,
            'jumlah_koli' => 8,
            'berat_kg' => 110.00,
            'tarif_per_kg' => 18000,
            'total_biaya' => 110.00 * 18000, // 1.980.000
            'keterangan' => 'Kargo Garmen Garment Fashion',
            'status' => 'dinotakan',
        ]);

        $item2_2 = WorksheetItem::create([
            'worksheet_id' => $ws2->id,
            'customer_id' => $c3->id,
            'jumlah_koli' => 6,
            'berat_kg' => 95.00,
            'tarif_per_kg' => 17500,
            'total_biaya' => 95.00 * 17500, // 1.662.500
            'keterangan' => 'Produk Perikanan Kemasan Box',
            'status' => 'selesai',
        ]);

        $ws2->recalculateTotals();

        // Invoice 1 untuk PT. Samudera (mencakup item1_1 dan item2_1)
        $subtotalInv1 = $item1_1->total_biaya + $item2_1->total_biaya; // 5.587.500
        $inv1 = Invoice::create([
            'nomor_nota' => 'INV/202610/0001',
            'tanggal' => now()->subDays(7)->toDateString(),
            'customer_id' => $c1->id,
            'sisa_hutang_sebelumnya' => 0,
            'subtotal_lembar_kerja' => $subtotalInv1,
            'penyesuaian_biaya' => 0,
            'total_nota' => $subtotalInv1,
            'total_tagihan_kumulatif' => $subtotalInv1,
            'jumlah_dibayar' => 3000000,
            'sisa_piutang' => $subtotalInv1 - 3000000, // 2.587.500
            'status_pembayaran' => 'sebagian',
            'catatan' => 'Pembayaran tahap 1 ditransfer Rp 3.000.000 via BCA',
            'user_id' => $fin->id,
        ]);

        $item1_1->update(['invoice_id' => $inv1->id]);
        $item2_1->update(['invoice_id' => $inv1->id]);

        Payment::create([
            'nomor_pembayaran' => 'PAY/202610/0001',
            'tanggal' => now()->subDays(6)->toDateString(),
            'invoice_id' => $inv1->id,
            'customer_id' => $c1->id,
            'jumlah' => 3000000,
            'metode' => 'transfer',
            'catatan' => 'Uang muka transfer BCA',
            'user_id' => $fin->id,
        ]);

        // SMU 3: GA-99104521 (Garuda Jakarta - Balikpapan) -> PT. Samudera & Toko Mitra (keduanya berstatus selesai/siap dinotakan)
        $ws3 = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-202610-0003',
            'tanggal' => now()->subDays(3)->toDateString(),
            'nomor_smu' => 'GA-99104521',
            'keterangan' => 'Penerbangan GA-550 CGK - BPN',
            'status' => 'selesai',
            'user_id' => $ops->id,
        ]);

        WorksheetItem::create([
            'worksheet_id' => $ws3->id,
            'customer_id' => $c1->id,
            'jumlah_koli' => 15,
            'berat_kg' => 320.00,
            'tarif_per_kg' => 15000,
            'total_biaya' => 320.00 * 15000, // 4.800.000
            'keterangan' => 'Pengiriman Elektronik Komputer',
            'status' => 'selesai',
        ]);

        WorksheetItem::create([
            'worksheet_id' => $ws3->id,
            'customer_id' => $c4->id,
            'jumlah_koli' => 10,
            'berat_kg' => 180.00,
            'tarif_per_kg' => 16000,
            'total_biaya' => 180.00 * 16000, // 2.880.000
            'keterangan' => 'Bahan Makanan Kering & Kemasan',
            'status' => 'selesai',
        ]);

        $ws3->recalculateTotals();

        // SMU 4: QZ-77120391 (AirAsia) -> Status Draft
        $ws4 = Worksheet::create([
            'nomor_lembar_kerja' => 'LK-202610-0004',
            'tanggal' => now()->toDateString(),
            'nomor_smu' => 'QZ-77120391',
            'keterangan' => 'Penerbangan QZ-7510 CGK - KNO',
            'status' => 'draft',
            'user_id' => $ops->id,
        ]);

        WorksheetItem::create([
            'worksheet_id' => $ws4->id,
            'customer_id' => $c3->id,
            'jumlah_koli' => 5,
            'berat_kg' => 85.50,
            'tarif_per_kg' => 16000,
            'total_biaya' => 85.50 * 16000, // 1.368.000
            'keterangan' => 'Kargo Dokumen Penting & Paket Ekspres',
            'status' => 'draft',
        ]);

        $ws4->recalculateTotals();
    }
}
