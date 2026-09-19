<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
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
        .totals-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .totals-table td {
            padding: 4px 6px;
            font-size: 11px;
        }
        .totals-table .grand-total {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #111;
            color: #059669;
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
                    <div class="doc-title">PURCHASE ORDER</div>
                    <div style="font-size: 12px; font-weight: bold; color: #374151;">No: {{ $purchaseOrder->po_number }}</div>
                    <div style="font-size: 10px; color: #6b7280;">Tanggal: {{ $purchaseOrder->order_date->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%; padding-right: 10px;">
                <div class="box">
                    <div style="font-weight: bold; margin-bottom: 4px; color: #059669;">KEPADA SUPPLIER:</div>
                    <div style="font-size: 12px; font-weight: bold;">{{ $purchaseOrder->supplier->name }}</div>
                    <div>Kontak: {{ $purchaseOrder->supplier->contact_person ?? '-' }}</div>
                    <div>Telp: {{ $purchaseOrder->supplier->phone ?? '-' }}</div>
                    <div>Alamat: {{ $purchaseOrder->supplier->address ?? '-' }} ({{ $purchaseOrder->supplier->city ?? '-' }})</div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 10px;">
                <div class="box">
                    <div style="font-weight: bold; margin-bottom: 4px; color: #059669;">GUDANG PENERIMAAN:</div>
                    <div style="font-size: 12px; font-weight: bold;">{{ $purchaseOrder->warehouse->name }}</div>
                    <div>Alamat: {{ $purchaseOrder->warehouse->address ?? '-' }}</div>
                    <div>Perkiraan Tiba: {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d/m/Y') : '-' }}</div>
                    <div>Status: <strong>{{ strtoupper(str_replace('_', ' ', $purchaseOrder->status)) }}</strong></div>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 45%;">Deskripsi Produk / Obat</th>
                <th style="width: 12%;">Satuan</th>
                <th style="width: 10%;" class="text-right">Qty</th>
                <th style="width: 14%;" class="text-right">Harga Beli</th>
                <th style="width: 14%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong>
                        <div style="font-size: 9px; color: #6b7280;">Kode: {{ $item->product->product_code ?? '-' }}</div>
                    </td>
                    <td>{{ $item->unit->name ?? '-' }}</td>
                    <td class="text-right">{{ (float) $item->quantity_ordered }}</td>
                    <td class="text-right">{{ format_rupiah($item->unit_price) }}</td>
                    <td class="text-right">{{ format_rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">{{ format_rupiah($purchaseOrder->subtotal) }}</td>
        </tr>
        @if($purchaseOrder->discount_amount > 0)
            <tr>
                <td>Diskon Faktur:</td>
                <td class="text-right">-{{ format_rupiah($purchaseOrder->discount_amount) }}</td>
            </tr>
        @endif
        @if($purchaseOrder->tax_amount > 0)
            <tr>
                <td>PPN:</td>
                <td class="text-right">+{{ format_rupiah($purchaseOrder->tax_amount) }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <td>Grand Total:</td>
            <td class="text-right">{{ format_rupiah($purchaseOrder->grand_total) }}</td>
        </tr>
    </table>

    @if($purchaseOrder->notes)
        <div style="font-size: 10px; background: #f9fafb; border: 1px solid #e5e7eb; padding: 6px; border-radius: 4px; margin-bottom: 20px;">
            <strong>Catatan:</strong> {{ $purchaseOrder->notes }}
        </div>
    @endif

    <table class="signature-table">
        <tr>
            <td>
                <div>Pemesan / Bagian Pengadaan,</div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; margin-top: 4px;">{{ $purchaseOrder->creator->name ?? 'Staff Purchasing' }}</div>
            </td>
            <td>
                <div>Menyetujui (Pimpinan / Apoteker),</div>
                <div class="sign-line"></div>
                <div style="font-weight: bold; margin-top: 4px;">{{ $purchaseOrder->approver->name ?? 'Pimpinan Apotek' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
