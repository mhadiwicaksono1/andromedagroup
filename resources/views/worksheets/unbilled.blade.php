@extends('layouts.cargo')

@section('title', 'Kargo Belum Ternota — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Kargo Belum Ternota (Unbilled Worksheets)</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Pantau muatan lembar kerja yang telah selesai namun belum diterbitkan ke Nota Penagihan, dikelompokkan per pelanggan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('worksheets.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Daftar Lembar Kerja</span>
            </a>
            <a href="{{ route('invoices.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Buat Nota Baru</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="uk-card uk-card-default p-4 border border-border flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">Customer Belum Ternota</p>
                <h3 class="text-xl font-bold text-foreground mt-1">{{ number_format($summary['total_customer'], 0, ',', '.') }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>

        <div class="uk-card uk-card-default p-4 border border-border flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">Total Muatan / LK</p>
                <h3 class="text-xl font-bold text-foreground mt-1">{{ number_format($summary['total_lk'], 0, ',', '.') }} <span class="text-xs font-normal text-muted-foreground">kargo</span></h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-sky-500/10 flex items-center justify-center text-sky-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <div class="uk-card uk-card-default p-4 border border-border flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">Total Volume Kargo</p>
                <h3 class="text-xl font-bold text-foreground mt-1">{{ number_format($summary['total_berat'], 2, ',', '.') }} <span class="text-xs font-normal text-muted-foreground">kg ({{ number_format($summary['total_koli'], 0, ',', '.') }} koli)</span></h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>

        <div class="uk-card uk-card-default p-4 border border-border flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider">Estimasi Nilai Muatan</p>
                <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="uk-card uk-card-default p-4 border border-border">
        <form action="{{ route('worksheets.unbilled') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau kode customer..."
                       class="uk-input text-xs w-full">
            </div>
            <button type="submit" class="uk-btn uk-btn-secondary uk-btn-sm text-xs shadow-xs">
                Filter
            </button>
            @if($search)
                <a href="{{ route('worksheets.unbilled') }}" class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-destructive">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table of Unbilled Worksheets by Customer -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-3 px-4 font-medium">Customer</th>
                        <th class="py-3 px-4 font-medium">Rincian SMU &amp; Lembar Kerja</th>
                        <th class="py-3 px-4 font-medium text-center">Total Koli / Kg</th>
                        <th class="py-3 px-4 font-medium text-right">Estimasi Nilai LK</th>
                        <th class="py-3 px-4 font-medium text-right">Hutang Lama</th>
                        <th class="py-3 px-4 font-medium text-right">Estimasi Tagihan</th>
                        <th class="py-3 px-4 font-medium text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($customers as $c)
                        @php
                            $unbilledItems = $c->worksheetItems;
                            $custTotalKoli = $unbilledItems->sum('jumlah_koli');
                            $custTotalBerat = $unbilledItems->sum('berat_kg');
                            $custTotalBiaya = $unbilledItems->sum('total_biaya');
                            $custHutangLama = $c->sisa_hutang;
                            $custTotalTagihan = $custTotalBiaya + $custHutangLama;
                        @endphp
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-foreground">{{ $c->nama }}</div>
                                <div class="text-[10px] font-mono text-muted-foreground mt-0.5">
                                    {{ $c->kode_customer }} &bull; {{ $c->telepon ?: '-' }}
                                </div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase {{ $c->kategori === 'prioritas' ? 'bg-amber-500/10 text-amber-600 border border-amber-500/20' : 'bg-muted text-muted-foreground' }}">
                                    {{ $c->kategori ?: 'Reguler' }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 max-w-md">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($unbilledItems as $item)
                                        @php $ws = $item->worksheet; @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-[10px] font-medium bg-muted border border-border"
                                              title="Tgl: {{ $ws ? $ws->tanggal->format('d/m/Y') : '-' }} | Berat: {{ number_format($item->berat_kg, 2) }} kg">
                                            <span class="font-mono font-semibold text-primary">{{ $ws->nomor_smu ?? '-' }}</span>
                                            <span class="text-muted-foreground">({{ $ws->nomor_lembar_kerja ?? '-' }})</span>
                                            <span class="px-1 py-0.2 rounded text-[8px] font-bold uppercase {{ ($ws->tipe ?? 'pecahan') === 'pecahan' ? 'bg-purple-500/10 text-purple-600' : 'bg-blue-500/10 text-blue-600' }}">
                                                {{ $ws->tipe ?? 'pecahan' }}
                                            </span>
                                            <span class="font-mono text-foreground font-semibold">{{ number_format($item->berat_kg, 1) }} kg</span>
                                        </span>
                                    @endforeach
                                </div>
                                <div class="text-[10px] text-muted-foreground mt-1">
                                    Total <strong>{{ $unbilledItems->count() }}</strong> muatan siap dinotakan
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="font-mono font-bold text-foreground">{{ number_format($custTotalBerat, 2, ',', '.') }} kg</div>
                                <div class="text-[10px] text-muted-foreground">{{ number_format($custTotalKoli, 0, ',', '.') }} koli</div>
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-foreground">
                                Rp {{ number_format($custTotalBiaya, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono text-muted-foreground">
                                @if($custHutangLama > 0)
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">Rp {{ number_format($custHutangLama, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted-foreground">Rp 0</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right font-mono font-bold text-primary">
                                Rp {{ number_format($custTotalTagihan, 0, ',', '.') }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('invoices.create', ['customer_id' => $c->id]) }}" 
                                   class="uk-btn uk-btn-primary uk-btn-sm text-xs inline-flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span>Buat Nota Langsung</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-muted-foreground">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-8 h-8 text-muted-foreground/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="font-medium text-sm">Tidak ada muatan lembar kerja yang belum dinotakan.</p>
                                    <p class="text-xs text-muted-foreground">Semua kargo yang berstatus selesai telah diterbitkan ke Nota Penagihan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-4 border-t border-border">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
