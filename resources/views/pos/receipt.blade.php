<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $sale->invoice_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px;
        }
        .receipt-card {
            background: #fff;
            width: 78mm;
            padding: 15px 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
        }
        .header-sub {
            font-size: 10px;
            line-height: 1.3;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 6px 0;
        }
        .item-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .grand-total {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed #000;
        }
        .action-bar {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            font-family: sans-serif;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-print { background: #059669; color: #fff; }
        .btn-close { background: #64748b; color: #fff; }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .receipt-card {
                width: 100%;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }
            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        <!-- Header Apotek -->
        <div class="text-center">
            <div class="header-title">APOTEK CARE</div>
            <div class="header-sub">SIA: 442/082/SIA/DPMPTSP/2026</div>
            <div class="header-sub">SIPA: 19880112/SIPA_31.71/2026/2001</div>
            <div class="header-sub">Jl. Kesehatan Raya No. 45, Jakarta</div>
            <div class="header-sub">Telp: (021) 555-0199 / 0812-3456-7890</div>
        </div>

        <div class="divider"></div>

        <!-- Meta Transaksi -->
        <div>
            <div class="meta-row">
                <span>No: {{ $sale->invoice_number }}</span>
                <span>{{ $sale->sale_date->format('d/m/y H:i') }}</span>
            </div>
            <div class="meta-row">
                <span>Kasir: {{ $sale->cashier->name ?? 'Kasir' }}</span>
                <span>Plg: {{ $sale->customer->name ?? 'Umum' }}</span>
            </div>
            @if($sale->shift_id)
                <div class="meta-row" style="font-size: 10px; color: #555;">
                    <span>Shift #{{ $sale->shift_id }}</span>
                    <span>Status: LUNAS</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Items List -->
        <table class="item-table">
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td colspan="2" class="font-bold">{{ $item->product->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 8px;">
                            {{ (float) $item->quantity }} {{ $item->unit->name ?? 'Pcs' }} x {{ number_format($item->unit_price, 0, ',', '.') }}
                            @if($item->discount_per_item > 0)
                                <span style="font-size: 9px;">(Disc -{{ number_format($item->discount_per_item, 0, ',', '.') }})</span>
                            @endif
                        </td>
                        <td class="text-right font-bold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Ringkasan Angka -->
        <div>
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($sale->discount_amount > 0)
                <div class="totals-row">
                    <span>Diskon Member/Plg:</span>
                    <span>-{{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($sale->tax_amount > 0)
                <div class="totals-row">
                    <span>PPN:</span>
                    <span>+{{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="totals-row grand-total">
                <span>TOTAL:</span>
                <span>{{ format_rupiah($sale->grand_total) }}</span>
            </div>

            <!-- Pembayaran & Kembalian -->
            @php
                $totalPaid = $sale->payments->sum('amount');
                $change = max(0, $totalPaid - $sale->grand_total);
            @endphp
            <div class="divider"></div>
            @foreach($sale->payments as $p)
                <div class="totals-row">
                    <span>Bayar ({{ strtoupper($p->payment_method) }}):</span>
                    <span>{{ number_format($p->amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="totals-row font-bold">
                <span>Kembalian:</span>
                <span>{{ format_rupiah($change) }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="text-center" style="font-size: 10px; line-height: 1.4;">
            <div>Semoga Lekas Sembuh</div>
            <div>Terima Kasih Atas Kunjungan Anda</div>
            <div style="font-size: 8px; margin-top: 4px; color: #555;">
                Obat yang sudah dibeli tidak dapat ditukar/dikembalikan
            </div>
        </div>
    </div>

    <!-- Tombol Cetak Browser -->
    <div class="action-bar">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fa-solid fa-print"></i> Cetak Struk (Ctrl + P)
        </button>
        <button onclick="window.close()" class="btn btn-close">
            Tutup
        </button>
    </div>

</body>
</html>
