<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Stok Opname {{ $stockOpname->opname_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.3;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #059669;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-title {
            font-size: 15px;
            font-weight: bold;
            color: #059669;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            color: #1f2937;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .meta-table td {
            vertical-align: top;
            font-size: 10px;
        }
        .box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            border-radius: 4px;
        }
        .summary-boxes {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-boxes td {
            border: 1px solid #e5e7eb;
            background-color: #f8fafc;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .items-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: left;
        }
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 4px;
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
        .text-rose {
            color: #be123c;
        }
        .text-blue {
            color: #1d4ed8;
        }
        .text-emerald {
            color: #047857;
        }
        .signatures {
            width: 100%;
            margin-top: 25px;
        }
        .signature-col {
            text-align: center;
            width: 33.33%;
            vertical-align: top;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 75%;
            margin: 45px auto 4px auto;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header">
        <tr>
            <td style="width: 50%;">
                <div class="company-title">APOTEK MEDICAL CARE</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">
                    Sistem Manajemen Inventori & Farmasi Terpadu<br>
                    Jl. Kesehatan No. 123, Indonesia
                </div>
            </td>
            <td style="width: 50%;">
                <div class="doc-title">BERITA ACARA STOK OPNAME</div>
                <div style="text-align: right; font-size: 10px; color: #64748b; margin-top: 2px;">
                    No: <strong>{{ $stockOpname->opname_number }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Meta Information -->
    <table class="meta-table">
        <tr>
            <td style="width: 50%; padding-right: 8px;">
                <div class="box">
                    <strong>Gudang:</strong> {{ $stockOpname->warehouse?->name ?? '-' }} ({{ $stockOpname->warehouse?->code ?? '-' }})<br>
                    <strong>Cakupan:</strong> {{ ucfirst($stockOpname->scope) }}<br>
                    <strong>Freeze Stok:</strong> {{ $stockOpname->freeze_stock ? 'Ya (Dibekukan)' : 'Tidak' }}
                </div>
            </td>
            <td style="width: 50%; padding-left: 8px;">
                <div class="box">
                    <strong>Tanggal Opname:</strong> {{ $stockOpname->opname_date?->format('d/m/Y') }}<br>
                    <strong>Status:</strong> {{ strtoupper($stockOpname->status) }}<br>
                    <strong>Petugas Inisiator:</strong> {{ $stockOpname->initiator?->name ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Summary Box -->
    <table class="summary-boxes">
        <tr>
            <td>Total Item: <strong>{{ number_format($stockOpname->total_items) }}</strong></td>
            <td>Item Sesuai: <strong class="text-emerald">{{ number_format($stockOpname->matched_items) }}</strong></td>
            <td>Ada Selisih: <strong class="text-rose">{{ number_format($stockOpname->discrepancy_items) }}</strong></td>
            <td>Waktu Selesai: <strong>{{ $stockOpname->completed_at?->format('d/m/Y H:i') ?? '-' }}</strong></td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th>Nama Obat / Produk</th>
                <th style="width: 75px;">No. Batch</th>
                <th style="width: 70px;">Lokasi Rak</th>
                <th class="text-right" style="width: 50px;">Qty Sistem</th>
                <th class="text-right" style="width: 50px;">Qty Fisik</th>
                <th class="text-center" style="width: 50px;">Selisih</th>
                <th>Alasan Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockOpname->items as $index => $item)
                @php
                    $diff = (float) $item->discrepancy;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name }}</strong>
                        <div style="color: #64748b; font-size: 8px;">{{ $item->product?->product_code }}</div>
                    </td>
                    <td>{{ $item->batch?->batch_number ?? '-' }}</td>
                    <td>{{ $item->storageLocation?->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format((float) $item->system_qty, 2) }}</td>
                    <td class="text-right text-bold">{{ number_format((float) $item->physical_qty, 2) }}</td>
                    <td class="text-center">
                        @if($diff > 0)
                            <span class="text-blue text-bold">+{{ number_format($diff, 2) }}</span>
                        @elseif($diff < 0)
                            <span class="text-rose text-bold">{{ number_format($diff, 2) }}</span>
                        @else
                            <span class="text-emerald">0.00</span>
                        @endif
                    </td>
                    <td>{{ $item->discrepancy_reason ?: ($diff != 0 ? '-' : 'Sesuai') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($stockOpname->notes)
        <div style="font-size: 9.5px; margin-bottom: 12px;">
            <strong>Catatan Tambahan:</strong><br>
            {{ $stockOpname->notes }}
        </div>
    @endif

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td class="signature-col">
                Petugas Penghitung,<br>
                <div class="signature-line"></div>
                <strong>{{ $stockOpname->initiator?->name ?? '( ................................. )' }}</strong>
            </td>
            <td class="signature-col">
                Pemeriksa / Reviewer,<br>
                <div class="signature-line"></div>
                <strong>( ................................. )</strong>
            </td>
            <td class="signature-col">
                Disetujui Oleh,<br>
                <div class="signature-line"></div>
                <strong>{{ $stockOpname->approver?->name ?? 'Manajer / Pimpinan' }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
