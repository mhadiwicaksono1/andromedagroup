@extends('layouts.cargo')

@section('title', 'Daftar Lembar Kerja (SMU) — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Lembar Kerja (Surat Muatan Udara)</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Kelola lembar kerja tipe Pernama (1 Customer) dan Pecahan (Multi-Customer Konsolidasi).</p>
        </div>
        @if(auth()->user()->isOperasional())
        <a href="{{ route('worksheets.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 self-start shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Buat Lembar Kerja Baru</span>
        </a>
        @endif
    </div>

    <!-- Search & Filter Bar (Franken UI Card) -->
    <div class="uk-card uk-card-default p-4 border border-border">
        <form action="{{ route('worksheets.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor LK, nomor SMU, keterangan, marking, atau customer..."
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
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dinotakan" {{ $status === 'dinotakan' ? 'selected' : '' }}>Dinotakan</option>
                </select>

                <button type="submit" class="uk-btn uk-btn-secondary uk-btn-sm text-xs shadow-xs">
                    Filter
                </button>

                @if($search || $status || $customerId)
                    <a href="{{ route('worksheets.index') }}" class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-destructive">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Worksheets (Franken UI Card & Table) -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-3 px-4 font-medium">No. Lembar Kerja</th>
                        <th class="py-3 px-4 font-medium">Tipe</th>
                        <th class="py-3 px-4 font-medium">Tanggal</th>
                        <th class="py-3 px-4 font-medium">Nomor SMU</th>
                        <th class="py-3 px-4 font-medium">Daftar Customer &amp; Marking</th>
                        <th class="py-3 px-4 font-medium text-center">Total Koli</th>
                        <th class="py-3 px-4 font-medium text-right">Total Berat</th>
                        <th class="py-3 px-4 font-medium text-right">Total Biaya SMU</th>
                        <th class="py-3 px-4 font-medium text-center">Status</th>
                        <th class="py-3 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($worksheets as $ws)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-foreground">
                                <a href="{{ route('worksheets.show', $ws) }}" class="text-primary hover:underline">
                                    {{ $ws->nomor_lembar_kerja }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if(($ws->tipe ?? 'pecahan') === 'pernama')
                                    <span class="badge-tipe-pernama">
                                        PERNAMA
                                    </span>
                                @else
                                    <span class="badge-tipe-pecahan">
                                        PECAHAN
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground whitespace-nowrap">
                                {{ $ws->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-foreground whitespace-nowrap">
                                {{ $ws->nomor_smu }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1 max-w-sm">
                                    @foreach($ws->items as $item)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-muted text-foreground border border-border" 
                                              title="{{ $item->customer->nama ?? '' }}: {{ $item->jumlah_koli }} koli, {{ number_format($item->berat_kg, 1) }} kg">
                                            <span>{{ $item->customer->nama ?? 'Customer' }}</span>
                                            @if($item->marking)
                                                <span class="font-mono font-semibold text-primary">[{{ $item->marking }}]</span>
                                            @endif
                                            <span class="text-muted-foreground">({{ $item->jumlah_koli }} koli)</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-muted-foreground whitespace-nowrap">
                                {{ $ws->total_koli }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-muted-foreground whitespace-nowrap">
                                {{ number_format($ws->total_berat_kg, 1) }} kg
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-foreground font-mono whitespace-nowrap">
                                Rp {{ number_format($ws->total_biaya, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($ws->status === 'draft')
                                    <span class="uk-label text-[10px]">DRAFT</span>
                                @elseif($ws->status === 'selesai')
                                    <span class="uk-label uk-label-warning text-[10px]">SELESAI</span>
                                @else
                                    <span class="uk-label uk-label-success text-[10px]">DINOTAKAN</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('worksheets.show', $ws) }}" title="Lihat Rincian SMU & Customer"
                                       class="uk-btn uk-btn-ghost uk-btn-sm p-1.5 text-muted-foreground hover:text-foreground">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    @if(auth()->user()->isOperasional() && $ws->status !== 'dinotakan')
                                        <a href="{{ route('worksheets.edit', $ws) }}" title="Edit Lembar Kerja"
                                           class="uk-btn uk-btn-ghost uk-btn-sm p-1.5 text-muted-foreground hover:text-foreground">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        @if(!$ws->items->whereNotNull('invoice_id')->count())
                                            <form action="{{ route('worksheets.destroy', $ws) }}" method="POST" 
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus Lembar Kerja {{ $ws->nomor_lembar_kerja }}? Data akan di-soft delete.')" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Lembar Kerja" class="uk-btn uk-btn-ghost uk-btn-sm p-1.5 text-muted-foreground hover:text-destructive">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-muted-foreground">
                                Tidak ada data lembar kerja yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($worksheets->hasPages())
            <div class="p-4 border-t border-border">
                {{ $worksheets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
