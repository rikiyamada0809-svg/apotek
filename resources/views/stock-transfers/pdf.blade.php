<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Transfer Stok - {{ $stockTransfer->transfer_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333333;
            margin: 20px 25px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #555;
        }
        .title {
            text-align: center;
            margin-bottom: 15px;
        }
        .title h2 {
            font-size: 13px;
            text-decoration: underline;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }
        .title span {
            font-size: 11px;
            font-weight: bold;
            color: #444;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 10.5px;
        }
        .meta-table td.label {
            width: 20%;
            font-weight: bold;
        }
        .meta-table td.colon {
            width: 2%;
        }
        .meta-table td.value {
            width: 28%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f2f2f2;
            border: 1px solid #444;
            padding: 6px 5px;
            font-size: 10px;
            text-align: center;
            text-transform: uppercase;
        }
        .items-table td {
            border: 1px solid #777;
            padding: 5px 6px;
            font-size: 10px;
        }
        .items-table td.center {
            text-align: center;
        }
        .signatures {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signatures td {
            text-align: center;
            vertical-align: top;
            font-size: 10px;
            width: 25%;
        }
        .sign-box {
            height: 55px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>APOTEK SEHAT SENTOSA</h1>
        <p>Sistem Pengelolaan Stok & Farmasi Terintegrasi</p>
        <p>Jl. Kesehatan Raya No. 123, Jakarta Selatan | Telp: (021) 555-0123</p>
    </div>

    <div class="title">
        <h2>SURAT JALAN / TRANSFER STOK ANTAR GUDANG</h2>
        <span>Nomor: {{ $stockTransfer->transfer_number }}</span>
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">Gudang Asal</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $stockTransfer->fromWarehouse?->name }}</strong> ({{ $stockTransfer->fromWarehouse?->code }})</td>
            <td class="label">Tanggal Transfer</td>
            <td class="colon">:</td>
            <td class="value">{{ $stockTransfer->transfer_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Gudang Tujuan</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $stockTransfer->toWarehouse?->name }}</strong> ({{ $stockTransfer->toWarehouse?->code }})</td>
            <td class="label">Status Transfer</td>
            <td class="colon">:</td>
            <td class="value">{{ strtoupper($stockTransfer->status) }}</td>
        </tr>
        <tr>
            <td class="label">Dibuat Oleh</td>
            <td class="colon">:</td>
            <td class="value">{{ $stockTransfer->creator?->name ?? '-' }}</td>
            <td class="label">Disetujui Oleh</td>
            <td class="colon">:</td>
            <td class="value">{{ $stockTransfer->approver?->name ?? '-' }}</td>
        </tr>
        @if($stockTransfer->notes)
        <tr>
            <td class="label">Catatan</td>
            <td class="colon">:</td>
            <td class="value" colspan="4">{{ $stockTransfer->notes }}</td>
        </tr>
        @endif
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Kode & Nama Obat</th>
                <th style="width: 80px;">Kategori</th>
                <th style="width: 85px;">No. Batch</th>
                <th style="width: 70px;">Exp. Date</th>
                <th style="width: 65px;">Qty Kirim</th>
                <th style="width: 65px;">Qty Terima</th>
                <th style="width: 90px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockTransfer->items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name }}</strong>
                        <div style="font-size: 9px; color: #666;">Kode: {{ $item->product?->product_code }}</div>
                    </td>
                    <td>{{ $item->product?->category?->name ?? '-' }}</td>
                    <td class="center">{{ $item->batch?->batch_number ?? '-' }}</td>
                    <td class="center">{{ $item->batch?->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                    <td class="center">
                        <strong>{{ number_format($item->quantity, 0) }}</strong> {{ $item->product?->baseUnit()?->unit?->name ?? 'Pcs' }}
                    </td>
                    <td class="center">
                        @if($stockTransfer->status === 'received')
                            <strong>{{ number_format($item->received_qty, 0) }}</strong> {{ $item->product?->baseUnit()?->unit?->name ?? 'Pcs' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div>Yang Menyerahkan,</div>
                <div>(Gudang Asal)</div>
                <div class="sign-box"></div>
                <div>( ..................................... )</div>
            </td>
            <td>
                <div>Pengantar / Driver,</div>
                <div>(Logistik)</div>
                <div class="sign-box"></div>
                <div>( ..................................... )</div>
            </td>
            <td>
                <div>Yang Menerima,</div>
                <div>(Gudang Tujuan)</div>
                <div class="sign-box"></div>
                <div>( {{ $stockTransfer->receiver?->name ?? '.....................................' }} )</div>
            </td>
            <td>
                <div>Mengetahui,</div>
                <div>(Penanggung Jawab / Apoteker)</div>
                <div class="sign-box"></div>
                <div>( ..................................... )</div>
            </td>
        </tr>
    </table>
</body>
</html>
