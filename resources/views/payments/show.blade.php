@extends('layouts.cargo')

@section('title', 'Kuitansi Pembayaran ' . $payment->nomor_pembayaran)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground font-mono">{{ $payment->nomor_pembayaran }}</h1>
                <span class="uk-label uk-label-success text-[10px]">BERHASIL DIALOKASIKAN</span>
            </div>
            <p class="text-xs text-muted-foreground mt-0.5">
                Tanggal Pembayaran: {{ $payment->tanggal->format('d F Y') }} • Diterima oleh: {{ $payment->user->name ?? 'Kasir' }}
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('payments.print', $payment) }}" target="_blank" 
               class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 text-primary shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Kuitansi</span>
            </a>

            <form action="{{ route('payments.destroy', $payment) }}" method="POST" 
                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pembayaran ini? Alokasi ke nota piutang akan dikembalikan.')" 
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="uk-btn uk-btn-destructive uk-btn-sm text-xs shadow-xs">
                    Batalkan Pembayaran
                </button>
            </form>

            <a href="{{ route('payments.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Summary Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Customer Info Card -->
        <div class="uk-card uk-card-default border border-border p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Informasi Pelanggan</span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-muted text-foreground border border-border">
                    {{ $payment->customer->kode_customer ?? '-' }}
                </span>
            </div>
            <h3 class="text-base font-bold text-foreground">{{ $payment->customer->nama ?? '-' }}</h3>
            <div class="mt-2 text-xs space-y-1 text-muted-foreground">
                <p>Telp: {{ $payment->customer->telepon ?? '-' }}</p>
                <p>Alamat: {{ $payment->customer->alamat ?? '-' }}</p>
                <div class="pt-2 mt-2 border-t border-border flex items-center justify-between">
                    <span class="font-medium text-foreground">Sisa Piutang Berjalan:</span>
                    <span class="font-mono font-bold text-amber-600">
                        Rp {{ number_format($payment->customer->sisa_hutang, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Payment Meta Card -->
        <div class="uk-card uk-card-default border border-border p-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground block mb-3">Rincian Pembayaran</span>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Nomor Kuitansi:</span>
                    <span class="font-mono font-bold text-foreground">{{ $payment->nomor_pembayaran }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Tanggal:</span>
                    <span class="text-foreground">{{ $payment->tanggal->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-muted-foreground">Metode Pembayaran:</span>
                    <span class="badge-metode-{{ $payment->metode ?? 'transfer' }}">
                        {{ $payment->metode }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Referensi / Catatan:</span>
                    <span class="text-foreground text-right max-w-[180px] truncate" title="{{ $payment->catatan }}">{{ $payment->catatan ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Total Paid Card -->
        <div class="uk-card uk-card-default border border-border p-5 flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground block">Total Nominal Diterima</span>
                <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">
                    Rp {{ number_format($payment->jumlah, 0, ',', '.') }}
                </div>
            </div>
            <div class="pt-3 border-t border-border text-[11px] text-muted-foreground">
                <span class="font-semibold text-foreground">Status Alokasi:</span> 
                {{ $payment->allocations->count() }} Nota Terpotong
            </div>
        </div>
    </div>

    <!-- Allocation Breakdown Table -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-border flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-foreground">Daftar Nota Piutang yang Dipotong / Dilunasi</h3>
                <p class="text-xs text-muted-foreground mt-0.5">Alokasi otomatis dana pembayaran terhadap tagihan-tagihan kargo pelanggan.</p>
            </div>
            <span class="text-xs text-muted-foreground font-mono">{{ $payment->allocations->count() }} nota</span>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-2.5 px-4 font-medium">Nomor Nota</th>
                        <th class="py-2.5 px-4 font-medium">Tanggal Nota</th>
                        <th class="py-2.5 px-4 font-medium text-right">Total Nilai Nota</th>
                        <th class="py-2.5 px-4 font-medium text-right text-emerald-600">Alokasi Pembayaran Ini</th>
                        <th class="py-2.5 px-4 font-medium text-right">Sisa Piutang Nota Saat Ini</th>
                        <th class="py-2.5 px-4 font-medium text-center">Status Pembayaran Nota</th>
                        <th class="py-2.5 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($payment->allocations as $alloc)
                        @if($alloc->invoice)
                            <tr class="hover:bg-muted/40 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-primary">
                                    <a href="{{ route('invoices.show', $alloc->invoice) }}" class="hover:underline">
                                        {{ $alloc->invoice->nomor_nota }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-muted-foreground">
                                    {{ $alloc->invoice->tanggal->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-muted-foreground">
                                    Rp {{ number_format($alloc->invoice->total_nota, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                    Rp {{ number_format($alloc->jumlah_alokasi, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-foreground font-semibold">
                                    Rp {{ number_format($alloc->invoice->sisa_piutang, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($alloc->invoice->status_pembayaran === 'lunas')
                                        <span class="uk-label uk-label-success text-[10px]">LUNAS</span>
                                    @elseif($alloc->invoice->status_pembayaran === 'sebagian')
                                        <span class="uk-label uk-label-warning text-[10px]">SEBAGIAN</span>
                                    @else
                                        <span class="uk-label uk-label-danger text-[10px]">BELUM LUNAS</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('invoices.show', $alloc->invoice) }}" class="uk-btn uk-btn-ghost uk-btn-xs text-primary">
                                        Buka Nota &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-muted-foreground">
                                Pembayaran ini tidak dialokasikan ke nota tertentu (Saldo Deposit Pelanggan).
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
