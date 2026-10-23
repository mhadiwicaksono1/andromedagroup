<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan Lembar Kerja Kargo — Andromeda Cargo</title>

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
                    <div class="report-title">Laporan Lembar Kerja</div>
                    <div class="report-meta">
                        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                        @if($selectedCustomer)
                            <br>Pelanggan: <strong>{{ $selectedCustomer->nama }}</strong>
                        @endif
                        <br>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </div>
                </td>
            </tr>
        </table>

        <!-- Summary KPI -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-card-label">Total Muatan (Items)</div>
                <div class="summary-card-value">{{ number_format($summary['total_items']) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Koli / Colly</div>
                <div class="summary-card-value">{{ number_format($summary['total_koli']) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Berat (Kg)</div>
                <div class="summary-card-value">{{ number_format($summary['total_berat'], 2, ',', '.') }} kg</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Biaya Kargo</div>
                <div class="summary-card-value">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">No</th>
                    <th>Tanggal</th>
                    <th>No. Lembar Kerja</th>
                    <th>No. Master SMU</th>
                    <th>Customer</th>
                    <th>Marking</th>
                    <th class="text-right">Koli</th>
                    <th class="text-right">Berat (Kg)</th>
                    <th class="text-right">Tarif/Kg</th>
                    <th class="text-right">Total Biaya</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="whitespace-nowrap">{{ $item->worksheet?->tanggal ? $item->worksheet->tanggal->format('d/m/Y') : '-' }}</td>
                        <td class="font-mono font-bold">{{ $item->worksheet?->nomor_lembar_kerja ?? '-' }}</td>
                        <td class="font-mono">{{ $item->worksheet?->nomor_smu ?? '-' }}</td>
                        <td>{{ $item->customer->nama ?? '-' }}</td>
                        <td>{{ $item->marking ?? '-' }}</td>
                        <td class="text-right font-mono">{{ $item->jumlah_koli }}</td>
                        <td class="text-right font-mono">{{ number_format($item->berat_kg, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($item->tarif_per_kg, 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                        <td class="text-center uppercase" style="font-size: 10px;">{{ $item->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-6 text-muted-foreground">Tidak ada data lembar kerja untuk filter yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="6" class="text-right uppercase">Grand Total:</td>
                    <td class="text-right font-mono">{{ number_format($summary['total_koli']) }}</td>
                    <td class="text-right font-mono">{{ number_format($summary['total_berat'], 2, ',', '.') }} kg</td>
                    <td></td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</td>
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
                    <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->name ?? 'Staff Operasional' }}</div>
                    <div style="color: #64748b;">Operasional Kargo</div>
                </td>
                <td>
                    <div>Diperiksa Oleh:</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">Bagian Keuangan</div>
                    <div style="color: #64748b;">Finance &amp; Billing</div>
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
