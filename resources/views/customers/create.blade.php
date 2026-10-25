@extends('layouts.cargo')

@section('title', 'Tambah Customer Baru — Andromeda Cargo')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-foreground">Tambah Pelanggan Baru</h1>
            <p class="text-xs text-muted-foreground mt-0.5">Input informasi profil customer sebagai referensi transaksi lembar kerja.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs flex items-center gap-1.5 shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <div class="uk-card uk-card-default border border-border p-5 sm:p-6">
        <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Kode Customer *</label>
                    <input type="text" name="kode_customer" value="{{ old('kode_customer', $nextCode) }}" required
                           class="uk-input text-xs font-mono font-bold text-primary @error('kode_customer') border-destructive @enderror">
                    @error('kode_customer')
                        <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Status Awal *</label>
                    <select name="status" required class="uk-select text-xs">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Kategori Customer & Opsi Tarif -->
            <div class="p-4 rounded-lg bg-muted/40 border border-border space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Kategori Pelanggan *</label>
                        <select name="kategori" id="select-kategori" required class="uk-select text-xs" onchange="updateTarifOptions()">
                            <option value="standar" {{ old('kategori', 'standar') === 'standar' ? 'selected' : '' }}>Standar (Tarif Tunggal Rp 2.100/kg)</option>
                            <option value="prioritas" {{ old('kategori') === 'prioritas' ? 'selected' : '' }}>Prioritas (2 Skema Tarif: Pecahan &amp; Pernama)</option>
                        </select>
                        <p class="text-[11px] text-muted-foreground mt-1">Klasifikasi customer untuk penentuan skema tarif otomatis pada lembar kerja.</p>
                    </div>

                    <!-- Mode Standar: 1 Input Tarif -->
                    <div id="container-tarif-standar" class="{{ old('kategori') === 'prioritas' ? 'hidden' : '' }}">
                        <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Tarif Standar (Rp / kg) *</label>
                        <input type="number" step="10" name="tarif_default" id="input-tarif-default" value="{{ old('tarif_default', 2100) }}"
                               class="uk-input text-xs font-mono font-bold text-foreground">
                        <p class="text-[11px] text-muted-foreground mt-1">Acuan tarif standar: <span class="font-bold text-foreground">Rp 2.100 / kg</span> (terkunci otomatis saat membuat lembar kerja).</p>
                    </div>
                </div>

                <!-- Mode Prioritas: 2 Input Tarif (Pecahan & Pernama) -->
                <div id="container-tarif-prioritas" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-border {{ old('kategori') === 'prioritas' ? '' : 'hidden' }}">
                    <div>
                        <label class="uk-form-label text-xs font-semibold text-amber-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>1. Tarif Pecahan (Rp / kg) *</span>
                            <span class="text-[10px] text-muted-foreground font-normal">Kargo Konsolidasi</span>
                        </label>
                        <input type="number" step="10" name="tarif_pecahan" id="input-tarif-pecahan" value="{{ old('tarif_pecahan', 2000) }}"
                               class="uk-input text-xs font-mono font-bold text-foreground">
                        <p class="text-[11px] text-muted-foreground mt-1">Otomatis dipakai jika lembar kerja bertipe <span class="font-bold text-foreground">Pecahan</span> (acuan: Rp 2.000).</p>
                    </div>

                    <div>
                        <label class="uk-form-label text-xs font-semibold text-blue-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>2. Tarif Pernama (Rp / kg) *</span>
                            <span class="text-[10px] text-muted-foreground font-normal">Khusus 1 Customer</span>
                        </label>
                        <input type="number" step="10" name="tarif_pernama" id="input-tarif-pernama" value="{{ old('tarif_pernama', 1980) }}"
                               class="uk-input text-xs font-mono font-bold text-foreground">
                        <p class="text-[11px] text-muted-foreground mt-1">Otomatis dipakai jika lembar kerja bertipe <span class="font-bold text-foreground">Pernama</span> (acuan: Rp 1.980).</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Nama Perusahaan / Pelanggan *</label>
                <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: PT. Samudera Logistik Jaya"
                       class="uk-input text-xs @error('nama') border-destructive @enderror">
                @error('nama')
                    <p class="text-xs text-destructive mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="telepon" value="{{ old('telepon') }}" placeholder="Contoh: 0812-3456-7890"
                           class="uk-input text-xs">
                </div>

                <div>
                    <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Contoh: billing@samudera.com"
                           class="uk-input text-xs">
                </div>
            </div>

            <div>
                <label class="uk-form-label text-xs font-semibold text-foreground uppercase tracking-wider mb-1.5 block">Alamat Kantor / Pergudangan</label>
                <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap penagihan atau pengiriman..."
                          class="uk-textarea text-xs">{{ old('alamat') }}</textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-2 border-t border-border">
                <a href="{{ route('customers.index') }}" class="uk-btn uk-btn-default uk-btn-sm text-xs">
                    Batal
                </a>
                <button type="submit" class="uk-btn uk-btn-primary uk-btn-sm text-xs font-semibold">
                    Simpan Customer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function updateTarifOptions() {
        const kat = document.getElementById('select-kategori').value;
        const containerStandar = document.getElementById('container-tarif-standar');
        const containerPrioritas = document.getElementById('container-tarif-prioritas');

        if (kat === 'prioritas') {
            containerStandar.classList.add('hidden');
            containerPrioritas.classList.remove('hidden');
        } else {
            containerStandar.classList.remove('hidden');
            containerPrioritas.classList.add('hidden');
        }
    }
</script>
@endpush
@endsection
