@extends('layouts.cargo')

@section('title', 'Lembar Kerja ' . $worksheet->nomor_lembar_kerja . ' — ' . $worksheet->nomor_smu)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('worksheets.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali</span>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">{{ $worksheet->nomor_lembar_kerja }}</h1>
                    
                    @if(($worksheet->tipe ?? 'pecahan') === 'pernama')
                        <span class="badge-tipe-pernama">
                            PERNAMA (1 CUSTOMER)
                        </span>
                    @else
                        <span class="badge-tipe-pecahan">
                            PECAHAN (KONSOLIDASI)
                        </span>
                    @endif

                    @if($worksheet->status === 'draft')
                        <span class="uk-label text-xs">DRAFT</span>
                    @elseif($worksheet->status === 'selesai')
                        <span class="uk-label uk-label-warning text-xs">SELESAI</span>
                    @else
                        <span class="uk-label uk-label-success text-xs">SEMUA DINOTAKAN</span>
                    @endif
                </div>
                <p class="text-xs text-muted-foreground mt-0.5">
                    Master SMU: <span class="font-mono font-bold text-foreground">{{ $worksheet->nomor_smu }}</span> • Tanggal: {{ $worksheet->tanggal->format('d F Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if(auth()->user()->hasPermission('worksheets.status') && $worksheet->status === 'draft')
                <form action="{{ route('worksheets.updateStatus', $worksheet) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="selesai">
                    <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Tandai Selesai</span>
                    </button>
                </form>
            @endif

            @if(auth()->user()->hasPermission('ias_payments.create'))
            <a href="{{ route('ias.payments.create', ['nomor_smu' => $worksheet->nomor_smu]) }}" 
               class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span>Bayar ke IAS</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('worksheets.edit') && $worksheet->status !== 'dinotakan')
                <a href="{{ route('worksheets.edit', $worksheet) }}" class="uk-btn uk-btn-secondary uk-btn-sm text-xs shadow-xs">
                    Edit SMU / Customer
                </a>
            @endif

            @if(auth()->user()->hasPermission('worksheets.delete') && $worksheet->status !== 'dinotakan')
                @if(!$worksheet->items->whereNotNull('invoice_id')->count())
                    <form action="{{ route('worksheets.destroy', $worksheet) }}" method="POST" class="inline"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus Lembar Kerja {{ $worksheet->nomor_lembar_kerja }}? Data akan di-soft delete.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="uk-btn uk-btn-destructive uk-btn-sm text-xs shadow-xs">
                            Hapus LK
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Master SMU Performance Cards (Franken UI Cards) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="uk-card uk-card-default p-3.5 border border-border">
            <span class="text-[11px] text-muted-foreground uppercase font-medium block">Total Customer</span>
            <div class="text-xl font-bold tracking-tight text-foreground mt-0.5">{{ count($worksheet->items) }} Pelanggan</div>
        </div>
        <div class="uk-card uk-card-default p-3.5 border border-border">
            <span class="text-[11px] text-muted-foreground uppercase font-medium block">Total Koli SMU</span>
            <div class="text-xl font-bold tracking-tight text-foreground font-mono mt-0.5">{{ $worksheet->total_koli }} Koli</div>
        </div>
        <div class="uk-card uk-card-default p-3.5 border border-border">
            <span class="text-[11px] text-muted-foreground uppercase font-medium block">Total Berat Riil</span>
            <div class="text-xl font-bold tracking-tight text-foreground font-mono mt-0.5">{{ number_format($worksheet->total_berat_kg, 2) }} kg</div>
        </div>
        <div class="uk-card uk-card-default p-3.5 border border-border">
            <span class="text-[11px] text-muted-foreground uppercase font-medium block">Total Nilai SMU</span>
            <div class="text-xl font-bold tracking-tight text-primary font-mono mt-0.5">Rp {{ number_format($worksheet->total_biaya, 0, ',', '.') }}</div>
        </div>
    </div>

    @if($worksheet->keterangan)
        <div class="uk-card uk-card-default p-4 border border-border text-xs text-muted-foreground">
            <strong class="text-foreground block mb-0.5 uppercase text-[10px]">Keterangan Rute / Penerbangan:</strong>
            {{ $worksheet->keterangan }}
        </div>
    @endif

    <!-- Breakdown Table of Customers inside this SMU -->
    <div class="uk-card uk-card-default border border-border">
        <div class="uk-card-header p-5 border-b border-border flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-foreground">Rincian Muatan Kargo per Customer</h2>
                <p class="text-xs text-muted-foreground mt-0.5">Daftar pelanggan, marking koli, dan rincian tarif dalam SMU {{ $worksheet->nomor_smu }}.</p>
            </div>
            <span class="text-xs text-muted-foreground font-mono">{{ count($worksheet->items) }} baris muatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-2.5 px-4 font-medium">Pelanggan (Customer)</th>
                        <th class="py-2.5 px-4 font-medium">Marking (Tanda Koli)</th>
                        <th class="py-2.5 px-4 font-medium text-center">Koli</th>
                        <th class="py-2.5 px-4 font-medium text-right">Berat (kg)</th>
                        <th class="py-2.5 px-4 font-medium text-right">Tarif / Kg</th>
                        <th class="py-2.5 px-4 font-medium text-right">Total Biaya</th>
                        <th class="py-2.5 px-4 font-medium">Keterangan Barang</th>
                        <th class="py-2.5 px-4 font-medium text-center">Status Nota</th>
                        <th class="py-2.5 px-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($worksheet->items as $item)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ route('customers.show', $item->customer) }}" class="font-semibold text-foreground hover:text-primary transition-colors">
                                    {{ $item->customer->nama }}
                                </a>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] font-mono text-muted-foreground">{{ $item->customer->kode_customer }}</span>
                                    @if($item->customer->kategori === 'prioritas')
                                        <span class="text-[9px] font-bold text-amber-700 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300 px-1 py-0.2 rounded">PRIORITAS</span>
                                    @else
                                        <span class="text-[9px] font-medium text-muted-foreground bg-muted px-1 py-0.2 rounded">STANDAR</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($item->marking)
                                    <span class="font-mono font-medium text-foreground bg-muted/70 px-2 py-0.5 rounded border border-border">
                                        {{ $item->marking }}
                                    </span>
                                @else
                                    <span class="text-muted-foreground italic">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-muted-foreground font-mono">{{ $item->jumlah_koli }}</td>
                            <td class="py-3 px-4 text-right font-mono text-muted-foreground">{{ number_format($item->berat_kg, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-muted-foreground">Rp {{ number_format($item->tarif_per_kg, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-bold text-foreground font-mono">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-muted-foreground">{{ $item->keterangan ?? '-' }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($item->status === 'dinotakan' && $item->invoice)
                                    <a href="{{ route('invoices.show', $item->invoice) }}" class="uk-label uk-label-success text-[10px] hover:opacity-80">
                                        {{ $item->invoice->nomor_nota }}
                                    </a>
                                @elseif($item->status === 'selesai')
                                    <span class="uk-label uk-label-warning text-[10px]">Siap Dinotakan</span>
                                @else
                                    <span class="uk-label text-[10px]">Draft</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($item->status === 'selesai' && !$item->invoice_id)
                                    <a href="{{ route('invoices.create') }}?customer_id={{ $item->customer_id }}" 
                                       class="uk-btn uk-btn-primary uk-btn-sm text-[11px] py-1 px-2.5 whitespace-nowrap shadow-xs">
                                        Buat Nota
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
