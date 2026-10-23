<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_access_to_all_modules()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $routes = [
            route('cargo.dashboard'),
            route('customers.index'),
            route('worksheets.index'),
            route('worksheets.create'),
            route('invoices.index'),
            route('payments.index'),
            route('ias.payments.index'),
            route('reports.worksheets'),
            route('reports.invoices'),
            route('reports.receivables'),
            route('reports.profit_loss'),
            route('users.index'),
            route('users.create'),
            route('roles.index'),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($admin)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_operasional_can_access_worksheets_and_customers_but_restricted_from_finance_and_users()
    {
        $operasional = User::factory()->create(['role' => 'operasional', 'status' => 'active']);

        // Allowed routes
        $allowedRoutes = [
            route('cargo.dashboard'),
            route('customers.index'),
            route('worksheets.index'),
            route('worksheets.create'),
            route('reports.worksheets'),
        ];

        foreach ($allowedRoutes as $url) {
            $response = $this->actingAs($operasional)->get($url);
            $response->assertStatus(200);
        }

        // Restricted finance & user routes
        $restrictedRoutes = [
            route('invoices.index'),
            route('payments.index'),
            route('ias.payments.index'),
            route('reports.invoices'),
            route('reports.receivables'),
            route('reports.profit_loss'),
            route('users.index'),
            route('users.create'),
            route('roles.index'),
        ];

        foreach ($restrictedRoutes as $url) {
            $response = $this->actingAs($operasional)->get($url);
            $response->assertStatus(403);
            $response->assertSee('Akses Halaman Dibatasi');
        }
    }

    public function test_keuangan_can_access_finance_and_view_worksheets_but_cannot_create_worksheets_or_manage_users()
    {
        $keuangan = User::factory()->create(['role' => 'keuangan', 'status' => 'active']);

        // Allowed routes
        $allowedRoutes = [
            route('cargo.dashboard'),
            route('customers.index'),
            route('worksheets.index'),
            route('invoices.index'),
            route('payments.index'),
            route('ias.payments.index'),
            route('reports.worksheets'),
            route('reports.invoices'),
            route('reports.receivables'),
            route('reports.profit_loss'),
        ];

        foreach ($allowedRoutes as $url) {
            $response = $this->actingAs($keuangan)->get($url);
            $response->assertStatus(200);
        }

        // Restricted operational creation & user management routes
        $restrictedRoutes = [
            route('worksheets.create'),
            route('users.index'),
            route('users.create'),
            route('roles.index'),
        ];

        foreach ($restrictedRoutes as $url) {
            $response = $this->actingAs($keuangan)->get($url);
            $response->assertStatus(403);
            $response->assertSee('Akses Halaman Dibatasi');
        }
    }

    public function test_sidebar_menus_are_hidden_for_operasional()
    {
        $operasional = User::factory()->create(['role' => 'operasional', 'status' => 'active']);

        $response = $this->actingAs($operasional)->get(route('cargo.dashboard'));
        $response->assertStatus(200);

        // Should see operational menus
        $response->assertSee('Lembar Kerja (SMU)');
        $response->assertSee('Master Customer');
        $response->assertSee('Lap. Lembar Kerja');

        // Should NOT see financial and user menus
        $response->assertDontSee('Nota &amp; Piutang', false);
        $response->assertDontSee('Pembayaran Piutang');
        $response->assertDontSee('Pembayaran ke IAS');
        $response->assertDontSee('Laporan Laba Rugi');
        $response->assertDontSee('Manajemen User');
        $response->assertDontSee('Hak Akses Role');
    }

    public function test_sidebar_menus_are_hidden_for_keuangan()
    {
        $keuangan = User::factory()->create(['role' => 'keuangan', 'status' => 'active']);

        $response = $this->actingAs($keuangan)->get(route('cargo.dashboard'));
        $response->assertStatus(200);

        // Should see financial menus
        $response->assertSee('Lembar Kerja (SMU)');
        $response->assertSee('Master Customer');
        $response->assertSee('Nota &amp; Piutang', false);
        $response->assertSee('Pembayaran Piutang');
        $response->assertSee('Pembayaran ke IAS');
        $response->assertSee('Laporan Laba Rugi');

        // Should NOT see user management
        $response->assertDontSee('Manajemen User');
        $response->assertDontSee('Hak Akses Role');
    }

    public function test_admin_can_dynamically_grant_and_revoke_permissions_for_a_role()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $operasionalUser = User::factory()->create(['role' => 'operasional', 'status' => 'active']);

        $operasionalRole = Role::where('slug', 'operasional')->first();
        $invoicePerm = Permission::where('slug', 'invoices.view')->first();

        // 1. Initially operasional cannot access invoices
        $response = $this->actingAs($operasionalUser)->get(route('invoices.index'));
        $response->assertStatus(403);

        // 2. Admin grants invoices.view permission to operasional role via update
        $newPermIds = array_merge(
            $operasionalRole->permissions->pluck('id')->toArray(),
            [$invoicePerm->id]
        );

        $response = $this->actingAs($admin)->put(route('roles.update', $operasionalRole->id), [
            'name' => 'Operasional Modifikasi',
            'permissions' => $newPermIds,
        ]);
        $response->assertRedirect(route('roles.index'));

        // 3. Immediately operasional can now access invoices!
        $operasionalRole->refresh();
        $this->assertTrue($operasionalRole->hasPermission('invoices.view'));

        $response = $this->actingAs($operasionalUser)->get(route('invoices.index'));
        $response->assertStatus(200);

        // 4. Admin revokes invoices.view permission
        $revokedPermIds = array_diff($newPermIds, [$invoicePerm->id]);
        $this->actingAs($admin)->put(route('roles.update', $operasionalRole->id), [
            'name' => 'Operasional Modifikasi',
            'permissions' => $revokedPermIds,
        ]);

        // 5. Immediately operasional is restricted again (403)
        $response = $this->actingAs($operasionalUser)->get(route('invoices.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_custom_role_with_specific_permissions()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $wsViewPerm = Permission::where('slug', 'worksheets.view')->first();
        $reportPerm = Permission::where('slug', 'reports.worksheets')->first();

        // Create new role: Supervisor Auditor
        $response = $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'Supervisor Auditor',
            'slug' => 'supervisor-auditor',
            'description' => 'Khusus memantau laporan dan data lembar kerja',
            'permissions' => [$wsViewPerm->id, $reportPerm->id],
        ]);
        $response->assertRedirect(route('roles.index'));

        $customRole = Role::where('slug', 'supervisor-auditor')->first();
        $this->assertNotNull($customRole);
        $this->assertTrue($customRole->hasPermission('worksheets.view'));
        $this->assertTrue($customRole->hasPermission('reports.worksheets'));
        $this->assertFalse($customRole->hasPermission('invoices.view'));

        // Assign user to this new role
        $auditorUser = User::factory()->create([
            'role' => $customRole->slug,
            'role_id' => $customRole->id,
            'status' => 'active',
        ]);

        $this->actingAs($auditorUser)->get(route('worksheets.index'))->assertStatus(200);
        $this->actingAs($auditorUser)->get(route('reports.worksheets'))->assertStatus(200);
        $this->actingAs($auditorUser)->get(route('invoices.index'))->assertStatus(403);
    }
}
