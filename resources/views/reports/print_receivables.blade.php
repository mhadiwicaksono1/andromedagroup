<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Buku Piutang Pelanggan — Andromeda Cargo</title>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 24px;
            font-size: 12px;
            line-height: 1.45;
        }

        .report-container {
            max-width: 1050px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 28px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .header-table td {
            vertical-align: top;
        }

        .report-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin: 0;
        }

        .report-meta {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .grand-total-banner {
            border: 1px solid #fecaca;
            background-color: #fef2f2;
            border-radius: 6px;
            padding: 12px 18px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .grand-total-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #991b1b;
            letter-spacing: 0.04em;
        }

        .grand-total-val {
            font-size: 20px;
            font-weight: 800;
            color: #dc2626;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .customer-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 18px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .customer-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .customer-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .customer-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            color: #0284c7;
            font-weight: 600;
        }

        .customer-subtext {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .customer-debt {
            text-align: right;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            color: #dc2626;
            font-size: 14px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .data-table th, .data-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
        }

        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.03em;
            font-weight: 600;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .font-bold { font-weight: 700; }
        .text-emerald { color: #059669; }
        .text-danger { color: #dc2626; }

        .signature-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
        }

        .no-print {
            max-width: 1050px;
            margin: 0 auto 16px auto;
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
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-close {
            background-color: #64748b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .report-container {
                max-width: 100%;
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.close()" class="btn-close">Tutup Tab</button>
        <button onclick="window.print()" class="btn-print">Cetak / Download PDF</button>
    </div>

    <div class="report-container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="{{ asset('images/logo-transparent.png') }}" alt="Andro Meda Group" style="height: 44px; width: auto; object-fit: contain;">
                        <div>
                            <div style="font-size: 14px; font-weight: 800; color: #0f172a; letter-spacing: 0.05em;">ANDRO MEDA GROUP</div>
                            <div style="font-size: 11px; color: #64748b;">Sistem Operasional Kargo &amp; Billing Piutang</div>
                        </div>
                    </div>
                </td>
                <td style="width: 45%; text-align: right;">
                    <div class="report-title">Buku Piutang Pelanggan</div>
                    <div class="report-meta">
                        @if($selectedCustomer)
                            Pelanggan: <strong>{{ $selectedCustomer->nama }} ({{ $selectedCustomer->kode_customer }})</strong><br>
                        @else
                            Monitoring: <strong>Seluruh Pelanggan Aktif</strong><br>
                        @endif
                        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </div>
                </td>
            </tr>
        </table>

        <!-- Grand Total Banner -->
        <div class="grand-total-banner">
            <div>
                <div class="grand-total-label">Total Keseluruhan Saldo Piutang Berjalan</div>
                <div style="font-size: 11px; color: #7f1d1d; margin-top: 2px;">
                    Akumulasi tagihan belum lunas dari {{ count($customers) }} pelanggan dengan nota aktif
                </div>
            </div>
            <div class="grand-total-val">
                Rp {{ number_format($grandTotalPiutang, 0, ',', '.') }}
            </div>
        </div>

        <!-- Customer Cards & Tables -->
        @forelse($customers as $c)
            @php
                $totalCustPiutang = $c->invoices->sum('sisa_piutang');
            @endphp
            <div class="customer-card">
                <div class="customer-header">
                    <div>
                        <span class="customer-name">{{ $c->nama }}</span>
                        <span class="customer-code">({{ $c->kode_customer }})</span>
                        <div class="customer-subtext">Telp: {{ $c->telepon ?? '-' }} • Alamat: {{ $c->alamat ?? '-' }}</div>
                    </div>
                    <div class="customer-debt">
                        <span style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 600; display: block;">Total Piutang:</span>
                        Rp {{ number_format($totalCustPiutang, 0, ',', '.') }}
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 25px;" class="text-center">No</th>
                            <th>No. Nota</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Muatan</th>
                            <th class="text-right">Total Tagihan</th>
                            <th class="text-right">Telah Dibayar</th>
                            <th class="text-right">Sisa Piutang</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($c->invoices as $iIdx => $inv)
                            <tr>
                                <td class="text-center">{{ $iIdx + 1 }}</td>
                                <td class="font-mono font-bold">{{ $inv->nomor_nota }}</td>
                                <td class="text-center">{{ $inv->tanggal->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $inv->worksheetItems->count() }} Item SMU</td>
                                <td class="text-right font-mono">Rp {{ number_format($inv->total_tagihan_kumulatif, 0, ',', '.') }}</td>
                                <td class="text-right font-mono text-emerald">Rp {{ number_format($inv->jumlah_dibayar, 0, ',', '.') }}</td>
                                <td class="text-right font-mono font-bold text-danger">Rp {{ number_format($inv->sisa_piutang, 0, ',', '.') }}</td>
                                <td class="text-center" style="font-size: 10px; font-weight: 600;">
                                    {{ strtoupper(str_replace('_', ' ', $inv->status_pembayaran)) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8fafc; font-weight: bold;">
                            <td colspan="4" class="text-right uppercase">Subtotal {{ $c->nama }}:</td>
                            <td class="text-right font-mono">Rp {{ number_format($c->invoices->sum('total_tagihan_kumulatif'), 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-emerald">Rp {{ number_format($c->invoices->sum('jumlah_dibayar'), 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-danger">Rp {{ number_format($totalCustPiutang, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @empty
            <div style="text-align: center; padding: 36px 0; color: #64748b;">
                Tidak ada data pelanggan yang memiliki saldo piutang berjalan.
            </div>
        @endforelse

        <!-- Signatures -->
        <table class="signature-table">
            <tr>
                <td>
                    <div>Dibuat Oleh:</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->name ?? 'Staff Billing' }}</div>
                    <div style="color: #64748b;">Billing &amp; Piutang</div>
                </td>
                <td>
                    <div>Diperiksa Oleh:</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">Bagian Keuangan</div>
                    <div style="color: #64748b;">Finance &amp; Accounting</div>
                </td>
                <td>
                    <div>Disetujui Oleh:</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">Pimpinan Cabang</div>
                    <div style="color: #64748b;">Andro Meda Group</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
