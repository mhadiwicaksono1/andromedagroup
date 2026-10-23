@extends('layouts.cargo')

@section('title', 'Dashboard Operasional & Piutang — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Top Welcome & Action Bar -->
    <div class="uk-card uk-card-default p-4 sm:p-5 border border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="uk-badge text-[10px] uppercase font-semibold">
                    Operasional Kargo
                </span>
                <span class="text-xs text-muted-foreground">•</span>
                <span class="text-xs text-muted-foreground">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">
                Selamat Datang, {{ auth()->user()->name }}
            </h1>
            <p class="text-xs text-muted-foreground mt-0.5">
                Role Anda: <span class="font-semibold text-foreground uppercase">{{ auth()->user()->role }}</span>. Kelola konsolidasi SMU multi-pelanggan, nota penagihan, dan piutang.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('worksheets.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Buat Lembar Kerja</span>
            </a>
            <a href="{{ route('invoices.create') }}" class="uk-btn uk-btn-secondary uk-btn-sm text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Terbitkan Nota</span>
            </a>
        </div>
    </div>

    <!-- 4 Key Performance Metrics (Franken UI Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <!-- Metric 1: Omzet Bulan Ini -->
        <div class="uk-card uk-card-default p-4 border border-border">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium uppercase tracking-wider text-muted-foreground">Omzet Bulan Ini</span>
                <span class="w-6 h-6 rounded-md bg-muted text-foreground flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-2.5">
                <div class="text-xl font-bold tracking-tight text-foreground font-mono">
                    Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-muted-foreground mt-0.5">Total nota terbit bulan {{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <!-- Metric 2: Total Sisa Piutang -->
        <div class="uk-card uk-card-default p-4 border border-border">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium uppercase tracking-wider text-muted-foreground">Total Sisa Piutang</span>
                <span class="w-6 h-6 rounded-md bg-muted text-foreground flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-2.5">
                <div class="text-xl font-bold tracking-tight text-rose-500 font-mono">
                    Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-muted-foreground mt-0.5">Tagihan berjalan belum terlunasi</p>
            </div>
        </div>

        <!-- Metric 3: LK Siap Dinotakan -->
        <div class="uk-card uk-card-default p-4 border border-border">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium uppercase tracking-wider text-muted-foreground">LK Siap Dinotakan</span>
                <span class="w-6 h-6 rounded-md bg-muted text-foreground flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-2.5">
                <div class="text-xl font-bold tracking-tight text-foreground font-mono">
                    {{ $worksheetsCount['selesai'] }}
                    <span class="text-[11px] text-muted-foreground font-normal">item muatan</span>
                </div>
                <p class="text-[10px] text-muted-foreground mt-0.5">Siap dikelompokkan ke nota penagihan</p>
            </div>
        </div>

        <!-- Metric 4: Customer Aktif -->
        <div class="uk-card uk-card-default p-4 border border-border">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-medium uppercase tracking-wider text-muted-foreground">Pelanggan Aktif</span>
                <span class="w-6 h-6 rounded-md bg-muted text-foreground flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-2.5">
                <div class="text-xl font-bold tracking-tight text-foreground font-mono">
                    {{ $totalCustomers }}
                    <span class="text-[11px] text-muted-foreground font-normal">customer</span>
                </div>
                <p class="text-[10px] text-muted-foreground mt-0.5">Terdaftar dalam master pelanggan</p>
            </div>
        </div>
    </div>

    <!-- Main Content: Worksheets & Debt Monitoring -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 2-col: Lembar Kerja Terkini -->
        <div class="lg:col-span-2 uk-card uk-card-default border border-border">
            <div class="uk-card-header p-5 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-foreground">Lembar Kerja (SMU) Terkini</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">Penerbangan konsolidasi kargo operasional terbaru</p>
                </div>
                <a href="{{ route('worksheets.index') }}" class="text-xs font-semibold text-primary hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                    <thead>
                        <tr class="text-muted-foreground border-b border-border">
                            <th class="py-2.5 px-4 font-medium">No. Lembar Kerja</th>
                            <th class="py-2.5 px-4 font-medium">Daftar Customer</th>
                            <th class="py-2.5 px-4 font-medium">No. Master SMU</th>
                            <th class="py-2.5 px-4 font-medium text-center">Koli</th>
                            <th class="py-2.5 px-4 font-medium text-right">Berat Riil</th>
                            <th class="py-2.5 px-4 font-medium text-right">Total Biaya SMU</th>
                            <th class="py-2.5 px-4 font-medium text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($recentWorksheets as $lk)
                            <tr class="hover:bg-muted/40 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-foreground">
                                    <a href="{{ route('worksheets.show', $lk) }}" class="text-primary hover:underline">
                                        {{ $lk->nomor_lembar_kerja }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1 max-w-[180px]">
                                        @foreach($lk->items->take(2) as $item)
                                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] bg-muted text-foreground border border-border">
                                                {{ $item->customer->nama ?? 'Customer' }}
                                            </span>
                                        @endforeach
                                        @if($lk->items->count() > 2)
                                            <span class="text-[10px] text-muted-foreground">+{{ $lk->items->count() - 2 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold text-foreground">
                                    {{ $lk->nomor_smu }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-muted-foreground">
                                    {{ $lk->total_koli }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-muted-foreground">
                                    {{ number_format($lk->total_berat_kg, 1) }} kg
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-foreground font-mono">
                                    Rp {{ number_format($lk->total_biaya, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($lk->status === 'draft')
                                        <span class="uk-label text-[10px]">Draft</span>
                                    @elseif($lk->status === 'selesai')
                                        <span class="uk-label uk-label-warning text-[10px]">Selesai</span>
                                    @else
                                        <span class="uk-label uk-label-success text-[10px]">Dinotakan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-muted-foreground">Belum ada data lembar kerja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 1-col: Monitoring Piutang Pelanggan -->
        <div class="uk-card uk-card-default border border-border">
            <div class="uk-card-header p-5 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-foreground">Monitoring Piutang</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">Customer dengan sisa saldo hutang</p>
                </div>
                <a href="{{ route('reports.receivables') }}" class="text-xs font-semibold text-primary hover:underline">
                    Buku Piutang &rarr;
                </a>
            </div>

            <div class="p-4 space-y-2.5">
                @forelse($customersWithDebt as $debtCust)
                    <div class="p-3 rounded-lg border border-border bg-card/60 hover:bg-muted/40 transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold text-foreground truncate max-w-[170px]">{{ $debtCust->nama }}</span>
                            <span class="text-[10px] text-muted-foreground font-mono">{{ $debtCust->kode_customer }}</span>
                        </div>
                        <div class="flex items-baseline justify-between mt-1.5">
                            <span class="text-[11px] text-muted-foreground">Sisa Piutang:</span>
                            <span class="text-xs font-bold text-rose-500 font-mono">
                                Rp {{ number_format($debtCust->total_piutang_aktif, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-muted-foreground">
                        Tidak ada customer dengan piutang aktif saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom: Nota Penagihan Terkini -->
    <div class="uk-card uk-card-default border border-border">
        <div class="uk-card-header p-5 border-b border-border flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-foreground">Nota Penagihan (Invoice) Terkini</h2>
                <p class="text-xs text-muted-foreground mt-0.5">Daftar faktur penagihan yang diterbitkan</p>
            </div>
            <a href="{{ route('invoices.index') }}" class="text-xs font-semibold text-primary hover:underline">
                Lihat Semua Nota &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border">
                        <th class="py-2.5 px-4 font-medium">Nomor Nota</th>
                        <th class="py-2.5 px-4 font-medium">Tanggal</th>
                        <th class="py-2.5 px-4 font-medium">Customer</th>
                        <th class="py-2.5 px-4 font-medium text-right">Total Tagihan</th>
                        <th class="py-2.5 px-4 font-medium text-right">Dibayar</th>
                        <th class="py-2.5 px-4 font-medium text-right">Sisa Piutang</th>
                        <th class="py-2.5 px-4 font-medium text-center">Status</th>
                        <th class="py-2.5 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($recentInvoices as $inv)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold">
                                <a href="{{ route('invoices.show', $inv) }}" class="text-primary hover:underline">
                                    {{ $inv->nomor_nota }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-muted-foreground">
                                {{ $inv->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 font-medium text-foreground">
                                {{ $inv->customer->nama ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-foreground font-semibold">
                                Rp {{ number_format($inv->total_tagihan_kumulatif, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-500 font-semibold">
                                Rp {{ number_format($inv->jumlah_dibayar, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-rose-500 font-bold">
                                Rp {{ number_format($inv->sisa_piutang, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($inv->status_pembayaran === 'lunas')
                                    <span class="uk-label uk-label-success text-[10px]">Lunas</span>
                                @elseif($inv->status_pembayaran === 'sebagian')
                                    <span class="uk-label uk-label-warning text-[10px]">Sebagian</span>
                                @else
                                    <span class="uk-label uk-label-danger text-[10px]">Belum Lunas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('invoices.print', $inv) }}" target="_blank" class="uk-btn uk-btn-default uk-btn-xs text-[11px]">
                                    Cetak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-muted-foreground">Belum ada nota penagihan diterbitkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
