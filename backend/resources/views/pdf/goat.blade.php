<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Kambing - {{ $goat->qr_code }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2d241e;
            line-height: 1.5;
            background-color: #fdfaf5;
            font-size: 11px;
        }
        .border-container {
            border: 2px solid #4a6741;
            padding: 20px;
            border-radius: 15px;
            background-color: #ffffff;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px double #4a6741;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #4a6741;
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 9px;
            color: #a67c52;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .section-title {
            color: #4a6741;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
            border-bottom: 1px solid #a67c52/20;
            padding-bottom: 4px;
            margin-top: 15px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .label {
            color: #7a6e65;
            width: 35%;
            font-weight: 500;
        }
        .value {
            font-weight: bold;
            color: #2d241e;
        }
        .qr-section {
            float: right;
            text-align: center;
            margin-left: 20px;
            border: 1px solid #a67c52/10;
            padding: 8px;
            background-color: #fdfaf5;
            border-radius: 8px;
        }
        .qr-section img {
            width: 80px;
            height: 80px;
        }
        .qr-section div {
            font-size: 8px;
            font-weight: bold;
            color: #4a6741;
            margin-top: 4px;
        }
        .pedigree-box {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .pedigree-box td {
            border: 1px solid #a67c52/20;
            padding: 8px;
            text-align: center;
            background-color: #fdfaf5;
            font-size: 10px;
            width: 33.33%;
        }
        .pedigree-title {
            font-size: 8px;
            color: #7a6e65;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .pedigree-name {
            font-weight: bold;
            color: #4a6741;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .data-table th {
            background-color: #4a6741;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 5px 8px;
            text-align: left;
        }
        .data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f0eae1;
        }
        .data-table tr:nth-child(even) td {
            background-color: #fdfaf5;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            background-color: #4a6741/10;
            color: #4a6741;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 8px;
            color: #7a6e65;
            border-top: 1px solid #f0eae1;
            padding-top: 10px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <div class="border-container">
        
        <!-- Header -->
        <div class="header">
            <h1>Sertifikat Identitas & Silsilah Ternak</h1>
            <p>Qandang Smart Farming System</p>
        </div>

        <!-- QR Code Right Column -->
        <div class="qr-section">
            <img src="data:image/svg+xml;base64, {{ $qrCode }}" alt="QR Code">
            <div>{{ $goat->qr_code }}</div>
        </div>

        <!-- Identity Details -->
        <div style="width: 70%; float: left;">
            <div class="section-title">Identitas Kambing</div>
            <table class="grid-table">
                <tr>
                    <td class="label">Nama Ternak</td>
                    <td class="value">: {{ $goat->name }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Ras / Breed</td>
                    <td class="value">: {{ $goat->breed }}</td>
                </tr>
                <tr>
                    <td class="label">Jenis Kelamin</td>
                    <td class="value">: {{ $goat->gender == 'male' ? 'Pejantan (Male)' : 'Betina (Female)' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Lahir</td>
                    <td class="value">: {{ $goat->birth_date ? \Carbon\Carbon::parse($goat->birth_date)->translatedFormat('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tujuan Ternak</td>
                    <td class="value">: <span class="badge">{{ $goat->purpose == 'milk' ? 'Pemerahan Susu' : ($goat->purpose == 'meat' ? 'Pedaging' : 'Breeding') }}</span></td>
                </tr>
                <tr>
                    <td class="label">Berat Saat Ini</td>
                    <td class="value">: {{ $goat->current_weight ? $goat->current_weight . ' kg' : '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="clear"></div>

        <!-- Pedigree / Lineage -->
        <div class="section-title">Garis Silsilah (Pedigree Lineage)</div>
        <table class="pedigree-box">
            <tr>
                <td>
                    <div class="pedigree-title">Anak (Subject)</div>
                    <div class="pedigree-name">{{ $goat->name }}</div>
                    <div style="font-size: 8px;">{{ $goat->qr_code }}</div>
                </td>
                <td>
                    <div class="pedigree-title">Bapak (Sire)</div>
                    @if($goat->sire)
                        <div class="pedigree-name">{{ $goat->sire->name }}</div>
                        <div style="font-size: 8px;">{{ $goat->sire->qr_code }}</div>
                    @else
                        <div style="color: #7a6e65; font-style: italic;">Tidak Terdata</div>
                    @endif
                </td>
                <td>
                    <div class="pedigree-title">Ibu (Dam)</div>
                    @if($goat->dam)
                        <div class="pedigree-name">{{ $goat->dam->name }}</div>
                        <div style="font-size: 8px;">{{ $goat->dam->qr_code }}</div>
                    @else
                        <div style="color: #7a6e65; font-style: italic;">Tidak Terdata</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Health Records -->
        <div class="section-title">Rekam Medis & Kesehatan</div>
        @if($goat->healthRecords && $goat->healthRecords->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kondisi / Keluhan</th>
                        <th>Tindakan / Pengobatan</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($goat->healthRecords as $record)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($record->date)->translatedFormat('d M Y') }}</td>
                            <td>{{ $record->condition }}</td>
                            <td>{{ $record->treatment }}</td>
                            <td>{{ $record->veterinarian ?: 'Petugas Kandang' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 10px; background-color: #fdfaf5; text-align: center; color: #7a6e65; border: 1px dashed #a67c52/20; border-radius: 8px;">
                Belum ada rekam medis terdaftar untuk kambing ini.
            </div>
        @endif

        <!-- Weight Logs -->
        <div class="section-title">Histori Penimbangan Berat Badan</div>
        @if($goat->weightLogs && $goat->weightLogs->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal Penimbangan</th>
                        <th>Berat (kg)</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($goat->weightLogs->take(5) as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->date)->translatedFormat('d M Y') }}</td>
                            <td>{{ $log->weight }} kg</td>
                            <td>{{ $log->notes ?: 'Timbangan Berkala' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 10px; background-color: #fdfaf5; text-align: center; color: #7a6e65; border: 1px dashed #a67c52/20; border-radius: 8px;">
                Belum ada histori berat badan tercatat.
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Sertifikat digital diterbitkan secara otomatis oleh Qandang Smart Farming System.<br>
            Dokumen ini sah dan terhubung langsung dengan basis data digital. Kode QR dapat dipindai untuk validasi data terkini.
        </div>

    </div>

</body>
</html>
