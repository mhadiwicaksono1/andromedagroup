@extends('layouts.cargo')

@section('title', '403 Akses Ditolak — Andromeda Cargo')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-16 h-16 rounded-2xl bg-destructive/10 text-destructive border border-destructive/20 flex items-center justify-center mx-auto shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-3.5a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <div class="space-y-2">
            <span class="text-xs font-mono font-bold uppercase tracking-widest text-destructive">Kode Status: 403 Forbidden</span>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">Akses Halaman Dibatasi</h1>
            <p class="text-xs text-muted-foreground leading-relaxed">
                Akun Anda terdaftar dengan peran <span class="font-bold text-foreground uppercase">({{ auth()->user()->role ?? 'Tamu' }})</span> dan tidak memiliki hak akses untuk membuka halaman atau melakukan tindakan ini.
            </p>
        </div>

        <div class="p-4 rounded-xl border border-border bg-muted/40 text-left text-xs space-y-2">
            <div class="font-semibold text-foreground flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                Informasi Wewenang Peran:
            </div>
            <ul class="space-y-1 text-muted-foreground text-[11px] list-disc list-inside">
                <li><strong>Operasional:</strong> Pengelolaan Job Order Lembar Kerja SMU &amp; Master Pelanggan.</li>
                <li><strong>Keuangan:</strong> Penerbitan Nota Penagihan, Pembayaran Piutang &amp; Pembayaran IAS.</li>
                <li><strong>Administrator:</strong> Akses penuh seluruh sistem dan Manajemen Pengguna.</li>
            </ul>
        </div>

        <div class="flex items-center justify-center gap-3 pt-2">
            <button onclick="window.history.back()" class="uk-btn uk-btn-default uk-btn-sm text-xs">
                &larr; Kembali
            </button>
            <a href="{{ route('cargo.dashboard') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs shadow-sm">
                Ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
