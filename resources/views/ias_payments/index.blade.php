@extends('layouts.cargo')

@section('title', 'Pembayaran ke IAS (InJourney Aviation Services) — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Pembayaran ke IAS (InJourney Aviation Services)</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Catatan pengeluaran &amp; impor transaksi pembayaran terminal handling &amp; pergudangan kargo ke IAS.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap self-start">
            <a href="{{ route('ias.payments.template') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Unduh Template CSV</span>
            </a>

            <button type="button" uk-toggle="target: #modal-import-ias" 
                    class="uk-btn uk-btn-secondary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span>Import Pembayaran IAS</span>
            </button>

            <a href="{{ route('ias.payments.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Catat Manual</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Dana Keluar ke IAS -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Dana Keluar ke IAS</span>
                <span class="p-1.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-xl font-bold font-mono text-amber-600 dark:text-amber-400">
                    Rp {{ number_format($summary['total_nominal'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-muted-foreground mt-0.5">
                    Akumulasi seluruh pembayaran ke IAS
                </p>
            </div>
        </div>

        <!-- Bulan Ini -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Bulan Ini</span>
                <span class="p-1.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-xl font-bold font-mono text-foreground">
                    Rp {{ number_format($summary['bulan_ini_nominal'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-muted-foreground mt-0.5 font-mono">
                    Periode {{ now()->translatedFormat('F Y') }}
                </p>
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Transaksi</span>
                <span class="p-1.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-xl font-bold font-mono text-foreground">
                    {{ $summary['total_transaksi'] }} Transaksi
                </span>
                <p class="text-[11px] text-muted-foreground mt-0.5">
                    {{ $summary['total_berhasil'] }} Berhasil
                </p>
            </div>
        </div>

        <!-- Cash Back IAS -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Cash Back IAS</span>
                <span class="p-1.5 rounded-md bg-teal-500/10 text-teal-600 dark:text-teal-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-xl font-bold font-mono text-teal-600 dark:text-teal-400">
                    Rp {{ number_format($summary['total_cashback'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-muted-foreground mt-0.5">
                    Bulan ini: <strong class="text-foreground">Rp {{ number_format($summary['bulan_ini_cashback'], 0, ',', '.') }}</strong> (@ Rp 200/kg)
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="uk-card uk-card-default border border-border p-4">
        <form action="{{ route('ias.payments.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor transaksi IAS, nomor nota, nomor SMU, atau catatan..."
                       class="uk-input text-xs w-full">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap">
                <select name="bank" class="uk-select text-xs">
                    <option value="">Semua Bank</option>
                    <option value="BCA" {{ $bank === 'BCA' ? 'selected' : '' }}>BCA</option>
                    <option value="Mandiri" {{ $bank === 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                    <option value="BNI" {{ $bank === 'BNI' ? 'selected' : '' }}>BNI</option>
                    <option value="BRI" {{ $bank === 'BRI' ? 'selected' : '' }}>BRI</option>
                </select>

                <select name="status" class="uk-select text-xs">
                    <option value="">Semua Status</option>
                    <option value="berhasil" {{ $status === 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="gagal" {{ $status === 'gagal' ? 'selected' : '' }}>Gagal</option>
                </select>

                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs shadow-xs">
                    Cari
                </button>
                <a href="{{ route('ias.payments.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs shadow-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table of IAS Payments -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="bg-muted/40 text-muted-foreground border-b border-border">
                        <th class="py-3 px-4 font-medium">Tanggal</th>
                        <th class="py-3 px-4 font-medium">No. Transaksi IAS</th>
                        <th class="py-3 px-4 font-medium">Dasar Nota</th>
                        <th class="py-3 px-4 font-medium">Dasar Master SMU</th>
                        <th class="py-3 px-4 font-medium text-right">Nominal Bayar (Rp)</th>
                        <th class="py-3 px-4 font-medium text-right text-teal-600 dark:text-teal-400">Cash Back (Rp)</th>
                        <th class="py-3 px-4 font-medium">Bank &amp; Metode</th>
                        <th class="py-3 px-4 font-medium text-center">Status</th>
                        <th class="py-3 px-4 font-medium">Keterangan / Sumber</th>
                        <th class="py-3 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($payments as $p)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 text-muted-foreground whitespace-nowrap">
                                {{ $p->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-foreground whitespace-nowrap">
                                {{ $p->nomor_transaksi_ias }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($p->invoices->isNotEmpty())
                                    <div class="space-y-0.5">
                                        <div class="flex flex-wrap items-center gap-1 max-w-[200px]">
                                             @foreach($p->invoices as $inv)
                                                <a href="{{ route('invoices.show', $inv) }}" class="font-mono font-bold text-primary hover:underline text-[11px]" title="Lihat Detail Nota">
                                                    {{ $inv->nomor_nota }}
                                                </a>{{ !$loop->last ? ',' : '' }}
                                            @endforeach
                                        </div>
                                        @php
                                            $custNames = $p->invoices->map(fn($i) => $i->customer?->nama)->filter()->unique()->values();
                                        @endphp
                                        @if($custNames->isNotEmpty())
                                            <span class="text-[10px] text-muted-foreground block truncate max-w-[180px]" title="{{ $custNames->implode(', ') }}">
                                                {{ $custNames->implode(', ') }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($p->nomor_nota)
                                    <span class="font-mono font-bold text-primary text-[11px]">{{ $p->nomor_nota }}</span>
                                @else
                                    <span class="text-muted-foreground">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($p->nomor_smu)
                                    @php
                                        $smus = array_filter(array_map('trim', explode(',', $p->nomor_smu)));
                                    @endphp
                                    <div class="space-y-0.5">
                                        <div class="flex flex-wrap items-center gap-1 max-w-[220px]">
                                            @foreach($smus as $smuItem)
                                                @php
                                                    $matchedWs = \App\Models\Worksheet::where('nomor_smu', $smuItem)->first();
                                                @endphp
                                                @if($matchedWs)
                                                    <a href="{{ route('worksheets.show', $matchedWs) }}" class="font-mono font-bold text-foreground hover:underline text-[11px]" title="Lihat Lembar Kerja">
                                                        {{ $smuItem }}
                                                    </a>{{ !$loop->last ? ',' : '' }}
                                                @else
                                                    <span class="font-mono font-bold text-foreground text-[11px]">{{ $smuItem }}{{ !$loop->last ? ',' : '' }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                        @if($p->worksheet)
                                            <span class="text-[10px] text-muted-foreground font-mono block">
                                                {{ number_format($p->worksheet->total_berat_kg, 1, ',', '.') }} kg / {{ $p->worksheet->total_koli }} koli
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted-foreground">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap">
                                Rp {{ number_format($p->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold {{ $p->cashback > 0 ? 'text-teal-600 dark:text-teal-400' : 'text-muted-foreground/50' }} whitespace-nowrap">
                                {{ $p->cashback > 0 ? 'Rp ' . number_format($p->cashback, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-semibold text-foreground">{{ $p->bank_pengirim ?? 'Bank' }}</span>
                                <span class="text-muted-foreground font-mono">({{ strtoupper($p->metode_bayar) }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($p->status === 'berhasil')
                                    <span class="uk-label uk-label-success text-[10px]">BERHASIL</span>
                                @elseif($p->status === 'pending')
                                    <span class="uk-label uk-label-warning text-[10px]">PENDING</span>
                                @else
                                    <span class="uk-label uk-label-danger text-[10px]">GAGAL</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 max-w-xs truncate" title="{{ $p->catatan }}">
                                <span class="text-foreground">{{ $p->catatan ?? '-' }}</span>
                                @if($p->file_referensi)
                                    <span class="block text-[10px] text-muted-foreground font-mono">File: {{ $p->file_referensi }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <form action="{{ route('ias.payments.destroy', $p) }}" method="POST"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pembayaran ke IAS ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="uk-btn uk-btn-destructive uk-btn-xs shadow-xs">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-muted-foreground">
                                Belum ada catatan pembayaran dari perusahaan ke IAS. Silakan klik tombol <strong>Import Pembayaran IAS</strong> atau <strong>Catat Manual</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-border">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Import CSV IAS -->
<div id="modal-import-ias" uk-modal>
    <div class="uk-modal-dialog uk-modal-body bg-card border border-border rounded-xl p-6 shadow-xl max-w-lg">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="text-base font-bold text-foreground">Import File Pembayaran ke IAS</h2>
        <p class="text-xs text-muted-foreground mt-1 mb-4">
            Unggah file transaksi pembayaran CSV dari bank atau sistem finance perusahaan untuk dicocokkan dengan tagihan IAS.
        </p>

        <form action="{{ route('ias.payments.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="p-4 rounded-lg border-2 border-dashed border-border text-center bg-muted/20 hover:bg-muted/40 transition-colors">
                <input type="file" name="file_import" accept=".csv,.txt" required class="w-full text-xs cursor-pointer">
                <p class="text-[11px] text-muted-foreground mt-2">
                    Format file: <strong>.CSV</strong> (pemisah koma atau titik koma). Maksimal 5MB.
                </p>
            </div>

            <div class="p-3 bg-muted/40 rounded-lg text-xs space-y-1 text-muted-foreground">
                <p class="font-bold text-foreground">Struktur Kolom CSV yang Didukung:</p>
                <p class="font-mono text-[11px]">tanggal, nomor_nota, nomor_smu, nomor_transaksi_ias, nominal, metode_bayar, bank_pengirim, catatan</p>
                <div class="pt-2">
                    <a href="{{ route('ias.payments.template') }}" class="text-primary hover:underline font-semibold inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Klik di sini untuk mengunduh template contoh CSV</span>
                    </a>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-border">
                <button class="uk-btn uk-btn-default uk-btn-sm text-xs uk-modal-close" type="button">Batal</button>
                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs shadow-sm">
                    Mulai Impor File
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
