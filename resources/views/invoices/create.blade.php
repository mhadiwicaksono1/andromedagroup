@extends('layouts.cargo')

@section('title', 'Terbitkan Nota Penagihan Baru — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Terbitkan Nota Penagihan Baru</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Pilih pelanggan, pilih muatan lembar kerja kargo, dan akumulasikan sisa piutang berjalan.</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <form action="{{ route('invoices.store') }}" method="POST" id="form-create-invoice" class="space-y-6">
        @csrf

        <!-- Card 1: Customer & Invoice Meta -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-primary">1. Informasi Nota &amp; Pemilihan Pelanggan</h3>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Nomor Nota *</label>
                    <input type="text" name="nomor_nota" value="{{ old('nomor_nota', $nextNumber) }}" required readonly
                           class="uk-input text-xs font-mono font-bold text-primary bg-muted cursor-not-allowed">
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Tipe Nota *</label>
                    <select name="tipe_nota" id="select_tipe_nota" required class="uk-select text-xs font-semibold">
                        <option value="pernama" {{ old('tipe_nota', 'pernama') === 'pernama' ? 'selected' : '' }}>NOTA PERNAMA (Banyak SMU)</option>
                        <option value="pecahan" {{ old('tipe_nota') === 'pecahan' ? 'selected' : '' }}>NOTA PECAHAN (Kargo Eceran)</option>
                    </select>
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Tanggal Nota *</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', $today) }}" required
                           class="uk-input text-xs">
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Pilih Customer *</label>
                    <select name="customer_id" id="select_customer" required class="select2 uk-select text-xs">
                        <option value="">-- Cari / Pilih Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (old('customer_id', $selectedCustomerId) == $c->id) ? 'selected' : '' }}>
                                {{ $c->kode_customer }} — {{ $c->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Customer Previous Debt Notice -->
            <div id="debt-notice-card" class="hidden uk-alert uk-alert-warning" uk-alert>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <span class="text-xs font-bold block" id="debt-notice-title">Sisa Hutang Customer Sebelumnya</span>
                            <span class="text-[11px] opacity-80">Tagihan ini akan ditambahkan secara otomatis ke total penagihan nota ini.</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-base font-mono font-bold" id="display-sisa-hutang-lama">Rp 0</span>
                        <input type="hidden" name="sisa_hutang_sebelumnya" id="input_sisa_hutang_sebelumnya" value="0">
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Select Unbilled Worksheets -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-primary">2. Pilih Lembar Kerja yang Akan Ditagihkan</h3>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Hanya menampilkan lembar kerja berstatus "Selesai" yang belum pernah dinotakan.</p>
                </div>
                <div class="flex items-center gap-1.5" id="bulk-toggle-container" style="display: none;">
                    <button type="button" id="btn-select-all" class="uk-btn uk-btn-default uk-btn-xs text-[11px] shadow-xs">
                        Pilih Semua
                    </button>
                    <button type="button" id="btn-deselect-all" class="uk-btn uk-btn-default uk-btn-xs text-[11px] shadow-xs">
                        Batal Pilih
                    </button>
                </div>
            </div>

            <!-- Worksheets Loading Spinner -->
            <div id="worksheets-loading" class="hidden py-8 text-center text-xs text-muted-foreground">
                <div class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                Memuat muatan kargo customer...
            </div>

            <!-- Empty Worksheets Placeholder -->
            <div id="worksheets-empty" class="py-10 text-center text-xs text-muted-foreground border border-dashed border-border rounded-lg">
                Silakan pilih pelanggan terlebih dahulu untuk memuat lembar kerja yang siap dinotakan.
            </div>

            <!-- Worksheets Table Container -->
            <div id="worksheets-table-wrapper" class="hidden overflow-x-auto rounded-lg border border-border">
                <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                    <thead>
                        <tr class="bg-muted/40 text-muted-foreground border-b border-border">
                            <th class="py-2.5 px-3 w-10 text-center font-medium">Pilih</th>
                            <th class="py-2.5 px-3 font-medium">No. Lembar Kerja</th>
                            <th class="py-2.5 px-3 font-medium">Tanggal</th>
                            <th class="py-2.5 px-3 font-medium">No. SMU</th>
                            <th class="py-2.5 px-3 font-medium">Koli / Berat</th>
                            <th class="py-2.5 px-3 font-medium">Tarif</th>
                            <th class="py-2.5 px-3 text-right font-medium">Total Biaya</th>
                        </tr>
                    </thead>
                    <tbody id="worksheets-tbody" class="divide-y divide-border">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card 3: Financial Calculation & Settlement -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-primary">3. Perhitungan Total Tagihan &amp; Pembayaran</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left: Form inputs for adjustments and initial payment -->
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider block">
                                Biaya Administrasi (ADM) (Rp)
                            </label>
                            <span class="text-[10px] text-teal-600 dark:text-teal-400 font-mono font-bold" id="badge-adm-rate">Rp 3.000 / SMU</span>
                        </div>
                        <input type="number" step="any" min="0" name="biaya_adm" id="input_biaya_adm" value="0" placeholder="0"
                               class="uk-input text-xs font-mono font-bold text-teal-600 dark:text-teal-400">
                        <span class="text-[10px] text-muted-foreground mt-1 block" id="help-biaya-adm">Otomatis dihitung Rp 3.000 (dapat disesuaikan jika perlu).</span>
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">
                            Penyesuaian Biaya / Diskon (Rp)
                        </label>
                        <input type="number" step="any" name="penyesuaian_biaya" id="input_penyesuaian" value="0" placeholder="0"
                               class="uk-input text-xs font-mono">
                        <span class="text-[10px] text-muted-foreground mt-1 block">Bisa berupa potongan (negatif) atau biaya tambahan (positif).</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">
                                Pembayaran Awal (Rp)
                            </label>
                            <input type="number" step="any" name="jumlah_dibayar" id="input_dibayar" value="0" min="0" placeholder="0"
                                   class="uk-input text-xs font-mono font-bold text-emerald-500">
                        </div>

                        <div>
                            <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">
                                Metode Pembayaran
                            </label>
                            <select name="metode_pembayaran" class="uk-select text-xs">
                                <option value="transfer">Transfer Bank</option>
                                <option value="tunai">Tunai (Cash)</option>
                                <option value="giro">Bilyet Giro</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Catatan Nota (Opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan syarat pembayaran, jatuh tempo, atau instruksi transfer..."
                                  class="uk-textarea text-xs"></textarea>
                    </div>
                </div>

                <!-- Right: Live Financial Summary Breakdown -->
                <div class="p-5 rounded-lg bg-muted/40 border border-border space-y-3 flex flex-col justify-between">
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between text-muted-foreground">
                            <span>Subtotal Muatan Terpilih:</span>
                            <span class="font-mono font-semibold text-foreground" id="calc-subtotal-lk">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-teal-600 dark:text-teal-400 font-semibold">
                            <span>+ Biaya Administrasi (ADM):</span>
                            <span class="font-mono font-bold" id="calc-biaya-adm">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-muted-foreground">
                            <span>Penyesuaian / Biaya Lain:</span>
                            <span class="font-mono font-semibold text-foreground" id="calc-penyesuaian">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-foreground font-semibold pt-2 border-t border-border">
                            <span>Total Nilai Nota Ini:</span>
                            <span class="font-mono text-primary font-bold" id="calc-total-nota">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-amber-500 pt-2 border-t border-border">
                            <span>+ Sisa Hutang Customer Sebelumnya:</span>
                            <span class="font-mono font-bold" id="calc-hutang-lama">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-foreground font-bold text-sm pt-2 border-t border-border">
                            <span>Total Tagihan Kumulatif:</span>
                            <span class="font-mono font-bold" id="calc-total-tagihan">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-emerald-500">
                            <span>- Pembayaran Masuk:</span>
                            <span class="font-mono font-bold" id="calc-dibayar">Rp 0</span>
                        </div>
                    </div>

                    <!-- Final Balance Highlight -->
                    <div class="p-3.5 rounded-lg bg-card border border-border flex items-center justify-between mt-3">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground block">Sisa Saldo Piutang:</span>
                            <span class="text-[10px] font-semibold text-muted-foreground" id="calc-status-label">Belum Lunas</span>
                        </div>
                        <div class="text-xl font-bold font-mono text-destructive" id="calc-sisa-piutang">
                            Rp 0
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 flex items-center justify-end gap-2 border-t border-border">
                <a href="{{ route('invoices.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs shadow-xs">
                    Batal
                </a>
                <button type="submit" id="btn-submit-invoice" disabled
                        class="uk-btn uk-btn-primary uk-btn-sm text-xs shadow-sm disabled:opacity-40 disabled:cursor-not-allowed">
                    Terbitkan Nota Penagihan
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectCustomer = document.getElementById('select_customer');
        const debtNoticeCard = document.getElementById('debt-notice-card');
        const displaySisaHutangLama = document.getElementById('display-sisa-hutang-lama');
        const inputSisaHutangLama = document.getElementById('input_sisa_hutang_sebelumnya');

        const worksheetsLoading = document.getElementById('worksheets-loading');
        const worksheetsEmpty = document.getElementById('worksheets-empty');
        const worksheetsTableWrapper = document.getElementById('worksheets-table-wrapper');
        const worksheetsTbody = document.getElementById('worksheets-tbody');
        const bulkToggleContainer = document.getElementById('bulk-toggle-container');

        const inputPenyesuaian = document.getElementById('input_penyesuaian');
        const inputDibayar = document.getElementById('input_dibayar');
        const inputBiayaAdm = document.getElementById('input_biaya_adm');
        const selectTipeNota = document.getElementById('select_tipe_nota');
        const btnSubmit = document.getElementById('btn-submit-invoice');

        // Display elements
        const calcSubtotalLK = document.getElementById('calc-subtotal-lk');
        const calcBiayaAdm = document.getElementById('calc-biaya-adm');
        const calcPenyesuaian = document.getElementById('calc-penyesuaian');
        const calcTotalNota = document.getElementById('calc-total-nota');
        const calcHutangLama = document.getElementById('calc-hutang-lama');
        const calcTotalTagihan = document.getElementById('calc-total-tagihan');
        const calcDibayar = document.getElementById('calc-dibayar');
        const calcSisaPiutang = document.getElementById('calc-sisa-piutang');
        const calcStatusLabel = document.getElementById('calc-status-label');

        let currentWorksheets = [];

        async function onCustomerChange() {
            const customerId = selectCustomer.value;
            if (!customerId) {
                debtNoticeCard.classList.add('hidden');
                inputSisaHutangLama.value = 0;
                worksheetsEmpty.style.display = 'block';
                worksheetsEmpty.textContent = 'Silakan pilih pelanggan terlebih dahulu untuk memuat lembar kerja yang siap dinotakan.';
                worksheetsTableWrapper.classList.add('hidden');
                bulkToggleContainer.style.display = 'none';
                currentWorksheets = [];
                recalculate();
                return;
            }

            worksheetsLoading.classList.remove('hidden');
            worksheetsEmpty.style.display = 'none';
            worksheetsTableWrapper.classList.add('hidden');
            bulkToggleContainer.style.display = 'none';

            try {
                const res = await fetch(`/api/customers/${customerId}/debt`);
                const json = await res.json();

                // 1. Debt balance
                const debt = parseFloat(json.sisa_hutang_sebelumnya) || 0;
                inputSisaHutangLama.value = debt;
                displaySisaHutangLama.textContent = 'Rp ' + debt.toLocaleString('id-ID');
                if (debt > 0) {
                    debtNoticeCard.classList.remove('hidden');
                } else {
                    debtNoticeCard.classList.add('hidden');
                }

                // 2. Unbilled worksheets
                currentWorksheets = json.unbilled_worksheets || [];
                worksheetsLoading.classList.add('hidden');

                if (currentWorksheets.length === 0) {
                    worksheetsEmpty.style.display = 'block';
                    worksheetsEmpty.innerHTML = `
                        <p class="text-amber-500 font-semibold mb-1">Tidak ada lembar kerja yang belum dinotakan untuk pelanggan ini.</p>
                        <p class="text-[11px] text-muted-foreground">Pastikan lembar kerja sudah dibuat dan statusnya sudah diubah ke "Selesai".</p>
                    `;
                    worksheetsTableWrapper.classList.add('hidden');
                    bulkToggleContainer.style.display = 'none';
                } else {
                    worksheetsEmpty.style.display = 'none';
                    worksheetsTableWrapper.classList.remove('hidden');
                    bulkToggleContainer.style.display = 'flex';

                    worksheetsTbody.innerHTML = currentWorksheets.map(ws => {
                        const lkNomor = ws.worksheet ? ws.worksheet.nomor_lembar_kerja : '-';
                        const smuNomor = ws.worksheet ? ws.worksheet.nomor_smu : '-';
                        const tgl = (ws.worksheet && ws.worksheet.tanggal) 
                            ? new Date(ws.worksheet.tanggal).toLocaleDateString('id-ID')
                            : (ws.created_at ? new Date(ws.created_at).toLocaleDateString('id-ID') : '-');
                        const keterangan = ws.keterangan ? `<span class="block text-[10px] text-muted-foreground truncate max-w-[180px]">${ws.keterangan}</span>` : '';
                        const markingBadge = ws.marking ? `<span class="inline-block mt-0.5 px-1.5 py-0.2 rounded font-mono text-[9px] bg-muted text-primary border border-border">Marking: ${ws.marking}</span>` : '';

                        return `
                        <tr class="hover:bg-muted/40 transition-colors">
                            <td class="py-2.5 px-3 text-center">
                                <input type="checkbox" name="worksheet_ids[]" value="${ws.id}" data-total="${ws.total_biaya}" checked
                                       class="uk-checkbox ws-checkbox cursor-pointer">
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-primary">
                                ${lkNomor}
                                ${keterangan}
                            </td>
                            <td class="py-2.5 px-3 text-muted-foreground">${tgl}</td>
                            <td class="py-2.5 px-3">
                                <span class="font-mono font-medium text-foreground block">${smuNomor}</span>
                                ${markingBadge}
                            </td>
                            <td class="py-2.5 px-3 text-foreground">${ws.jumlah_koli} koli / ${parseFloat(ws.berat_kg).toFixed(1)} kg</td>
                            <td class="py-2.5 px-3 text-muted-foreground font-mono">Rp ${parseFloat(ws.tarif_per_kg).toLocaleString('id-ID')}</td>
                            <td class="py-2.5 px-3 text-right font-bold font-mono text-foreground">Rp ${parseFloat(ws.total_biaya).toLocaleString('id-ID')}</td>
                        </tr>
                        `;
                    }).join('');

                    // Bind checkbox change events
                    document.querySelectorAll('.ws-checkbox').forEach(cb => {
                        cb.addEventListener('change', recalculate);
                    });
                }

                recalculate();
            } catch (err) {
                console.error('Fetch customer debt error:', err);
                worksheetsLoading.classList.add('hidden');
                worksheetsEmpty.style.display = 'block';
                worksheetsEmpty.textContent = 'Gagal memuat data lembar kerja. Silakan coba kembali.';
            }
        }

        function recalculate() {
            const checkboxes = document.querySelectorAll('.ws-checkbox:checked');
            const smuCount = checkboxes.length;

            // Auto suggest Tipe Nota: if > 1 -> Pernama, if 1 -> Pecahan
            if (selectTipeNota && !selectTipeNota.dataset.manual) {
                if (smuCount > 1) {
                    selectTipeNota.value = 'pernama';
                } else if (smuCount === 1) {
                    selectTipeNota.value = 'pecahan';
                }
            }

            const currentTipeNota = selectTipeNota ? selectTipeNota.value : (smuCount > 1 ? 'pernama' : 'pecahan');

            // Auto calculate Biaya Administrasi (ADM):
            // Nota Pernama: 1x ADM (Rp 3.000) untuk seluruh SMU
            // Nota Pecahan: Rp 3.000 x jumlah SMU
            let biayaAdm = 0;
            if (smuCount > 0) {
                const autoAdm = (currentTipeNota === 'pernama') ? 3000 : (smuCount * 3000);
                if (!inputBiayaAdm.dataset.manual) {
                    inputBiayaAdm.value = autoAdm;
                    biayaAdm = autoAdm;
                } else {
                    biayaAdm = parseFloat(inputBiayaAdm.value) || 0;
                }
            } else {
                if (!inputBiayaAdm.dataset.manual) {
                    inputBiayaAdm.value = 0;
                }
                biayaAdm = parseFloat(inputBiayaAdm.value) || 0;
            }

            // Update badge & helper info
            const badgeAdmRate = document.getElementById('badge-adm-rate');
            const helpAdm = document.getElementById('help-biaya-adm');
            if (badgeAdmRate) {
                badgeAdmRate.textContent = (currentTipeNota === 'pernama') ? 'Rp 3.000 (Tetap 1 Nota)' : 'Rp 3.000 / SMU';
            }
            if (helpAdm) {
                helpAdm.textContent = (currentTipeNota === 'pernama')
                    ? 'Otomatis dihitung Rp 3.000 tetap untuk 1 nota pernama (semua SMU). Dapat disesuaikan jika perlu.'
                    : 'Otomatis dihitung Rp 3.000 per baris SMU terpilih (dapat disesuaikan jika perlu).';
            }

            let subtotalLK = 0;
            checkboxes.forEach(cb => {
                subtotalLK += parseFloat(cb.dataset.total) || 0;
            });

            const penyesuaian = parseFloat(inputPenyesuaian.value) || 0;
            const sisaHutangLama = parseFloat(inputSisaHutangLama.value) || 0;
            const dibayar = parseFloat(inputDibayar.value) || 0;

            const totalNota = subtotalLK + biayaAdm + penyesuaian;
            const totalTagihan = sisaHutangLama + totalNota;
            const sisaPiutang = Math.max(0, totalTagihan - dibayar);

            calcSubtotalLK.textContent = 'Rp ' + subtotalLK.toLocaleString('id-ID');
            calcBiayaAdm.textContent = '+ Rp ' + biayaAdm.toLocaleString('id-ID');
            calcPenyesuaian.textContent = (penyesuaian >= 0 ? '+ ' : '') + 'Rp ' + penyesuaian.toLocaleString('id-ID');
            calcTotalNota.textContent = 'Rp ' + totalNota.toLocaleString('id-ID');
            calcHutangLama.textContent = 'Rp ' + sisaHutangLama.toLocaleString('id-ID');
            calcTotalTagihan.textContent = 'Rp ' + totalTagihan.toLocaleString('id-ID');
            calcDibayar.textContent = 'Rp ' + dibayar.toLocaleString('id-ID');
            calcSisaPiutang.textContent = 'Rp ' + sisaPiutang.toLocaleString('id-ID');

            if (sisaPiutang <= 0) {
                calcStatusLabel.textContent = 'Lunas';
                calcStatusLabel.className = 'text-[10px] text-emerald-500 font-bold uppercase';
                calcSisaPiutang.className = 'text-xl font-bold font-mono text-emerald-500';
            } else if (dibayar > 0) {
                calcStatusLabel.textContent = 'Terbayar Sebagian';
                calcStatusLabel.className = 'text-[10px] text-amber-500 font-bold uppercase';
                calcSisaPiutang.className = 'text-xl font-bold font-mono text-amber-500';
            } else {
                calcStatusLabel.textContent = 'Belum Lunas (Piutang Penuh)';
                calcStatusLabel.className = 'text-[10px] text-destructive font-bold uppercase';
                calcSisaPiutang.className = 'text-xl font-bold font-mono text-destructive';
            }

            // Enable submit if at least 1 worksheet is checked
            btnSubmit.disabled = checkboxes.length === 0;
        }

        // Initialize Select2 & Event listeners
        $(document).ready(function() {
            $('#select_customer').select2({
                width: '100%',
                placeholder: '-- Cari / Pilih Customer --'
            }).on('change', function() {
                onCustomerChange();
            });
        });

        selectCustomer.addEventListener('change', onCustomerChange);
        inputPenyesuaian.addEventListener('input', recalculate);
        inputDibayar.addEventListener('input', recalculate);
        inputBiayaAdm.addEventListener('input', function() {
            inputBiayaAdm.dataset.manual = 'true';
            recalculate();
        });
        selectTipeNota.addEventListener('change', function() {
            selectTipeNota.dataset.manual = 'true';
            delete inputBiayaAdm.dataset.manual;
            recalculate();
        });

        document.getElementById('btn-select-all')?.addEventListener('click', () => {
            document.querySelectorAll('.ws-checkbox').forEach(cb => cb.checked = true);
            recalculate();
        });

        document.getElementById('btn-deselect-all')?.addEventListener('click', () => {
            document.querySelectorAll('.ws-checkbox').forEach(cb => cb.checked = false);
            recalculate();
        });

        // Trigger on load if customer already selected
        if (selectCustomer.value) {
            onCustomerChange();
        }
    });
</script>
@endpush
@endsection
