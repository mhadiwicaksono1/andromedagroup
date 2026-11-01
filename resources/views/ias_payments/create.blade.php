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

        <!-- Card 2: Dasar Pembayaran: Master SMU (Lembar Kerja) -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-border pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-foreground">2. Dasar Pembayaran: Master SMU (Lembar Kerja)</h3>
                </div>
                <span class="text-[11px] text-muted-foreground">Koli, berat &amp; cashback IAS otomatis ditarik dari data fisik Header SMU (Bisa Lebih dari 1 pengiriman jika SMU dipecah)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Pilihan Master SMU (Lembar Kerja) -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider block">
                            Pilih Master SMU (Lembar Kerja) *
                        </label>
                        <span class="text-[11px] text-primary font-medium">Header SMU Maskapai</span>
                    </div>
                    
                    <select name="worksheet_id" id="select_worksheet" class="select2 uk-select text-xs" data-placeholder="-- Cari / Pilih Nomor SMU --">
                        <option value="">-- Cari / Pilih Nomor Master SMU --</option>
                        @foreach($worksheets as $ws)
                            @php
                                $wsCusts = $ws->items->map(fn($it) => $it->customer?->nama)->filter()->unique()->values();
                                $wsNotas = $ws->items->map(fn($it) => $it->invoice?->nomor_nota)->filter()->unique()->values();
                                $isSelected = ($selectedWorksheetId == $ws->id) || ($selectedSmu == $ws->nomor_smu);
                            @endphp
                            <option value="{{ $ws->id }}" 
                                    data-nomor-smu="{{ $ws->nomor_smu }}"
                                    data-koli="{{ $ws->display_koli_smu }}"
                                    data-berat="{{ (float) $ws->display_berat_smu }}"
                                    data-tanggal="{{ $ws->tanggal->format('d/m/Y') }}"
                                    data-tipe="{{ strtoupper($ws->tipe) }}"
                                    data-customers="{{ $wsCusts->implode(', ') }}"
                                    data-notas="{{ $wsNotas->implode(', ') }}"
                                    data-total-biaya="{{ (float) $ws->total_biaya }}"
                                    {{ $isSelected ? 'selected' : '' }}>
                                {{ $ws->nomor_smu }} — {{ $ws->display_koli_smu }} Koli / {{ number_format($ws->display_berat_smu, 1) }} kg [{{ strtoupper($ws->tipe) }}] ({{ $wsCusts->take(2)->implode(', ') }}{{ count($wsCusts) > 2 ? '...' : '' }})
                            </option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-2 pt-1">
                        <span class="text-[11px] text-muted-foreground whitespace-nowrap">Nomor SMU:</span>
                        <input type="text" name="nomor_smu" id="input_nomor_smu" value="{{ old('nomor_smu', $selectedSmu) }}" required
                               placeholder="Contoh: GA-88291044 / JT-991204" class="uk-input text-xs font-mono font-bold h-8">
                    </div>
                    <span class="text-[10px] text-muted-foreground block">Otomatis terisi dari SMU terpilih di atas, atau ketik nomor SMU jika manual.</span>
                    @error('nomor_smu')
                        <span class="text-destructive text-[11px] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Header SMU Metrics Card -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider block">
                            Fisik Header SMU (Koli &amp; Kg)
                        </label>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold uppercase tracking-wider">Header SMU Terverifikasi</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 p-3.5 rounded-lg border border-border bg-muted/30">
                        <div>
                            <span class="text-[10px] text-muted-foreground uppercase font-medium block">Koli SMU (Header):</span>
                            <span id="badge-header-koli" class="text-lg font-bold font-mono text-foreground block">0 Koli</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-muted-foreground uppercase font-medium block">Berat SMU (Header):</span>
                            <span id="badge-header-berat" class="text-lg font-bold font-mono text-foreground block">0.00 kg</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-muted-foreground block">
                        Perhitungan biaya handling terminal &amp; cashback IAS menggunakan data fisik header Master SMU ini.
                    </span>
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
                            <span class="text-xs font-bold text-foreground block">Ringkasan Master SMU Terpilih</span>
                            <span class="text-[11px] text-muted-foreground">Informasi acuan SMU untuk menetapkan nominal pembayaran terminal IAS &amp; cashback</span>
                        </div>
                    </div>
                    <span id="basis-status-badge" class="uk-label uk-label-default text-[10px] uppercase font-mono self-start sm:self-auto">
                        Belum Ada SMU Dipilih
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <span class="text-[11px] text-muted-foreground block">Nomor Master SMU:</span>
                        <span id="preview-smu" class="font-mono font-bold text-foreground block truncate">-</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-muted-foreground block">Header Koli &amp; Berat:</span>
                        <span id="preview-berat" class="font-mono font-semibold text-foreground block">-</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-muted-foreground block">Pelanggan Terkait:</span>
                        <span id="preview-customer" class="font-semibold text-foreground truncate block">-</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-muted-foreground block">Nota Penagihan:</span>
                        <span id="preview-nota" class="font-mono font-bold text-primary block truncate">-</span>
                    </div>
                </div>

                <!-- Quick Apply Amount Buttons -->
                <div class="pt-2 border-t border-border/70 flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-semibold text-muted-foreground">Terapkan Cepat:</span>
                    <button type="button" id="btn-use-handling-estimate" 
                            class="uk-btn uk-btn-default uk-btn-xs text-xs font-medium hidden">
                        Estimasi Handling IAS (<span id="val-handling-estimate">Rp 0</span>)
                    </button>
                    <button type="button" id="btn-use-cashback-estimate" 
                            class="uk-btn uk-btn-default uk-btn-xs text-xs font-medium text-emerald-600 dark:text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10 hidden">
                        Hitung Cashback (Rp 200/kg Header): <span id="val-cashback-estimate" class="font-bold ml-1">Rp 0</span>
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
    const selectWorksheet = document.getElementById('select_worksheet');
    const inputNomorSmu = document.getElementById('input_nomor_smu');
    const badgeHeaderKoli = document.getElementById('badge-header-koli');
    const badgeHeaderBerat = document.getElementById('badge-header-berat');
    const inputNominal = document.getElementById('input_nominal');
    const inputCashback = document.getElementById('input_cashback');

    const previewCustomer = document.getElementById('preview-customer');
    const previewNota = document.getElementById('preview-nota');
    const previewSmu = document.getElementById('preview-smu');
    const previewBerat = document.getElementById('preview-berat');
    const basisStatusBadge = document.getElementById('basis-status-badge');

    const btnUseHandling = document.getElementById('btn-use-handling-estimate');
    const btnUseCashback = document.getElementById('btn-use-cashback-estimate');

    const valHandlingEstimate = document.getElementById('val-handling-estimate');
    const valCashbackEstimate = document.getElementById('val-cashback-estimate');

    let currentHandlingEstimate = 0;
    let currentCashbackEstimate = 0;

    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function updateBasisSummary() {
        const selectedOpt = selectWorksheet ? selectWorksheet.options[selectWorksheet.selectedIndex] : null;
        const hasWorksheet = selectedOpt && selectedOpt.value;

        if (hasWorksheet) {
            const smuNo = selectedOpt.dataset.nomorSmu || '';
            const koli = parseInt(selectedOpt.dataset.koli) || 0;
            const berat = parseFloat(selectedOpt.dataset.berat) || 0;
            const custs = selectedOpt.dataset.customers || '-';
            const notas = selectedOpt.dataset.notas || '-';

            inputNomorSmu.value = smuNo;
            badgeHeaderKoli.textContent = koli.toLocaleString('id-ID') + ' Koli';
            badgeHeaderBerat.textContent = berat.toFixed(2) + ' kg';

            previewSmu.textContent = smuNo;
            previewBerat.textContent = `${koli} Koli / ${berat.toFixed(2)} kg`;
            previewCustomer.textContent = custs;
            previewNota.textContent = notas;

            basisStatusBadge.textContent = 'SMU Terhubung';
            basisStatusBadge.className = 'uk-label uk-label-success text-[10px] uppercase font-mono self-start sm:self-auto';

            currentHandlingEstimate = Math.round(berat * 1500);
            currentCashbackEstimate = Math.round(berat * 200);

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
        } else {
            badgeHeaderKoli.textContent = '0 Koli';
            badgeHeaderBerat.textContent = '0.00 kg';
            previewSmu.textContent = inputNomorSmu.value || '-';
            previewBerat.textContent = '-';
            previewCustomer.textContent = '-';
            previewNota.textContent = '-';

            basisStatusBadge.textContent = 'Belum Ada SMU Dipilih';
            basisStatusBadge.className = 'uk-label uk-label-default text-[10px] uppercase font-mono self-start sm:self-auto';

            btnUseHandling.classList.add('hidden');
            btnUseCashback.classList.add('hidden');
        }
    }

    if (selectWorksheet) {
        selectWorksheet.addEventListener('change', function () {
            updateBasisSummary();
            if ((!inputNominal.value || inputNominal.value == '0') && currentHandlingEstimate > 0) {
                inputNominal.value = currentHandlingEstimate;
            }
            if ((!inputCashback.value || inputCashback.value == '0') && currentCashbackEstimate > 0) {
                inputCashback.value = currentCashbackEstimate;
            }
            if (inputNomorSmu.value) {
                const noteInput = document.querySelector('textarea[name="catatan"]');
                if (noteInput && !noteInput.value) {
                    noteInput.value = `Pembayaran terminal handling IAS untuk Master SMU: ${inputNomorSmu.value}`;
                }
            }
        });
    }

    if (inputNomorSmu) {
        inputNomorSmu.addEventListener('input', updateBasisSummary);
    }

    btnUseHandling?.addEventListener('click', function () {
        if (currentHandlingEstimate > 0) {
            inputNominal.value = currentHandlingEstimate;
            inputNominal.focus();
        }
    });

    btnUseCashback?.addEventListener('click', function () {
        if (currentCashbackEstimate > 0) {
            inputCashback.value = currentCashbackEstimate;
            inputCashback.focus();
        }
    });

    if (typeof $ !== 'undefined') {
        $('.select2').select2({ width: '100%' });
        $(selectWorksheet).on('select2:select select2:unselect select2:clear', function () {
            selectWorksheet.dispatchEvent(new Event('change'));
        });
    }

    updateBasisSummary();
});
</script>
@endsection
