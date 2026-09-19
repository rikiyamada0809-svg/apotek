<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Penarikan Obat - {{ $batchRecall->recall_number }}</title>
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
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 10.5px;
        }
        .meta-table td.label {
            width: 22%;
            font-weight: bold;
        }
        .meta-table td.colon {
            width: 2%;
        }
        .meta-table td.value {
            width: 26%;
        }
        .summary-box {
            border: 1px solid #777;
            background-color: #f9f9f9;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-box td {
            font-size: 10px;
            padding: 3px 5px;
        }
        .summary-box td.label {
            width: 40%;
            font-weight: bold;
        }
        .signatures {
            width: 100%;
            margin-top: 35px;
            page-break-inside: avoid;
        }
        .signatures td {
            text-align: center;
            vertical-align: top;
            font-size: 10px;
            width: 33.33%;
        }
        .sign-box {
            height: 55px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>APOTEK SEHAT SENTOSA</h1>
        <p>Sistem Pengelolaan Farmasi & Standar Pelayanan Kefarmasian</p>
        <p>Jl. Kesehatan Raya No. 123, Jakarta Selatan | Telp: (021) 555-0123</p>
    </div>

    <div class="title">
        <h2>BERITA ACARA PENARIKAN OBAT (BATCH RECALL)</h2>
        <span>Nomor: {{ $batchRecall->recall_number }}</span>
    </div>

    <p style="font-size: 10.5px; line-height: 1.5; margin-bottom: 15px;">
        Pada hari ini, tanggal <strong>{{ $batchRecall->recall_date?->format('d F Y') }}</strong>, telah dilaksanakan tindakan karantina dan penarikan obat (recall) dari peredaran di Apotek Sehat Sentosa dengan rincian data sebagai berikut:
    </p>

    <table class="meta-table">
        <tr>
            <td class="label">Nama Produk Obat</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $batchRecall->batch?->product?->name }}</strong></td>
            <td class="label">Sumber Instruksi</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $batchRecall->recall_source }}</strong></td>
        </tr>
        <tr>
            <td class="label">Kode Produk</td>
            <td class="colon">:</td>
            <td class="value">{{ $batchRecall->batch?->product?->product_code }}</td>
            <td class="label">Tanggal Recall</td>
            <td class="colon">:</td>
            <td class="value">{{ $batchRecall->recall_date?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Batch</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $batchRecall->batch?->batch_number }}</strong></td>
            <td class="label">Status Saat Ini</td>
            <td class="colon">:</td>
            <td class="value">{{ strtoupper($batchRecall->status) }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Kadaluarsa</td>
            <td class="colon">:</td>
            <td class="value">{{ $batchRecall->batch?->expiry_date?->format('d/m/Y') }}</td>
            <td class="label">Petugas Pencatat</td>
            <td class="colon">:</td>
            <td class="value">{{ $batchRecall->creator?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Distributor / Supplier</td>
            <td class="colon">:</td>
            <td class="value" colspan="4">{{ $batchRecall->batch?->supplier?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Alasan Penarikan</td>
            <td class="colon">:</td>
            <td class="value" colspan="4" style="color: #a00; font-weight: bold;">
                {{ $batchRecall->recall_reason }}
            </td>
        </tr>
    </table>

    <div class="summary-box">
        <h4 style="margin: 0 0 6px 0; font-size: 11px; text-transform: uppercase;">Rekapitulasi Kuantitas & Tindakan Fisik</h4>
        <table>
            <tr>
                <td class="label">1. Total Obat Terdampak:</td>
                <td>{{ number_format($batchRecall->affected_qty, 0) }} Unit</td>
            </tr>
            <tr>
                <td class="label">2. Stok Fisik Dikarantina di Gudang/Outlet:</td>
                <td><strong>{{ number_format($currentStock, 0) }} Unit</strong> (Status: Dibekukan dari Penjualan)</td>
            </tr>
            <tr>
                <td class="label">3. Jumlah Obat yang Telah Terjual ke Pasien:</td>
                <td>{{ number_format($batchRecall->sold_qty, 0) }} Unit</td>
            </tr>
            <tr>
                <td class="label">4. Jumlah Obat yang Berhasil Ditarik Kembali:</td>
                <td><strong>{{ number_format($batchRecall->recovered_qty, 0) }} Unit</strong> ({{ $batchRecall->recovery_percentage }}% Recovery Rate)</td>
            </tr>
            <tr>
                <td class="label">5. Tindakan Lanjutan / Penanganan Fisik:</td>
                <td>{{ $batchRecall->action_taken ?? 'Stok dikarantina dan disiapkan untuk diserahkan ke distributor' }}</td>
            </tr>
            @if($batchRecall->notes)
            <tr>
                <td class="label">6. Catatan Khusus:</td>
                <td>{{ $batchRecall->notes }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="font-size: 10px; line-height: 1.4; color: #555;">
        Demikian Berita Acara Penarikan Obat ini dibuat dengan sebenarnya sesuai dengan standar pengelolaan farmasi dan instruksi pengawasan obat yang berlaku.
    </p>

    <table class="signatures">
        <tr>
            <td>
                <div>Petugas Pengelola Obat / Gudang,</div>
                <div class="sign-box"></div>
                <div>( ..................................... )</div>
            </td>
            <td>
                <div>Perwakilan Distributor / Produsen,</div>
                <div class="sign-box"></div>
                <div>( ..................................... )</div>
            </td>
            <td>
                <div>Apoteker Penanggung Jawab (APA),</div>
                <div class="sign-box"></div>
                <div>( ..................................... )</div>
                <div style="font-size: 9px; color: #666;">SIPA: .....................................</div>
            </td>
        </tr>
    </table>
</body>
</html>
