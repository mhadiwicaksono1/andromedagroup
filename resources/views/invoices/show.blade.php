@extends('layouts.cargo')

@section('title', 'Nota Penagihan ' . $invoice->nomor_nota)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground font-mono">{{ $invoice->nomor_nota }}</h1>
                @if($invoice->tipe_nota)
                    <span class="uk-label {{ $invoice->tipe_nota === 'pecahan' ? 'uk-label-warning' : 'uk-label-primary' }} text-[10px] uppercase font-bold">
                        {{ $invoice->tipe_nota === 'pecahan' ? 'NOTA PECAHAN' : 'NOTA PERNAMA' }}
                    </span>
                @endif
                @if($invoice->status_pembayaran === 'lunas')
                    <span class="uk-label uk-label-success text-[10px]">LUNAS</span>
                @elseif($invoice->status_pembayaran === 'sebagian')
                    <span class="uk-label uk-label-warning text-[10px]">SEBAGIAN</span>
                @else
                    <span class="uk-label uk-label-danger text-[10px]">BELUM LUNAS</span>
                @endif
            </div>
            <p class="text-xs text-muted-foreground mt-0.5">Tanggal Terbit: {{ $invoice->tanggal->format('d F Y') }}</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if($invoice->sisa_piutang > 0)
                <button type="button" uk-toggle="target: #modal-payment"
                        class="uk-btn uk-btn-primary uk-btn-sm text-xs flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Catat Pembayaran Piutang</span>
                </button>
            @endif

            <a href="{{ route('ias.payments.create', ['invoice_id' => $invoice->id]) }}" 
               class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <span>Bayar ke IAS</span>
            </a>

            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" 
               class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 text-primary shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak Nota PDF</span>
            </a>

            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan nota ini? Semua lembar kerja akan dikembalikan ke status Selesai.')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="uk-btn uk-btn-destructive uk-btn-sm text-xs shadow-xs">
                    Batalkan Nota
                </button>
            </form>

            <a href="{{ route('invoices.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Customer & Summary Banner -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Customer Info -->
        <div class="uk-card uk-card-default border border-border p-6">
            <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground block mb-2">Pelanggan Tertagih</span>
            <h3 class="text-base font-bold text-foreground">{{ $invoice->customer->nama }}</h3>
            <p class="text-xs font-mono text-primary mt-0.5">{{ $invoice->customer->kode_customer }}</p>
            <div class="text-xs text-muted-foreground mt-3 space-y-1">
                <p>Telp: {{ $invoice->customer->telepon ?? '-' }}</p>
                <p>Alamat: {{ $invoice->customer->alamat ?? '-' }}</p>
            </div>
        </div>

        <!-- Total Tagihan -->
        <div class="uk-card uk-card-default border border-border p-4 sm:p-5 flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground block">Total Tagihan Kumulatif</span>
                <div class="text-xl font-bold font-mono text-foreground mt-1">
                    Rp {{ number_format($invoice->total_tagihan_kumulatif, 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-muted-foreground mt-0.5">Nilai nota Rp {{ number_format($invoice->total_nota, 0, ',', '.') }} + Hutang lama Rp {{ number_format($invoice->sisa_hutang_sebelumnya, 0, ',', '.') }}</p>
            </div>
            <div class="text-xs text-muted-foreground pt-3 border-t border-border">
                Terbayar: <span class="font-bold text-emerald-500 font-mono">Rp {{ number_format($invoice->jumlah_dibayar, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Sisa Piutang Highlight -->
        <div class="uk-card uk-card-default border border-border p-6 flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-destructive block">Sisa Saldo Piutang</span>
                <div class="text-3xl font-bold font-mono text-destructive mt-1">
                    Rp {{ number_format($invoice->sisa_piutang, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-muted-foreground mt-0.5">
                    {{ $invoice->sisa_piutang <= 0 ? 'Tagihan nota ini telah terlunasi penuh.' : 'Jumlah yang masih harus ditagihkan.' }}
                </p>
            </div>
            <div class="text-xs text-muted-foreground pt-3 border-t border-border">
                Status: <span class="font-bold uppercase {{ $invoice->status_pembayaran === 'lunas' ? 'text-emerald-500' : 'text-amber-500' }}">{{ $invoice->status_pembayaran }}</span>
            </div>
        </div>
    </div>

    <!-- Items: Included Worksheets Table -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-border flex items-center justify-between">
            <h3 class="text-base font-semibold text-foreground">Rincian Muatan Kargo (Surat Muatan Udara)</h3>
            <span class="text-xs text-muted-foreground">{{ count($invoice->worksheetItems) }} baris muatan ditagihkan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-2.5 px-3 font-medium">No. LK</th>
                        <th class="py-2.5 px-3 font-medium">Tanggal</th>
                        <th class="py-2.5 px-3 font-medium">No. SMU</th>
                        <th class="py-2.5 px-3 font-medium">Marking</th>
                        <th class="py-2.5 px-3 font-medium text-center">Origin</th>
                        <th class="py-2.5 px-3 font-medium text-center">Koli</th>
                        <th class="py-2.5 px-3 font-medium text-right">Berat (kg)</th>
                        <th class="py-2.5 px-3 font-medium text-right">Tarif / Kg</th>
                        <th class="py-2.5 px-3 font-medium text-right">Total Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach($invoice->worksheetItems as $item)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3 px-3 font-mono font-bold text-primary">
                                @if($item->worksheet)
                                    <a href="{{ route('worksheets.show', $item->worksheet) }}" class="hover:underline">
                                        {{ $item->worksheet->nomor_lembar_kerja }}
                                    </a>
                                @else
                                    -
                                @endif
                                @if($item->keterangan)
                                    <span class="block text-[10px] text-muted-foreground font-sans font-normal mt-0.5">{{ $item->keterangan }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-muted-foreground">
                                {{ $item->worksheet ? $item->worksheet->tanggal->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-foreground font-mono font-medium">
                                {{ $item->worksheet ? $item->worksheet->nomor_smu : '-' }}
                            </td>
                            <td class="py-3 px-3 text-foreground font-mono font-bold">
                                {{ $item->marking ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono uppercase text-muted-foreground">
                                {{ $item->origin ?? $item->worksheet->origin ?? 'lop' }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-medium">{{ $item->jumlah_koli }}</td>
                            <td class="py-3 px-3 text-right font-mono">{{ number_format($item->berat_kg, 1) }}</td>
                            <td class="py-3 px-3 text-right text-muted-foreground font-mono">Rp {{ number_format($item->tarif_per_kg, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right font-bold text-foreground font-mono">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Calculation Breakdown Box -->
        <div class="p-4 sm:p-6 border-t border-border flex justify-end">
            <div class="w-full max-w-sm space-y-2 text-xs">
                <div class="flex justify-between text-muted-foreground">
                    <span>Subtotal Kargo:</span>
                    <span class="font-mono text-foreground">Rp {{ number_format($invoice->subtotal_lembar_kerja, 0, ',', '.') }}</span>
                </div>
                @if($invoice->biaya_adm > 0)
                    <div class="flex justify-between text-teal-600 dark:text-teal-400 font-medium">
                        <span>+ Biaya ADM ({{ $invoice->tipe_nota === 'pernama' ? count($invoice->worksheetItems) . ' SMU @ Rp 3.000' : '1 Nota (Rp 3.000)' }}):</span>
                        <span class="font-mono font-bold">Rp {{ number_format($invoice->biaya_adm, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($invoice->penyesuaian_biaya != 0)
                    <div class="flex justify-between text-muted-foreground">
                        <span>Penyesuaian Biaya / Diskon:</span>
                        <span class="font-mono text-foreground">Rp {{ number_format($invoice->penyesuaian_biaya, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-foreground font-semibold pt-2 border-t border-border">
                    <span>Total Nilai Nota Ini:</span>
                    <span class="font-mono text-primary font-bold">Rp {{ number_format($invoice->total_nota, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-amber-500">
                    <span>+ Sisa Hutang Customer Sebelumnya:</span>
                    <span class="font-mono font-bold">Rp {{ number_format($invoice->sisa_hutang_sebelumnya, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-foreground font-bold text-sm pt-2 border-t border-border">
                    <span>Total Tagihan Kumulatif:</span>
                    <span class="font-mono text-foreground font-bold">Rp {{ number_format($invoice->total_tagihan_kumulatif, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-emerald-500">
                    <span>- Total Telah Terbayar:</span>
                    <span class="font-mono font-bold">Rp {{ number_format($invoice->jumlah_dibayar, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-destructive font-bold text-sm pt-2 border-t border-border">
                    <span>Sisa Piutang Berjalan:</span>
                    <span class="font-mono">Rp {{ number_format($invoice->sisa_piutang, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Records Table -->
    <div class="uk-card uk-card-default border border-border overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-border flex items-center justify-between">
            <h3 class="text-base font-semibold text-foreground">Riwayat Pembayaran &amp; Angsuran</h3>
            <span class="text-xs text-muted-foreground">{{ count($invoice->payments) }} transaksi pembayaran</span>
        </div>

        <div class="overflow-x-auto">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                <thead>
                    <tr class="text-muted-foreground border-b border-border bg-muted/40">
                        <th class="py-2.5 px-4 font-medium">No. Bukti Bayar</th>
                        <th class="py-2.5 px-4 font-medium">Tanggal</th>
                        <th class="py-2.5 px-4 font-medium">Metode</th>
                        <th class="py-2.5 px-4 font-medium">Catatan</th>
                        <th class="py-2.5 px-4 font-medium">Diterima Oleh</th>
                        <th class="py-2.5 px-4 font-medium text-right">Jumlah Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($invoice->payments as $pay)
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-emerald-500">{{ $pay->nomor_pembayaran }}</td>
                            <td class="py-3 px-4 text-muted-foreground">{{ $pay->tanggal->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 text-foreground uppercase font-semibold">{{ $pay->metode }}</td>
                            <td class="py-3 px-4 text-muted-foreground">{{ $pay->catatan ?? '-' }}</td>
                            <td class="py-3 px-4 text-foreground">{{ $pay->user->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-500 font-mono">Rp {{ number_format($pay->jumlah, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-muted-foreground">Belum ada pembayaran dicatat untuk nota ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Record Payment (Franken UI Modal) -->
@if($invoice->sisa_piutang > 0)
<div id="modal-payment" uk-modal>
    <div class="uk-modal-dialog uk-modal-body bg-card border border-border rounded-xl max-w-md p-6">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        
        <h3 class="text-lg font-bold tracking-tight text-foreground mb-4">Catat Pembayaran Piutang</h3>

        <form action="{{ route('invoices.payments.store', $invoice) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Maksimal Sisa Piutang</label>
                <div class="p-2.5 rounded-lg bg-muted font-mono font-bold text-destructive text-sm">
                    Rp {{ number_format($invoice->sisa_piutang, 0, ',', '.') }}
                </div>
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Tanggal Pembayaran *</label>
                <input type="date" name="tanggal" value="{{ now()->toDateString() }}" required
                       class="uk-input text-xs">
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Jumlah Pembayaran (Rp) *</label>
                <input type="number" name="jumlah" max="{{ $invoice->sisa_piutang }}" min="1" step="any" value="{{ $invoice->sisa_piutang }}" required
                       class="uk-input text-xs font-mono font-bold text-emerald-500">
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Metode Pembayaran *</label>
                <select name="metode" required class="uk-select text-xs">
                    <option value="transfer">Transfer Bank BCA</option>
                    <option value="tunai">Cash</option>
                </select>
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">Keterangan / Nomor Referensi Transfer</label>
                <input type="text" name="catatan" placeholder="Contoh: Bukti transfer BCA ref#889102"
                       class="uk-input text-xs">
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-border">
                <button type="button" class="uk-btn uk-btn-default uk-modal-close uk-btn-sm text-xs shadow-xs">
                    Batal
                </button>
                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs shadow-sm">
                    Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
