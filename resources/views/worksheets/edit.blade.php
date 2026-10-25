@extends('layouts.cargo')

@section('title', 'Edit Lembar Kerja — ' . $worksheet->nomor_lembar_kerja)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Edit Lembar Kerja (SMU)</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Perbarui informasi master SMU, tipe transaksi, atau baris kargo pelanggan.</p>
        </div>
        <a href="{{ route('worksheets.show', $worksheet) }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <form action="{{ route('worksheets.update', $worksheet) }}" method="POST" id="form-worksheet" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Client-Side & Top-Level Validation Alert Box -->
        <div id="client-validation-alert" class="uk-alert uk-alert-danger mb-4 p-4 rounded-lg text-xs shadow-sm hidden" uk-alert>
            <a href class="uk-alert-close" uk-close></a>
            <div class="font-bold uppercase tracking-wider mb-1 flex items-center gap-2 text-destructive">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>Mohon Periksa Data Formulir Berikut:</span>
            </div>
            <ul id="client-validation-list" class="list-disc list-inside space-y-1 mt-2 text-destructive font-medium"></ul>
        </div>

        @if($errors->has('items'))
            <div class="uk-alert uk-alert-danger p-3 rounded-lg text-xs mb-4" uk-alert>
                <div class="font-bold">{{ $errors->first('items') }}</div>
            </div>
        @endif

        <!-- Card 1: Master SMU & Tipe Transaksi -->
        <div class="uk-card uk-card-default border border-border">
            <div class="uk-card-header p-5 border-b border-border">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-primary">1. Informasi Master SMU &amp; Tipe Transaksi</h3>
            </div>

            <div class="uk-card-body p-6 space-y-5">
                <!-- Tipe Lembar Kerja (Pernama vs Pecahan) -->
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-2 block">
                        Tipe Lembar Kerja *
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-3 p-3.5 rounded-lg border border-border cursor-pointer hover:bg-muted/40 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:ring-1 has-[:checked]:ring-primary">
                            <input type="radio" name="tipe" value="pecahan" {{ old('tipe', $worksheet->tipe ?? 'pecahan') === 'pecahan' ? 'checked' : '' }} 
                                   onchange="onTipeChange('pecahan')" class="uk-radio text-primary mt-0.5">
                            <div>
                                <span class="text-xs font-bold text-foreground block">Pecahan (Konsolidasi Multi-Pelanggan)</span>
                                <span class="text-[11px] text-muted-foreground block mt-0.5">Satu nomor Master SMU memuat barang dari banyak customer sekaligus dengan rincian koli &amp; tarif masing-masing.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-lg border border-border cursor-pointer hover:bg-muted/40 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:ring-1 has-[:checked]:ring-primary">
                            <input type="radio" name="tipe" value="pernama" {{ old('tipe', $worksheet->tipe ?? 'pecahan') === 'pernama' ? 'checked' : '' }} 
                                   onchange="onTipeChange('pernama')" class="uk-radio text-primary mt-0.5">
                            <div>
                                <span class="text-xs font-bold text-foreground block">Pernama (Khusus 1 Pelanggan Tunggal)</span>
                                <span class="text-[11px] text-muted-foreground block mt-0.5">Satu nomor Master SMU khusus ditujukan untuk 1 customer saja (hanya 1 baris pelanggan).</span>
                            </div>
                        </label>
                    </div>
                    @error('tipe')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-border">
                    <div>
                        <label class="uk-form-label text-xs font-medium text-foreground mb-1 block">No. Lembar Kerja *</label>
                        <input type="text" name="nomor_lembar_kerja" value="{{ old('nomor_lembar_kerja', $worksheet->nomor_lembar_kerja) }}" required readonly
                               class="uk-input text-xs font-mono font-bold bg-muted cursor-not-allowed @error('nomor_lembar_kerja') border-destructive @enderror">
                        @error('nomor_lembar_kerja')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-medium text-foreground mb-1 block">Tanggal Transaksi *</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $worksheet->tanggal->format('Y-m-d')) }}" required
                               class="uk-input text-xs @error('tanggal') border-destructive @enderror">
                        @error('tanggal')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-medium text-foreground mb-1 block">Nomor Master SMU *</label>
                        <input type="text" name="nomor_smu" value="{{ old('nomor_smu', $worksheet->nomor_smu) }}" required
                               class="uk-input text-xs font-mono font-semibold @error('nomor_smu') border-destructive @enderror">
                        @error('nomor_smu')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="uk-form-label text-xs font-medium text-foreground mb-1 flex items-center justify-between">
                            <span>Koli SMU *</span>
                            <span class="text-[10px] text-muted-foreground font-normal">Fisik Master</span>
                        </label>
                        <input type="number" name="koli_smu" id="header_koli_smu" value="{{ old('koli_smu', $worksheet->display_koli_smu) }}" min="1" placeholder="Contoh: 10"
                               class="uk-input text-xs font-mono font-semibold @error('koli_smu') border-destructive @enderror">
                        @error('koli_smu')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-medium text-foreground mb-1 flex items-center justify-between">
                            <span>Kilo SMU (kg) *</span>
                            <span class="text-[10px] text-muted-foreground font-normal">Berat Master</span>
                        </label>
                        <input type="number" step="0.01" name="berat_smu" id="header_berat_smu" value="{{ old('berat_smu', $worksheet->display_berat_smu) }}" min="0.01" placeholder="Contoh: 150.00"
                               class="uk-input text-xs font-mono font-semibold @error('berat_smu') border-destructive @enderror">
                        @error('berat_smu')
                            <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-medium text-foreground mb-1 block">Status Lembar Kerja *</label>
                        <select name="status" required class="uk-select text-xs">
                            <option value="selesai" {{ old('status', $worksheet->status) === 'selesai' ? 'selected' : '' }}>Selesai (Siap Ditagihkan)</option>
                            <option value="draft" {{ old('status', $worksheet->status) === 'draft' ? 'selected' : '' }}>Draft (Proses Timbang)</option>
                        </select>
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-medium text-foreground mb-1 block">Origin (Asal Bandara) *</label>
                        <select name="origin" class="uk-select text-xs font-semibold">
                            <option value="lop" {{ old('origin', $worksheet->origin ?? 'lop') === 'lop' ? 'selected' : '' }}>LOP — Lombok</option>
                            <option value="sub" {{ old('origin', $worksheet->origin) === 'sub' ? 'selected' : '' }}>SUB — Surabaya</option>
                            <option value="dps" {{ old('origin', $worksheet->origin) === 'dps' ? 'selected' : '' }}>DPS — Denpasar</option>
                            <option value="cgk" {{ old('origin', $worksheet->origin) === 'cgk' ? 'selected' : '' }}>CGK — Jakarta</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="uk-form-label text-xs font-medium text-foreground mb-1 block">Keterangan / Rute Penerbangan</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan', $worksheet->keterangan) }}"
                           class="uk-input text-xs">
                </div>
            </div>
        </div>

        <!-- Card 2: Multi-Customer Items Table with Marking -->
        <div class="uk-card uk-card-default border border-border">
            <div class="uk-card-header p-5 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-primary">2. Rincian Pelanggan, Marking &amp; Muatan Kargo</h3>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Perbarui customer, marking koli, berat riil, dan tarif per kg.</p>
                </div>
                <button type="button" id="btn-add-customer-row" 
                        class="uk-btn uk-btn-secondary uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs self-start">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Baris Pelanggan</span>
                </button>
            </div>

            <div class="uk-card-body p-6 space-y-4">
                <div class="overflow-x-auto rounded-lg border border-border">
                    <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs" id="table-items">
                        <thead>
                            <tr class="bg-muted/50 text-muted-foreground border-b border-border">
                                <th class="py-2.5 px-3 min-w-[200px]">Customer *</th>
                                <th class="py-2.5 px-3 min-w-[140px]">Marking (Tanda Koli)</th>
                                <th class="py-2.5 px-3 w-20 text-center">Koli *</th>
                                <th class="py-2.5 px-3 w-28 text-right">Berat (kg) *</th>
                                <th class="py-2.5 px-3 w-32 text-right">Tarif/kg (Rp) *</th>
                                <th class="py-2.5 px-3 w-36 text-right">Total Biaya</th>
                                <th class="py-2.5 px-3 min-w-[160px]">Keterangan Barang</th>
                                <th class="py-2.5 px-3 w-12 text-center col-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody" class="divide-y divide-border">
                            @foreach($worksheet->items as $index => $item)
                                <tr class="item-row hover:bg-muted/40 transition-colors">
                                    <td class="py-2.5 px-3">
                                        <select name="items[{{ $index }}][customer_id]" class="item-customer select2 uk-select text-xs">
                                            <option value="">-- Cari / Pilih Customer --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}" 
                                                        data-kategori="{{ $c->kategori }}" 
                                                        data-tarif="{{ (float) ($c->tarif_default ?? 2100) }}"
                                                        data-tarif-pecahan="{{ (float) ($c->tarif_pecahan ?? 2000) }}"
                                                        data-tarif-pernama="{{ (float) ($c->tarif_pernama ?? 1980) }}"
                                                        {{ $item->customer_id == $c->id ? 'selected' : '' }}>
                                                    {{ $c->kode_customer }} — {{ $c->nama }} [{{ strtoupper($c->kategori) }}]
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" name="items[{{ $index }}][marking]" value="{{ old('items.'.$index.'.marking', $item->marking) }}" placeholder="Contoh: MK-01, Fragile..." 
                                               class="item-marking uk-input text-xs font-mono">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="number" name="items[{{ $index }}][jumlah_koli]" value="{{ old('items.'.$index.'.jumlah_koli', $item->jumlah_koli) }}" min="1" required 
                                               class="item-koli uk-input text-xs text-center font-mono">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="number" step="0.01" name="items[{{ $index }}][berat_kg]" value="{{ old('items.'.$index.'.berat_kg', $item->berat_kg) }}" min="0.01" required 
                                               class="item-berat uk-input text-xs font-mono text-right">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="number" step="10" name="items[{{ $index }}][tarif_per_kg]" value="{{ old('items.'.$index.'.tarif_per_kg', $item->tarif_per_kg) }}" min="0" required readonly
                                               class="item-tarif uk-input text-xs font-mono text-right font-semibold bg-muted/60 cursor-not-allowed"
                                               title="Tarif terkunci otomatis sesuai skema Master Customer">
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <span class="item-total-text font-bold text-foreground font-mono">
                                            Rp {{ number_format($item->total_biaya, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="text" name="items[{{ $index }}][keterangan]" value="{{ old('items.'.$index.'.keterangan', $item->keterangan) }}" placeholder="Rincian barang..." 
                                               class="uk-input text-xs">
                                    </td>
                                    <td class="py-2.5 px-3 text-center col-action">
                                        <button type="button" class="btn-remove-row uk-btn uk-btn-ghost uk-btn-sm p-1 text-muted-foreground hover:text-destructive">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Grand Totals Display Box -->
                <div class="p-4 rounded-lg bg-muted/40 border border-border grid grid-cols-2 sm:grid-cols-4 gap-4 items-center">
                    <div>
                        <span class="text-[11px] text-muted-foreground block uppercase font-medium">Total Pelanggan:</span>
                        <span class="text-sm font-bold text-foreground font-mono" id="grand-cust-count">{{ count($worksheet->items) }} Customer</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-muted-foreground block uppercase font-medium">Total Koli SMU:</span>
                        <span class="text-sm font-bold text-foreground font-mono" id="grand-koli">{{ $worksheet->total_koli }} Koli</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-muted-foreground block uppercase font-medium">Total Berat SMU:</span>
                        <span class="text-sm font-bold text-foreground font-mono" id="grand-berat">{{ number_format($worksheet->total_berat_kg, 2) }} kg</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] text-muted-foreground block uppercase font-medium">Total Biaya SMU:</span>
                        <span class="text-lg font-bold text-primary font-mono" id="grand-biaya">
                            Rp {{ number_format($worksheet->total_biaya, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Submit Footer -->
            <div class="uk-card-footer p-4 border-t border-border flex items-center justify-end gap-2.5">
                <a href="{{ route('worksheets.show', $worksheet) }}" class="uk-btn uk-btn-default uk-btn-sm text-xs font-medium shadow-xs">
                    Batal
                </a>
                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs font-semibold shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Customer Options Template for JS -->
<template id="customer-options-template">
    <option value="">-- Pilih Customer --</option>
    @foreach($customers as $c)
        <option value="{{ $c->id }}" 
                data-kategori="{{ $c->kategori }}" 
                data-tarif="{{ (float) ($c->tarif_default ?? 2100) }}"
                data-tarif-pecahan="{{ (float) ($c->tarif_pecahan ?? 2000) }}"
                data-tarif-pernama="{{ (float) ($c->tarif_pernama ?? 1980) }}">
            {{ $c->kode_customer }} — {{ $c->nama }} [{{ strtoupper($c->kategori) }}]
        </option>
    @endforeach
</template>

@push('scripts')
<script>
    let currentTipe = '{{ old('tipe', $worksheet->tipe ?? 'pecahan') }}';

    function getTarifForOption(opt, tipe) {
        if (!opt || !opt.value) return 2100;
        const kat = opt.getAttribute('data-kategori');
        if (kat === 'prioritas') {
            if (tipe === 'pernama') {
                return parseFloat(opt.getAttribute('data-tarif-pernama')) || 1980;
            } else {
                return parseFloat(opt.getAttribute('data-tarif-pecahan')) || 2000;
            }
        } else {
            return parseFloat(opt.getAttribute('data-tarif')) || 2100;
        }
    }

    function onTipeChange(tipe) {
        currentTipe = tipe;
        const btnAddRow = document.getElementById('btn-add-customer-row');
        const rows = document.querySelectorAll('.item-row');

        if (tipe === 'pernama') {
            if (btnAddRow) btnAddRow.classList.add('hidden');
            if (rows.length > 1) {
                if (confirm('Tipe Pernama hanya memperbolehkan 1 customer tunggal. Hapus baris tambahan?')) {
                    for (let i = 1; i < rows.length; i++) {
                        rows[i].remove();
                    }
                }
            }
            const firstRowDelete = document.querySelector('.item-row .btn-remove-row');
            if (firstRowDelete) firstRowDelete.classList.add('invisible');
        } else {
            if (btnAddRow) btnAddRow.classList.remove('hidden');
            const firstRowDelete = document.querySelector('.item-row .btn-remove-row');
            if (firstRowDelete) firstRowDelete.classList.remove('invisible');
        }

        // Auto update tarif on all rows based on new tipe
        document.querySelectorAll('.item-row').forEach(row => {
            const custSelect = row.querySelector('.item-customer');
            if (custSelect && custSelect.value) {
                const opt = custSelect.options[custSelect.selectedIndex];
                const inputTarif = row.querySelector('.item-tarif');
                if (opt && inputTarif) {
                    inputTarif.value = getTarifForOption(opt, currentTipe);
                }
            }
        });

        recalculate();
    }

    function onCustomerSelect(selectEl) {
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        if (!selectedOpt) return;
        const tarif = getTarifForOption(selectedOpt, currentTipe);
        const row = selectEl.closest('.item-row');
        if (row) {
            const inputTarif = row.querySelector('.item-tarif');
            if (inputTarif) {
                inputTarif.value = tarif;
            }
        }
        recalculate();
    }

    function recalculate() {
        let totalKoli = 0;
        let totalBerat = 0;
        let totalBiaya = 0;
        const rows = document.querySelectorAll('.item-row');

        rows.forEach(row => {
            const koli = parseInt(row.querySelector('.item-koli')?.value) || 0;
            const berat = parseFloat(row.querySelector('.item-berat')?.value) || 0;
            const tarif = parseFloat(row.querySelector('.item-tarif')?.value) || 0;
            // Pembulatan ke atas ke kelipatan seribu terdekat sesuai standar nota kargo
            const raw = berat * tarif;
            const rowTotal = raw > 0 ? Math.ceil(raw / 1000) * 1000 : 0;

            const totalText = row.querySelector('.item-total-text');
            if (totalText) totalText.textContent = 'Rp ' + rowTotal.toLocaleString('id-ID');

            totalKoli += koli;
            totalBerat += berat;
            totalBiaya += rowTotal;
        });

        const headerKoli = document.getElementById('header_koli_smu');
        const headerBerat = document.getElementById('header_berat_smu');

        // Jika mode pernama, sync dua arah
        if (currentTipe === 'pernama') {
            const firstRowKoli = document.querySelector('.item-row .item-koli');
            const firstRowBerat = document.querySelector('.item-row .item-berat');
            if (headerKoli && firstRowKoli && !headerKoli.value && firstRowKoli.value) {
                headerKoli.value = firstRowKoli.value;
            }
            if (headerBerat && firstRowBerat && !headerBerat.value && firstRowBerat.value) {
                headerBerat.value = firstRowBerat.value;
            }
        }

        const custCountEl = document.getElementById('grand-cust-count');
        if (custCountEl) custCountEl.textContent = rows.length + ' Customer';
        const koliEl = document.getElementById('grand-koli');
        if (koliEl) koliEl.textContent = totalKoli.toLocaleString('id-ID') + ' Koli';
        const beratEl = document.getElementById('grand-berat');
        if (beratEl) beratEl.textContent = totalBerat.toFixed(2) + ' kg';
        const biayaEl = document.getElementById('grand-biaya');
        if (biayaEl) biayaEl.textContent = 'Rp ' + totalBiaya.toLocaleString('id-ID');
    }

    function bindRowEvents(row) {
        row.querySelector('.item-koli')?.addEventListener('input', function() {
            if (currentTipe === 'pernama') {
                const headerKoli = document.getElementById('header_koli_smu');
                if (headerKoli) headerKoli.value = this.value;
            }
            recalculate();
        });
        row.querySelector('.item-berat')?.addEventListener('input', function() {
            if (currentTipe === 'pernama') {
                const headerBerat = document.getElementById('header_berat_smu');
                if (headerBerat) headerBerat.value = this.value;
            }
            recalculate();
        });
        row.querySelector('.item-tarif')?.addEventListener('input', recalculate);

        row.querySelector('.btn-remove-row')?.addEventListener('click', function () {
            if (currentTipe === 'pernama') {
                return;
            }
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                recalculate();
            } else {
                alert('Minimal harus ada 1 baris pelanggan dalam lembar kerja.');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.getElementById('items-tbody');
        const btnAddRow = document.getElementById('btn-add-customer-row');
        const customerOptionsHtml = document.getElementById('customer-options-template').innerHTML;

        let rowIndex = {{ count($worksheet->items) }};

        document.querySelectorAll('.item-row').forEach(bindRowEvents);

        if (btnAddRow) {
            btnAddRow.addEventListener('click', function () {
                if (currentTipe === 'pernama') return;

                const tr = document.createElement('tr');
                tr.className = 'item-row hover:bg-muted/40 transition-colors';
                tr.innerHTML = `
                    <td class="py-2.5 px-3">
                        <select name="items[${rowIndex}][customer_id]" class="item-customer select2 uk-select text-xs">
                            ${customerOptionsHtml}
                        </select>
                    </td>
                    <td class="py-2.5 px-3">
                        <input type="text" name="items[${rowIndex}][marking]" placeholder="Contoh: MK-01, Fragile..." 
                               class="item-marking uk-input text-xs font-mono">
                    </td>
                    <td class="py-2.5 px-3">
                        <input type="number" name="items[${rowIndex}][jumlah_koli]" value="1" min="1" required 
                               class="item-koli uk-input text-xs text-center font-mono">
                    </td>
                    <td class="py-2.5 px-3">
                        <input type="number" step="0.01" name="items[${rowIndex}][berat_kg]" placeholder="0.00" min="0.01" required 
                               class="item-berat uk-input text-xs font-mono text-right">
                    </td>
                    <td class="py-2.5 px-3">
                        <input type="number" step="10" name="items[${rowIndex}][tarif_per_kg]" value="2100" min="0" required readonly
                               class="item-tarif uk-input text-xs font-mono text-right font-semibold bg-muted/60 cursor-not-allowed"
                               title="Tarif terkunci otomatis sesuai skema Master Customer">
                    </td>
                    <td class="py-2.5 px-3 text-right">
                        <span class="item-total-text font-bold text-foreground font-mono">Rp 0</span>
                    </td>
                    <td class="py-2.5 px-3">
                        <input type="text" name="items[${rowIndex}][keterangan]" placeholder="Rincian barang..." 
                               class="uk-input text-xs">
                    </td>
                    <td class="py-2.5 px-3 text-center col-action">
                        <button type="button" class="btn-remove-row uk-btn uk-btn-ghost uk-btn-sm p-1 text-muted-foreground hover:text-destructive">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                `;

                tbody.appendChild(tr);
                bindRowEvents(tr);
                $(tr).find('.item-customer.select2').select2({
                    width: '100%',
                    placeholder: '-- Cari / Pilih Customer --'
                });
                rowIndex++;
                recalculate();
            });
        }

        const headerKoli = document.getElementById('header_koli_smu');
        const headerBerat = document.getElementById('header_berat_smu');

        headerKoli?.addEventListener('input', function() {
            if (currentTipe === 'pernama') {
                const firstRowKoli = document.querySelector('.item-row .item-koli');
                if (firstRowKoli) {
                    firstRowKoli.value = this.value;
                    recalculate();
                }
            }
        });

        headerBerat?.addEventListener('input', function() {
            if (currentTipe === 'pernama') {
                const firstRowBerat = document.querySelector('.item-row .item-berat');
                if (firstRowBerat) {
                    firstRowBerat.value = this.value;
                    recalculate();
                }
            }
        });

        // Initialize Select2 on existing item-customer
        $(document).ready(function() {
            $('.item-customer.select2').select2({
                width: '100%',
                placeholder: '-- Cari / Pilih Customer --'
            });

            $(document).on('change', '.item-customer', function() {
                onCustomerSelect(this);
            });
        });

        // Form validation on submit
        const form = document.getElementById('form-worksheet');
        const alertBox = document.getElementById('client-validation-alert');
        const alertList = document.getElementById('client-validation-list');

        function showErrorAlert(messages) {
            if (!alertBox || !alertList) {
                alert(messages.join('\n'));
                return;
            }
            alertList.innerHTML = messages.map(m => `<li>${m}</li>`).join('');
            alertBox.classList.remove('hidden');
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        form?.addEventListener('submit', function (e) {
            const errors = [];

            const smuInput = form.querySelector('input[name="nomor_smu"]');
            if (!smuInput || !smuInput.value.trim()) {
                errors.push('Nomor Master SMU wajib diisi.');
                smuInput?.classList.add('border-destructive');
            } else {
                smuInput?.classList.remove('border-destructive');
            }

            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 0) {
                errors.push('Minimal harus ada 1 baris kargo pelanggan.');
            }

            if (currentTipe === 'pernama' && rows.length > 1) {
                errors.push('Tipe Pernama hanya boleh berisi tepat 1 baris pelanggan.');
            }

            rows.forEach((row, idx) => {
                const custSelect = row.querySelector('.item-customer');
                const koliInput = row.querySelector('.item-koli');
                const beratInput = row.querySelector('.item-berat');
                const tarifInput = row.querySelector('.item-tarif');

                if (!custSelect || !custSelect.value) {
                    errors.push(`Baris ${idx + 1}: Customer wajib dipilih.`);
                    $(row).find('.select2-selection').addClass('!border-destructive');
                } else {
                    $(row).find('.select2-selection').removeClass('!border-destructive');
                }

                if (!koliInput || !koliInput.value || parseInt(koliInput.value) < 1) {
                    errors.push(`Baris ${idx + 1}: Koli kargo minimal 1.`);
                    koliInput?.classList.add('border-destructive');
                } else {
                    koliInput?.classList.remove('border-destructive');
                }

                if (!beratInput || !beratInput.value || parseFloat(beratInput.value) <= 0) {
                    errors.push(`Baris ${idx + 1}: Berat kargo (kg) harus lebih dari 0.`);
                    beratInput?.classList.add('border-destructive');
                } else {
                    beratInput?.classList.remove('border-destructive');
                }

                if (!tarifInput || tarifInput.value === '' || parseFloat(tarifInput.value) < 0) {
                    errors.push(`Baris ${idx + 1}: Tarif per kg wajib diisi.`);
                    tarifInput?.classList.add('border-destructive');
                } else {
                    tarifInput?.classList.remove('border-destructive');
                }
            });

            if (errors.length > 0) {
                e.preventDefault();
                showErrorAlert(errors);
                return false;
            }

            if (alertBox) alertBox.classList.add('hidden');
        });

        onTipeChange(currentTipe);
        document.querySelectorAll('.item-row').forEach(row => {
            const custSelect = row.querySelector('.item-customer');
            if (custSelect && custSelect.value) {
                onCustomerSelect(custSelect);
            }
        });
        recalculate();
    });
</script>
@endpush
@endsection
