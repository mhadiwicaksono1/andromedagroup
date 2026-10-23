@extends('layouts.cargo')

@section('title', 'Laporan Lembar Kerja — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Laporan Lembar Kerja (SMU)</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Rekapitulasi operasional kargo berdasarkan rentang tanggal dan pelanggan.</p>
        </div>
        <a href="{{ route('reports.worksheets.print', request()->query()) }}" target="_blank" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 self-start shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Laporan</span>
        </a>
    </div>

    <!-- Filter Form -->
    <div class="uk-card uk-card-default border border-border p-5">
        <form action="{{ route('reports.worksheets') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
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
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Status</label>
                <select name="status" class="uk-select text-xs">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dinotakan" {{ $status === 'dinotakan' ? 'selected' : '' }}>Dinotakan</option>
                </select>
            </div>

            <div>
                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs w-full shadow-sm">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="uk-card uk-card-default border border-border p-3.5">
            <span class="text-[11px] text-muted-foreground uppercase font-semibold">Total Item Muatan</span>
            <div class="text-xl font-bold text-foreground mt-0.5">{{ $summary['total_items'] }}</div>
        </div>
        <div class="uk-card uk-card-default border border-border p-3.5">
            <span class="text-[11px] text-muted-foreground uppercase font-semibold">Total Koli</span>
            <div class="text-xl font-bold text-primary mt-0.5 font-mono">{{ number_format($summary['total_koli']) }}</div>
        </div>
        <div class="uk-card uk-card-default border border-border p-3.5">
            <span class="text-[11px] text-muted-foreground uppercase font-semibold">Total Berat (kg)</span>
            <div class="text-xl font-bold text-foreground mt-0.5 font-mono">{{ number_format($summary['total_berat'], 1) }} kg</div>
        </div>
        <div class="uk-card uk-card-default border border-border p-3.5">
            <span class="text-[11px] text-muted-foreground uppercase font-semibold">Total Nilai Biaya</span>
            <div class="text-xl font-bold text-emerald-500 mt-0.5 font-mono">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="bg-muted/40 text-muted-foreground border-b border-border">
                        <th class="py-3 px-4 font-medium">No. Lembar Kerja</th>
                        <th class="py-3 px-4 font-medium">Tanggal</th>
                        <th class="py-3 px-4 font-medium">Pelanggan</th>
                        <th class="py-3 px-4 font-medium">No. Master SMU</th>
                        <th class="py-3 px-4 font-medium">Koli / Berat</th>
                        <th class="py-3 px-4 font-medium">Tarif/kg</th>
                        <th class="py-3 px-4 font-medium text-right">Total Biaya</th>
                        <th class="py-3 px-4 font-medium text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($items as $item)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-primary">
                                @if($item->worksheet)
                                    <a href="{{ route('worksheets.show', $item->worksheet) }}" class="hover:underline">
                                        {{ $item->worksheet->nomor_lembar_kerja }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground">
                                {{ $item->worksheet ? $item->worksheet->tanggal->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-foreground">
                                {{ $item->customer->nama ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-foreground font-medium">
                                {{ $item->worksheet ? $item->worksheet->nomor_smu : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-foreground">
                                {{ $item->jumlah_koli }} koli / {{ number_format($item->berat_kg, 1) }} kg
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground font-mono">
                                Rp {{ number_format($item->tarif_per_kg, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold font-mono text-foreground">
                                Rp {{ number_format($item->total_biaya, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->status === 'draft')
                                    <span class="uk-label text-[10px]">DRAFT</span>
                                @elseif($item->status === 'selesai')
                                    <span class="uk-label uk-label-warning text-[10px]">SELESAI</span>
                                @else
                                    <span class="uk-label uk-label-success text-[10px]">DINOTAKAN</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-muted-foreground">
                                Tidak ada data lembar kerja untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($items, 'hasPages') && $items->hasPages())
            <div class="p-4 border-t border-border">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
