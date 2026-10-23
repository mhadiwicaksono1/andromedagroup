<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // 2. Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group'); // e.g. Lembar Kerja, Nota & Piutang, dll.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Role-Permission pivot table
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        // 4. Add role_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
        });

        // 5. Seed default roles
        $now = now();
        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Akses penuh tanpa batas ke seluruh modul, pengaturan, dan manajemen hak akses sistem.',
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $operasionalRoleId = DB::table('roles')->insertGetId([
            'name' => 'Operasional',
            'slug' => 'operasional',
            'description' => 'Bertanggung jawab atas operasional fisik kargo, Job Order Lembar Kerja SMU, dan data customer.',
            'is_system' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $keuanganRoleId = DB::table('roles')->insertGetId([
            'name' => 'Keuangan & Billing',
            'slug' => 'keuangan',
            'description' => 'Bertanggung jawab atas penerbitan nota penagihan (invoice), pelunasan piutang, dan pembayaran IAS.',
            'is_system' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 6. Seed standard permissions
        $permissions = [
            // Lembar Kerja (SMU)
            ['name' => 'Lihat Lembar Kerja SMU', 'slug' => 'worksheets.view', 'group' => 'Lembar Kerja SMU', 'description' => 'Melihat daftar dan rincian lembar kerja kargo'],
            ['name' => 'Buat Lembar Kerja SMU', 'slug' => 'worksheets.create', 'group' => 'Lembar Kerja SMU', 'description' => 'Membuat dan menerbitkan Job Order lembar kerja baru'],
            ['name' => 'Edit Lembar Kerja SMU', 'slug' => 'worksheets.edit', 'group' => 'Lembar Kerja SMU', 'description' => 'Mengubah data rincian lembar kerja yang masih berjalan'],
            ['name' => 'Update Status Lembar Kerja', 'slug' => 'worksheets.status', 'group' => 'Lembar Kerja SMU', 'description' => 'Menandai status lembar kerja selesai atau batal'],
            ['name' => 'Hapus Lembar Kerja SMU', 'slug' => 'worksheets.delete', 'group' => 'Lembar Kerja SMU', 'description' => 'Menghapus data lembar kerja'],

            // Master Customer
            ['name' => 'Lihat Data Customer', 'slug' => 'customers.view', 'group' => 'Master Customer', 'description' => 'Melihat daftar customer dan kartu mutasi piutang'],
            ['name' => 'Tambah Customer Baru', 'slug' => 'customers.create', 'group' => 'Master Customer', 'description' => 'Mendaftarkan customer/pelanggan baru ke sistem'],
            ['name' => 'Edit Data Customer', 'slug' => 'customers.edit', 'group' => 'Master Customer', 'description' => 'Mengubah informasi kontak dan profil customer'],
            ['name' => 'Hapus / Nonaktifkan Customer', 'slug' => 'customers.delete', 'group' => 'Master Customer', 'description' => 'Menghapus atau menonaktifkan status customer'],

            // Nota Penagihan & Piutang (Invoice)
            ['name' => 'Lihat Nota Penagihan', 'slug' => 'invoices.view', 'group' => 'Nota & Piutang', 'description' => 'Melihat daftar dan rincian nota tagihan customer'],
            ['name' => 'Buat & Terbitkan Nota', 'slug' => 'invoices.create', 'group' => 'Nota & Piutang', 'description' => 'Menerbitkan nota penagihan baru dari lembar kerja'],
            ['name' => 'Cetak Nota Penagihan', 'slug' => 'invoices.print', 'group' => 'Nota & Piutang', 'description' => 'Mencetak dokumen resmi nota penagihan customer'],
            ['name' => 'Hapus / Batalkan Nota', 'slug' => 'invoices.delete', 'group' => 'Nota & Piutang', 'description' => 'Membatalkan atau menghapus nota penagihan'],

            // Penerimaan Pembayaran Customer
            ['name' => 'Lihat Pembayaran Piutang', 'slug' => 'payments.view', 'group' => 'Pembayaran Piutang', 'description' => 'Melihat histori penerimaan uang dan pembayaran piutang'],
            ['name' => 'Catat Penerimaan Pembayaran', 'slug' => 'payments.create', 'group' => 'Pembayaran Piutang', 'description' => 'Mencatat pelunasan piutang customer dan alokasi nota'],
            ['name' => 'Cetak Kwitansi Pembayaran', 'slug' => 'payments.print', 'group' => 'Pembayaran Piutang', 'description' => 'Mencetak tanda terima / kwitansi pembayaran resmi'],
            ['name' => 'Hapus Catatan Pembayaran', 'slug' => 'payments.delete', 'group' => 'Pembayaran Piutang', 'description' => 'Menghapus atau membatalkan catatan pembayaran kas/bank'],

            // Pembayaran ke IAS
            ['name' => 'Lihat Pembayaran IAS', 'slug' => 'ias_payments.view', 'group' => 'Pembayaran IAS', 'description' => 'Melihat histori pembayaran handling ke IAS'],
            ['name' => 'Catat Pembayaran ke IAS', 'slug' => 'ias_payments.create', 'group' => 'Pembayaran IAS', 'description' => 'Mencatat pengeluaran biaya terminal handling ke IAS'],
            ['name' => 'Import CSV Pembayaran IAS', 'slug' => 'ias_payments.import', 'group' => 'Pembayaran IAS', 'description' => 'Mengunggah dan mengimpor file batch CSV pembayaran IAS'],
            ['name' => 'Hapus Pembayaran IAS', 'slug' => 'ias_payments.delete', 'group' => 'Pembayaran IAS', 'description' => 'Menghapus catatan pembayaran ke IAS'],

            // Laporan & Analisis
            ['name' => 'Laporan Lembar Kerja SMU', 'slug' => 'reports.worksheets', 'group' => 'Laporan & Keuangan', 'description' => 'Mengakses dan mencetak laporan tonase & fisik SMU'],
            ['name' => 'Laporan Nota Penagihan', 'slug' => 'reports.invoices', 'group' => 'Laporan & Keuangan', 'description' => 'Mengakses dan mencetak rekapitulasi nota penagihan'],
            ['name' => 'Laporan Monitoring Piutang', 'slug' => 'reports.receivables', 'group' => 'Laporan & Keuangan', 'description' => 'Mengakses dan mencetak kartu kontrol piutang customer'],
            ['name' => 'Laporan Laba Rugi Kargo', 'slug' => 'reports.profit_loss', 'group' => 'Laporan & Keuangan', 'description' => 'Mengakses dan mencetak kalkulasi pendapatan vs beban IAS'],

            // Pengaturan & Sistem
            ['name' => 'Kelola Data Pengguna', 'slug' => 'users.manage', 'group' => 'Pengaturan & Sistem', 'description' => 'Menambah, mengedit, dan mengelola akun staf pengguna'],
            ['name' => 'Kelola Hak Akses Role', 'slug' => 'roles.manage', 'group' => 'Pengaturan & Sistem', 'description' => 'Mengatur izin modul & permission untuk masing-masing peran'],
        ];

        $permMap = [];
        foreach ($permissions as $p) {
            $permId = DB::table('permissions')->insertGetId([
                'name' => $p['name'],
                'slug' => $p['slug'],
                'group' => $p['group'],
                'description' => $p['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $permMap[$p['slug']] = $permId;
        }

        // 7. Assign permissions to Admin (All)
        foreach ($permMap as $permId) {
            DB::table('role_permission')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 8. Assign permissions to Operasional
        $operasionalPerms = [
            'worksheets.view', 'worksheets.create', 'worksheets.edit', 'worksheets.status', 'worksheets.delete',
            'customers.view', 'customers.create', 'customers.edit',
            'reports.worksheets',
        ];
        foreach ($operasionalPerms as $slug) {
            if (isset($permMap[$slug])) {
                DB::table('role_permission')->insert([
                    'role_id' => $operasionalRoleId,
                    'permission_id' => $permMap[$slug],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 9. Assign permissions to Keuangan
        $keuanganPerms = [
            'worksheets.view',
            'customers.view',
            'invoices.view', 'invoices.create', 'invoices.print', 'invoices.delete',
            'payments.view', 'payments.create', 'payments.print', 'payments.delete',
            'ias_payments.view', 'ias_payments.create', 'ias_payments.import', 'ias_payments.delete',
            'reports.worksheets', 'reports.invoices', 'reports.receivables', 'reports.profit_loss',
        ];
        foreach ($keuanganPerms as $slug) {
            if (isset($permMap[$slug])) {
                DB::table('role_permission')->insert([
                    'role_id' => $keuanganRoleId,
                    'permission_id' => $permMap[$slug],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 10. Link existing users in database to roles table
        DB::table('users')->where('role', 'admin')->update(['role_id' => $adminRoleId]);
        DB::table('users')->where('role', 'operasional')->update(['role_id' => $operasionalRoleId]);
        DB::table('users')->where('role', 'keuangan')->update(['role_id' => $keuanganRoleId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
