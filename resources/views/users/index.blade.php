@extends('layouts.cargo')

@section('title', 'Manajemen Pengguna (User) — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Manajemen Pengguna (User)</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Kelola akun staf operasional, finance/kasir, dan administrator sistem.</p>
        </div>
        <a href="{{ route('users.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 self-start shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah User Baru</span>
        </a>
    </div>

    <!-- Filter Form -->
    <div class="uk-card uk-card-default border border-border p-4">
        <form action="{{ route('users.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..."
                       class="uk-input text-xs w-full">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap">
                <select name="role" class="uk-select text-xs">
                    <option value="">Semua Peran (Role)</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Administrator</option>
                    <option value="operasional" {{ $role === 'operasional' ? 'selected' : '' }}>Operasional Kargo</option>
                    <option value="keuangan" {{ $role === 'keuangan' ? 'selected' : '' }}>Keuangan / Kasir</option>
                </select>

                <select name="status" class="uk-select text-xs">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs shadow-xs">
                    Cari
                </button>
                <a href="{{ route('users.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs shadow-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="bg-muted/40 text-muted-foreground border-b border-border">
                        <th class="py-3 px-4 font-medium">Nama Pengguna</th>
                        <th class="py-3 px-4 font-medium">Email</th>
                        <th class="py-3 px-4 font-medium text-center">Peran / Hak Akses</th>
                        <th class="py-3 px-4 font-medium text-center">Status</th>
                        <th class="py-3 px-4 font-medium">Tanggal Dibuat</th>
                        <th class="py-3 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($users as $user)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-foreground whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $user->name }}</span>
                                    @if(auth()->id() === $user->id)
                                        <span class="text-[9px] font-mono font-bold bg-primary/15 text-primary px-1.5 py-0.5 rounded">AKUN ANDA</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-muted-foreground whitespace-nowrap">
                                {{ $user->email }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($user->role === 'admin')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                        Admin
                                    </span>
                                @elseif($user->role === 'keuangan')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Keuangan
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        Operasional
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($user->status === 'active')
                                    <span class="uk-label uk-label-success text-[10px]">AKTIF</span>
                                @else
                                    <span class="uk-label text-[10px]">NONAKTIF</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground whitespace-nowrap">
                                {{ $user->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('users.edit', $user) }}" class="uk-btn uk-btn-default uk-btn-xs shadow-xs">
                                        Edit
                                    </a>

                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="uk-btn uk-btn-destructive uk-btn-xs shadow-xs">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-muted-foreground">
                                Tidak ada data pengguna yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-border">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
