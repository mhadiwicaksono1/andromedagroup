<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota Penagihan — {{ $invoice->nomor_nota }}</title>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #ffffff;
            color: #1e293b;
            margin: 0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            padding: 32px;
            border-radius: 8px;
        }

        .header-table, .items-table, .calc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .items-table th, .items-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 12px;
        }

        .items-table th {
            background-color: #f1f5f9;
            text-align: left;
            font-weight: 600;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .font-mono {
            font-family: monospace;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
        }

        .status-lunas {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .status-sebagian {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .status-belum_lunas {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .no-print {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-print {
            background-color: #0284c7;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-close {
            background-color: #64748b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            body {
                padding: 0;
            }
            .invoice-container {
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn-close">Kembali ke Aplikasi</a>
        <button onclick="window.print()" class="btn-print">Cetak / Download PDF</button>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <table class="header-table" style="margin-bottom: 24px;">
            <tr>
                <td style="width: 58%; vertical-align: top;">
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 8px;">
                        <img src="{{ asset('images/logo-transparent.png') }}" alt="Andro Meda Group" style="height: 50px; width: auto; object-fit: contain;">
                        <div>
                            <h1 class="brand-title" style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; letter-spacing: 0.02em;">ANDRO MEDA GROUP</h1>
                            <p class="brand-subtitle" style="margin: 2px 0 0 0; font-size: 10px; color: #64748b; text-transform: uppercase;">Divisi Ekspedisi Kargo Udara &amp; Logistik</p>
                        </div>
                    </div>
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: #475569; line-height: 1.4;">
                        Pergudangan Kargo Bandara Soekarno-Hatta Blok C-12, Tangerang<br>
                        Telp: (021) 5591-2345 / 0812-9988-7766 • Email: billing@andromedacargo.com
                    </p>
                </td>
                <td style="width: 45%;" class="text-right">
                    <h2 style="margin: 0; font-size: 18px; color: #0f172a; font-weight: 800; letter-spacing: 0.04em;">
                        @if(($invoice->tipe_nota ?? '') === 'pecahan')
                            NOTA PECAHAN
                        @elseif(($invoice->tipe_nota ?? '') === 'pernama')
                            NOTA PERNAMA
                        @else
                            NOTA PENAGIHAN
                        @endif
                    </h2>
                    <p class="font-mono font-bold" style="margin: 4px 0; font-size: 13px; color: #0284c7;">
                        {{ $invoice->nomor_nota }}
                    </p>
                    <p style="margin: 2px 0; font-size: 11px; color: #475569;">
                        Tanggal: <strong>{{ $invoice->tanggal->format('d/m/Y') }}</strong>
                    </p>
                    <div style="margin-top: 6px;">
                        <span class="badge-status status-{{ $invoice->status_pembayaran }}">
                            {{ str_replace('_', ' ', strtoupper($invoice->status_pembayaran)) }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Customer Destination Box -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 15%; font-size: 11px; color: #64748b; font-weight: 600;">Kepada:</td>
                    <td style="width: 85%;">
                        <strong style="font-size: 14px; color: #0f172a;">{{ $invoice->customer->nama }}</strong>
                        <span class="font-mono" style="font-size: 11px; color: #64748b; margin-left: 6px;">({{ $invoice->customer->kode_customer }})</span>
                        <div style="font-size: 11px; color: #334155; margin-top: 2px;">
                            {{ $invoice->customer->alamat ?? 'Alamat belum diatur' }} • Telp: {{ $invoice->customer->telepon ?? '-' }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Table of Worksheets (SMU) Items -->
        <table class="items-table" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">NO</th>
                    <th style="width: 15%;">SMU</th>
                    <th style="width: 22%;">MARKING</th>
                    <th style="width: 10%;" class="text-center">ORIGIN</th>
                    <th style="width: 12%;" class="text-center">QTY</th>
                    <th style="width: 11%;" class="text-right">KILO</th>
                    <th style="width: 11%;" class="text-right">HARGA</th>
                    <th style="width: 14%;" class="text-right">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->worksheetItems as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-mono font-bold">{{ $item->worksheet->nomor_smu ?? '-' }}</td>
                        <td class="font-mono font-bold text-slate-800">{{ $item->marking ?? '-' }}</td>
                        <td class="text-center font-mono uppercase">{{ $item->origin ?? $item->worksheet->origin ?? 'lop' }}</td>
                        <td class="text-center font-mono font-medium">{{ $item->jumlah_koli }} KOLI</td>
                        <td class="text-right font-mono">{{ number_format($item->berat_kg, 0, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($item->tarif_per_kg, 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @if($invoice->biaya_adm > 0)
                    <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
                        <td colspan="3" class="font-bold text-slate-700" style="padding: 7px 10px;">
                            JUMLAH ADM
                        </td>
                        <td class="text-center font-mono font-bold" style="padding: 7px 10px;">
                            {{ $invoice->worksheetItems->count() }}
                        </td>
                        <td class="text-center font-mono font-bold" style="padding: 7px 10px;">
                            {{ number_format($invoice->worksheetItems->sum('jumlah_koli'), 0, ',', '.') }} KOLI
                        </td>
                        <td class="text-right font-mono font-bold" style="padding: 7px 10px;">
                            {{ number_format($invoice->worksheetItems->sum('berat_kg'), 0, ',', '.') }}
                        </td>
                        <td class="text-right font-mono text-slate-500" style="padding: 7px 10px; font-size: 11px;">
                            @ Rp 3.000
                        </td>
                        <td class="text-right font-mono font-bold text-slate-900" style="padding: 7px 10px;">
                            Rp {{ number_format($invoice->biaya_adm, 0, ',', '.') }}
                        </td>
                    </tr>
                @else
                    <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
                        <td colspan="4" class="text-right font-bold" style="padding: 7px 10px;">TOTAL MUATAN:</td>
                        <td class="text-center font-mono font-bold" style="padding: 7px 10px;">{{ number_format($invoice->worksheetItems->sum('jumlah_koli'), 0, ',', '.') }} KOLI</td>
                        <td class="text-right font-mono font-bold" style="padding: 7px 10px;">{{ number_format($invoice->worksheetItems->sum('berat_kg'), 0, ',', '.') }}</td>
                        <td></td>
                        <td class="text-right font-mono font-bold" style="padding: 7px 10px;">Rp {{ number_format($invoice->subtotal_lembar_kerja, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr style="background-color: #f1f5f9; font-weight: 800; font-size: 13px; border-top: 2px solid #0f172a;">
                    <td colspan="7" class="text-right" style="padding: 8px 10px; letter-spacing: 0.05em;">TOTAL:</td>
                    <td class="text-right font-mono" style="padding: 8px 10px; color: #0284c7;">Rp {{ number_format($invoice->total_nota, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Prominent Bank Info directly under table as shown in image -->
        <div style="font-weight: 800; font-size: 13px; font-family: monospace; margin: 10px 0 16px 2px; color: #0f172a;">
            BCA 7195091927 An ABDUL SATAR
        </div>

        <!-- Calculation Summary Box -->
        <table style="width: 100%; margin-bottom: 24px;">
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; font-size: 11px;">
                        <strong style="color: #0f172a; display: block; margin-bottom: 4px;">Informasi Pembayaran Bank:</strong>
                        Bank Central Asia (BCA)<br>
                        No. Rekening: <strong>7195091927</strong><br>
                        Atas Nama: <strong>ABDUL SATAR</strong><br>
                        <em>Harap cantumkan nomor nota pada berita transfer.</em>
                    </div>
                    @if($invoice->catatan)
                        <div style="margin-top: 10px; font-size: 11px; color: #475569;">
                            <strong>Catatan:</strong> {{ $invoice->catatan }}
                        </div>
                    @endif
                </td>
                <td style="width: 45%; vertical-align: top;">
                    <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">Subtotal Kargo:</td>
                            <td class="text-right font-mono" style="padding: 4px 0;">Rp {{ number_format($invoice->subtotal_lembar_kerja, 0, ',', '.') }}</td>
                        </tr>
                        @if($invoice->biaya_adm > 0)
                            <tr style="color: #0d9488;">
                                <td style="padding: 4px 0;">
                                    + ADM ({{ $invoice->worksheetItems->count() }} SMU @ Rp 3.000):
                                </td>
                                <td class="text-right font-mono font-bold" style="padding: 4px 0;">
                                    Rp {{ number_format($invoice->biaya_adm, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                        @if($invoice->penyesuaian_biaya != 0)
                            <tr>
                                <td style="padding: 4px 0; color: #64748b;">Penyesuaian / Potongan:</td>
                                <td class="text-right font-mono" style="padding: 4px 0;">Rp {{ number_format($invoice->penyesuaian_biaya, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr style="border-top: 1px solid #e2e8f0; font-weight: bold;">
                            <td style="padding: 6px 0;">Total Nilai Nota:</td>
                            <td class="text-right font-mono" style="padding: 6px 0;">Rp {{ number_format($invoice->total_nota, 0, ',', '.') }}</td>
                        </tr>
                        @if($invoice->sisa_hutang_sebelumnya > 0)
                            <tr style="color: #b45309;">
                                <td style="padding: 4px 0;">+ Sisa Hutang Sebelumnya:</td>
                                <td class="text-right font-mono font-bold" style="padding: 4px 0;">Rp {{ number_format($invoice->sisa_hutang_sebelumnya, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr style="border-top: 2px solid #0f172a; font-weight: 800; font-size: 13px;">
                            <td style="padding: 6px 0;">TOTAL TAGIHAN:</td>
                            <td class="text-right font-mono" style="padding: 6px 0; color: #0284c7;">Rp {{ number_format($invoice->total_tagihan_kumulatif, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; color: #15803d;">Pembayaran Diterima:</td>
                            <td class="text-right font-mono font-bold" style="padding: 4px 0; color: #15803d;">Rp {{ number_format($invoice->jumlah_dibayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr style="border-top: 1px solid #e2e8f0; font-weight: 800; font-size: 13px;">
                            <td style="padding: 6px 0; color: #b91c1c;">SISA PIUTANG:</td>
                            <td class="text-right font-mono font-bold" style="padding: 6px 0; color: #b91c1c;">Rp {{ number_format($invoice->sisa_piutang, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Signature Section -->
        <table style="width: 100%; margin-top: 40px; text-align: center; font-size: 11px;">
            <tr>
                <td style="width: 33%;">
                    Tanda Terima Pelanggan,
                    <div style="height: 60px;"></div>
                    <strong>( {{ $invoice->customer->nama }} )</strong>
                </td>
                <td style="width: 33%;">
                </td>
                <td style="width: 33%;">
                    Hormat Kami,
                    <div style="height: 60px;"></div>
                    <strong>( Bagian Keuangan )</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
