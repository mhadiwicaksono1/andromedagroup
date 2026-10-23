@extends('layouts.cargo')

@section('title', 'Laporan Laba Rugi Operasional — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Laporan Laba Rugi Operasional Kargo</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Analisis pendapatan muatan SMU terhadap estimasi beban pokok maskapai &amp; pergudangan IAS.</p>
        </div>
        <a href="{{ route('reports.profit_loss.print', request()->query()) }}" target="_blank" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 self-start shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Laporan</span>
        </a>
    </div>

    <!-- Filter Form -->
    <div class="uk-card uk-card-default border border-border p-5">
        <form action="{{ route('reports.profit_loss') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="uk-input text-xs">
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="uk-input text-xs">
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Customer</label>
                <select name="customer_id" class="select2 uk-select text-xs">
                    <option value="">Semua Pelanggan</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>
                            {{ $c->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                    Beban Pokok Maskapai/kg (Rp)
                </label>
                <input type="number" step="10" name="modal_rate" value="{{ $modalRatePerKg }}" min="0" required
                       class="uk-input text-xs font-mono font-bold text-foreground">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex-1 shadow-sm">
                    Terapkan Filter
                </button>
                <a href="{{ route('reports.profit_loss') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs shadow-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pendapatan Kargo -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Pendapatan Jasa Kargo</span>
                <span class="p-1.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-xl font-bold font-mono text-foreground">
                    Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-muted-foreground mt-0.5 font-mono">
                    {{ $summary['total_smu'] }} Master SMU • {{ number_format($summary['total_berat_kg'], 1) }} kg
                </p>
            </div>
        </div>

        <!-- Beban Pokok Maskapai/IAS -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Beban Pokok &amp; IAS</span>
                <span class="p-1.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-xl font-bold font-mono text-amber-600 dark:text-amber-400">
                    Rp {{ number_format($summary['total_beban'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-muted-foreground mt-0.5 font-mono">
                    Basis modal: Rp {{ number_format($modalRatePerKg, 0, ',', '.') }}/kg
                </p>
            </div>
        </div>

        <!-- Laba Kotor / Bersih Operasional -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Laba Kotor Operasional</span>
                <span class="p-1.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-xl font-bold font-mono {{ $summary['total_laba_kotor'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive' }}">
                    Rp {{ number_format($summary['total_laba_kotor'], 0, ',', '.') }}
                </span>
                <p class="text-[11px] text-muted-foreground mt-0.5">
                    {{ $summary['total_laba_kotor'] >= 0 ? 'Surplus Operasional' : 'Defisit Operasional' }}
                </p>
            </div>
        </div>

        <!-- Margin Keuntungan (%) -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Margin Keuntungan (%)</span>
                <span class="p-1.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-xl font-bold font-mono text-purple-600 dark:text-purple-400">
                    {{ number_format($summary['overall_margin'], 1) }}%
                </span>
                <p class="text-[11px] text-muted-foreground mt-0.5">
                    Laba Bersih vs Omzet
                </p>
            </div>
        </div>
    </div>

    <!-- P&L Table Breakdown per SMU -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Rincian Laba Rugi per Master SMU &amp; Lembar Kerja</h3>
            <span class="text-xs text-muted-foreground font-mono">Total {{ $pnlRows->count() }} Data SMU</span>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="bg-muted/40 text-muted-foreground border-b border-border">
                        <th class="py-3 px-4 font-medium">Tanggal</th>
                        <th class="py-3 px-4 font-medium">No. Lembar Kerja</th>
                        <th class="py-3 px-4 font-medium">Master SMU</th>
                        <th class="py-3 px-4 font-medium">Pelanggan</th>
                        <th class="py-3 px-4 font-medium text-center">Koli</th>
                        <th class="py-3 px-4 font-medium text-right">Berat (kg)</th>
                        <th class="py-3 px-4 font-medium text-right">Pendapatan Kargo</th>
                        <th class="py-3 px-4 font-medium text-right">Beban Pokok</th>
                        <th class="py-3 px-4 font-medium text-right">Laba Operasional</th>
                        <th class="py-3 px-4 font-medium text-center">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($pnlRows as $row)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 text-muted-foreground whitespace-nowrap">
                                {{ $row['worksheet']->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-primary whitespace-nowrap">
                                <a href="{{ route('worksheets.show', $row['worksheet']) }}" class="hover:underline">
                                    {{ $row['worksheet']->nomor_lembar_kerja }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-foreground whitespace-nowrap">
                                {{ $row['worksheet']->nomor_smu }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-foreground">
                                    {{ implode(', ', $row['customers']) ?: '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-muted-foreground whitespace-nowrap">
                                {{ $row['total_koli'] }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-foreground whitespace-nowrap">
                                {{ number_format($row['total_berat_kg'], 1) }} kg
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold font-mono text-foreground whitespace-nowrap">
                                Rp {{ number_format($row['pendapatan'], 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-amber-600 dark:text-amber-400 whitespace-nowrap">
                                Rp {{ number_format($row['beban_pokok'], 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold font-mono whitespace-nowrap {{ $row['laba_kotor'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive' }}">
                                Rp {{ number_format($row['laba_kotor'], 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold font-mono {{ $row['margin_persen'] >= 15 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                    {{ number_format($row['margin_persen'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-muted-foreground">
                                Tidak ada data lembar kerja untuk rentang tanggal yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($pnlRows->count() > 0)
                    <tfoot>
                        <tr class="bg-muted/60 font-bold border-t border-border">
                            <td colspan="4" class="py-3.5 px-4 text-right uppercase text-xs">TOTAL AKUMULASI:</td>
                            <td class="py-3.5 px-4 text-center font-mono">{{ $summary['total_koli'] }}</td>
                            <td class="py-3.5 px-4 text-right font-mono">{{ number_format($summary['total_berat_kg'], 1) }} kg</td>
                            <td class="py-3.5 px-4 text-right font-mono text-foreground">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono text-amber-600 dark:text-amber-400">Rp {{ number_format($summary['total_beban'], 0, ',', '.') }}</td>
                            <td class="py-3.5 px-4 text-right font-mono {{ $summary['total_laba_kotor'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-destructive' }}">
                                Rp {{ number_format($summary['total_laba_kotor'], 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-purple-600 dark:text-purple-400">
                                {{ number_format($summary['overall_margin'], 1) }}%
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
