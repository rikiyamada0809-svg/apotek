<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #222;
            margin: 15px 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 15px;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 9px;
            color: #555;
        }
        .title-block {
            text-align: center;
            margin-bottom: 12px;
        }
        .title-block h2 {
            font-size: 12px;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .title-block span {
            font-size: 9px;
            color: #666;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #777;
            padding: 4px 5px;
            font-size: 9px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .footer-note {
            margin-top: 15px;
            font-size: 8px;
            color: #777;
            text-align: right;
        }
        .signatures {
            width: 100%;
            margin-top: 25px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }
        .sig-space {
            height: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>APOTEK SEHAT SENTOSA</h1>
        <p>SIA: 445/123/SIA-DINKES/2024 &bull; SIPA: 19850101/SIPA-31.71/2024/2001</p>
        <p>Jl. Kesehatan Raya No. 45, Jakarta &bull; Telp: (021) 555-7890</p>
    </div>

    <div class="title-block">
        <h2>{{ $reportTitle }}</h2>
        <span>Periode: {{ $startDate }} s/d {{ $endDate }}</span>
    </div>

    <table class="data-table">
        @if(isset($reportCategory) && $reportCategory === 'Penjualan')
            @if($type === 'product')
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 45%;">Nama Produk Obat</th>
                        <th style="width: 20%;">Kategori</th>
                        <th style="width: 12%;">Qty Terjual</th>
                        <th style="width: 18%;">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $idx => $it)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $it->product?->name ?? 'Produk' }}</td>
                            <td>{{ $it->product?->category?->name ?? '-' }}</td>
                            <td class="text-right">{{ number_format($it->total_qty) }}</td>
                            <td class="text-right">Rp {{ number_format($it->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f9f9f9;">
                        <td colspan="4" class="text-right text-bold">TOTAL OMZET:</td>
                        <td class="text-right text-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @elseif($type === 'gross_profit')
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 22%;">No. Faktur</th>
                        <th style="width: 30%;">Nama Produk</th>
                        <th style="width: 8%;">Qty</th>
                        <th style="width: 15%;">Harga Jual</th>
                        <th style="width: 15%;">HPP (Beli)</th>
                        <th style="width: 15%;">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $idx => $it)
                        @php
                            $sp = (float) $it->unit_price;
                            $hpp = (float) ($it->batch?->purchase_price ?? $it->product?->purchase_price ?? 0);
                            $profit = ($sp - $hpp) * (float) $it->quantity;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $it->sale?->invoice_number }}</td>
                            <td>{{ $it->product?->name }}</td>
                            <td class="text-center">{{ $it->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($sp, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                            <td class="text-right text-bold">Rp {{ number_format($profit, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f9f9f9;">
                        <td colspan="6" class="text-right text-bold">TOTAL LABA KOTOR:</td>
                        <td class="text-right text-bold">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @else
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 25%;">No. Faktur</th>
                        <th style="width: 20%;">Tanggal & Waktu</th>
                        <th style="width: 25%;">Pelanggan</th>
                        <th style="width: 10%;">Kasir</th>
                        <th style="width: 15%;">Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $idx => $sale)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $sale->invoice_number }}</td>
                            <td class="text-center">{{ $sale->sale_date?->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->customer?->name ?? 'Umum' }}</td>
                            <td>{{ $sale->cashier?->name ?? '-' }}</td>
                            <td class="text-right">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f9f9f9;">
                        <td colspan="5" class="text-right text-bold">TOTAL:</td>
                        <td class="text-right text-bold">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        @elseif(isset($reportCategory) && $reportCategory === 'Stok & Gudang')
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Nama Produk Obat</th>
                    <th style="width: 20%;">Kategori</th>
                    <th style="width: 10%;">Stok</th>
                    <th style="width: 20%;">Valuasi Aset</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $p)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->category?->name ?? '-' }}</td>
                        <td class="text-right">{{ number_format($p->filtered_stock ?? $p->stocks->sum('quantity')) }}</td>
                        <td class="text-right">Rp {{ number_format($p->stock_valuation ?? ($p->stocks->sum('quantity') * (float) $p->purchase_price), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f9f9f9;">
                    <td colspan="4" class="text-right text-bold">TOTAL VALUASI ASET:</td>
                    <td class="text-right text-bold">Rp {{ number_format($totalValuation ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @else
            <!-- Finance -->
            @if($type === 'profit_loss')
                <thead>
                    <tr>
                        <th style="width: 60%;">Komponen Laporan</th>
                        <th style="width: 40%; text-align: right;">Jumlah Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-bold">Total Pendapatan Penjualan (Omzet)</td>
                        <td class="text-right text-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;">Beban Pokok Penjualan (HPP)</td>
                        <td class="text-right" style="color: #a00;">(Rp {{ number_format($totalHpp, 0, ',', '.') }})</td>
                    </tr>
                    <tr style="background: #f2f2f2;">
                        <td class="text-bold">Laba Kotor Operasional</td>
                        <td class="text-right text-bold">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left: 20px;">Kerugian Pemusnahan Obat Rusak/Expired</td>
                        <td class="text-right" style="color: #a00;">(Rp {{ number_format($destructionLoss, 0, ',', '.') }})</td>
                    </tr>
                    <tr style="background: #e6f4ea;">
                        <td class="text-bold">Estimasi Laba Bersih Operasional</td>
                        <td class="text-right text-bold" style="font-size: 11px;">Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            @else
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 25%;">No. Faktur</th>
                        <th style="width: 30%;">Supplier</th>
                        <th style="width: 15%;">Jatuh Tempo</th>
                        <th style="width: 25%;">Total Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $idx => $inv)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->supplier?->name ?? '-' }}</td>
                            <td class="text-center">{{ $inv->due_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-right">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f9f9f9;">
                        <td colspan="4" class="text-right text-bold">TOTAL:</td>
                        <td class="text-right text-bold">Rp {{ number_format($totalPurchases ?? ($totalDebt ?? 0), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        @endif
    </table>

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Penanggung Jawab Keuangan</strong>
                <div class="sig-space"></div>
                <strong>( _______________________ )</strong>
            </td>
            <td>
                Jakarta, {{ now()->format('d F Y') }}<br>
                <strong>Apoteker Pengelola Apotek (APA)</strong>
                <div class="sig-space"></div>
                <strong>( apt. Apoteker Pengelola, S.Farm )</strong><br>
                <span>SIPA: 19850101/SIPA-31.71/2024/2001</span>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dicetak otomatis oleh Sistem Farmasi Apotek Sehat Sentosa pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
