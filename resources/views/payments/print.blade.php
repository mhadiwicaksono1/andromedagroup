<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran {{ $payment->nomor_pembayaran }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
            margin-bottom: 20px;
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
            margin-top: 3px;
        }

        .receipt-badge {
            display: inline-block;
            background-color: #0284c7;
            color: white;
            font-size: 14px;
            font-weight: bold;
            padding: 6px 16px;
            border-radius: 4px;
            letter-spacing: 1px;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .content-table td {
            padding: 8px 6px;
            vertical-align: top;
        }

        .content-table .label {
            width: 28%;
            color: #475569;
            font-weight: 600;
        }

        .content-table .colon {
            width: 2%;
        }

        .content-table .value {
            width: 70%;
            color: #0f172a;
        }

        .amount-box {
            background-color: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 20px;
            font-weight: 800;
            font-family: monospace;
            color: #15803d;
            display: inline-block;
            margin-top: 10px;
        }

        .allocations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 25px;
            font-size: 12px;
        }

        .allocations-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            padding: 8px;
            border: 1px solid #cbd5e1;
        }

        .allocations-table td {
            padding: 7px 8px;
            border: 1px solid #e2e8f0;
        }

        .signatures-table {
            width: 100%;
            margin-top: 40px;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            margin-top: 65px;
            border-bottom: 1px solid #94a3b8;
            width: 75%;
            margin-left: auto;
            margin-right: auto;
        }

        .no-print {
            max-width: 800px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-print {
            background-color: #0284c7;
            color: white;
        }

        .btn-close {
            background-color: #64748b;
            color: white;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt-container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="{{ route('payments.show', $payment) }}" class="btn btn-close">Kembali</a>
        <button onclick="window.print()" class="btn btn-print">Cetak Kuitansi</button>
    </div>

    <div class="receipt-container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="vertical-align: middle;">
                    <div class="brand-title">PT. ANDROMEDA KARGO NUSANTARA</div>
                    <div class="brand-subtitle">
                        Spesialis Pengiriman Kargo Udara &amp; Konsolidasi SMU Domestik<br>
                        Terminal Kargo Bandara Soekarno-Hatta, Cengkareng • Telp: (021) 550-2918
                    </div>
                </td>
                <td style="text-align: right; vertical-align: middle;">
                    <span class="receipt-badge">KUITANSI PEMBAYARAN</span>
                    <div style="font-family: monospace; font-size: 13px; font-weight: bold; margin-top: 6px; color: #0284c7;">
                        {{ $payment->nomor_pembayaran }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Receipt Main Details -->
        <table class="content-table">
            <tr>
                <td class="label">Telah Diterima Dari</td>
                <td class="colon">:</td>
                <td class="value">
                    <strong>{{ $payment->customer->nama ?? '-' }}</strong> 
                    <span style="font-family: monospace; color: #64748b;">({{ $payment->customer->kode_customer ?? '-' }})</span><br>
                    <span style="font-size: 11px; color: #475569;">{{ $payment->customer->alamat ?? 'Alamat tidak tertera' }} • Telp: {{ $payment->customer->telepon ?? '-' }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Tanggal Penerimaan</td>
                <td class="colon">:</td>
                <td class="value">{{ $payment->tanggal->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Metode Pembayaran</td>
                <td class="colon">:</td>
                <td class="value" style="text-transform: uppercase; font-weight: bold;">
                    {{ $payment->metode }}
                    @if($payment->catatan)
                        <span style="font-weight: normal; color: #475569;">(Referensi: {{ $payment->catatan }})</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Jumlah Nominal</td>
                <td class="colon">:</td>
                <td class="value">
                    <div class="amount-box">
                        Rp {{ number_format($payment->jumlah, 0, ',', '.') }}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="label">Untuk Alokasi</td>
                <td class="colon">:</td>
                <td class="value">
                    Pelunasan Piutang Pengiriman Kargo Udara (SMU)
                </td>
            </tr>
        </table>

        <!-- Allocations Detail Table -->
        @if($payment->allocations->count() > 0)
            <div style="font-weight: bold; font-size: 12px; color: #0f172a; margin-top: 15px;">
                Rincian Nota Piutang yang Dipotong:
            </div>
            <table class="allocations-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No.</th>
                        <th style="width: 25%;">Nomor Nota</th>
                        <th style="width: 20%;">Tanggal Nota</th>
                        <th style="width: 25%; text-align: right;">Total Nilai Nota</th>
                        <th style="width: 25%; text-align: right;">Jumlah Dipotong</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payment->allocations as $idx => $alloc)
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td style="font-family: monospace; font-weight: bold;">{{ $alloc->invoice->nomor_nota ?? '-' }}</td>
                            <td>{{ $alloc->invoice ? $alloc->invoice->tanggal->format('d/m/Y') : '-' }}</td>
                            <td style="text-align: right; font-family: monospace;">Rp {{ number_format($alloc->invoice->total_nota ?? 0, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold; color: #15803d;">
                                Rp {{ number_format($alloc->jumlah_alokasi, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Signatures -->
        <table class="signatures-table">
            <tr>
                <td class="signature-box">
                    <div>Penyetor / Pelanggan,</div>
                    <div class="signature-line"></div>
                    <div style="font-weight: bold; margin-top: 5px;">{{ $payment->customer->nama ?? 'Pelanggan' }}</div>
                </td>
                <td class="signature-box">
                    <div>Diterima Oleh Kasir / Keuangan,</div>
                    <div class="signature-line"></div>
                    <div style="font-weight: bold; margin-top: 5px;">{{ $payment->user->name ?? 'Kasir Keuangan' }}</div>
                    <div style="font-size: 10px; color: #64748b;">PT. Andromeda Kargo Nusantara</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
