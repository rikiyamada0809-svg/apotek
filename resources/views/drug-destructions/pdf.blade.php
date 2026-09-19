<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pemusnahan Obat - {{ $drugDestruction->destruction_number }}</title>
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
            font-size: 11px;
        }
        .meta-table td.label {
            width: 25%;
            font-weight: bold;
        }
        .meta-table td.colon {
            width: 2%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th, .items-table td {
            border: 1px solid #777;
            padding: 5px 6px;
            font-size: 10px;
        }
        .items-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .signatures {
            width: 100%;
            margin-top: 30px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
        }
        .signature-space {
            height: 60px;
        }
        .closing-text {
            margin-bottom: 20px;
            text-align: justify;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>APOTEK SEHAT SENTOSA</h1>
        <p>SIA: 445/123/SIA-DINKES/2024 &bull; SIPA: 19850101/SIPA-31.71/2024/2001</p>
        <p>Jl. Kesehatan Raya No. 45, Jakarta | Telp: (021) 555-7890 | Email: apotek@sehatsentosa.com</p>
    </div>

    <div class="title">
        <h2>BERITA ACARA PEMUSNAHAN OBAT</h2>
        <span>Nomor: {{ $drugDestruction->destruction_number }}</span>
    </div>

    <p class="closing-text">
        Pada hari ini, tanggal <strong>{{ \Carbon\Carbon::parse($drugDestruction->destruction_date)->translatedFormat('d F Y') }}</strong>, bertempat di Apotek Sehat Sentosa, kami yang bertanda tangan di bawah ini telah melaksanakan pemusnahan perbekalan farmasi yang telah rusak, kadaluarsa (expired), atau tidak memenuhi syarat penggunaan sesuai ketentuan peraturan perundang-undangan yang berlaku:
    </p>

    <table class="meta-table">
        <tr>
            <td class="label">Metode Pemusnahan</td>
            <td class="colon">:</td>
            <td>{{ $drugDestruction->method }}</td>
        </tr>
        <tr>
            <td class="label">Nama Saksi</td>
            <td class="colon">:</td>
            <td>{{ $drugDestruction->witness_name }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan / Instansi Saksi</td>
            <td class="colon">:</td>
            <td>{{ $drugDestruction->witness_role }}</td>
        </tr>
        @if($drugDestruction->notes)
        <tr>
            <td class="label">Catatan Tambahan</td>
            <td class="colon">:</td>
            <td>{{ $drugDestruction->notes }}</td>
        </tr>
        @endif
    </table>

    <p><strong>Daftar perbekalan farmasi yang dimusnahkan adalah sebagai berikut:</strong></p>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 32%;">Nama Obat / Produk</th>
                <th style="width: 14%;">No. Batch</th>
                <th style="width: 12%;">Tgl Expired</th>
                <th style="width: 10%;">Jumlah</th>
                <th style="width: 13%;">Harga Beli</th>
                <th style="width: 15%;">Total Kerugian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($drugDestruction->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product?->name ?? '-' }}</td>
                    <td class="text-center">{{ $item->batch?->batch_number ?? '-' }}</td>
                    <td class="text-center">{{ $item->batch?->expiry_date ? \Carbon\Carbon::parse($item->batch->expiry_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 0, ',', '.') }} {{ $item->product?->productUnits->first()?->unit?->name ?? 'Unit' }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->loss_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #fafafa;">
                <td colspan="6" class="text-right text-bold">TOTAL KERUGIAN:</td>
                <td class="text-right text-bold">Rp {{ number_format($drugDestruction->total_loss_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="closing-text">
        Demikian Berita Acara Pemusnahan Obat ini dibuat dengan sebenarnya dalam rangkap secukupnya untuk dipergunakan sebagaimana mestinya dan dilaporkan kepada instansi yang berwenang (Dinas Kesehatan / Balai Besar POM).
    </p>

    <table class="signatures">
        <tr>
            <td>
                Saksi Pemusnahan,<br>
                <strong>{{ $drugDestruction->witness_role }}</strong>
                <div class="signature-space"></div>
                <strong>({{ $drugDestruction->witness_name }})</strong>
            </td>
            <td>
                Penanggung Jawab / Apoteker Pengelola Apotek,
                <div class="signature-space"></div>
                <strong>({{ $drugDestruction->executor?->name ?? 'Apoteker Pengelola' }})</strong><br>
                <span>SIPA: 19850101/SIPA-31.71/2024/2001</span>
            </td>
        </tr>
    </table>
</body>
</html>
