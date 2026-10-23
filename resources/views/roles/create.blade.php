@extends('layouts.cargo')

@section('title', 'Tambah Peran Baru — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('roles.index') }}" class="text-xs text-muted-foreground hover:text-foreground flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Daftar Peran</span>
                </a>
                <span class="text-muted-foreground/40 text-xs">/</span>
                <span class="text-xs font-semibold text-foreground">Buat Peran Baru</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Peran (Role) Baru</span>
            </h1>
            <p class="text-xs text-muted-foreground mt-0.5">
                Definisikan nama peran baru dan pilih hak akses yang akan diberikan pada peran ini.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('roles.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <span>Batal / Kembali</span>
            </a>
            <button type="button" id="btn-check-all" class="uk-btn uk-btn-secondary uk-btn-sm text-xs">
                Pilih Semua Modul
            </button>
            <button type="button" id="btn-uncheck-all" class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-destructive">
                Kosongkan Semua
            </button>
        </div>
    </div>

    <form action="{{ route('roles.store') }}" method="POST" id="role-create-form" class="space-y-6">
        @csrf

        <!-- General Info Card -->
        <div class="uk-card uk-card-default border border-border rounded-xl p-5 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Nama Peran *
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Supervisor Kargo, Auditor Internal"
                           class="uk-input text-xs">
                    @error('name')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Slug / Kode Unik (Opsional)
                    </label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="Otomatis dari nama (contoh: supervisor-kargo)"
                           class="uk-input text-xs font-mono">
                    @error('slug')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Keterangan / Deskripsi
                    </label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Deskripsi tugas peran ini..."
                           class="uk-input text-xs">
                    @error('description')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Permission Matrix Groups -->
        <div class="space-y-5">
            <div class="flex items-center justify-between border-b border-border pb-2">
                <div class="text-xs font-bold text-foreground uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <span>Daftar Hak Akses Berdasarkan Modul</span>
                </div>
                <span class="text-[11px] text-muted-foreground">Centang fitur yang diizinkan untuk peran ini</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @foreach($permissionsByGroup as $groupName => $permissions)
                    @php
                        $groupTotal = count($permissions);
                    @endphp
                    <div class="uk-card uk-card-default border border-border rounded-xl p-4 shadow-2xs hover:border-border/80 transition-colors flex flex-col justify-between group-box">
                        <!-- Group Header -->
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-border">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                                <h2 class="text-xs font-bold text-foreground">{{ $groupName }}</h2>
                                <span class="text-[10px] bg-muted text-muted-foreground px-1.5 py-0.5 rounded font-mono group-count-badge">
                                    0 / {{ $groupTotal }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" class="btn-toggle-group text-[11px] font-semibold text-primary hover:underline" data-action="check">
                                    Pilih Semua
                                </button>
                                <span class="text-muted-foreground/30 text-[10px]">|</span>
                                <button type="button" class="btn-toggle-group text-[11px] text-muted-foreground hover:text-destructive hover:underline" data-action="uncheck">
                                    Kosongkan
                                </button>
                            </div>
                        </div>

                        <!-- Checkbox Items -->
                        <div class="space-y-2.5 my-1">
                            @foreach($permissions as $perm)
                                @php
                                    $isChecked = is_array(old('permissions')) && in_array($perm->id, old('permissions'));
                                @endphp
                                <label class="flex items-start gap-2.5 p-2 rounded-lg hover:bg-muted/50 cursor-pointer transition-colors border border-transparent hover:border-border/50 {{ $isChecked ? 'bg-muted/20' : '' }}">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                           class="perm-checkbox mt-0.5 rounded border-border text-primary focus:ring-primary w-4 h-4 cursor-pointer"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-semibold text-foreground block">{{ $perm->name }}</span>
                                            <span class="text-[9px] font-mono text-muted-foreground bg-muted/60 px-1 py-0.5 rounded shrink-0">
                                                {{ $perm->slug }}
                                            </span>
                                        </div>
                                        @if($perm->description)
                                            <p class="text-[11px] text-muted-foreground mt-0.5 leading-snug">
                                                {{ $perm->description }}
                                            </p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sticky Bottom Bar -->
        <div class="sticky bottom-4 z-20 p-4 rounded-xl border border-border bg-card/95 backdrop-blur-md shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Setelah peran tersimpan, Anda dapat langsung mengaitkannya ke pengguna baru atau yang sudah ada.</span>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('roles.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs">
                    Batal
                </a>
                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Simpan Peran Baru</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Script for Toggle All and Group Helpers -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateGroupCounts() {
        document.querySelectorAll('.group-box').forEach(box => {
            const total = box.querySelectorAll('.perm-checkbox').length;
            const checked = box.querySelectorAll('.perm-checkbox:checked').length;
            const badge = box.querySelector('.group-count-badge');
            if (badge) {
                badge.textContent = `${checked} / ${total}`;
            }
        });
    }

    document.querySelectorAll('.btn-toggle-group').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const groupBox = this.closest('.group-box');
            if (!groupBox) return;

            const checkboxes = groupBox.querySelectorAll('.perm-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = (action === 'check');
                cb.closest('label').classList.toggle('bg-muted/20', cb.checked);
            });
            updateGroupCounts();
        });
    });

    document.getElementById('btn-check-all')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = true;
            cb.closest('label').classList.add('bg-muted/20');
        });
        updateGroupCounts();
    });

    document.getElementById('btn-uncheck-all')?.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('label').classList.remove('bg-muted/20');
        });
        updateGroupCounts();
    });

    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            this.closest('label').classList.toggle('bg-muted/20', this.checked);
            updateGroupCounts();
        });
    });

    updateGroupCounts();
});
</script>
@endsection
