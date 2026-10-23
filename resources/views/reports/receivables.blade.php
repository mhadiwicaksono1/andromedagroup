@extends('layouts.cargo')

@section('title', 'Monitoring Piutang Pelanggan — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Monitoring Kartu Piutang Pelanggan</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Buku pembantu piutang untuk memantau sisa hutang dan riwayat faktur tertunggak per customer.</p>
        </div>
        <a href="{{ route('reports.receivables.print', request()->query()) }}" target="_blank" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 self-start">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Cetak Buku Piutang</span>
        </a>
    </div>

    <!-- Grand Total Piutang Banner -->
    <div class="uk-card uk-card-default border border-border p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-destructive block">Total Keseluruhan Saldo Piutang Berjalan</span>
            <div class="text-2xl font-bold font-mono text-destructive mt-1">
                Rp {{ number_format($grandTotalPiutang, 0, ',', '.') }}
            </div>
            <p class="text-xs text-muted-foreground mt-1">Akumulasi sisa penagihan dari {{ count($customers) }} pelanggan dengan nota aktif.</p>
        </div>

        <!-- Filter By Customer -->
        <form action="{{ route('reports.receivables') }}" method="GET" class="flex items-center gap-2">
            <select name="customer_id" onchange="this.form.submit()" class="uk-select text-xs">
                <option value="">Semua Pelanggan Berpiutang</option>
                @foreach($allCustomers as $ac)
                    <option value="{{ $ac->id }}" {{ $customerId == $ac->id ? 'selected' : '' }}>
                        {{ $ac->nama }}
                    </option>
                @endforeach
            </select>
            @if($customerId)
                <a href="{{ route('reports.receivables') }}" class="uk-btn uk-btn-ghost uk-btn-sm text-xs text-destructive">Reset</a>
            @endif
        </form>
    </div>

    <!-- Customer Debt Cards -->
    <div class="space-y-6">
        @forelse($customers as $c)
            @php
                $totalCustPiutang = $c->invoices->sum('sisa_piutang');
            @endphp
            <div class="uk-card uk-card-default border border-border overflow-hidden">
                <!-- Customer Header -->
                <div class="p-4 sm:p-5 bg-muted/40 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-foreground">{{ $c->nama }}</h3>
                            <span class="font-mono text-xs text-primary">({{ $c->kode_customer }})</span>
                        </div>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Telp: {{ $c->telepon ?? '-' }} • Alamat: {{ $c->alamat ?? '-' }}
                        </p>
                    </div>

                    <div class="text-right sm:border-l sm:border-border sm:pl-5">
                        <span class="text-[11px] text-muted-foreground block">Total Piutang Customer:</span>
                        <span class="text-lg font-bold font-mono text-destructive">
                            Rp {{ number_format($totalCustPiutang, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Active Invoices Table for this Customer -->
                <div class="overflow-x-auto">
                    <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                        <thead>
                            <tr class="text-muted-foreground border-b border-border bg-muted/20">
                                <th class="py-2.5 px-4 font-medium">Nomor Nota</th>
                                <th class="py-2.5 px-4 font-medium">Tanggal Nota</th>
                                <th class="py-2.5 px-4 font-medium text-center">Lembar Kerja</th>
                                <th class="py-2.5 px-4 font-medium text-right">Total Tagihan</th>
                                <th class="py-2.5 px-4 font-medium text-right">Telah Dibayar</th>
                                <th class="py-2.5 px-4 font-medium text-right">Sisa Piutang</th>
                                <th class="py-2.5 px-4 font-medium text-center">Status</th>
                                <th class="py-2.5 px-4 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($c->invoices as $inv)
                                <tr class="hover:bg-muted/40 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-primary">
                                        <a href="{{ route('invoices.show', $inv) }}" class="hover:underline">
                                            {{ $inv->nomor_nota }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-muted-foreground">{{ $inv->tanggal->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4 text-center text-foreground font-medium">{{ $inv->worksheetItems->count() }} Item SMU</td>
                                    <td class="py-3 px-4 text-right font-semibold font-mono text-foreground">
                                        Rp {{ number_format($inv->total_tagihan_kumulatif, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold font-mono text-emerald-500">
                                        Rp {{ number_format($inv->jumlah_dibayar, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold font-mono text-destructive">
                                        Rp {{ number_format($inv->sisa_piutang, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($inv->status_pembayaran === 'lunas')
                                            <span class="uk-label uk-label-success text-[10px]">LUNAS</span>
                                        @elseif($inv->status_pembayaran === 'sebagian')
                                            <span class="uk-label uk-label-warning text-[10px]">SEBAGIAN</span>
                                        @else
                                            <span class="uk-label uk-label-danger text-[10px]">BELUM LUNAS</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('invoices.show', $inv) }}" class="uk-btn uk-btn-default uk-btn-xs">
                                                Detail
                                            </a>
                                            <a href="{{ route('invoices.print', $inv) }}" target="_blank" class="uk-btn uk-btn-default uk-btn-xs text-primary">
                                                Cetak
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="uk-card uk-card-default border border-border p-12 text-center text-muted-foreground">
                Tidak ada pelanggan yang memiliki sisa piutang berjalan saat ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
