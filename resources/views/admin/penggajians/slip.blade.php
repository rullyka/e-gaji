<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $periode->nama_periode ?? 'Periode Gaji' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 8pt;
            line-height: 1.3;
            background-color: #fff;
        }

        .page-container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .slip-container {
            width: 48%;
            margin-bottom: 10mm;
            border: 1px solid #ddd;
            box-sizing: border-box;
            page-break-inside: avoid;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .slip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3mm;
            border-bottom: 1px solid #ddd;
            background-color: #f9f9f9;
        }

        .company-logo {
            height: 10mm;
            max-width: 25mm;
        }

        .company-info {
            text-align: center;
            flex-grow: 1;
        }

        .company-name {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .company-address {
            font-size: 7pt;
        }

        .slip-title {
            font-size: 9pt;
            font-weight: bold;
            margin-top: 1mm;
        }

        .slip-number {
            font-size: 7pt;
            color: #666;
        }

        .slip-body {
            padding: 3mm;
        }

        .employee-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3mm;
            border-bottom: 1px solid #eee;
            padding-bottom: 2mm;
        }

        .info-left,
        .info-right {
            width: 48%;
        }

        .info-row {
            display: flex;
            margin-bottom: 1mm;
            font-size: 7pt;
        }

        .info-label {
            width: 40%;
            font-weight: bold;
        }

        .info-value {
            width: 60%;
        }

        .salary-details {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 3mm;
        }

        .salary-details th {
            padding: 1.5mm;
            text-align: left;
            background-color: #f0f0f0;
            border-bottom: 1px solid #ddd;
            font-size: 7pt;
            font-weight: bold;
        }

        .salary-details td {
            padding: 1mm 1.5mm;
            border-bottom: 1px dotted #eee;
        }

        .no-border {
            border-bottom: none !important;
        }

        .amount {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            padding-top: 1.5mm;
            border-top: 1px solid #ddd;
            border-bottom: none;
            background-color: #f8f8f8;
        }

        .net-salary {
            margin-top: 2mm;
            padding: 1.5mm;
            background-color: #f0f0f0;
            text-align: right;
            font-weight: bold;
            font-size: 8pt;
            border: 1px solid #ddd;
            border-left: 4px solid #28a745;
        }

        .attendance-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5mm;
            margin-top: 3mm;
            font-size: 7pt;
            padding: 2mm;
            border: 1px solid #eee;
            background-color: #f9f9f9;
            margin-bottom: 3mm;
        }

        .attendance-item {
            display: flex;
            justify-content: space-between;
        }

        .attendance-label {
            font-weight: normal;
        }

        .attendance-value {
            font-weight: bold;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 3mm;
        }

        .signature-box {
            width: 30%;
            text-align: center;
            font-size: 7pt;
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

        .cut-text {
            text-align: center;
            font-size: 8pt;
            color: #999;
            position: relative;
        }

        .cut-text:before,
        .cut-text:after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background-color: #ddd;
        }

        .cut-text:before {
            left: 0;
        }

        .cut-text:after {
            right: 0;
        }

        .slip-footer {
            border-top: 1px dashed #999;
            padding: 2mm 0;
            text-align: center;
            background-color: #f9f9f9;
        }

        .component-title {
            font-weight: bold;
            font-size: 8pt;
            margin-top: 2mm;
            margin-bottom: 1mm;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 1mm;
        }

        .detail-component {
            margin-bottom: 3mm;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dotted #f0f0f0;
            padding: 0.8mm 0;
        }

        .detail-label {
            font-size: 7pt;
        }

        .detail-value {
            font-size: 7pt;
            font-weight: bold;
            text-align: right;
        }

        /* Colored indicators */
        .indicator-present {
            color: #28a745;
        }

        .indicator-absent {
            color: #dc3545;
        }

        .indicator-leave {
            color: #ffc107;
        }

        .indicator-overtime {
            color: #17a2b8;
        }
    </style>
</head>

<body>
    <div class="page-container">
        @php $slipCount = 0; @endphp

        @foreach ($penggajians as $index => $penggajian)
            @php
                $slipCount++;

                // Prepare potongan and tunjangan
                $detailTunjangan = is_array($penggajian->detail_tunjangan) ? $penggajian->detail_tunjangan : [];
                $detailPotongan = is_array($penggajian->detail_potongan) ? $penggajian->detail_potongan : [];
                $detailDepartemen = is_array($penggajian->detail_departemen) ? $penggajian->detail_departemen : [];

                // Get standard pendapatan items
                $pendapatanItems = [
                    'Gaji Pokok' => $penggajian->gaji_pokok,
                ];

                // Get standard potongan items
                $potonganItems = [];

                // Add tunjangan items
                foreach ($detailTunjangan as $item) {
                    if (isset($item['nama']) && isset($item['nominal'])) {
                        $pendapatanItems[$item['nama']] = $item['nominal'];
                    }
                }

                // Add potongan items
                foreach ($detailPotongan as $item) {
                    if (isset($item['nama']) && isset($item['nominal'])) {
                        $potonganItems[$item['nama']] = $item['nominal'];
                    }
                }

                // Get keys for easier iteration
                $pendapatanKeys = array_keys($pendapatanItems);
                $potonganKeys = array_keys($potonganItems);

                // Maximum rows for display
                $maxRows = max(count($pendapatanItems), count($potonganItems));

                // Calculate totals for verification
                $totalPendapatan = array_sum($pendapatanItems);
                $totalPotongan = array_sum($potonganItems);
                $netSalary = $totalPendapatan - $totalPotongan;
            @endphp

            <div class="slip-container">
                <div class="slip-header">
                    @if (file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="Logo Perusahaan" class="company-logo">
                    @else
                        <div
                            style="width:25mm;height:10mm;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;background:#f9f9f9;font-size:7pt;color:#777;">
                            LOGO</div>
                    @endif
                    <div class="company-info">
                        <div class="company-name">{{ config('app.company_name', 'PT. MAJU BERSAMA INDONESIA') }}</div>
                        <div class="company-address">
                            {{ config('app.company_address', 'Jl. Jendral Sudirman No. 123, Jakarta Selatan, 12190') }}
                        </div>
                        <div class="slip-title">SLIP GAJI KARYAWAN</div>
                    </div>
                </div>

                <div class="slip-body">
                    <div class="employee-info">
                        <div class="info-left">
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
                                <div class="info-value">: {{ $detailDepartemen['departemen'] ?? '-' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Jabatan</div>
                                <div class="info-value">: {{ $detailDepartemen['jabatan'] ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="info-right">
                            <div class="info-row">
                                <div class="info-label">Bagian</div>
                                <div class="info-value">: {{ $detailDepartemen['bagian'] ?? '-' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Periode</div>
                                <div class="info-value">:
                                    {{ isset($penggajian->periode_awal) ? \Carbon\Carbon::parse($penggajian->periode_awal)->format('d/m/Y') : '-' }}
                                    -
                                    {{ isset($penggajian->periode_akhir) ? \Carbon\Carbon::parse($penggajian->periode_akhir)->format('d/m/Y') : '-' }}
                                </div>
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
                            <th width="32%">PENDAPATAN</th>
                            <th width="18%" class="amount">JUMLAH</th>
                            <th width="32%">POTONGAN</th>
                            <th width="18%" class="amount">JUMLAH</th>
                        </tr>

                        @for ($i = 0; $i < $maxRows; $i++)
                            <tr @if ($i == $maxRows - 1) class="no-border" @endif>
                                <td>{{ isset($pendapatanKeys[$i]) ? $pendapatanKeys[$i] : '' }}</td>
                                <td class="amount">
                                    {{ isset($pendapatanKeys[$i]) ? 'Rp ' . number_format($pendapatanItems[$pendapatanKeys[$i]], 0, ',', '.') : '' }}
                                </td>
                                <td>{{ isset($potonganKeys[$i]) ? $potonganKeys[$i] : '' }}</td>
                                <td class="amount">
                                    {{ isset($potonganKeys[$i]) ? 'Rp ' . number_format($potonganItems[$potonganKeys[$i]], 0, ',', '.') : '' }}
                                </td>
                            </tr>
                        @endfor

                        <!-- Total Row -->
                        <tr class="total-row">
                            <td>Total Pendapatan</td>
                            <td class="amount">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                            <td>Total Potongan</td>
                            <td class="amount">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</td>
                        </tr>
                    </table>

                    <div class="net-salary">
                        GAJI BERSIH: Rp {{ number_format($netSalary, 0, ',', '.') }}
                    </div>

                    <div class="component-title">REKAP KEHADIRAN</div>
                    <div class="attendance-summary">
                        <div class="attendance-item">
                            <span class="attendance-label">Total Hari:</span>
                            <span class="attendance-value">{{ $dataAbsensi['total_hari'] ?? 0 }} hari</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Hari Kerja:</span>
                            <span class="attendance-value">{{ $hariKerja }} hari</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Hadir:</span>
                            <span class="attendance-value indicator-present">{{ $hariHadir }} hari</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Izin:</span>
                            <span class="attendance-value indicator-leave">{{ $hariIzin }} hari</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Cuti:</span>
                            <span class="attendance-value indicator-leave">{{ $hariCuti }} hari</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Izin Cuti:</span>
                            <span class="attendance-value indicator-leave">{{ $izinCuti }} hari</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Tidak Hadir:</span>
                            <span class="attendance-value indicator-absent">{{ $hariTidakHadir }} hari</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Tingkat Kehadiran:</span>
                            <span class="attendance-value">{{ $kehadiranRate }}%</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Keterlambatan:</span>
                            <span class="attendance-value">{{ $keterlambatanFormatted }}</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Pulang Awal:</span>
                            <span class="attendance-value">{{ $pulangAwalFormatted }}</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Total Lembur:</span>
                            <span class="attendance-value indicator-overtime">{{ $totalLembur }} jam</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Lembur Hari Biasa:</span>
                            <span class="attendance-value indicator-overtime">{{ $lemburHariBiasa }} jam</span>
                        </div>
                        <div class="attendance-item">
                            <span class="attendance-label">Lembur Hari Libur:</span>
                            <span class="attendance-value indicator-overtime">{{ $lemburHariLibur }} jam</span>
                        </div>
                    </div>

                    <!-- Add detailed lembur information if available -->
                    @if (isset($dataAbsensi['lembur_disetujui']) && count($dataAbsensi['lembur_disetujui']) > 0)
                        <div class="detail-component">
                            <div class="component-title">DETAIL LEMBUR</div>
                            @foreach ($dataAbsensi['lembur_disetujui'] as $lembur)
                                <div class="detail-row">
                                    <div class="detail-label">
                                        {{ \Carbon\Carbon::parse($lembur->tanggal_lembur)->format('d/m/Y') }}
                                        ({{ $lembur->jenis_lembur }})
                                    </div>
                                    <div class="detail-value">
                                        {{ \Carbon\Carbon::parse($lembur->jam_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($lembur->jam_selesai)->format('H:i') }}
                                        ({{ $lembur->durasi ?? \Carbon\Carbon::parse($lembur->jam_mulai)->diffInHours(\Carbon\Carbon::parse($lembur->jam_selesai)) }}
                                        jam)</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Add detailed absensi information if available -->
                    @if (isset($dataAbsensi['absensi']) && count($dataAbsensi['absensi']) > 0)
                        <div class="detail-component">
                            <div class="component-title">DETAIL KETIDAKHADIRAN</div>
                            @php
                                $nonHadirAbsensi = $dataAbsensi['absensi']->filter(function ($item) {
                                    return $item->status != 'Hadir';
                                });
                            @endphp

                            @if ($nonHadirAbsensi->count() > 0)
                                @foreach ($nonHadirAbsensi as $absen)
                                    <div class="detail-row">
                                        <div class="detail-label">
                                            {{ \Carbon\Carbon::parse($absen->tanggal)->format('d/m/Y') }}</div>
                                        <div class="detail-value">{{ $absen->status }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="detail-row">
                                    <div class="detail-label">Tidak ada ketidakhadiran</div>
                                    <div class="detail-value">-</div>
                                </div>
                            @endif
                        </div>
                    @endif

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

                    <div class="watermark">Slip gaji periode:
                        {{ $penggajian->periodeGaji->nama_periode ?? 'PERIODE' }}</div>
                </div>

                @if ($index < count($penggajians) - 1)
                    <div class="slip-footer">
                        <div class="cut-text">✂ GUNTING DISINI</div>
                    </div>
                @endif
            </div>

            @if ($slipCount % 3 == 0 && $index < count($penggajians) - 1)
    </div>
    <div class="page-break"></div>
    <div class="page-container">
        @php $slipCount = 0; @endphp
        @endif
        @endforeach
    </div>
</body>

</html>
