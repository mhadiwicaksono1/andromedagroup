<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan Nota Penagihan — Andromeda Cargo</title>

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

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 10px 14px;
        }

        .summary-card-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }

        .summary-card-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.03em;
            font-weight: 700;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #fcfcfd;
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
                    <div class="report-title">Laporan Nota Penagihan</div>
                    <div class="report-meta">
                        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                        @if($selectedCustomer)
                            <br>Pelanggan: <strong>{{ $selectedCustomer->nama }}</strong>
                        @endif
                        @if($status)
                            <br>Status: <strong>{{ strtoupper(str_replace('_', ' ', $status)) }}</strong>
                        @endif
                        <br>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </div>
                </td>
            </tr>
        </table>

        <!-- Summary KPI -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-card-label">Total Nota Terbit</div>
                <div class="summary-card-value">{{ number_format($summary['total_nota_count']) }} Nota</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Nilai Faktur / Omzet</div>
                <div class="summary-card-value">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Telah Terbayar</div>
                <div class="summary-card-value text-emerald">Rp {{ number_format($summary['total_dibayar'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Sisa Piutang Berjalan</div>
                <div class="summary-card-value text-danger">Rp {{ number_format($summary['total_sisa_piutang'], 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">No</th>
                    <th>No. Nota</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th class="text-right">Nilai Nota</th>
                    <th class="text-right">Hutang Lama</th>
                    <th class="text-right">Total Tagihan</th>
                    <th class="text-right">Dibayar</th>
                    <th class="text-right">Sisa Piutang</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $idx => $inv)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold">{{ $inv->nomor_nota }}</td>
                        <td class="text-center">{{ $inv->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $inv->customer->nama ?? '-' }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($inv->total_nota, 0, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($inv->sisa_hutang_sebelumnya, 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($inv->total_tagihan_kumulatif, 0, ',', '.') }}</td>
                        <td class="text-right font-mono text-emerald">Rp {{ number_format($inv->jumlah_dibayar, 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold text-danger">Rp {{ number_format($inv->sisa_piutang, 0, ',', '.') }}</td>
                        <td class="text-center" style="font-size: 10px; font-weight: 600;">
                            {{ strtoupper(str_replace('_', ' ', $inv->status_pembayaran)) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-6 text-muted-foreground">Tidak ada data nota penagihan untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="4" class="text-right uppercase">Total:</td>
                    <td class="text-right font-mono">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($invoices->sum('sisa_hutang_sebelumnya'), 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($invoices->sum('total_tagihan_kumulatif'), 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-emerald">Rp {{ number_format($summary['total_dibayar'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold text-danger">Rp {{ number_format($summary['total_sisa_piutang'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

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
