<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Etiket Obat Racikan - {{ $compound->compound_number }}</title>
    <style>
        @page {
            size: 80mm 60mm;
            margin: 2mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 8px;
        }
        .etiket-box {
            border: 2px solid #0056b3;
            border-radius: 6px;
            padding: 8px;
            max-width: 320px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 1.5px solid #0056b3;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .header h3 {
            margin: 0;
            font-size: 13px;
            color: #0056b3;
            text-transform: uppercase;
        }
        .header p {
            margin: 1px 0;
            font-size: 9px;
            color: #333;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 4px;
        }
        .patient-section {
            border-bottom: 1px dashed #999;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .patient-name {
            font-size: 13px;
            font-weight: bold;
        }
        .medicine-section {
            text-align: center;
            padding: 6px 0;
            border-bottom: 1px dashed #999;
            margin-bottom: 6px;
        }
        .medicine-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .medicine-qty {
            font-size: 11px;
            color: #444;
            margin-top: 2px;
        }
        .instructions {
            font-size: 13px;
            font-weight: bold;
            color: #0056b3;
            text-align: center;
            margin: 6px 0;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #555;
            margin-top: 4px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .etiket-box { border-width: 1px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; background: #0056b3; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
            🖨️ Cetak Etiket
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; font-weight: bold; background: #6c757d; color: #fff; border: none; border-radius: 6px; cursor: pointer; margin-left: 8px;">
            Tutup
        </button>
    </div>

    <div class="etiket-box">
        <div class="header">
            <h3>APOTEK SEHAT SENTOSA</h3>
            <p>SIA: 445/123/SIA-DINKES/2024 &bull; Telp: (021) 555-7890</p>
            <p>Apoteker: {{ $compound->preparer?->name ?? 'Apoteker Pengelola' }}</p>
            <div style="font-weight: bold; font-size: 11px; margin-top: 3px; color: #0056b3; letter-spacing: 0.5px;">ETIKET OBAT RACIKAN</div>
        </div>

        <div class="meta-row">
            <span>No: <strong>{{ $compound->compound_number }}</strong></span>
            <span>Tgl: {{ now()->format('d/m/Y') }}</span>
        </div>

        <div class="patient-section">
            <div class="meta-row">
                <span>Pasien: <span class="patient-name">{{ $compound->prescription?->customer?->name ?? 'Pasien Umum' }}</span></span>
            </div>
            @if($compound->prescription?->doctor)
                <div class="meta-row" style="margin-bottom: 0;">
                    <span>Dokter: {{ $compound->prescription->doctor->name }}</span>
                </div>
            @endif
        </div>

        <div class="medicine-section">
            <div class="medicine-name">{{ $compound->name }}</div>
            <div class="medicine-qty">Jumlah: <strong>{{ $compound->total_packages }} Kemasan / Bungkus</strong></div>
        </div>

        <div class="instructions">
            SEHARI 3 X 1 BUNGKUS<br>
            <span style="font-size: 11px; font-weight: normal; color: #000;">SESUDAH MAKAN</span>
        </div>

        <div class="footer">
            <span>KOMPOSISI: {{ $compound->items->pluck('product.name')->join(', ') }}</span>
        </div>
        <div class="footer" style="border-top: 1px dotted #ccc; padding-top: 2px;">
            <span>Simpan di tempat kering & terlindung dari sinar matahari langsung</span>
        </div>
    </div>
</body>
</html>
