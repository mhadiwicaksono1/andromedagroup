@extends('layouts.cargo')

@section('title', 'Kelola Role & Hak Akses — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span>Kelola Peran &amp; Hak Akses (Role &amp; Permissions)</span>
            </h1>
            <p class="text-xs text-muted-foreground mt-0.5">
                Konfigurasi izin akses modul dan fitur aplikasi untuk masing-masing peran staf secara fleksibel.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('roles.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Peran Baru</span>
            </a>
        </div>
    </div>


    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($roles as $role)
            @php
                $permCount = $role->slug === 'admin' ? $totalPermissions : $role->permissions_count;
                $pct = $totalPermissions > 0 ? round(($permCount / $totalPermissions) * 100) : 0;
            @endphp
            <div class="uk-card uk-card-default border border-border rounded-xl p-5 flex flex-col justify-between hover:border-primary/50 transition-all duration-200 shadow-xs relative overflow-hidden">
                @if($role->is_system)
                    <div class="absolute top-0 right-0 bg-primary/10 text-primary text-[10px] font-semibold px-2.5 py-0.5 rounded-bl-lg border-l border-b border-primary/20">
                        Role Sistem
                    </div>
                @endif

                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $role->slug === 'admin' ? 'bg-indigo-500' : ($role->slug === 'operasional' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                        <h2 class="text-sm font-bold text-foreground">{{ $role->name }}</h2>
                        <span class="text-[10px] font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded">
                            {{ $role->slug }}
                        </span>
                    </div>

                    <p class="text-xs text-muted-foreground line-clamp-2 min-h-[34px] mb-4">
                        {{ $role->description ?? 'Tidak ada keterangan khusus untuk peran ini.' }}
                    </p>

                    <!-- Stats Badge -->
                    <div class="grid grid-cols-2 gap-2 mb-4 p-2.5 rounded-lg bg-muted/40 border border-border/60 text-xs">
                        <div>
                            <div class="text-[10px] text-muted-foreground uppercase tracking-wider">Pengguna</div>
                            <div class="font-bold text-foreground text-sm flex items-center gap-1 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>{{ $role->users_count }} Akun</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] text-muted-foreground uppercase tracking-wider">Hak Akses</div>
                            <div class="font-bold text-foreground text-sm flex items-center gap-1 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span>{{ $permCount }} / {{ $totalPermissions }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-[11px] text-muted-foreground mb-1">
                            <span>Cakupan Wewenang</span>
                            <span class="font-semibold text-foreground">{{ $pct }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-muted rounded-full overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-3 border-t border-border flex items-center justify-between gap-2 mt-auto">
                    <a href="{{ route('roles.edit', $role->id) }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex-1 flex items-center justify-center gap-1.5 hover:border-primary">
                        <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>Atur Hak Akses</span>
                    </a>

                    @if(!$role->is_system)
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peran {{ $role->name }}? Pastikan tidak ada pengguna yang sedang memakai peran ini.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-destructive hover:bg-destructive/10" title="Hapus Peran">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Info Box -->
    <div class="p-4 rounded-xl border border-border bg-card/60 text-xs text-muted-foreground flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-start sm:items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <span class="font-semibold text-foreground">Sistem Hak Akses Real-Time:</span>
                Setiap perubahan hak akses yang Anda simpan akan langsung berlaku seketika pada menu navigasi dan rute halaman seluruh staf terkait tanpa perlu merestart server.
            </div>
        </div>
        <a href="{{ route('users.index') }}" class="text-xs text-primary font-semibold hover:underline shrink-0 flex items-center gap-1">
            <span>Buka Data Pengguna</span>
            <span>&rarr;</span>
        </a>
    </div>
</div>
@endsection
