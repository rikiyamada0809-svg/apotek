<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Retur Pembelian {{ $purchaseReturn->return_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-title {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            text-align: right;
            color: #1f2937;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 10.5px;
        }
        .box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 8px;
            border-radius: 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px 5px;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 5px;
            font-size: 10px;
        }
        .total-box {
            width: 40%;
            margin-left: auto;
            margin-bottom: 20px;
        }
        .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        .total-table td {
            padding: 4px 6px;
            font-size: 10.5px;
        }
        .signatures {
            width: 100%;
            margin-top: 30px;
        }
        .signature-col {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 75%;
            margin: 50px auto 4px auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 55%;">
                    <div class="company-title">{{ config('app.name', 'APOTEK') }}</div>
                    <div style="font-size: 10px; color: #6b7280; margin-top: 2px;">
                        Sistem Informasi Manajemen Apotek Farmasi<br>
                        Telp: 0812-3456-7890 | Email: admin@apotek.local
                    </div>
                </td>
                <td style="width: 45%;" class="doc-title">
                    SURAT RETUR PEMBELIAN
                    <div style="font-size: 11px; font-weight: normal; color: #4b5563; margin-top: 3px;">
                        No: <strong>{{ $purchaseReturn->return_number }}</strong>
                    </div>
                    <div style="font-size: 9px; font-weight: normal; color: #6b7280;">
                        Tgl Retur: {{ \Carbon\Carbon::parse($purchaseReturn->return_date)->translatedFormat('d F Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 48%;">
                <div class="box">
                    <strong style="color: #374151; font-size: 11px;">SUPPLIER TUJUAN:</strong><br>
                    <strong>{{ $purchaseReturn->supplier->name ?? '-' }}</strong><br>
                    {{ $purchaseReturn->supplier->address ?? '-' }}<br>
                    Telp: {{ $purchaseReturn->supplier->phone ?? '-' }}
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%;">
                <div class="box">
                    <strong style="color: #374151; font-size: 11px;">INFORMASI REFERENSI:</strong><br>
                    Ref. PO: <strong>{{ $purchaseReturn->purchaseOrder->po_number ?? '-' }}</strong><br>
                    Gudang: {{ $purchaseReturn->purchaseOrder->warehouse->name ?? '-' }}<br>
                    Kompensasi: 
                    @if($purchaseReturn->resolution_type === 'replacement')
                        <strong>Penggantian Barang Baru</strong>
                    @elseif($purchaseReturn->resolution_type === 'credit_note')
                        <strong>Nota Kredit / Potong Faktur</strong>
                    @else
                        <strong>Pengembalian Dana Tunai</strong>
                    @endif
                    <br>
                    Alasan: {{ $purchaseReturn->reason }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th>Nama Produk Obat</th>
                <th style="width: 110px;">Batch / ED</th>
                <th style="width: 60px; text-align: center;">Qty</th>
                <th style="width: 80px; text-align: right;">Harga Satuan</th>
                <th style="width: 90px; text-align: right;">Subtotal</th>
                <th style="width: 120px;">Alasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseReturn->items as $idx => $item)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name ?? '-' }}</strong><br>
                        <span style="font-size: 9px; color: #6b7280;">SKU: {{ $item->product->sku ?? '-' }}</span>
                    </td>
                    <td>
                        @if($item->batch)
                            {{ $item->batch->batch_number }}<br>
                            <span style="font-size: 9px; color: #6b7280;">ED: {{ \Carbon\Carbon::parse($item->batch->expiry_date)->format('d/m/Y') }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ number_format($item->quantity, 0) }}
                    </td>
                    <td style="text-align: right;">
                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </td>
                    <td style="font-size: 9px;">{{ $item->reason ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <table class="total-table">
            <tr style="border-top: 1px solid #d1d5db; font-weight: bold;">
                <td style="text-align: right;">TOTAL RETUR:</td>
                <td style="text-align: right; color: #059669; font-size: 12px;">
                    Rp {{ number_format($purchaseReturn->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    @if($purchaseReturn->notes)
        <div style="font-size: 10px; color: #4b5563; margin-bottom: 20px; padding: 6px; background-color: #f9fafb; border-left: 3px solid #059669;">
            <strong>Catatan:</strong> {{ $purchaseReturn->notes }}
        </div>
    @endif

    <table class="signatures">
        <tr>
            <td class="signature-col">
                Dibuat Oleh,
                <div class="signature-line"></div>
                <strong>{{ $purchaseReturn->creator->name ?? 'Staff Gudang' }}</strong>
            </td>
            <td class="signature-col">
                Disetujui Oleh,
                <div class="signature-line"></div>
                <strong>{{ $purchaseReturn->approver->name ?? 'Apoteker / Pimpinan' }}</strong>
            </td>
            <td class="signature-col">
                Diterima Oleh Supplier,
                <div class="signature-line"></div>
                <strong>( ..................................... )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
