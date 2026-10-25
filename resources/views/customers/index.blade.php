@extends('layouts.cargo')

@section('title', 'Master Customer — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Master Pelanggan (Customer)</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Kelola data pelanggan sebagai referensi utama lembar kerja dan penagihan nota kargo.</p>
        </div>
        <a href="{{ route('customers.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 self-start shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Customer Baru</span>
        </a>
    </div>

    <!-- Search & Filter Controls -->
    <div class="uk-card uk-card-default p-4 border border-border">
        <form action="{{ route('customers.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, kode customer, telepon, atau alamat..."
                       class="uk-input text-xs w-full">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" class="uk-select text-xs w-full sm:w-auto">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                <button type="submit" class="uk-btn uk-btn-secondary uk-btn-sm text-xs shadow-xs">
                    Filter
                </button>

                @if($search || $status)
                    <a href="{{ route('customers.index') }}" class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-destructive">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-3 px-4 font-medium">Kode</th>
                        <th class="py-3 px-4 font-medium">Nama Pelanggan</th>
                        <th class="py-3 px-4 font-medium">Kontak / Telepon</th>
                        <th class="py-3 px-4 font-medium">Alamat</th>
                        <th class="py-3 px-4 font-medium text-center">Lembar Kerja</th>
                        <th class="py-3 px-4 font-medium text-right">Sisa Piutang</th>
                        <th class="py-3 px-4 font-medium text-center">Status</th>
                        <th class="py-3 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($customers as $c)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-primary">
                                {{ $c->kode_customer }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $c) }}" class="font-semibold text-foreground hover:text-primary transition-colors">
                                    {{ $c->nama }}
                                </a>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($c->kategori === 'prioritas')
                                        <span class="inline-flex items-center text-[10px] font-bold text-amber-500 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20">
                                            PRIORITAS • Pecahan: Rp {{ number_format($c->tarif_pecahan ?? 2000, 0, ',', '.') }} | Pernama: Rp {{ number_format($c->tarif_pernama ?? 1980, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[10px] font-medium text-muted-foreground bg-muted px-1.5 py-0.5 rounded border border-border">
                                            STANDAR • Rp {{ number_format($c->tarif_default ?? 2100, 0, ',', '.') }}/kg
                                        </span>
                                    @endif
                                </div>
                                @if($c->email)
                                    <span class="block text-[11px] text-muted-foreground mt-0.5">{{ $c->email }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-foreground font-mono">
                                {{ $c->telepon ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground max-w-xs truncate" title="{{ $c->alamat }}">
                                {{ $c->alamat ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-muted text-foreground border border-border">
                                    {{ $c->worksheets_count }} LK
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold font-mono {{ $c->total_sisa_piutang > 0 ? 'text-destructive' : 'text-muted-foreground' }}">
                                Rp {{ number_format($c->total_sisa_piutang ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($c->status === 'active')
                                    <span class="uk-label uk-label-success text-[10px]">Aktif</span>
                                @else
                                    <span class="uk-label text-[10px]">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('customers.show', $c) }}" title="Lihat Profil & Transaksi"
                                       class="uk-btn uk-btn-default uk-btn-xs p-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('customers.edit', $c) }}" title="Edit Customer"
                                       class="uk-btn uk-btn-default uk-btn-xs p-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('customers.toggleStatus', $c) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                title="{{ $c->status === 'active' ? 'Nonaktifkan Customer' : 'Aktifkan Customer' }}"
                                                class="uk-btn uk-btn-default uk-btn-xs p-1.5 {{ $c->status === 'active' ? 'text-amber-500' : 'text-emerald-500' }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('customers.destroy', $c) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus customer {{ $c->nama }}? (Soft Delete)')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Customer" class="uk-btn uk-btn-default uk-btn-xs p-1.5 text-muted-foreground hover:text-destructive">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-muted-foreground">
                                Tidak ada data customer ditemukan.
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
