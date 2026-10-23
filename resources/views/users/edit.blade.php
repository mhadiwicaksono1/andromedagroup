@extends('layouts.cargo')

@section('title', 'Edit Pengguna — ' . $user->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Edit Pengguna</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Perbarui profil, hak akses, atau kata sandi pengguna.</p>
        </div>
        <a href="{{ route('users.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form -->
    <div class="uk-card uk-card-default border border-border p-6">
        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Nama Lengkap *
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="uk-input text-xs">
                    @error('name')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Email Pengguna *
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="uk-input text-xs">
                    @error('email')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Peran (Role) *
                    </label>
                    <select name="role" required class="uk-select text-xs">
                        @foreach($roles as $r)
                            <option value="{{ $r->slug }}" {{ old('role', $user->role) === $r->slug ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Status Akun *
                    </label>
                    <select name="status" required class="uk-select text-xs">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="p-4 rounded-lg bg-muted/40 border border-border space-y-3 pt-3">
                <span class="text-xs font-bold text-foreground block">Ubah Password (Kosongkan jika tidak ingin mengubah)</span>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                            Password Baru
                        </label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter"
                               class="uk-input text-xs font-mono">
                        @error('password')
                            <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                            Konfirmasi Password Baru
                        </label>
                        <input type="password" name="password_confirmation" placeholder="Ketik ulang password baru"
                               class="uk-input text-xs font-mono">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-2 border-t border-border">
                <a href="{{ route('users.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs shadow-xs">
                    Batal
                </a>
                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
