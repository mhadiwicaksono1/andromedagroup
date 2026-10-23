<?php

namespace App\Http\Controllers;

use App\Models\Role as RoleModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $role = $request->query('role');
        $status = $request->query('status');

        $query = User::with('roleModel');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();
        $roles = RoleModel::all();

        return view('users.index', compact('users', 'search', 'role', 'status', 'roles'));
    }

    public function create()
    {
        $roles = RoleModel::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validRoles = RoleModel::pluck('slug')->toArray();
        if (empty($validRoles)) {
            $validRoles = ['admin', 'operasional', 'keuangan'];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => ['required', Rule::in($validRoles)],
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $roleModel = RoleModel::where('slug', $validated['role'])->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'role_id' => $roleModel?->id,
            'status' => $validated['status'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        $roles = RoleModel::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validRoles = RoleModel::pluck('slug')->toArray();
        if (empty($validRoles)) {
            $validRoles = ['admin', 'operasional', 'keuangan'];
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in($validRoles)],
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $roleModel = RoleModel::where('slug', $validated['role'])->first();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->role_id = $roleModel?->id;
        $user->status = $validated['status'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        // Hindari menghapus admin terakhir
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus satu-satunya Administrator sistem.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$userName} berhasil dihapus.");
    }
}
