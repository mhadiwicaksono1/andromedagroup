@extends('layouts.cargo')

@section('title', 'Laporan Nota Penagihan — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Laporan Nota Penagihan</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Rekapitulasi faktur nota, omzet, dan realisasi penerimaan pembayaran.</p>
        </div>
        <a href="{{ route('reports.invoices.print', request()->query()) }}" target="_blank" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 self-start shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Laporan</span>
        </a>
    </div>

    <!-- Filter Form -->
    <div class="uk-card uk-card-default border border-border p-5">
        <form action="{{ route('reports.invoices') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
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
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Status Pembayaran</label>
                <select name="status" class="uk-select text-xs">
                    <option value="">Semua Status</option>
                    <option value="belum_lunas" {{ $status === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="sebagian" {{ $status === 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                    <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>Lunas</option>
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
            <span class="text-[11px] text-muted-foreground uppercase font-semibold">Total Nota Terbit</span>
            <div class="text-xl font-bold text-foreground mt-0.5">{{ $summary['total_nota_count'] }}</div>
        </div>
        <div class="uk-card uk-card-default border border-border p-3.5">
            <span class="text-[11px] text-muted-foreground uppercase font-semibold">Total Omzet Nota</span>
            <div class="text-xl font-bold text-primary mt-0.5 font-mono">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</div>
        </div>
        <div class="uk-card uk-card-default border border-border p-3.5">
            <span class="text-[11px] text-muted-foreground uppercase font-semibold">Total Terbayar</span>
            <div class="text-xl font-bold text-emerald-500 mt-0.5 font-mono">Rp {{ number_format($summary['total_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="uk-card uk-card-default border border-border p-3.5">
            <span class="text-[11px] text-muted-foreground uppercase font-semibold">Total Sisa Piutang</span>
            <div class="text-xl font-bold text-destructive mt-0.5 font-mono">Rp {{ number_format($summary['total_sisa_piutang'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="bg-muted/40 text-muted-foreground border-b border-border">
                        <th class="py-3 px-4 font-medium">No. Nota</th>
                        <th class="py-3 px-4 font-medium">Tanggal</th>
                        <th class="py-3 px-4 font-medium">Pelanggan</th>
                        <th class="py-3 px-4 font-medium text-right">Nilai Nota</th>
                        <th class="py-3 px-4 font-medium text-right">Hutang Lama</th>
                        <th class="py-3 px-4 font-medium text-right">Total Tagihan</th>
                        <th class="py-3 px-4 font-medium text-right">Dibayar</th>
                        <th class="py-3 px-4 font-medium text-right">Sisa Piutang</th>
                        <th class="py-3 px-4 font-medium text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-primary">
                                <a href="{{ route('invoices.show', $inv) }}" class="hover:underline">
                                    {{ $inv->nomor_nota }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground">
                                {{ $inv->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-foreground">
                                {{ $inv->customer->nama ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-muted-foreground">
                                Rp {{ number_format($inv->total_nota, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-muted-foreground">
                                Rp {{ number_format($inv->sisa_hutang_sebelumnya, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold font-mono text-foreground">
                                Rp {{ number_format($inv->total_tagihan_kumulatif, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-semibold font-mono text-emerald-500">
                                Rp {{ number_format($inv->jumlah_dibayar, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold font-mono text-destructive">
                                Rp {{ number_format($inv->sisa_piutang, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($inv->status_pembayaran === 'lunas')
                                    <span class="uk-label uk-label-success text-[10px]">LUNAS</span>
                                @elseif($inv->status_pembayaran === 'sebagian')
                                    <span class="uk-label uk-label-warning text-[10px]">SEBAGIAN</span>
                                @else
                                    <span class="uk-label uk-label-danger text-[10px]">BELUM LUNAS</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-muted-foreground">
                                Tidak ada data nota penagihan untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($invoices, 'hasPages') && $invoices->hasPages())
            <div class="p-4 border-t border-border">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
