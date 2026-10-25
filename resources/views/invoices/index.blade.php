@extends('layouts.cargo')

@section('title', 'Daftar Nota Penagihan & Piutang — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Nota Penagihan (Invoices) &amp; Piutang</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Kelola penagihan lembar kerja kargo, akumulasi sisa hutang, dan status pelunasan.</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 self-start shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Buat Nota Penagihan Baru</span>
        </a>
    </div>

    <!-- Search & Filter Controls -->
    <div class="uk-card uk-card-default p-4 border border-border">
        <form action="{{ route('invoices.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor nota atau nama customer..."
                       class="uk-input text-xs w-full">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap">
                <select name="customer_id" class="select2 uk-select text-xs min-w-[180px] max-w-[220px]">
                    <option value="">Semua Pelanggan</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>
                            {{ $c->nama }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="uk-select text-xs">
                    <option value="">Semua Status</option>
                    <option value="belum_lunas" {{ $status === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="sebagian" {{ $status === 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                    <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>

                <button type="submit" class="uk-btn uk-btn-secondary uk-btn-sm text-xs shadow-xs">
                    Filter
                </button>

                @if($search || $status || $customerId)
                    <a href="{{ route('invoices.index') }}" class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-destructive">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Invoices -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-3 px-4 font-medium">Nomor Nota</th>
                        <th class="py-3 px-4 font-medium">Tanggal</th>
                        <th class="py-3 px-4 font-medium">Customer</th>
                        <th class="py-3 px-4 font-medium text-center">LK</th>
                        <th class="py-3 px-4 font-medium text-right">Nilai Nota</th>
                        <th class="py-3 px-4 font-medium text-right">Hutang Lama</th>
                        <th class="py-3 px-4 font-medium text-right">Total Tagihan</th>
                        <th class="py-3 px-4 font-medium text-right">Terbayar</th>
                        <th class="py-3 px-4 font-medium text-right">Sisa Piutang</th>
                        <th class="py-3 px-4 font-medium text-center">Status</th>
                        <th class="py-3 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold">
                                <a href="{{ route('invoices.show', $inv) }}" class="text-primary hover:underline">
                                    {{ $inv->nomor_nota }}
                                </a>
                                @if($inv->tipe_nota)
                                    <span class="block text-[9px] uppercase font-semibold text-muted-foreground mt-0.5">
                                        {{ $inv->tipe_nota === 'pecahan' ? 'Nota Pecahan' : 'Nota Pernama' }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground">
                                {{ $inv->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-foreground">
                                {{ $inv->customer->nama ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-muted text-foreground border border-border">
                                    {{ count($inv->worksheets) }} LK
                                </span>
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
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($inv->sisa_piutang > 0)
                                        <a href="{{ route('payments.create', ['customer_id' => $inv->customer_id, 'invoice_id' => $inv->id]) }}" 
                                           title="Bayar Piutang Nota {{ $inv->nomor_nota }}"
                                           class="uk-btn uk-btn-primary uk-btn-xs px-2 py-1 text-[11px] font-semibold flex items-center gap-1 shadow-xs whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <span>Bayar Piutang</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('invoices.show', $inv) }}" title="Lihat Rincian"
                                       class="uk-btn uk-btn-default uk-btn-xs p-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('invoices.print', $inv) }}" target="_blank" title="Cetak Nota Resmi"
                                       class="uk-btn uk-btn-default uk-btn-xs p-1.5 text-primary">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-muted-foreground">
                                Tidak ada nota penagihan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-border">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
