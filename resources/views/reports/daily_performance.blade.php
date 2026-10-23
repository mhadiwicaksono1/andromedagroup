@extends('layouts.cargo')

@section('title', 'Rekap Mutasi Kas & Profit Harian — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span>Rekap Mutasi Kas &amp; Profit Harian</span>
            </h1>
            <p class="text-xs text-muted-foreground mt-0.5">
                Perhitungan realisasi Uang Masuk, Pembayaran IAS, Cash Back, Total Nota Customer, dan Laba Bersih harian.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('reports.daily_performance.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Export CSV</span>
            </a>

            <a href="{{ route('reports.daily_performance.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
               target="_blank" 
               class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Rekap (Tab Baru)</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="uk-card uk-card-default border border-border p-4 rounded-xl shadow-xs space-y-3">
        <form action="{{ route('reports.daily_performance') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <span class="text-xs font-semibold text-foreground whitespace-nowrap">Periode:</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="uk-input text-xs w-full sm:w-auto">
                <span class="text-xs text-muted-foreground">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="uk-input text-xs w-full sm:w-auto">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <span class="text-xs font-semibold text-foreground whitespace-nowrap">Rate Cashback:</span>
                <div class="relative w-28">
                    <input type="number" step="any" min="0" name="cashback_rate" value="{{ $cashbackRate }}" 
                           class="uk-input text-xs font-mono font-bold pr-10">
                    <span class="absolute right-2 top-2 text-[10px] text-muted-foreground">/kg</span>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="uk-btn uk-btn-secondary uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs w-full sm:w-auto">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span>Terapkan Filter</span>
                </button>

                <a href="{{ route('reports.daily_performance', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString()]) }}" 
                   class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-muted-foreground hover:text-foreground">
                    Reset
                </a>
            </div>

            <!-- Quick Filter Presets -->
            <div class="sm:ml-auto flex items-center gap-1.5 flex-wrap text-[11px]">
                <span class="text-muted-foreground">Cepat:</span>
                <a href="{{ route('reports.daily_performance', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString(), 'cashback_rate' => $cashbackRate]) }}" 
                   class="px-2 py-0.5 rounded border border-border text-muted-foreground hover:text-foreground hover:border-primary transition-colors {{ $startDate === now()->startOfMonth()->toDateString() && $endDate === now()->toDateString() ? 'bg-secondary text-primary font-bold' : '' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('reports.daily_performance', ['start_date' => now()->subMonth()->startOfMonth()->toDateString(), 'end_date' => now()->subMonth()->endOfMonth()->toDateString(), 'cashback_rate' => $cashbackRate]) }}" 
                   class="px-2 py-0.5 rounded border border-border text-muted-foreground hover:text-foreground hover:border-primary transition-colors">
                    Bulan Lalu
                </a>
                <a href="{{ route('reports.daily_performance', ['start_date' => now()->subDays(6)->toDateString(), 'end_date' => now()->toDateString(), 'cashback_rate' => $cashbackRate]) }}" 
                   class="px-2 py-0.5 rounded border border-border text-muted-foreground hover:text-foreground hover:border-primary transition-colors">
                    7 Hari Terakhir
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5">
        <!-- Uang Masuk -->
        <div class="uk-card uk-card-default border border-border p-4 rounded-xl shadow-2xs">
            <span class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider block">Mutasi Uang Masuk</span>
            <div class="text-base sm:text-lg font-bold font-mono text-blue-600 dark:text-blue-400 mt-1">
                Rp {{ number_format($summary['total_uang_masuk'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-muted-foreground mt-0.5 block">Pelunasan piutang customer</span>
        </div>

        <!-- Pembayaran ke IAS -->
        <div class="uk-card uk-card-default border border-border p-4 rounded-xl shadow-2xs">
            <span class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider block">Pembayaran ke IAS</span>
            <div class="text-base sm:text-lg font-bold font-mono text-amber-600 dark:text-amber-400 mt-1">
                Rp {{ number_format($summary['total_pembayaran_ias'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-muted-foreground mt-0.5 block">Biaya terminal handling kargo</span>
        </div>

        <!-- Cash Back IAS -->
        <div class="uk-card uk-card-default border border-border p-4 rounded-xl shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider block">Total Cash Back</span>
                <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                    Rp {{ number_format($cashbackRate, 0, ',', '.') }}/kg
                </span>
            </div>
            <div class="text-base sm:text-lg font-bold font-mono text-teal-600 dark:text-teal-400 mt-1">
                Rp {{ number_format($summary['total_cashback'], 0, ',', '.') }}
            </div>
            <div class="flex items-center justify-between text-[10px] text-muted-foreground mt-0.5">
                <span>Klaim Bulanan</span>
                <span class="font-mono font-semibold text-foreground">{{ number_format($summary['total_tonase_kg'], 1, ',', '.') }} kg SMU</span>
            </div>
        </div>

        <!-- Total Nota Customer -->
        <div class="uk-card uk-card-default border border-border p-4 rounded-xl shadow-2xs">
            <span class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider block">Total Nota Customer</span>
            <div class="text-base sm:text-lg font-bold font-mono text-indigo-600 dark:text-indigo-400 mt-1">
                Rp {{ number_format($summary['total_nota'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-muted-foreground mt-0.5 block">Nilai tagihan yang diterbitkan</span>
        </div>

        <!-- Total Profit Bersih -->
        <div class="uk-card uk-card-default border-2 border-emerald-500/40 bg-emerald-500/5 p-4 rounded-xl shadow-2xs col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-bold tracking-wider block">Profit Bersih</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                    {{ number_format($summary['margin_profit_persen'], 1) }}%
                </span>
            </div>
            <div class="text-base sm:text-lg font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-muted-foreground mt-0.5 block">Total Nota - (IAS - Cash Back)</span>
        </div>
    </div>

    <!-- Main Table View -->
    <div class="uk-card uk-card-default border border-border rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-border bg-muted/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-xs font-bold text-foreground uppercase tracking-wider">
                    Tabel Mutasi &amp; Profit Harian (Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})
                </h2>
                <span class="text-[11px] text-muted-foreground">Struktur data disesuaikan dengan format pencatatan keuangan kargo.</span>
            </div>
            <div class="text-[11px] text-muted-foreground font-mono">
                Total: {{ count($rows) }} Hari Kalender
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="bg-muted/60 text-foreground border-b border-border text-[11px] uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 font-bold text-center w-28">TANGGAL</th>
                        <th class="py-3 px-4 font-bold text-right text-blue-600 dark:text-blue-400">MUTASI UANG MASUK</th>
                        <th class="py-3 px-4 font-bold text-right text-amber-600 dark:text-amber-400">PEMBAYARAN KE IAS</th>
                        <th class="py-3 px-4 font-bold text-right text-teal-600 dark:text-teal-400">CASH BACK</th>
                        <th class="py-3 px-4 font-bold text-right text-indigo-600 dark:text-indigo-400">TOTAL NOTA KE COSTUMER</th>
                        <th class="py-3 px-4 font-bold text-right text-emerald-600 dark:text-emerald-400 bg-emerald-500/5">PROFIT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border font-mono">
                    @forelse($rows as $row)
                        <tr class="{{ $row['has_activity'] ? 'hover:bg-muted/40' : 'opacity-40 hover:opacity-100 hover:bg-muted/20' }}">
                            <!-- Tanggal -->
                            <td class="py-2.5 px-4 text-center font-bold text-foreground">
                                {{ $row['tanggal_formatted'] }}
                            </td>

                            <!-- Uang Masuk -->
                            <td class="py-2.5 px-4 text-right {{ $row['uang_masuk'] > 0 ? 'text-foreground font-semibold' : 'text-muted-foreground/60' }}">
                                {{ $row['uang_masuk'] > 0 ? 'Rp ' . number_format($row['uang_masuk'], 0, ',', '.') : '-' }}
                            </td>

                            <!-- Pembayaran IAS -->
                            <td class="py-2.5 px-4 text-right {{ $row['pembayaran_ias'] > 0 ? 'text-foreground font-semibold' : 'text-muted-foreground/60' }}">
                                {{ $row['pembayaran_ias'] > 0 ? 'Rp ' . number_format($row['pembayaran_ias'], 0, ',', '.') : '-' }}
                            </td>

                            <!-- Cash Back -->
                            <td class="py-2.5 px-4 text-right {{ $row['cashback'] > 0 ? 'text-teal-600 dark:text-teal-400 font-semibold' : 'text-muted-foreground/60' }}">
                                {{ $row['cashback'] > 0 ? 'Rp ' . number_format($row['cashback'], 0, ',', '.') : '-' }}
                            </td>

                            <!-- Total Nota -->
                            <td class="py-2.5 px-4 text-right {{ $row['total_nota'] > 0 ? 'text-foreground font-semibold' : 'text-muted-foreground/60' }}">
                                {{ $row['total_nota'] > 0 ? 'Rp ' . number_format($row['total_nota'], 0, ',', '.') : '-' }}
                            </td>

                            <!-- Profit -->
                            <td class="py-2.5 px-4 text-right bg-emerald-500/5 font-bold {{ $row['profit'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($row['profit'] < 0 ? 'text-destructive' : 'text-muted-foreground/60') }}">
                                @if($row['has_activity'])
                                    Rp {{ number_format($row['profit'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-muted-foreground">
                                Tidak ada transaksi pada rentang tanggal yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-muted/80 font-mono font-bold text-foreground border-t-2 border-border text-xs">
                    <tr>
                        <td class="py-3 px-4 text-center font-bold uppercase">TOTAL</td>
                        <td class="py-3 px-4 text-right text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($summary['total_uang_masuk'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-amber-600 dark:text-amber-400">
                            Rp {{ number_format($summary['total_pembayaran_ias'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-teal-600 dark:text-teal-400">
                            Rp {{ number_format($summary['total_cashback'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-indigo-600 dark:text-indigo-400">
                            Rp {{ number_format($summary['total_nota'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 bg-emerald-500/10">
                            Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Formula & Operational Notes Box -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-xl border border-border bg-card/60 text-xs text-muted-foreground space-y-1.5 shadow-2xs">
            <div class="font-bold text-foreground flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Rumus Perhitungan Profit Bersih:
            </div>
            <p class="text-[11px] leading-relaxed">
                <code class="bg-muted px-1.5 py-0.5 rounded font-mono text-foreground font-semibold">Profit = Total Nota ke Customer - (Pembayaran ke IAS - Cash Back)</code>
                <br>
                Biaya Bersih IAS dihitung dari nilai pembayaran operasional ke IAS dikurangi perolehan potongan Cash Back yang diterima.
            </p>
        </div>

        <div class="p-4 rounded-xl border border-border bg-card/60 text-xs text-muted-foreground space-y-1.5 shadow-2xs">
            <div class="font-bold text-foreground flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                Ketentuan &amp; Mekanisme Cashback:
            </div>
            <p class="text-[11px] leading-relaxed">
                Cashback kargo bernilai <strong>Rp 200 per kg SMU</strong> dan <strong>didapatkan/diklaim setiap bulan</strong> dari pihak IAS/maskapai. Sistem mengestimasikan akrual harian (Tonase SMU &times; Rp 200) agar profit harian terpantau riil, dan terakumulasi penuh di baris total untuk penagihan bulanan.
            </p>
        </div>
    </div>
</div>
@endsection
