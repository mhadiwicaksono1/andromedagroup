@extends('layouts.cargo')

@section('title', 'Catat Penerimaan Pembayaran — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Catat Penerimaan Pembayaran</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Pilih customer dan input nominal pembayaran. Sistem akan memotong piutang nota secara otomatis (FIFO).</p>
        </div>
        <a href="{{ route('payments.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <form action="{{ route('payments.store') }}" method="POST" id="form-create-payment" class="space-y-6">
        @csrf

        <!-- Card 1: Customer Selection & Debt Status -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-primary">1. Informasi Pembayaran &amp; Pelanggan</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">No. Kuitansi Pembayaran *</label>
                    <input type="text" name="nomor_pembayaran" value="{{ $nextNumber }}" required readonly
                           class="uk-input text-xs font-mono font-bold text-primary bg-muted cursor-not-allowed">
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Tanggal Penerimaan *</label>
                    <input type="date" name="tanggal" value="{{ $today }}" required
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

            <!-- Customer Debt Summary Banner -->
            <div id="debt-banner" class="hidden p-4 rounded-lg bg-amber-500/10 border border-amber-500/20 text-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold flex-shrink-0">
                            Rp
                        </div>
                        <div>
                            <span class="text-xs font-bold text-foreground block">Total Saldo Piutang Berjalan Customer:</span>
                            <span class="text-[11px] text-muted-foreground block" id="debt-count-text">Memuat tagihan...</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-lg font-mono font-bold text-amber-600 dark:text-amber-400" id="debt-amount-text">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Payment Details -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-primary">2. Nominal &amp; Metode Pembayaran</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">
                        Jumlah Pembayaran (Rp) *
                    </label>
                    <input type="number" step="any" min="1" name="jumlah" id="input_jumlah" required placeholder="0"
                           class="uk-input text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400" oninput="calculateAllocation()">
                    <span class="text-[10px] text-muted-foreground mt-1 block">Input nominal uang yang ditransfer atau dibayar customer.</span>
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">
                        Metode Pembayaran *
                    </label>
                    <select name="metode" required class="uk-select text-xs">
                        <option value="transfer">Transfer Bank BCA</option>
                        <option value="tunai">Cash</option>
                    </select>
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">
                        Catatan / No. Bukti Transfer Bank
                    </label>
                    <input type="text" name="catatan" placeholder="Contoh: Transfer BCA Ref #88210..."
                           class="uk-input text-xs">
                </div>
            </div>
        </div>

        <!-- Card 3: Real-Time FIFO Allocation Preview -->
        <div class="uk-card uk-card-default border border-border p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-primary">3. Simulasi Alokasi Pemotongan Nota Piutang (FIFO)</h3>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Nota piutang tertua akan dilunasi terlebih dahulu sesuai urutan tanggal penerbitan.</p>
                </div>
                <span class="text-[11px] font-mono font-medium text-muted-foreground" id="allocation-status-text">Pilih customer terlebih dahulu</span>
            </div>

            <div id="invoices-empty" class="py-8 text-center text-xs text-muted-foreground border border-dashed border-border rounded-lg">
                Silakan pilih pelanggan terlebih dahulu untuk melihat daftar nota yang belum lunas.
            </div>

            <div id="invoices-table-container" class="hidden overflow-x-auto rounded-lg border border-border">
                <table class="uk-table uk-table-divider uk-table-hover uk-table-small text-xs">
                    <thead>
                        <tr class="bg-muted/40 text-muted-foreground border-b border-border">
                            <th class="py-2.5 px-3 font-medium">No. Nota</th>
                            <th class="py-2.5 px-3 font-medium">Tanggal</th>
                            <th class="py-2.5 px-3 text-right font-medium">Total Nilai Nota</th>
                            <th class="py-2.5 px-3 text-right font-medium">Sisa Piutang Awal</th>
                            <th class="py-2.5 px-3 text-right font-medium text-primary">Alokasi Bayar</th>
                            <th class="py-2.5 px-3 text-right font-medium">Sisa Piutang Baru</th>
                            <th class="py-2.5 px-3 text-center font-medium">Status Baru</th>
                        </tr>
                    </thead>
                    <tbody id="invoices-tbody" class="divide-y divide-border">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Financial Calculation Summary Box -->
            <div class="p-4 rounded-lg bg-muted/40 border border-border grid grid-cols-2 sm:grid-cols-4 gap-4 items-center">
                <div>
                    <span class="text-[11px] text-muted-foreground block uppercase font-medium">Total Piutang Awal:</span>
                    <span class="text-sm font-bold text-foreground font-mono" id="sum-piutang-awal">Rp 0</span>
                </div>
                <div>
                    <span class="text-[11px] text-muted-foreground block uppercase font-medium">Nominal Bayar:</span>
                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 font-mono" id="sum-nominal-bayar">Rp 0</span>
                </div>
                <div>
                    <span class="text-[11px] text-muted-foreground block uppercase font-medium">Alokasi ke Nota:</span>
                    <span class="text-sm font-bold text-primary font-mono" id="sum-alokasi-nota">Rp 0</span>
                </div>
                <div class="text-right">
                    <span class="text-[11px] text-muted-foreground block uppercase font-medium">Sisa Piutang Berjalan:</span>
                    <span class="text-base font-bold text-foreground font-mono" id="sum-sisa-akhir">Rp 0</span>
                </div>
            </div>
        </div>

        <!-- Submit Footer -->
        <div class="flex items-center justify-end gap-2.5">
            <a href="{{ route('payments.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs shadow-xs">
                Batal
            </a>
            <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs font-semibold shadow-sm">
                Simpan &amp; Alokasikan Pembayaran
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let unpaidInvoices = [];
    let totalDebt = 0;

    async function onCustomerSelect() {
        const customerId = document.getElementById('select_customer').value;
        const banner = document.getElementById('debt-banner');
        const emptyBox = document.getElementById('invoices-empty');
        const tableContainer = document.getElementById('invoices-table-container');
        const statusText = document.getElementById('allocation-status-text');

        if (!customerId) {
            banner.classList.add('hidden');
            emptyBox.classList.remove('hidden');
            tableContainer.classList.add('hidden');
            emptyBox.textContent = 'Silakan pilih pelanggan terlebih dahulu untuk melihat daftar nota yang belum lunas.';
            statusText.textContent = 'Pilih customer terlebih dahulu';
            unpaidInvoices = [];
            totalDebt = 0;
            calculateAllocation();
            return;
        }

        try {
            const res = await fetch(`/api/customers/${customerId}/debt`);
            const data = await res.json();

            totalDebt = parseFloat(data.sisa_hutang_sebelumnya) || 0;
            unpaidInvoices = data.unpaid_invoices || [];

            // Update banner
            banner.classList.remove('hidden');
            document.getElementById('debt-amount-text').textContent = 'Rp ' + totalDebt.toLocaleString('id-ID');
            document.getElementById('debt-count-text').textContent = unpaidInvoices.length + ' Nota belum lunas';

            if (unpaidInvoices.length === 0) {
                emptyBox.classList.remove('hidden');
                tableContainer.classList.add('hidden');
                emptyBox.innerHTML = `
                    <p class="text-emerald-600 font-semibold mb-1">Customer ini tidak memiliki nota piutang yang belum lunas.</p>
                    <p class="text-[11px] text-muted-foreground">Pembayaran yang dimasukkan akan dicatat sebagai deposit saldo berjalan pelanggan.</p>
                `;
                statusText.textContent = 'Tidak ada nota tertagih';
            } else {
                emptyBox.classList.add('hidden');
                tableContainer.classList.remove('hidden');
                statusText.textContent = unpaidInvoices.length + ' Nota siap dialokasikan';
            }

            calculateAllocation();
        } catch (err) {
            console.error('Fetch customer debt error:', err);
        }
    }

    function calculateAllocation() {
        const nominalInput = parseFloat(document.getElementById('input_jumlah').value) || 0;
        let remaining = nominalInput;
        let totalAllocated = 0;

        const tbody = document.getElementById('invoices-tbody');
        tbody.innerHTML = '';

        unpaidInvoices.forEach(inv => {
            const sisaAwal = parseFloat(inv.sisa_piutang) || 0;
            let allocated = 0;

            if (remaining > 0) {
                allocated = Math.min(remaining, sisaAwal);
                remaining -= allocated;
                totalAllocated += allocated;
            }

            const sisaBaru = Math.max(0, sisaAwal - allocated);
            let statusLabel = '';

            if (sisaBaru <= 0 && allocated > 0) {
                statusLabel = '<span class="uk-label uk-label-success text-[9px]">LUNAS</span>';
            } else if (allocated > 0) {
                statusLabel = '<span class="uk-label uk-label-warning text-[9px]">SEBAGIAN</span>';
            } else {
                statusLabel = '<span class="uk-label uk-label-danger text-[9px]">BELUM LUNAS</span>';
            }

            const tr = document.createElement('tr');
            tr.className = allocated > 0 ? 'bg-primary/5 transition-colors' : 'hover:bg-muted/30 transition-colors';
            tr.innerHTML = `
                <td class="py-2.5 px-3 font-mono font-bold text-foreground">${inv.nomor_nota}</td>
                <td class="py-2.5 px-3 text-muted-foreground">${new Date(inv.tanggal).toLocaleDateString('id-ID')}</td>
                <td class="py-2.5 px-3 text-right font-mono text-muted-foreground">Rp ${parseFloat(inv.total_nota).toLocaleString('id-ID')}</td>
                <td class="py-2.5 px-3 text-right font-mono font-semibold text-foreground">Rp ${sisaAwal.toLocaleString('id-ID')}</td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-primary">Rp ${allocated.toLocaleString('id-ID')}</td>
                <td class="py-2.5 px-3 text-right font-mono font-semibold text-foreground">Rp ${sisaBaru.toLocaleString('id-ID')}</td>
                <td class="py-2.5 px-3 text-center">${statusLabel}</td>
            `;
            tbody.appendChild(tr);
        });

        const sisaAkhir = Math.max(0, totalDebt - totalAllocated);

        document.getElementById('sum-piutang-awal').textContent = 'Rp ' + totalDebt.toLocaleString('id-ID');
        document.getElementById('sum-nominal-bayar').textContent = 'Rp ' + nominalInput.toLocaleString('id-ID');
        document.getElementById('sum-alokasi-nota').textContent = 'Rp ' + totalAllocated.toLocaleString('id-ID');
        document.getElementById('sum-sisa-akhir').textContent = 'Rp ' + sisaAkhir.toLocaleString('id-ID');
    }

    $(document).ready(function () {
        $('#select_customer').select2({
            width: '100%',
            placeholder: '-- Cari / Pilih Customer --'
        }).on('change', function () {
            onCustomerSelect();
        });

        const custSelect = document.getElementById('select_customer');
        if (custSelect && custSelect.value) {
            onCustomerSelect();
        }
    });
</script>
@endpush
@endsection
