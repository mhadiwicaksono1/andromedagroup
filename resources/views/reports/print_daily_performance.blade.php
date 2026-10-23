<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Mutasi Kas &amp; Profit Harian ({{ $startDate }} s/d {{ $endDate }}) — Andromeda Cargo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Geist', ui-sans-serif, system-ui, -apple-system, sans-serif;
            color: #09090b;
            background-color: #ffffff;
        }
        .font-mono {
            font-family: 'Geist Mono', ui-monospace, monospace;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 1.2cm 1cm 1.2cm 1cm;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body class="p-6 max-w-5xl mx-auto text-xs leading-normal">

    <!-- Top Action Bar (No-Print) -->
    <div class="no-print mb-6 p-4 bg-zinc-100 rounded-xl border border-zinc-200 flex items-center justify-between">
        <div>
            <h1 class="text-sm font-bold text-zinc-900">Mode Pratinjau Cetak: Rekap Mutasi Kas &amp; Profit Harian</h1>
            <p class="text-[11px] text-zinc-500">Halaman ini didesain khusus dokumen cetak / ekspor PDF tanpa menu navigasi aplikasi.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3.5 py-1.5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-xs cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="px-3 py-1.5 bg-white border border-zinc-300 hover:bg-zinc-50 text-zinc-700 rounded-lg text-xs font-semibold cursor-pointer">
                Tutup Tab
            </button>
        </div>
    </div>

    <!-- Document Header -->
    <div class="border-b-2 border-zinc-900 pb-4 mb-4 flex items-start justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-zinc-950 uppercase">ANDROMEDA CARGO LOGISTICS</h2>
            <p class="text-[11px] text-zinc-600">Solusi Layanan Pengiriman Kargo Udara, Terminal Handling &amp; Keuangan Terpadu</p>
            <p class="text-[10px] text-zinc-500">Bandara Internasional • Layanan Surat Muatan Udara (SMU) &amp; Pengelolaan IAS</p>
        </div>
        <div class="text-right">
            <div class="text-xs font-bold uppercase tracking-wider text-zinc-900">REKAP MUTASI &amp; PROFIT HARIAN</div>
            <div class="text-[11px] font-mono text-zinc-700 mt-0.5">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
            </div>
            <div class="text-[10px] text-zinc-400 mt-1">Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
        </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-5 gap-2.5 mb-5 p-3 rounded-lg bg-zinc-50 border border-zinc-200 text-xs">
        <div>
            <span class="text-[10px] text-zinc-500 uppercase font-bold block">Uang Masuk</span>
            <span class="font-mono font-bold text-zinc-900 block mt-0.5">Rp {{ number_format($summary['total_uang_masuk'], 0, ',', '.') }}</span>
        </div>
        <div>
            <span class="text-[10px] text-zinc-500 uppercase font-bold block">Bayar ke IAS</span>
            <span class="font-mono font-bold text-zinc-900 block mt-0.5">Rp {{ number_format($summary['total_pembayaran_ias'], 0, ',', '.') }}</span>
        </div>
        <div>
            <span class="text-[10px] text-zinc-500 uppercase font-bold block">Cash Back (Rp 200/kg)</span>
            <span class="font-mono font-bold text-zinc-900 block mt-0.5">Rp {{ number_format($summary['total_cashback'], 0, ',', '.') }}</span>
            <span class="text-[9px] text-zinc-500 font-mono block">{{ number_format($summary['total_tonase_kg'], 1, ',', '.') }} kg SMU</span>
        </div>
        <div>
            <span class="text-[10px] text-zinc-500 uppercase font-bold block">Total Nota</span>
            <span class="font-mono font-bold text-zinc-900 block mt-0.5">Rp {{ number_format($summary['total_nota'], 0, ',', '.') }}</span>
        </div>
        <div class="bg-emerald-50 -my-1 -mr-1 p-2 rounded border border-emerald-200">
            <span class="text-[10px] text-emerald-800 uppercase font-bold block">Profit Bersih</span>
            <span class="font-mono font-bold text-emerald-700 block mt-0.5">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Main Table -->
    <div class="border border-zinc-300 rounded overflow-hidden mb-6">
        <table class="w-full border-collapse text-[11px]">
            <thead>
                <tr class="bg-zinc-100 text-zinc-900 border-b border-zinc-300 font-bold uppercase tracking-wider text-[10px]">
                    <th class="py-2 px-3 text-center border-r border-zinc-300 w-24">TANGGAL</th>
                    <th class="py-2 px-3 text-right border-r border-zinc-300">UANG MASUK</th>
                    <th class="py-2 px-3 text-right border-r border-zinc-300">PEMBAYARAN KE IAS</th>
                    <th class="py-2 px-3 text-right border-r border-zinc-300">CASH BACK</th>
                    <th class="py-2 px-3 text-right border-r border-zinc-300">TOTAL NOTA KE COSTUMER</th>
                    <th class="py-2 px-3 text-right bg-zinc-50">PROFIT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 font-mono">
                @foreach($rows as $row)
                    @if($row['has_activity'])
                    <tr class="hover:bg-zinc-50">
                        <td class="py-1.5 px-3 text-center border-r border-zinc-200 font-bold text-zinc-800">
                            {{ $row['tanggal_formatted'] }}
                        </td>
                        <td class="py-1.5 px-3 text-right border-r border-zinc-200">
                            {{ $row['uang_masuk'] > 0 ? 'Rp ' . number_format($row['uang_masuk'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-1.5 px-3 text-right border-r border-zinc-200">
                            {{ $row['pembayaran_ias'] > 0 ? 'Rp ' . number_format($row['pembayaran_ias'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-1.5 px-3 text-right border-r border-zinc-200">
                            {{ $row['cashback'] > 0 ? 'Rp ' . number_format($row['cashback'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-1.5 px-3 text-right border-r border-zinc-200 font-semibold text-zinc-900">
                            {{ $row['total_nota'] > 0 ? 'Rp ' . number_format($row['total_nota'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-bold {{ $row['profit'] >= 0 ? 'text-zinc-950 bg-zinc-50/50' : 'text-red-600' }}">
                            Rp {{ number_format($row['profit'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot class="bg-zinc-200 text-zinc-950 font-mono font-bold border-t-2 border-zinc-400 text-xs">
                <tr>
                    <td class="py-2 px-3 text-center border-r border-zinc-300">TOTAL</td>
                    <td class="py-2 px-3 text-right border-r border-zinc-300">
                        Rp {{ number_format($summary['total_uang_masuk'], 0, ',', '.') }}
                    </td>
                    <td class="py-2 px-3 text-right border-r border-zinc-300">
                        Rp {{ number_format($summary['total_pembayaran_ias'], 0, ',', '.') }}
                    </td>
                    <td class="py-2 px-3 text-right border-r border-zinc-300">
                        Rp {{ number_format($summary['total_cashback'], 0, ',', '.') }}
                    </td>
                    <td class="py-2 px-3 text-right border-r border-zinc-300">
                        Rp {{ number_format($summary['total_nota'], 0, ',', '.') }}
                    </td>
                    <td class="py-2 px-3 text-right bg-zinc-300">
                        Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Calculation Notes -->
    <div class="text-[10px] text-zinc-600 mb-8 border-l-2 border-zinc-400 pl-3 space-y-0.5">
        <div>* Rumus: <strong>Profit = Total Nota ke Costumer - (Pembayaran ke IAS - Cash Back)</strong>. Data ditarik otomatis dari realisasi transaksi sistem.</div>
        <div>* Ketentuan Cash Back: <strong>Rp 200 per kg SMU</strong>, diakumulasikan dan <strong>didapatkan/diklaim setiap bulan</strong> dari pihak IAS/maskapai.</div>
    </div>

    <!-- Signature Section -->
    <div class="grid grid-cols-2 gap-8 text-center text-xs mt-12 pt-6">
        <div>
            <p class="text-zinc-500 mb-16">Dibuat &amp; Diverifikasi Oleh:</p>
            <p class="font-bold underline text-zinc-950 uppercase">{{ auth()->user()->name ?? 'Bagian Keuangan' }}</p>
            <p class="text-[10px] text-zinc-500">Staf Keuangan &amp; Kasir Kargo</p>
        </div>
        <div>
            <p class="text-zinc-500 mb-16">Mengetahui &amp; Disetujui:</p>
            <p class="font-bold underline text-zinc-950 uppercase">( ............................................ )</p>
            <p class="text-[10px] text-zinc-500">Pimpinan / Kepala Cabang</p>
        </div>
    </div>

</body>
</html>
