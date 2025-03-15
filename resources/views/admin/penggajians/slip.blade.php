<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji Karyawan - {{ $periode->nama_periode ?? 'Periode Gaji' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 9pt;
            line-height: 1.3;
            background-color: #fff;
        }

        .page-container {
            width: 100%;
            box-sizing: border-box;
        }

        .slip-wrapper {
            margin-bottom: 8mm;
            position: relative;
            page-break-inside: avoid;
        }

        .slip-content {
            padding: 8mm;
            box-sizing: border-box;
            background-color: #fff;
            min-height: 82mm;
        }

        .slip-footer {
            position: relative;
            padding: 4mm 0;
            text-align: center;
            border-bottom: 1px dashed #999;
        }

        .cut-text {
            position: absolute;
            right: 8mm;
            top: 0;
            font-size: 9pt;
            color: #999;
            background-color: #fff;
            padding: 0 2mm;
        }

        .slip-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5mm;
        }

        .company-logo {
            height: 12mm;
            max-width: 30mm;
        }

        .company-info {
            text-align: center;
            flex-grow: 1;
        }

        .company-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .company-address {
            font-size: 8pt;
        }

        .slip-title {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            margin-top: 2mm;
        }

        .employee-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6mm;
        }

        .info-column {
            width: 48%;
        }

        .info-row {
            display: flex;
            margin-bottom: 1mm;
        }

        .info-label {
            width: 30mm;
            font-weight: bold;
        }

        .info-value {
            flex: 1;
        }

        .salary-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4mm;
            font-size: 8pt;
        }

        .salary-details th {
            padding: 2mm 1.5mm;
            text-align: center;
            font-size: 8pt;
            border-bottom: 1px solid #ddd;
            background-color: #f8f8f8;
        }

        .salary-details td {
            padding: 2mm 1.5mm;
            text-align: left;
            border-bottom: 1px dotted #eee;
        }

        .salary-details tr:last-child td {
            border-bottom: none;
        }

        .amount {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            padding-top: 2mm;
            border-top: 1px solid #ddd;
            border-bottom: none;
        }

        .net-salary {
            margin-top: 3mm;
            padding: 2.5mm;
            background-color: #f8f8f8;
            text-align: right;
            font-weight: bold;
            font-size: 10pt;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 6mm;
            font-size: 8pt;
        }

        .signature-box {
            width: 25mm;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 10mm;
            margin-bottom: 1mm;
        }

        .watermark {
            text-align: center;
            color: #999;
            font-style: italic;
            font-size: 7pt;
            margin-top: 2mm;
        }

        .page-break {
            page-break-after: always;
            height: 0;
            margin: 0;
            padding: 0;
        }

        .slip-number {
            font-size: 8pt;
            color: #999;
            text-align: right;
            position: absolute;
            right: 8mm;
            bottom: 3mm;
        }

    </style>
</head>
<body>
    <div class="page-container">
        @php $slipCount = 0; @endphp
        @foreach($penggajians as $index => $penggajian)
        @php
        $slipCount++;

        // Prepare potongan and tunjangan
        $detailTunjangan = is_array($penggajian->detail_tunjangan) ? $penggajian->detail_tunjangan : [];
        $detailPotongan = is_array($penggajian->detail_potongan) ? $penggajian->detail_potongan : [];
        $detailDepartemen = is_array($penggajian->detail_departemen) ? $penggajian->detail_departemen : [];

        // Get tunjangan from jabatan and profesi if available
        $tunjanganJabatan = isset($penggajian->karyawan->jabatan) ? $penggajian->karyawan->jabatan->tunjangan_jabatan : 0;
        $tunjanganProfesi = isset($penggajian->karyawan->profesi) ? $penggajian->karyawan->profesi->tunjangan_profesi : 0;

        // Potongan wajib (based on standard rates)
        $bpjsKesehatan = $penggajian->gaji_pokok * 0.01; // Assume 1%
        $bpjsTK = $penggajian->gaji_pokok * 0.02; // Assume 2%
        $pph21 = $penggajian->gaji_pokok * 0.05; // Example rate

        // Standard potongan
        $standarPotongan = [
        'BPJS Kesehatan (1%)' => $bpjsKesehatan,
        'BPJS TK (2%)' => $bpjsTK,
        'PPh 21' => $pph21
        ];

        // Standard pendapatan
        $standarPendapatan = [
        'Gaji Pokok' => $penggajian->gaji_pokok
        ];

        if ($tunjanganJabatan > 0) {
        $standarPendapatan['Tunjangan Jabatan'] = $tunjanganJabatan;
        }

        if ($tunjanganProfesi > 0) {
        $standarPendapatan['Tunjangan Profesi'] = $tunjanganProfesi;
        }

        // Convert detail tunjangan to associative array if it's array of objects
        $detailTunjanganAssoc = [];
        if (!empty($detailTunjangan)) {
        foreach ($detailTunjangan as $item) {
        if (is_array($item) && isset($item['nama']) && isset($item['nominal'])) {
        $detailTunjanganAssoc[$item['nama']] = $item['nominal'];
        }
        }
        }

        // Convert detail potongan to associative array if it's array of objects
        $detailPotonganAssoc = [];
        if (!empty($detailPotongan)) {
        foreach ($detailPotongan as $item) {
        if (is_array($item) && isset($item['nama']) && isset($item['nominal'])) {
        $detailPotonganAssoc[$item['nama']] = $item['nominal'];
        }
        }
        }

        // Merge with detail tunjangan
        $pendapatanItems = array_merge($standarPendapatan, $detailTunjanganAssoc);

        // Merge with detail potongan
        $potonganItems = array_merge($standarPotongan, $detailPotonganAssoc);

        // Maximum rows for display
        $maxRows = max(count($pendapatanItems), count($potonganItems));
        $pendapatanKeys = array_keys($pendapatanItems);
        $potonganKeys = array_keys($potonganItems);
        @endphp

        <div class="slip-wrapper">
            <div class="slip-content">
                <div class="slip-header">
                    @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Perusahaan" class="company-logo">
                    @else
                    <div style="width:30mm;height:12mm;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;background:#f9f9f9;font-size:8pt;color:#777;">LOGO</div>
                    @endif
                    <div class="company-info">
                        <div class="company-name">{{ config('app.company_name', 'PT. MAJU BERSAMA INDONESIA') }}</div>
                        <div class="company-address">{{ config('app.company_address', 'Jl. Jendral Sudirman No. 123, Jakarta Selatan, 12190') }}</div>
                        <div class="slip-title">SLIP GAJI KARYAWAN</div>
                    </div>
                </div>

                <div class="employee-info">
                    <div class="info-column">
                        <div class="info-row">
                            <div class="info-label">Nama</div>
                            <div class="info-value">: {{ $penggajian->karyawan->nama_karyawan ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">NIK</div>
                            <div class="info-value">: {{ $penggajian->karyawan->nik ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Departemen</div>
                            <div class="info-value">: {{ $penggajian->karyawan->departemen->name_departemen ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Jabatan</div>
                            <div class="info-value">: {{ $penggajian->karyawan->jabatan->name_jabatan ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="info-column">
                        <div class="info-row">
                            <div class="info-label">Periode</div>
                            <div class="info-value">: {{ isset($penggajian->periode_awal) ? \Carbon\Carbon::parse($penggajian->periode_awal)->format('d/m/Y') : '-' }} - {{ isset($penggajian->periode_akhir) ? \Carbon\Carbon::parse($penggajian->periode_akhir)->format('d/m/Y') : '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Nama Periode</div>
                            <div class="info-value">: {{ $penggajian->periodeGaji->nama_periode ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Status</div>
                            <div class="info-value">: {{ $penggajian->karyawan->statuskaryawan ?? '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Tgl. Cetak</div>
                            <div class="info-value">: {{ now()->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>

                <table class="salary-details">
                    <tr>
                        <th width="24%">KETERANGAN</th>
                        <th width="24%">JUMLAH</th>
                        <th width="24%">POTONGAN</th>
                        <th width="24%">JUMLAH</th>
                    </tr>

                    @for($i = 0; $i < $maxRows; $i++) <tr>
                        @if(isset($pendapatanKeys[$i]))
                        <td>{{ $pendapatanKeys[$i] }}</td>
                        <td class="amount">Rp {{ number_format($pendapatanItems[$pendapatanKeys[$i]], 0, ',', '.') }}</td>
                        @else
                        <td></td>
                        <td></td>
                        @endif

                        @if(isset($potonganKeys[$i]))
                        <td>{{ $potonganKeys[$i] }}</td>
                        <td class="amount">Rp {{ number_format($potonganItems[$potonganKeys[$i]], 0, ',', '.') }}</td>
                        @else
                        <td></td>
                        <td></td>
                        @endif
                        </tr>
                        @endfor

                        <!-- Total -->
                        <tr class="total-row">
                            <td>Total Pendapatan</td>
                            <td class="amount">Rp {{ number_format($penggajian->gaji_pokok + $penggajian->tunjangan, 0, ',', '.') }}</td>
                            <td>Total Potongan</td>
                            <td class="amount">Rp {{ number_format($penggajian->potongan, 0, ',', '.') }}</td>
                        </tr>
                </table>

                <div class="net-salary">
                    GAJI BERSIH: Rp {{ number_format($penggajian->gaji_bersih, 0, ',', '.') }}
                </div>

                <div class="signature-section">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div>HRD</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div>KEUANGAN</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div>KARYAWAN</div>
                    </div>
                </div>

                <div class="slip-number">{{ $index + 1 }}/{{ count($penggajians) }}</div>
            </div>

            @if($index < count($penggajians) - 1) <!-- Cut line with text properly structured in the slip wrapper -->
                <div class="slip-footer">
                    <div class="cut-text">✂ GUNTING DISINI</div>
                </div>
                @endif
        </div>

        @if($slipCount % 3 == 0 && $index < count($penggajians) - 1) <!-- Add page break after every 3 slips -->
            <div class="page-break"></div>
            @php $slipCount = 0; @endphp
            @endif

            @endforeach
    </div>
</body>
</html>
