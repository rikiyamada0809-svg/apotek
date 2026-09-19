<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Penerimaan Barang {{ $goodsReceipt->receipt_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            color: #1f2937;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 11px;
        }
        .box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            border-radius: 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px 6px;
            font-size: 11px;
            font-weight: bold;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            font-size: 11px;
        }
        .sign-line {
            width: 180px;
            margin: 60px auto 0 auto;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="company-title">APOTEK CARE PHARMACY</div>
                    <div style="font-size: 10px; color: #6b7280;">Sistem Informasi & Manajemen Farmasi Terpadu</div>
                    <div style="font-size: 10px; color: #6b7280;">Jl. Kesehatan Raya No. 45, Jakarta | Telp: (021) 555-0199</div>
                </td>
                <td class="text-right">
                    <div class="doc-title">BUKTI PENERIMAAN BARANG</div>
                    <div style="font-size: 12px; font-weight: bold; color: #374151;">No: {{ $goodsReceipt->receipt_number }}</div>
                    <div style="font-size: 10px; color: #6b7280;">Tanggal: {{ $goodsReceipt->receipt_date->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%; padding-right: 10px;">
                <div class="box">
                    <div style="font-weight: bold; margin-bottom: 4px; color: #059669;">DITERIMA DARI SUPPLIER:</div>
                    <div style="font-size: 12px; font-weight: bold;">{{ $goodsReceipt->supplier->name }}</div>
                    <div>Kontak: {{ $goodsReceipt->supplier->contact_person ?? '-' }}</div>
                    <div>No. Surat Jalan: <strong>{{ $goodsReceipt->delivery_note_number ?? '-' }}</strong></div>
                    <div>Referensi PO: <strong>{{ $goodsReceipt->purchaseOrder->po_number ?? '-' }}</strong></div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 10px;">
                <div class="box">
                    <div style="font-weight: bold; margin-bottom: 4px; color: #059669;">GUDANG PENERIMAAN:</div>
                    <div style="font-size: 12px; font-weight: bold;">{{ $goodsReceipt->warehouse->name }}</div>
                    <div>Petugas Penerima: {{ $goodsReceipt->receiver->name ?? '-' }}</div>
                    <div>Status Verifikasi: <strong>{{ strtoupper($goodsReceipt->status) }}</strong></div>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 35%;">Nama Produk / Obat</th>
                <th style="width: 15%;">No. Batch</th>
                <th style="width: 15%;">Tgl Expired</th>
                <th style="width: 10%;" class="text-right">Qty Diterima</th>
                <th style="width: 10%;">Lokasi Rak</th>
                <th style="width: 10%;" class="text-center">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goodsReceipt->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                        <div style="font-size: 9px; color: #6b7280;">Kode: {{ $item->product->product_code ?? '-' }}</div>
                    </td>
                    <td><code>{{ $item->batch_number }}</code></td>
                    <td>{{ $item->expiry_date->format('d/m/Y') }}</td>
                    <td class="text-right"><strong>{{ (float) $item->quantity_received }}</strong></td>
                    <td>{{ $item->storageLocation->code ?? 'Gudang' }}</td>
                    <td class="text-center">{{ $item->condition === 'good' ? 'Baik' : 'Rusak' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($goodsReceipt->notes)
        <div style="font-size: 10px; background: #f9fafb; border: 1px solid #e5e7eb; padding: 6px; border-radius: 4px; margin-bottom: 20px;">
            <strong>Catatan Petugas:</strong> {{ $goodsReceipt->notes }}
        </div>
    @endif

    <table class="signature-table">
        <tr>
            <td>
                <div>Pengirim / Kurir Supplier,</div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; margin-top: 4px;">( ..................................... )</div>
            </td>
            <td>
                <div>Petugas Gudang Penerima,</div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; margin-top: 4px;">{{ $goodsReceipt->receiver->name ?? 'Admin Gudang' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
