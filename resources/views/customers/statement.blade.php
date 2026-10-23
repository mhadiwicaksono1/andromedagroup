<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Kartu Piutang — {{ $customer->nama }} ({{ $customer->kode_customer }})</title>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 24px;
            font-size: 12px;
            line-height: 1.5;
        }

        .statement-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 32px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .header-table, .items-table, .meta-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td, .meta-table td {
            vertical-align: top;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        .doc-title {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .items-table th, .items-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 9px;
            font-size: 11px;
        }

        .items-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.03em;
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
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .badge-type {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 3px;
        }

        .badge-invoice {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .badge-payment {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
        }

        .kpi-card.highlight {
            background-color: #fef2f2;
            border-color: #fecaca;
        }

        .kpi-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }

        .kpi-value {
            font-size: 15px;
            font-weight: 800;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #0f172a;
        }

        .signatures-table {
            width: 100%;
            margin-top: 36px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            width: 45%;
            vertical-align: top;
            font-size: 11px;
        }

        .signature-space {
            height: 60px;
        }

        .signature-line {
            border-bottom: 1px solid #334155;
            width: 75%;
            margin: 0 auto 4px auto;
        }

        .no-print {
            max-width: 850px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-print {
            background-color: #0284c7;
            color: white;
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-close {
            background-color: #64748b;
            color: white;
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 12mm 12mm;
            }
            body {
                background: white;
                padding: 0;
            }
            .statement-container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="{{ route('customers.show', $customer) }}" class="btn-close">Kembali ke Detail Customer</a>
        <button onclick="window.print()" class="btn-print">Cetak / Download PDF</button>
    </div>

    <div class="statement-container">
        <!-- Official Corporate Header -->
        <table class="header-table" style="margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px;">
            <tr>
                <td style="width: 58%; vertical-align: top;">
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 6px;">
                        <img src="{{ asset('images/logo-transparent.png') }}" alt="Andro Meda Group" style="height: 48px; width: auto; object-fit: contain;">
                        <div>
                            <h1 class="brand-title" style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; letter-spacing: 0.02em;">ANDRO MEDA GROUP</h1>
                            <p class="brand-subtitle" style="margin: 2px 0 0 0; font-size: 10px; color: #64748b; text-transform: uppercase;">Divisi Ekspedisi Kargo Udara &amp; Logistik Terintegrasi</p>
                        </div>
                    </div>
                    <p style="margin: 4px 0 0 0; font-size: 10.5px; color: #475569; line-height: 1.4;">
                        Pergudangan Kargo Bandara Soekarno-Hatta Blok C-12, Tangerang<br>
                        Telp: (021) 5591-2345 / 0812-9988-7766 • Email: billing@andromedacargo.com
                    </p>
                </td>
                <td style="width: 42%;" class="text-right">
                    <h2 class="doc-title">BUKU KARTU PIUTANG</h2>
                    <p style="margin: 2px 0; font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase;">
                        STATEMENT OF ACCOUNT
                    </p>
                    <p class="font-mono" style="margin: 4px 0 0 0; font-size: 11px; color: #0284c7; font-weight: bold;">
                        KODE: {{ $customer->kode_customer }}
                    </p>
                    <p style="margin: 2px 0 0 0; font-size: 10.5px; color: #475569;">
                        Tanggal Cetak: <strong>{{ now()->translatedFormat('d F Y') }}</strong>
                    </p>
                </td>
            </tr>
        </table>

        <!-- Customer Profile & Metadata -->
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 6px; margin-bottom: 18px;">
            <table class="meta-table">
                <tr>
                    <td style="width: 55%;">
                        <span style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Pelanggan / Perusahaan:</span><br>
                        <strong style="font-size: 13.5px; color: #0f172a;">{{ $customer->nama }}</strong><br>
                        <span style="font-size: 11px; color: #475569;">{{ $customer->alamat ?? 'Alamat belum diatur' }}</span><br>
                        <span style="font-size: 11px; color: #475569;">Telp: {{ $customer->telepon ?? '-' }} • Email: {{ $customer->email ?? '-' }}</span>
                    </td>
                    <td style="width: 45%;" class="text-right">
                        <span style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Klasifikasi Tarif Pelanggan:</span><br>
                        <strong style="font-size: 12px; color: #0f172a; text-transform: uppercase;">
                            Kategori {{ $customer->kategori }} (Rp {{ number_format($customer->tarif_default ?? 2100, 0, ',', '.') }}/kg)
                        </strong><br>
                        <span style="font-size: 10.5px; color: #166534; font-weight: 600;">Status Akun: AKTIF</span>
                    </td>
                </tr>
            </table>
        </div>

        @php
            $totalDebit = collect($kartuPiutang)->sum('debit');
            $totalKredit = collect($kartuPiutang)->sum('kredit');
        @endphp

        <!-- 3 Financial Summary Cards -->
        <table style="width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 16px;">
            <tr>
                <td style="width: 33.3%;">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Tagihan (Debit)</div>
                        <div class="kpi-value" style="color: #991b1b;">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 33.3%;">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Pembayaran (Kredit)</div>
                        <div class="kpi-value" style="color: #166534;">Rp {{ number_format($totalKredit, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 33.3%;">
                    <div class="kpi-card highlight">
                        <div class="kpi-label" style="color: #991b1b;">Sisa Saldo Piutang Berjalan</div>
                        <div class="kpi-value" style="color: #b91c1c;">Rp {{ number_format($totalSisaHutang, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Formal Ledger Table -->
        <table class="items-table" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="width: 4%;" class="text-center">No.</th>
                    <th style="width: 11%;">Tanggal</th>
                    <th style="width: 13%;">Jenis</th>
                    <th style="width: 18%;">No. Transaksi / Nota</th>
                    <th style="width: 26%;">Uraian / Keterangan</th>
                    <th style="width: 13%;" class="text-right">Debit (Tagihan)</th>
                    <th style="width: 13%;" class="text-right">Kredit (Bayar)</th>
                    <th style="width: 14%;" class="text-right">Saldo Piutang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kartuPiutang as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $row['tanggal']->format('d/m/Y') }}</td>
                        <td>
                            @if($row['tipe'] === 'invoice')
                                <span class="badge-type badge-invoice">Nota Tagihan</span>
                            @else
                                <span class="badge-type badge-payment">Pembayaran</span>
                            @endif
                        </td>
                        <td class="font-mono font-bold">{{ $row['nomor_referensi'] }}</td>
                        <td>{{ $row['keterangan'] }}</td>
                        <td class="text-right font-mono" style="color: #991b1b;">
                            {{ $row['debit'] > 0 ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right font-mono" style="color: #166534; font-weight: bold;">
                            {{ $row['kredit'] > 0 ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right font-mono font-bold" style="color: #0f172a;">
                            Rp {{ number_format($row['saldo'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 24px; color: #64748b;">
                            Belum ada riwayat transaksi nota tagihan maupun penerimaan pembayaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="5" style="text-align: right; padding: 8px 10px; font-size: 11px;">
                        TOTAL AKUMULASI MUTASI &amp; SISA PIUTANG:
                    </td>
                    <td class="text-right font-mono" style="color: #991b1b; padding: 8px 9px;">
                        Rp {{ number_format($totalDebit, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-mono" style="color: #166534; padding: 8px 9px;">
                        Rp {{ number_format($totalKredit, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-mono font-bold" style="color: #b91c1c; font-size: 12px; padding: 8px 9px;">
                        Rp {{ number_format($totalSisaHutang, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Payment Instructions & Banking Info -->
        <table style="width: 100%; margin-bottom: 16px;">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px 12px; font-size: 10.5px;">
                        <strong style="color: #0f172a;">Informasi Rekening Pembayaran Resmi:</strong><br>
                        Bank: <strong>BCA (Bank Central Asia)</strong> • KCP Bandara Soekarno Hatta<br>
                        No. Rekening: <strong class="font-mono" style="font-size: 11.5px; color: #0284c7;">882-019-2831</strong><br>
                        Atas Nama: <strong>PT. Andromeda Kargo Nusantara</strong><br>
                        <span style="color: #64748b; font-style: italic;">Harap cantumkan nomor nota / kode customer pada saat transfer dan kirimkan bukti ke WhatsApp Finance: 0812-9988-7711</span>
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right; font-size: 10px; color: #64748b; padding-left: 12px;">
                    <p style="margin: 0;">Dokumen ini merupakan catatan mutasi resmi penagihan &amp; pembayaran kargo.</p>
                </td>
            </tr>
        </table>

        <!-- Signatures -->
        <table class="signatures-table">
            <tr>
                <td class="signature-box">
                    <div>Diterima &amp; Dikonfirmasi Oleh,</div>
                    <div style="font-weight: 600; color: #475569; margin-top: 2px;">Customer / Pelanggan</div>
                    <div class="signature-space"></div>
                    <div class="signature-line"></div>
                    <strong style="color: #0f172a;">( {{ $customer->nama }} )</strong>
                </td>
                <td class="signature-box">
                    <div>Tangerang, {{ now()->translatedFormat('d F Y') }}</div>
                    <div style="font-weight: 600; color: #475569; margin-top: 2px;">Bagian Keuangan &amp; Penagihan Piutang</div>
                    <div class="signature-space"></div>
                    <div class="signature-line"></div>
                    <strong style="color: #0f172a;">PT. ANDROMEDA KARGO NUSANTARA</strong>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
