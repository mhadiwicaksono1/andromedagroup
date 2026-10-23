<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with user counts and permission summaries.
     */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        $totalPermissions = Permission::count();

        return view('roles.index', compact('roles', 'totalPermissions'));
    }

    /**
     * Show form to create a new role.
     */
    public function create()
    {
        $permissionsByGroup = Permission::all()->groupBy('group');

        return view('roles.create', compact('permissionsByGroup'));
    }

    /**
     * Store a new custom role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|alpha_dash|unique:roles,slug',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Check unique slug fallback
        $existingCount = Role::where('slug', $slug)->count();
        if ($existingCount > 0) {
            $slug .= '-' . (Role::max('id') + 1);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')
            ->with('success', "Peran '{$role->name}' berhasil dibuat dengan wewenang yang dipilih.");
    }

    /**
     * Show the permission matrix editor for a specific role.
     */
    public function edit(Role $role)
    {
        $permissionsByGroup = Permission::all()->groupBy('group');
        $rolePermissionIds = $role->permissions()->pluck('permissions.id')->toArray();

        return view('roles.edit', compact('role', 'permissionsByGroup', 'rolePermissionIds'));
    }

    /**
     * Update permissions and details for a role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // If system admin role, name/slug cannot be corrupted and all permissions remain active
        if ($role->is_system && $role->slug === 'admin') {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? $role->description,
            ]);
            // Admin always has all permissions
            $role->syncPermissions(Permission::pluck('id')->toArray());

            return redirect()->route('roles.index')
                ->with('success', "Hak akses Administrator selalu aktif penuh untuk seluruh modul sistem.");
        }

        $role->name = $validated['name'];
        $role->description = $validated['description'] ?? null;
        $role->save();

        // Sync selected permissions
        $selectedPermissions = $request->input('permissions', []);
        $role->syncPermissions($selectedPermissions);

        return redirect()->route('roles.index')
            ->with('success', "Pengaturan hak akses untuk peran '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Remove custom role.
     */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', "Peran sistem bawaan '{$role->name}' tidak dapat dihapus.");
        }

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return redirect()->route('roles.index')
                ->with('error', "Peran '{$role->name}' tidak dapat dihapus karena masih digunakan oleh {$userCount} pengguna.");
        }

        $roleName = $role->name;
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Peran '{$roleName}' berhasil dihapus.");
    }
}
