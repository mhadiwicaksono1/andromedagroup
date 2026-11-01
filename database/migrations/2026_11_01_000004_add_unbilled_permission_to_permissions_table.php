<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $perm = Permission::firstOrCreate(
            ['slug' => 'worksheets.unbilled'],
            [
                'name' => 'Menu Belum Ternota (Lihat & Buat Nota)',
                'group' => 'Belum Ternota',
                'description' => 'Mengakses menu Belum Ternota untuk melihat rekapitulasi kargo per customer dan membuat nota langsung',
            ]
        );
        $perm->update([
            'name' => 'Menu Belum Ternota (Lihat & Buat Nota)',
            'group' => 'Belum Ternota',
            'description' => 'Mengakses menu Belum Ternota untuk melihat rekapitulasi kargo per customer dan membuat nota langsung',
        ]);

        // Assign to Admin, Operasional, and Keuangan roles by default
        $roles = Role::whereIn('slug', ['admin', 'operasional', 'keuangan'])->get();
        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $perm = Permission::where('slug', 'worksheets.unbilled')->first();
        if ($perm) {
            DB::table('role_permission')->where('permission_id', $perm->id)->delete();
            $perm->delete();
        }
    }
};
