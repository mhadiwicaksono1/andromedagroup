<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan Laba Rugi Operasional — Andromeda Cargo</title>

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
        .text-amber { color: #d97706; }

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
                    <div class="report-title">Laporan Laba Rugi Operasional</div>
                    <div class="report-meta">
                        Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
                        @if($selectedCustomer)
                            <br>Pelanggan: <strong>{{ $selectedCustomer->nama }}</strong>
                        @endif
                        <br>Basis Modal: <strong>Rp {{ number_format($modalRatePerKg, 0, ',', '.') }}/kg</strong>
                        <br>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                    </div>
                </td>
            </tr>
        </table>

        <!-- Summary KPI -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-card-label">Pendapatan Kargo</div>
                <div class="summary-card-value">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Beban Pokok Maskapai/IAS</div>
                <div class="summary-card-value text-amber">Rp {{ number_format($summary['total_beban'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Laba Kotor Operasional</div>
                <div class="summary-card-value {{ $summary['total_laba_kotor'] >= 0 ? 'text-emerald' : 'text-danger' }}">
                    Rp {{ number_format($summary['total_laba_kotor'], 0, ',', '.') }}
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Margin Keuntungan (%)</div>
                <div class="summary-card-value" style="color: #7c3aed;">
                    {{ number_format($summary['overall_margin'], 1) }}%
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">No</th>
                    <th>Tanggal</th>
                    <th>No. Lembar Kerja</th>
                    <th>Master SMU</th>
                    <th>Pelanggan</th>
                    <th class="text-center">Koli</th>
                    <th class="text-right">Berat (Kg)</th>
                    <th class="text-right">Pendapatan Kargo</th>
                    <th class="text-right">Beban Pokok</th>
                    <th class="text-right">Laba Bersih</th>
                    <th class="text-center">Margin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pnlRows as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ $row['worksheet']->tanggal->format('d/m/Y') }}</td>
                        <td class="font-mono font-bold">{{ $row['worksheet']->nomor_lembar_kerja }}</td>
                        <td class="font-mono">{{ $row['worksheet']->nomor_smu }}</td>
                        <td>{{ implode(', ', $row['customers']) ?: '-' }}</td>
                        <td class="text-center font-mono">{{ $row['total_koli'] }}</td>
                        <td class="text-right font-mono">{{ number_format($row['total_berat_kg'], 1, ',', '.') }} kg</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($row['pendapatan'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono text-amber">Rp {{ number_format($row['beban_pokok'], 0, ',', '.') }}</td>
                        <td class="text-right font-mono font-bold {{ $row['laba_kotor'] >= 0 ? 'text-emerald' : 'text-danger' }}">
                            Rp {{ number_format($row['laba_kotor'], 0, ',', '.') }}
                        </td>
                        <td class="text-center font-mono" style="font-weight: 700; font-size: 10px;">
                            {{ number_format($row['margin_persen'], 1) }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-6 text-muted-foreground">Tidak ada data operasional SMU untuk rentang tanggal yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="5" class="text-right uppercase">Total:</td>
                    <td class="text-center font-mono">{{ $summary['total_koli'] }}</td>
                    <td class="text-right font-mono">{{ number_format($summary['total_berat_kg'], 1, ',', '.') }} kg</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono text-amber">Rp {{ number_format($summary['total_beban'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold {{ $summary['total_laba_kotor'] >= 0 ? 'text-emerald' : 'text-danger' }}">
                        Rp {{ number_format($summary['total_laba_kotor'], 0, ',', '.') }}
                    </td>
                    <td class="text-center font-mono" style="color: #7c3aed;">
                        {{ number_format($summary['overall_margin'], 1) }}%
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <table class="signature-table">
            <tr>
                <td>
                    <div>Dibuat Oleh:</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->name ?? 'Staff Keuangan' }}</div>
                    <div style="color: #64748b;">Finance &amp; Costing</div>
                </td>
                <td>
                    <div>Diperiksa Oleh:</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">Manager Operasional</div>
                    <div style="color: #64748b;">Operasional Kargo</div>
                </td>
                <td>
                    <div>Disetujui Oleh:</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">Direktur Cabang</div>
                    <div style="color: #64748b;">Andro Meda Group</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
