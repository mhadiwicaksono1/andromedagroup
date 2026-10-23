@extends('layouts.cargo')

@section('title', 'Catat Pembayaran ke IAS — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Catat Pembayaran ke IAS</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Input transaksi pembayaran terminal handling &amp; pergudangan kargo ke InJourney Aviation Services.</p>
        </div>
        <a href="{{ route('ias.payments.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('ias.payments.store') }}" method="POST" id="form-ias-payment" class="space-y-6">
        @csrf

        <!-- Card 1: Data Transaksi IAS -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-border pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-primary">1. Informasi Transaksi IAS</h3>
                </div>
                <span class="text-[11px] text-muted-foreground">InJourney Aviation Services Terminal Handling</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Tanggal Pembayaran *
                    </label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', $today) }}" required
                           class="uk-input text-xs">
                    @error('tanggal')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Nomor Transaksi / Billing IAS *
                    </label>
                    <input type="text" name="nomor_transaksi_ias" value="{{ old('nomor_transaksi_ias', $autoNomor) }}" required
                           class="uk-input text-xs font-mono font-bold">
                    @error('nomor_transaksi_ias')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Status Transaksi *
                    </label>
                    <select name="status" required class="uk-select text-xs">
                        <option value="berhasil" {{ old('status', 'berhasil') === 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="gagal" {{ old('status') === 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                    @error('status')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Card 2: Dasar Pembayaran: Nota Penagihan (Bisa Lebih dari 1 Nota) -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-border pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-foreground">2. Dasar Pembayaran: Nota Penagihan (Bisa Lebih dari 1 Nota)</h3>
                </div>
                <span class="text-[11px] text-muted-foreground">Nomor SMU, berat kargo &amp; estimasi biaya terminal IAS otomatis ditarik dari nota yang dipilih</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Multi-Select Pilihan Nota Penagihan -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider block">
                            Pilih Nota Penagihan (Multi-Select)
                        </label>
                        <span class="text-[11px] text-primary font-medium">Bisa pilih &gt; 1 nota</span>
                    </div>
                    
                    <select name="invoice_ids[]" id="select_invoices" multiple="multiple" class="select2 uk-select text-xs" data-placeholder="-- Cari / Pilih satu atau beberapa Nota --">
                        @foreach($invoices as $inv)
                            @php
                                $invSmus = $inv->worksheetItems->map(fn($it) => $it->worksheet?->nomor_smu)->filter()->unique()->values();
                                $invWeight = $inv->worksheetItems->sum('berat_kg');
                                $isSelected = in_array($inv->id, old('invoice_ids', $selectedInvoiceIds ?? []));
                            @endphp
                            <option value="{{ $inv->id }}" 
                                    data-nomor-nota="{{ $inv->nomor_nota }}"
                                    data-total="{{ (float) $inv->total_nota }}"
                                    data-customer="{{ $inv->customer->nama ?? 'Umum' }}"
                                    data-smus="{{ $invSmus->implode(', ') }}"
                                    data-weight="{{ (float) $invWeight }}"
                                    {{ $isSelected ? 'selected' : '' }}>
                                {{ $inv->nomor_nota }} — {{ $inv->customer->nama ?? 'Umum' }} (Rp {{ number_format($inv->total_nota, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-2 pt-1">
                        <span class="text-[11px] text-muted-foreground whitespace-nowrap">Rangkuman No. Nota:</span>
                        <input type="text" name="nomor_nota" id="input_nomor_nota" value="{{ old('nomor_nota') }}" 
                               placeholder="Contoh: INV/202610/0001, INV/202610/0002" class="uk-input text-xs font-mono h-8">
                    </div>
                    <span class="text-[10px] text-muted-foreground block">Otomatis terisi dari nota yang dipilih di atas, atau bisa diketik manual jika pembayaran massal.</span>
                    @error('nomor_nota')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Otomatisasi Rujukan Master SMU dari Nota -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider block">
                            Dasar Master SMU (Otomatis dari Nota)
                        </label>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold uppercase tracking-wider">Terhubung Otomatis</span>
                    </div>

                    <div class="relative">
                        <input type="text" name="nomor_smu" id="input_nomor_smu" value="{{ old('nomor_smu', $selectedSmu) }}" 
                               placeholder="Nomor SMU otomatis muncul saat nota dipilih..." class="uk-input text-xs font-mono font-bold bg-muted/40">
                    </div>
                    <span class="text-[10px] text-muted-foreground block">
                        Kumpulan nomor Master SMU maskapai yang otomatis dirangkum dari isi nota yang dipilih.
                    </span>
                    @error('nomor_smu')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror

                    <div id="badge-smus-container" class="flex flex-wrap gap-1.5 pt-1">
                        <!-- Badges SMU injected dynamically -->
                    </div>
                </div>
            </div>

            <!-- Dynamic Selected Invoices Table -->
            <div id="selected-invoices-box" class="space-y-2 hidden">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-foreground">Daftar Nota Penagihan Terpilih (<span id="count-selected-invoices">0</span> Nota)</span>
                    <button type="button" id="btn-clear-invoices" class="text-[11px] text-destructive hover:underline cursor-pointer">
                        Kosongkan Pilihan Nota
                    </button>
                </div>
                <div class="border border-border rounded-lg overflow-hidden">
                    <table class="uk-table uk-table-small uk-table-divider text-xs">
                        <thead>
                            <tr class="bg-muted/50 text-muted-foreground">
                                <th class="py-2 px-3">No. Nota</th>
                                <th class="py-2 px-3">Pelanggan</th>
                                <th class="py-2 px-3">Master SMU</th>
                                <th class="py-2 px-3 text-right">Berat Kargo</th>
                                <th class="py-2 px-3 text-right">Total Nota</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-selected-invoices" class="divide-y divide-border font-mono">
                            <!-- Injected dynamically via JS -->
                        </tbody>
                        <tfoot class="bg-muted/30 font-semibold text-foreground">
                            <tr>
                                <td colspan="3" class="py-2 px-3">Total Akumulasi Dasar Nota:</td>
                                <td class="py-2 px-3 text-right font-mono text-foreground" id="table-total-weight">0 kg</td>
                                <td class="py-2 px-3 text-right font-mono text-primary font-bold" id="table-total-amount">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Dynamic Basis Preview Card -->
            <div id="basis-info-card" class="p-4 rounded-xl border border-border bg-muted/40 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-border/70 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </span>
                        <div>
                            <span class="text-xs font-bold text-foreground block">Ringkasan Dasar Pembayaran IAS</span>
                            <span class="text-[11px] text-muted-foreground">Informasi acuan nota &amp; SMU untuk menetapkan jumlah nominal pembayaran</span>
                        </div>
                    </div>
                    <span id="basis-status-badge" class="uk-label uk-label-default text-[10px] uppercase font-mono self-start sm:self-auto">
                        Belum Ada Dasar Dipilih
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <span class="text-[11px] text-muted-foreground block">Pelanggan Terkait:</span>
                        <span id="preview-customer" class="font-semibold text-foreground truncate block">-</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-muted-foreground block">Dasar Nota Terkait:</span>
                        <span id="preview-nota" class="font-mono font-bold text-primary block truncate">-</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-muted-foreground block">Dasar Master SMU:</span>
                        <span id="preview-smu" class="font-mono font-bold text-foreground block truncate">-</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-muted-foreground block">Total Berat Kargo:</span>
                        <span id="preview-berat" class="font-mono font-semibold text-foreground block">-</span>
                    </div>
                </div>

                <!-- Quick Apply Amount Buttons -->
                <div class="pt-2 border-t border-border/70 flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-semibold text-muted-foreground">Terapkan Cepat:</span>
                    <button type="button" id="btn-use-nota-amount" 
                            class="uk-btn uk-btn-default uk-btn-xs text-xs font-medium hidden">
                        Gunakan Total Nota (<span id="val-nota-amount">Rp 0</span>)
                    </button>
                    <button type="button" id="btn-use-handling-estimate" 
                            class="uk-btn uk-btn-default uk-btn-xs text-xs font-medium hidden">
                        Estimasi Handling Terminal IAS (<span id="val-handling-estimate">Rp 0</span>)
                    </button>
                    <button type="button" id="btn-use-cashback-estimate" 
                            class="uk-btn uk-btn-default uk-btn-xs text-xs font-medium text-emerald-600 dark:text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10 hidden">
                        Hitung Cashback (Rp 200/kg): <span id="val-cashback-estimate" class="font-bold ml-1">Rp 0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Card 3: Jumlah Pembayaran & Rekening Keuangan -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-border pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">3. Jumlah Pembayaran &amp; Rekening Pengirim</h3>
                </div>
                <span class="text-[11px] text-muted-foreground">Realisasi kas/bank keluar untuk pembayaran ke IAS</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Jumlah Pembayaran (Rp) *
                    </label>
                    <input type="number" step="any" min="1" name="nominal" id="input_nominal" value="{{ old('nominal') }}" required placeholder="0"
                           class="uk-input text-base font-mono font-bold text-amber-600 dark:text-amber-400">
                    <span class="text-[10px] text-muted-foreground mt-1 block">Nominal yang dibayarkan ke IAS</span>
                    @error('nominal')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider block">
                            Cash Back IAS (Rp)
                        </label>
                        <span class="text-[10px] text-teal-600 dark:text-teal-400 font-mono font-bold">Rp 200/kg</span>
                    </div>
                    <input type="number" step="any" min="0" name="cashback" id="input_cashback" value="{{ old('cashback', 0) }}" placeholder="0"
                           class="uk-input text-base font-mono font-bold text-emerald-600 dark:text-emerald-400">
                    <span class="text-[10px] text-muted-foreground mt-1 block">
                        Klaim bulanan @ Rp 200/kg SMU (potongan/insentif IAS)
                    </span>
                    @error('cashback')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Bank Pengirim *
                    </label>
                    <select name="bank_pengirim" required class="uk-select text-xs">
                        <option value="BCA" {{ old('bank_pengirim') === 'BCA' ? 'selected' : '' }}>BCA</option>
                        <option value="Mandiri" {{ old('bank_pengirim') === 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                        <option value="BNI" {{ old('bank_pengirim') === 'BNI' ? 'selected' : '' }}>BNI</option>
                        <option value="BRI" {{ old('bank_pengirim') === 'BRI' ? 'selected' : '' }}>BRI</option>
                    </select>
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                        Metode Pembayaran *
                    </label>
                    <select name="metode_bayar" required class="uk-select text-xs">
                        <option value="transfer" {{ old('metode_bayar') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="virtual_account" {{ old('metode_bayar') === 'virtual_account' ? 'selected' : '' }}>Virtual Account (VA)</option>
                        <option value="giro" {{ old('metode_bayar') === 'giro' ? 'selected' : '' }}>Bilyet Giro</option>
                        <option value="tunai" {{ old('metode_bayar') === 'tunai' ? 'selected' : '' }}>Tunai / Kasir</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1 block">
                    Keterangan / Rincian Biaya
                </label>
                <textarea name="catatan" id="input_catatan" rows="3" placeholder="Contoh: Terminal handling charge & sewa pergudangan kargo IAS..."
                          class="uk-textarea text-xs">{{ old('catatan') }}</textarea>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="pt-2 flex items-center justify-end gap-2 border-t border-border">
            <a href="{{ route('ias.payments.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs shadow-xs">
                Batal
            </a>
            <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs shadow-sm">
                Simpan Pembayaran IAS
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectInvoices = document.getElementById('select_invoices');
    const inputNomorNota = document.getElementById('input_nomor_nota');
    const inputNomorSmu = document.getElementById('input_nomor_smu');
    const badgeSmusContainer = document.getElementById('badge-smus-container');
    const inputNominal = document.getElementById('input_nominal');
    const inputCatatan = document.getElementById('input_catatan');

    const selectedInvoicesBox = document.getElementById('selected-invoices-box');
    const countSelectedInvoices = document.getElementById('count-selected-invoices');
    const tbodySelectedInvoices = document.getElementById('tbody-selected-invoices');
    const tableTotalWeight = document.getElementById('table-total-weight');
    const tableTotalAmount = document.getElementById('table-total-amount');
    const btnClearInvoices = document.getElementById('btn-clear-invoices');

    const previewCustomer = document.getElementById('preview-customer');
    const previewNota = document.getElementById('preview-nota');
    const previewSmu = document.getElementById('preview-smu');
    const previewBerat = document.getElementById('preview-berat');
    const basisStatusBadge = document.getElementById('basis-status-badge');

    const btnUseNota = document.getElementById('btn-use-nota-amount');
    const btnUseHandling = document.getElementById('btn-use-handling-estimate');
    const btnUseCashback = document.getElementById('btn-use-cashback-estimate');

    const valNotaAmount = document.getElementById('val-nota-amount');
    const valHandlingEstimate = document.getElementById('val-handling-estimate');
    const valCashbackEstimate = document.getElementById('val-cashback-estimate');

    let currentNotaTotal = 0;
    let currentHandlingEstimate = 0;
    let currentCashbackEstimate = 0;

    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function updateBasisSummary() {
        const selectedOptions = Array.from(selectInvoices.selectedOptions);
        const hasInvoices = selectedOptions.length > 0;

        let customersList = [];
        let notasList = [];
        let smusList = [];
        let totalNotaAmount = 0;
        let totalNotaWeight = 0;

        tbodySelectedInvoices.innerHTML = '';
        if (badgeSmusContainer) badgeSmusContainer.innerHTML = '';

        if (hasInvoices) {
            selectedInvoicesBox.classList.remove('hidden');
            countSelectedInvoices.textContent = selectedOptions.length;

            selectedOptions.forEach(opt => {
                const notaNo = opt.dataset.nomorNota || opt.text.split('—')[0].trim();
                const custName = opt.dataset.customer || '-';
                const smuText = opt.dataset.smus || '-';
                const w = parseFloat(opt.dataset.weight || 0);
                const tot = parseFloat(opt.dataset.total || 0);

                notasList.push(notaNo);
                if (custName && custName !== '-' && !customersList.includes(custName)) {
                    customersList.push(custName);
                }
                if (opt.dataset.smus) {
                    opt.dataset.smus.split(',').forEach(s => {
                        const trimmed = s.trim();
                        if (trimmed && !smusList.includes(trimmed)) {
                            smusList.push(trimmed);
                        }
                    });
                }

                totalNotaAmount += tot;
                totalNotaWeight += w;

                // Append to selected invoices table
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="py-2 px-3 font-bold text-primary">${notaNo}</td>
                    <td class="py-2 px-3 font-sans text-muted-foreground">${custName}</td>
                    <td class="py-2 px-3">${smuText}</td>
                    <td class="py-2 px-3 text-right">${w.toLocaleString('id-ID')} kg</td>
                    <td class="py-2 px-3 text-right font-bold">Rp ${tot.toLocaleString('id-ID')}</td>
                `;
                tbodySelectedInvoices.appendChild(tr);
            });

            tableTotalWeight.textContent = totalNotaWeight.toLocaleString('id-ID') + ' kg';
            tableTotalAmount.textContent = formatRupiah(totalNotaAmount);

            // Update auto-filled fields
            inputNomorNota.value = notasList.join(', ');
            inputNomorSmu.value = smusList.join(', ');

            // Render SMU badge pills
            if (badgeSmusContainer && smusList.length > 0) {
                smusList.forEach(smu => {
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20';
                    badge.textContent = smu;
                    badgeSmusContainer.appendChild(badge);
                });
            }
        } else {
            selectedInvoicesBox.classList.add('hidden');
            if (inputNomorNota.value && inputNomorNota.value.includes(',')) {
                notasList = inputNomorNota.value.split(',').map(s => s.trim());
            } else if (inputNomorNota.value) {
                notasList = [inputNomorNota.value];
            }
            if (inputNomorSmu.value) {
                smusList = inputNomorSmu.value.split(',').map(s => s.trim());
            }
        }

        let totalBerat = totalNotaWeight;
        currentNotaTotal = totalNotaAmount;
        currentHandlingEstimate = 0;
        currentCashbackEstimate = 0;

        // Estimasi handling rate terminal (Rp 1.500 per kg standard modal handling IAS)
        // Estimasi cashback kargo (Rp 200 per kg SMU klaim bulanan)
        if (totalBerat > 0) {
            currentHandlingEstimate = Math.round(totalBerat * 1500);
            currentCashbackEstimate = Math.round(totalBerat * 200);
        }

        // Render preview card
        previewCustomer.textContent = customersList.length > 0 ? customersList.join(', ') : '-';
        previewNota.textContent = notasList.length > 0 ? (notasList.length > 2 ? `${notasList.length} Nota (${notasList.slice(0, 2).join(', ')}...)` : notasList.join(', ')) : (inputNomorNota.value || '-');
        previewSmu.textContent = smusList.length > 0 ? (smusList.length > 2 ? `${smusList.length} SMU (${smusList.slice(0, 2).join(', ')}...)` : smusList.join(', ')) : (inputNomorSmu.value || '-');
        previewBerat.textContent = totalBerat > 0 ? (totalBerat.toLocaleString('id-ID') + ' kg') : '-';

        // Badge update
        if (hasInvoices) {
            basisStatusBadge.textContent = `${selectedOptions.length} Nota Dipilih (${smusList.length} SMU)`;
            basisStatusBadge.className = 'uk-label uk-label-success text-[10px] uppercase font-mono self-start sm:self-auto';
        } else if (inputNomorNota.value || inputNomorSmu.value) {
            basisStatusBadge.textContent = 'Dasar Input Manual';
            basisStatusBadge.className = 'uk-label uk-label-default text-[10px] uppercase font-mono self-start sm:self-auto';
        } else {
            basisStatusBadge.textContent = 'Belum Ada Nota Dipilih';
            basisStatusBadge.className = 'uk-label uk-label-default text-[10px] uppercase font-mono self-start sm:self-auto';
        }

        // Quick buttons update
        if (currentNotaTotal > 0) {
            valNotaAmount.textContent = formatRupiah(currentNotaTotal);
            btnUseNota.classList.remove('hidden');
        } else {
            btnUseNota.classList.add('hidden');
        }

        if (currentHandlingEstimate > 0) {
            valHandlingEstimate.textContent = formatRupiah(currentHandlingEstimate);
            btnUseHandling.classList.remove('hidden');
        } else {
            btnUseHandling.classList.add('hidden');
        }

        if (currentCashbackEstimate > 0) {
            valCashbackEstimate.textContent = formatRupiah(currentCashbackEstimate);
            btnUseCashback.classList.remove('hidden');
        } else {
            btnUseCashback.classList.add('hidden');
        }
    }

    // Attach listeners
    selectInvoices.addEventListener('change', function () {
        updateBasisSummary();
        // If nominal is empty and we have a suggested amount, populate handling estimate or nota total
        if (!inputNominal.value || inputNominal.value == '0') {
            if (currentHandlingEstimate > 0) {
                inputNominal.value = currentHandlingEstimate;
            } else if (currentNotaTotal > 0) {
                inputNominal.value = currentNotaTotal;
            }
        }
        // Auto-populate or suggest cashback if 0
        if ((!inputCashback.value || inputCashback.value == '0') && currentCashbackEstimate > 0) {
            inputCashback.value = currentCashbackEstimate;
        }
        // Update default catatan if empty
        if (!inputCatatan.value && inputNomorNota.value) {
            const smuSuffix = inputNomorSmu.value ? ` (SMU: ${inputNomorSmu.value})` : '';
            inputCatatan.value = `Pembayaran terminal handling IAS untuk Nota: ${inputNomorNota.value}${smuSuffix}`;
        }
    });

    inputNomorNota.addEventListener('input', updateBasisSummary);
    inputNomorSmu.addEventListener('input', updateBasisSummary);

    // Quick set buttons click handlers
    btnUseNota.addEventListener('click', function () {
        if (currentNotaTotal > 0) {
            inputNominal.value = currentNotaTotal;
            inputNominal.focus();
        }
    });

    btnUseHandling.addEventListener('click', function () {
        if (currentHandlingEstimate > 0) {
            inputNominal.value = currentHandlingEstimate;
            inputNominal.focus();
        }
    });

    btnUseCashback.addEventListener('click', function () {
        if (currentCashbackEstimate > 0) {
            inputCashback.value = currentCashbackEstimate;
            inputCashback.focus();
        }
    });

    btnClearInvoices.addEventListener('click', function () {
        Array.from(selectInvoices.options).forEach(opt => opt.selected = false);
        if (typeof $ !== 'undefined' && $(selectInvoices).hasClass('select2-hidden-accessible')) {
            $(selectInvoices).trigger('change.select2');
        }
        inputNomorNota.value = '';
        inputNomorSmu.value = '';
        inputCashback.value = 0;
        updateBasisSummary();
    });

    // jQuery Select2 bridge if initialized
    if (typeof $ !== 'undefined') {
        $(selectInvoices).on('select2:select select2:unselect select2:clear', function () {
            selectInvoices.dispatchEvent(new Event('change'));
        });
    }

    // Initial trigger
    updateBasisSummary();
});
</script>
@endsection
