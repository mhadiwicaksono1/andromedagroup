@extends('layouts.cargo')

@section('title', 'Penerimaan Pembayaran Piutang — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Penerimaan Pembayaran Pelanggan</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Catat penerimaan kas / transfer bank dari customer dengan alokasi otomatis (FIFO) ke nota piutang.</p>
        </div>
        <a href="{{ route('payments.create') }}" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 self-start shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Catat Pembayaran Baru</span>
        </a>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="uk-card uk-card-default p-4 border border-border">
            <span class="text-[11px] text-muted-foreground uppercase font-medium block">Penerimaan Bulan Ini</span>
            <div class="text-xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 font-mono mt-1">
                Rp {{ number_format($totalBulanIni, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-muted-foreground mt-1 block">Bulan {{ now()->translatedFormat('F Y') }}</span>
        </div>

        <div class="uk-card uk-card-default p-4 border border-border">
            <span class="text-[11px] text-muted-foreground uppercase font-medium block">Total Seluruh Penerimaan</span>
            <div class="text-xl font-bold tracking-tight text-foreground font-mono mt-1">
                Rp {{ number_format($totalSemua, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-muted-foreground mt-1 block">Akumulasi pembayaran tercatat</span>
        </div>

        <div class="uk-card uk-card-default p-4 border border-border">
            <span class="text-[11px] text-muted-foreground uppercase font-medium block">Total Transaksi Kas Masuk</span>
            <div class="text-xl font-bold tracking-tight text-primary font-mono mt-1">
                {{ $payments->total() }} Transaksi
            </div>
            <span class="text-[10px] text-muted-foreground mt-1 block">Bukti pembayaran kuitansi</span>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="uk-card uk-card-default p-4 border border-border">
        <form action="{{ route('payments.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor pembayaran, customer, atau catatan..."
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

                <select name="metode" class="uk-select text-xs">
                    <option value="">Semua Metode</option>
                    <option value="transfer" {{ $metode === 'transfer' ? 'selected' : '' }}>Transfer Bank BCA</option>
                    <option value="tunai" {{ $metode === 'tunai' ? 'selected' : '' }}>Cash</option>
                </select>

                <input type="date" name="start_date" value="{{ $startDate }}" title="Dari Tanggal" class="uk-input text-xs w-auto">
                <input type="date" name="end_date" value="{{ $endDate }}" title="Sampai Tanggal" class="uk-input text-xs w-auto">

                <button type="submit" class="uk-btn uk-btn-secondary uk-btn-sm text-xs shadow-xs">
                    Filter
                </button>

                @if($search || $customerId || $metode || $startDate || $endDate)
                    <a href="{{ route('payments.index') }}" class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-destructive">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Payments -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-3 px-4 font-medium">No. Pembayaran</th>
                        <th class="py-3 px-4 font-medium">Tanggal</th>
                        <th class="py-3 px-4 font-medium">Customer</th>
                        <th class="py-3 px-4 font-medium">Metode</th>
                        <th class="py-3 px-4 font-medium text-right">Nominal Bayar</th>
                        <th class="py-3 px-4 font-medium">Alokasi Pelunasan Nota</th>
                        <th class="py-3 px-4 font-medium">Catatan / Referensi</th>
                        <th class="py-3 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($payments as $p)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-foreground whitespace-nowrap">
                                <a href="{{ route('payments.show', $p) }}" class="text-primary hover:underline">
                                    {{ $p->nomor_pembayaran }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground whitespace-nowrap">
                                {{ $p->tanggal->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('customers.show', $p->customer) }}" class="font-semibold text-foreground hover:text-primary transition-colors">
                                    {{ $p->customer->nama ?? '-' }}
                                </a>
                                <span class="block text-[10px] font-mono text-muted-foreground">{{ $p->customer->kode_customer ?? '' }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="badge-metode-{{ $p->metode ?? 'transfer' }}">
                                    {{ $p->metode === 'transfer' ? 'Transfer Bank BCA' : ($p->metode === 'tunai' || $p->metode === 'cash' ? 'Cash' : ucfirst($p->metode)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @if($p->allocations->count() > 0)
                                        @foreach($p->allocations as $alloc)
                                            @if($alloc->invoice)
                                                <a href="{{ route('invoices.show', $alloc->invoice) }}" 
                                                   class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] bg-muted hover:bg-primary/10 border border-border text-foreground transition-colors"
                                                   title="Dialokasikan Rp {{ number_format($alloc->jumlah_alokasi, 0, ',', '.') }} ke nota {{ $alloc->invoice->nomor_nota }}">
                                                    <span class="font-mono font-semibold">{{ $alloc->invoice->nomor_nota }}</span>
                                                    <span class="text-emerald-600 font-mono">(Rp {{ number_format($alloc->jumlah_alokasi, 0, ',', '.') }})</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    @elseif($p->invoice)
                                        <a href="{{ route('invoices.show', $p->invoice) }}" class="inline-block px-1.5 py-0.5 rounded text-[10px] bg-muted border border-border font-mono">
                                            {{ $p->invoice->nomor_nota }}
                                        </a>
                                    @else
                                        <span class="text-muted-foreground italic text-[11px]">Deposit / Saldo Berjalan</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-muted-foreground max-w-[200px] truncate" title="{{ $p->catatan }}">
                                {{ $p->catatan ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('payments.show', $p) }}" title="Lihat Bukti Pembayaran"
                                       class="uk-btn uk-btn-ghost uk-btn-sm p-1.5 text-muted-foreground hover:text-foreground">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    <a href="{{ route('payments.print', $p) }}" target="_blank" title="Cetak Kuitansi"
                                       class="uk-btn uk-btn-ghost uk-btn-sm p-1.5 text-muted-foreground hover:text-primary">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </a>

                                    <form action="{{ route('payments.destroy', $p) }}" method="POST" 
                                          onsubmit="return confirm('Batalkan pembayaran {{ $p->nomor_pembayaran }}? Alokasi ke nota piutang akan dipulihkan kembali.')" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Batalkan Pembayaran" class="uk-btn uk-btn-ghost uk-btn-sm p-1.5 text-muted-foreground hover:text-destructive">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                Belum ada data penerimaan pembayaran yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-border">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
