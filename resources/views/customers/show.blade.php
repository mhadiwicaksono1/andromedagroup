@extends('layouts.cargo')

@section('title', 'Detail Customer — ' . $customer->nama)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">{{ $customer->nama }}</h1>
                @if($customer->status === 'active')
                    <span class="uk-label uk-label-success text-[10px]">Aktif</span>
                @else
                    <span class="uk-label text-[10px]">Nonaktif</span>
                @endif
                @if($customer->kategori === 'prioritas')
                    <span class="inline-flex items-center text-[10px] font-bold text-amber-500 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20">
                        PRIORITAS • Pecahan: Rp {{ number_format($customer->tarif_pecahan ?? 2000, 0, ',', '.') }} | Pernama: Rp {{ number_format($customer->tarif_pernama ?? 1980, 0, ',', '.') }}
                    </span>
                @else
                    <span class="inline-flex items-center text-[10px] font-medium text-muted-foreground bg-muted px-1.5 py-0.5 rounded border border-border">
                        STANDAR • Rp {{ number_format($customer->tarif_default ?? 2100, 0, ',', '.') }}/kg
                    </span>
                @endif
            </div>
            <p class="text-xs text-muted-foreground font-mono mt-0.5">Kode: {{ $customer->kode_customer }}</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('payments.create') }}?customer_id={{ $customer->id }}" 
               class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span>Catat Pembayaran</span>
            </a>

            <a href="{{ route('customers.statement', $customer) }}" target="_blank"
               class="uk-btn uk-btn-secondary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Cetak Kartu Piutang</span>
            </a>

            <a href="{{ route('customers.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Customer Details -->
        <div class="md:col-span-2 uk-card uk-card-default border border-border p-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">Informasi Kontak &amp; Alamat</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-muted-foreground block mb-1">Telepon / WhatsApp:</span>
                    <span class="text-foreground font-mono font-semibold">{{ $customer->telepon ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-muted-foreground block mb-1">Email:</span>
                    <span class="text-foreground font-medium">{{ $customer->email ?? '-' }}</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-muted-foreground block mb-1">Alamat Penagihan &amp; Pengiriman:</span>
                    <span class="text-foreground leading-relaxed">{{ $customer->alamat ?? 'Belum ada alamat tersimpan.' }}</span>
                </div>
            </div>
        </div>

        <!-- Sisa Hutang Card -->
        <div class="uk-card uk-card-default border border-border p-5 flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Total Sisa Piutang Berjalan</span>
                <div class="mt-1.5 text-2xl font-bold font-mono {{ $totalSisaHutang > 0 ? 'text-destructive' : 'text-emerald-500' }}">
                    Rp {{ number_format($totalSisaHutang, 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-muted-foreground mt-1">Saldo kewajiban tagihan yang belum terbayar</p>
            </div>
            <div class="mt-4 pt-4 border-t border-border flex items-center justify-between">
                <a href="{{ route('payments.create') }}?customer_id={{ $customer->id }}" 
                   class="uk-btn uk-btn-primary uk-btn-xs text-[11px] shadow-xs">
                    Bayar Piutang &rarr;
                </a>
                <a href="{{ route('invoices.create') }}?customer_id={{ $customer->id }}" 
                   class="text-xs text-primary hover:underline">
                    Terbitkan Nota &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Section: Kartu Piutang & Riwayat Transaksi (Customer Statement of Account) -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-foreground">Kartu Piutang &amp; Riwayat Transaksi</h3>
                    <span class="uk-badge bg-primary/10 text-primary text-[10px] font-bold px-2 py-0.5 rounded">
                        BUKU PEMBANTU PIUTANG
                    </span>
                </div>
                <p class="text-xs text-muted-foreground mt-0.5">
                    Kronologi penambahan piutang dari nota kargo dan pengurangan piutang dari pembayaran customer.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('customers.statement', $customer) }}" target="_blank"
                   class="uk-btn uk-btn-default uk-btn-xs text-[11px] flex items-center gap-1 text-primary shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Rekening Koran</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-2.5 px-4 font-medium">Tanggal</th>
                        <th class="py-2.5 px-4 font-medium">Jenis Transaksi</th>
                        <th class="py-2.5 px-4 font-medium">No. Referensi</th>
                        <th class="py-2.5 px-4 font-medium">Keterangan</th>
                        <th class="py-2.5 px-4 font-medium text-right text-destructive">Tagihan (+ Debit)</th>
                        <th class="py-2.5 px-4 font-medium text-right text-emerald-600">Pembayaran (- Kredit)</th>
                        <th class="py-2.5 px-4 font-medium text-right font-bold text-foreground">Saldo Piutang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($kartuPiutang as $row)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3 px-4 text-muted-foreground whitespace-nowrap">
                                {{ $row['tanggal']->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($row['tipe'] === 'invoice')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        NOTA TAGIHAN
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                        PEMBAYARAN KAS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono font-bold whitespace-nowrap">
                                <a href="{{ $row['url'] }}" class="text-primary hover:underline">
                                    {{ $row['nomor_referensi'] }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-foreground">
                                {{ $row['keterangan'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-destructive whitespace-nowrap">
                                @if($row['debit'] > 0)
                                    Rp {{ number_format($row['debit'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                @if($row['kredit'] > 0)
                                    Rp {{ number_format($row['kredit'], 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-foreground whitespace-nowrap">
                                Rp {{ number_format($row['saldo'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-muted-foreground">
                                Belum ada riwayat transaksi nota maupun pembayaran untuk pelanggan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 1: Lembar Kerja Terkait -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-border flex items-center justify-between">
            <h3 class="text-sm font-semibold text-foreground">Riwayat Muatan Kargo (SMU)</h3>
            <span class="text-xs text-muted-foreground">{{ count($customer->worksheetItems) }} baris kargo tercatat</span>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-2.5 px-4 font-medium">No. Lembar Kerja</th>
                        <th class="py-2.5 px-4 font-medium">Tanggal</th>
                        <th class="py-2.5 px-4 font-medium">No. Master SMU</th>
                        <th class="py-2.5 px-4 font-medium">Koli / Berat</th>
                        <th class="py-2.5 px-4 font-medium">Tarif / Kg</th>
                        <th class="py-2.5 px-4 font-medium text-right">Total Biaya</th>
                        <th class="py-2.5 px-4 font-medium text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($customer->worksheetItems as $item)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3 px-4 font-semibold text-foreground">
                                @if($item->worksheet)
                                    <a href="{{ route('worksheets.show', $item->worksheet) }}" class="text-primary hover:underline font-mono">
                                        {{ $item->worksheet->nomor_lembar_kerja }}
                                    </a>
                                @else
                                    -
                                @endif
                                @if($item->keterangan)
                                    <span class="block text-[10px] text-muted-foreground">{{ $item->keterangan }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-muted-foreground">{{ $item->worksheet ? $item->worksheet->tanggal->format('d/m/Y') : '-' }}</td>
                            <td class="py-3 px-4 text-foreground font-mono font-medium">{{ $item->worksheet ? $item->worksheet->nomor_smu : '-' }}</td>
                            <td class="py-3 px-4 text-foreground">{{ $item->jumlah_koli }} koli ({{ number_format($item->berat_kg, 1) }} kg)</td>
                            <td class="py-3 px-4 text-muted-foreground font-mono">Rp {{ number_format($item->tarif_per_kg, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 font-bold text-foreground font-mono text-right">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
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
                            <td colspan="7" class="py-6 text-center text-muted-foreground">Belum ada muatan kargo tercatat untuk pelanggan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
