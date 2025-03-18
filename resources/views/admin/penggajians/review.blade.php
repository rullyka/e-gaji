@extends('adminlte::page')

@section('title', 'Review Penggajian')

@section('content_header')
    <h1>Review Penggajian</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Data Penggajian</h3>
                    <div class="card-tools">
                        <a href="{{ route('penggajian.create') }}" class="btn btn-sm btn-default">
                            <i class="mr-1 fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if (!isset($karyawan) || !isset($periode))
                        <div class="alert alert-danger">
                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                            Data karyawan atau periode tidak ditemukan.
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">Data Karyawan</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="mb-3 text-center col-md-4">
                                                @if (isset($karyawan->foto_karyawan))
                                                    <img src="{{ asset('storage/karyawan/foto/' . $karyawan->foto_karyawan) }}"
                                                        alt="Foto {{ $karyawan->nama_karyawan }}" class="rounded img-fluid"
                                                        style="max-height: 150px;">
                                                @else
                                                    <img src="{{ asset('images/default-avatar.png') }}" alt="Foto Default"
                                                        class="rounded img-fluid" style="max-height: 150px;">
                                                @endif
                                            </div>
                                            <div class="col-md-8">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th>Nama</th>
                                                        <td>{{ $karyawan->nama_karyawan ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>NIK</th>
                                                        <td>{{ $karyawan->nik ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Departemen</th>
                                                        <td>{{ $karyawan->departemen->name_departemen ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Bagian</th>
                                                        <td>{{ $karyawan->bagian->name_bagian ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Jabatan</th>
                                                        <td>{{ $karyawan->jabatan->name_jabatan ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Profesi</th>
                                                        <td>{{ $karyawan->profesi->name_profesi ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Status</th>
                                                        <td>
                                                            @if (isset($karyawan->status))
                                                                <span
                                                                    class="badge badge-{{ $karyawan->status == 'aktif' ? 'success' : ($karyawan->status == 'nonaktif' ? 'danger' : 'warning') }}">
                                                                    {{ $karyawan->status }}
                                                                </span>
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">Data Periode</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Periode</th>
                                                <td>{{ $periode->nama_periode ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Mulai</th>
                                                <td>{{ isset($periode->tanggal_mulai) ? $periode->tanggal_mulai->format('d-m-Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Selesai</th>
                                                <td>{{ isset($periode->tanggal_selesai) ? $periode->tanggal_selesai->format('d-m-Y') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah Hari</th>
                                                <td>{{ isset($periode->tanggal_mulai) && isset($periode->tanggal_selesai) ? $periode->tanggal_mulai->diffInDays($periode->tanggal_selesai) + 1 : 0 }}
                                                    hari</td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    @if (isset($periode->status))
                                                        <span
                                                            class="badge badge-{{ $periode->status == 'aktif' ? 'success' : 'secondary' }}">
                                                            {{ $periode->status }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Absensi -->
                        @if (isset($dataAbsensi))
                            <div class="mt-3 card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Data Absensi ({{ $dataAbsensi['total_hari_kerja'] ?? 0 }} Hari
                                        Kerja)</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box bg-success">
                                                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Hadir</span>
                                                    <span class="info-box-number">{{ $dataAbsensi['hadir'] ?? 0 }}
                                                        hari</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon"><i class="fas fa-calendar-minus"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Izin</span>
                                                    <span="info-box-number">{{ $dataAbsensi['izin'] ?? 0 }}
                                                        hari</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-umbrella-beach"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Cuti</span>
                                                    <span
                                                        class="info-box-number">{{ ($dataAbsensi['cuti'] ?? 0) + ($dataAbsensi['izin_cuti'] ?? 0) }}
                                                        hari</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 row">
                                        <div class="col-md-4">
                                            <div class="info-box bg-danger">
                                                <span class="info-box-icon"><i class="fas fa-calendar-times"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Tidak Hadir</span>
                                                    <span class="info-box-number">{{ $dataAbsensi['tidak_hadir'] ?? 0 }}
                                                        hari</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-secondary">
                                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Keterlambatan</span>
                                                    <span class="info-box-number">{{ $dataAbsensi['keterlambatan_display'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-secondary">
                                                <span class="info-box-icon"><i class="fas fa-sign-out-alt"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Pulang Awal</span>
                                                    <span class="info-box-number">{{ $dataAbsensi['pulang_awal_display'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 row">
                                        <div class="col-md-4">
                                            <div class="info-box bg-primary">
                                                <span class="info-box-icon"><i class="fas fa-business-time"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Lembur Hari Biasa</span>
                                                    <span
                                                        class="info-box-number">{{ $dataAbsensi['lembur_hari_biasa'] ?? 0 }}
                                                        jam</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-primary">
                                                <span class="info-box-icon"><i class="fas fa-business-time"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Lembur Hari Libur</span>
                                                    <span
                                                        class="info-box-number">{{ $dataAbsensi['lembur_hari_libur'] ?? 0 }}
                                                        jam</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box bg-primary">
                                                <span class="info-box-icon"><i class="fas fa-business-time"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Lembur</span>
                                                    <span class="info-box-number">{{ $dataAbsensi['total_lembur'] ?? 0 }}
                                                        jam</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add this section after the absensi summary section -->
                                    @if(isset($dataAbsensi['lembur_disetujui']) && count($dataAbsensi['lembur_disetujui']) > 0)
                                    <div class="mt-3">
                                        <h5>Detail Lembur Disetujui</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Jam Mulai</th>
                                                        <th>Jam Selesai</th>
                                                        <th>Durasi</th>
                                                        <th>Jenis</th>
                                                        <th>Nominal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($dataAbsensi['lembur_disetujui'] as $lembur)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($lembur->tanggal_lembur)->format('d-m-Y') }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($lembur->jam_mulai)->format('H:i') }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($lembur->jam_selesai)->format('H:i') }}</td>
                                                        <td>{{ $lembur->durasi ?? '-' }} jam</td>
                                                        <td>{{ $lembur->jenis_lembur }}</td>
                                                        <td class="text-right">
                                                            @if($lembur->jenis_lembur == 'Hari Libur')
                                                                Rp {{ number_format($lembur->durasi * $karyawan->jabatan->uang_lembur_libur, 0, ',', '.') }}
                                                            @else
                                                                Rp {{ number_format($lembur->durasi * $karyawan->jabatan->uang_lembur_biasa, 0, ',', '.') }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="bg-light">
                                                        <th colspan="3" class="text-right">Total</th>
                                                        <th>{{ $dataAbsensi['total_lembur'] }} jam</th>
                                                        <th></th>
                                                        <th class="text-right">
                                                            Rp {{ number_format($dataAbsensi['tunjangan_lembur_biasa'] + $dataAbsensi['tunjangan_lembur_libur'], 0, ',', '.') }}
                                                        </th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="mt-3 table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Jam Masuk</th>
                                                    <th>Jam Pulang</th>
                                                    <th>Total Jam</th>
                                                    <th>Keterlambatan</th>
                                                    <th>Pulang Awal</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($dataAbsensi['absensi']) && count($dataAbsensi['absensi']) > 0)
                                                    @foreach ($dataAbsensi['absensi'] as $absensi)
                                                        <tr>
                                                            <td>{{ isset($absensi->tanggal) ? $absensi->tanggal->format('d-m-Y') : '-' }}
                                                            </td>
                                                            <td>{{ isset($absensi->jam_masuk) ? $absensi->jam_masuk->format('H:i') : '-' }}
                                                            </td>
                                                            <td>{{ isset($absensi->jam_pulang) ? $absensi->jam_pulang->format('H:i') : '-' }}
                                                            </td>
                                                            <td>{{ $absensi->total_jam ?? '-' }}</td>
                                                            <td>
                                                                @if (isset($absensi->keterlambatan) && $absensi->keterlambatan > 0)
                                                                    <span
                                                                        class="badge badge-warning">{{ $absensi->keterlambatan }}
                                                                        menit</span>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (isset($absensi->pulang_awal) && $absensi->pulang_awal > 0)
                                                                    <span
                                                                        class="badge badge-warning">{{ $absensi->pulang_awal }}
                                                                        menit</span>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (isset($absensi->status))
                                                                    @if ($absensi->status == 'Hadir')
                                                                        <span class="badge badge-success">Hadir</span>
                                                                    @elseif($absensi->status == 'Izin' || $absensi->status == 'Cuti')
                                                                        <span
                                                                            class="badge badge-warning">{{ $absensi->status }}</span>
                                                                    @else
                                                                        <span
                                                                            class="badge badge-danger">{{ $absensi->status }}</span>
                                                                    @endif
                                                                @else
                                                                    <span class="badge badge-danger">Tidak Hadir</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="7" class="text-center">Tidak ada data absensi</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('penggajian.process') }}" method="POST">
                            @csrf
                            <input type="hidden" name="karyawan_id" value="{{ $karyawan->id ?? '' }}">
                            <input type="hidden" name="periode_id" value="{{ $periode->id ?? '' }}">

                            <div class="row">
                                <!-- Gaji Pokok & Tunjangan -->
                                <div class="col-md-12">
                                    <div class="card card-outline card-success">
                                        <div class="card-header">
                                            <h3 class="card-title">Gaji Pokok & Tunjangan</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="text-left" width="60%">Keterangan</th>
                                                            <th class="text-right" width="40%">Nominal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Gaji Pokok -->
                                                        <tr>
                                                            <td>Gaji Pokok</td>
                                                            <td class="text-right">
                                                                <input type="text" name="gaji_pokok" id="gaji_pokok"
                                                                    class="text-right form-control"
                                                                    value="{{ isset($karyawan->jabatan) ? $karyawan->jabatan->gaji_pokok : 0 }}"
                                                                    min="0" required readonly>
                                                            </td>
                                                        </tr>

                                                        <!-- Divider for Tunjangan Tetap -->
                                                        <tr class="bg-light">
                                                            <td colspan="2" class="text-center font-weight-bold">
                                                                Tunjangan Tetap</td>
                                                        </tr>

                                                        <!-- Tunjangan Jabatan -->
                                                        @if (isset($karyawan->jabatan) &&
                                                                isset($karyawan->jabatan->tunjangan_jabatan) &&
                                                                $karyawan->jabatan->tunjangan_jabatan > 0)
                                                            <tr>
                                                                <td>Tunjangan Jabatan</td>
                                                                <td class="text-right">
                                                                    <input type="hidden" name="tunjangan[0][nama]"
                                                                        value="Tunjangan Jabatan">
                                                                    <input type="text" name="tunjangan[0][nominal]"
                                                                        class="text-right form-control tunjangan-item"
                                                                        value="{{ $karyawan->jabatan->tunjangan_jabatan }}"
                                                                        min="0" required readonly>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        <!-- Tunjangan Profesi -->
                                                        @if (isset($karyawan->profesi) &&
                                                                isset($karyawan->profesi->tunjangan_profesi) &&
                                                                $karyawan->profesi->tunjangan_profesi > 0)
                                                            <tr>
                                                                <td>Tunjangan Profesi</td>
                                                                <td class="text-right">
                                                                    <input type="hidden" name="tunjangan[1][nama]"
                                                                        value="Tunjangan Profesi">
                                                                    <input type="text" name="tunjangan[1][nominal]"
                                                                        class="text-right form-control tunjangan-item"
                                                                        value="{{ $karyawan->profesi->tunjangan_profesi }}"
                                                                        min="0" required readonly>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        <!-- Divider for Tunjangan Tambahan -->
                                                        <tr class="bg-light">
                                                            <td colspan="2" class="text-center font-weight-bold">
                                                                Tunjangan Tambahan</td>
                                                        </tr>

                                                        <!-- Tunjangan Lembur -->
                                                        <!-- Tunjangan Lembur Hari Biasa -->
                                                        <tr>
                                                            <td>
                                                                Tunjangan Lembur Hari Kerja
                                                                <small class="text-muted">
                                                                    ({{ isset($karyawan->jabatan) ? number_format($karyawan->jabatan->uang_lembur_biasa, 0, ',', '.') : 0 }}/jam)
                                                                </small>
                                                            </td>
                                                            <td class="text-right">
                                                                <input type="hidden" name="tunjangan[2][nama]"
                                                                    value="Tunjangan Lembur Hari Kerja">
                                                                <input type="text" name="tunjangan[2][nominal]"
                                                                    class="text-right form-control tunjangan-item"
                                                                    value="{{ $dataAbsensi['tunjangan_lembur_biasa'] ?? 0 }}"
                                                                    min="0" readonly>
                                                            </td>
                                                        </tr>

                                                        <!-- Tunjangan Lembur Hari Libur -->
                                                        <tr>
                                                            <td>
                                                                Tunjangan Lembur Hari Libur
                                                                <small class="text-muted">
                                                                    ({{ isset($karyawan->jabatan) ? number_format($karyawan->jabatan->uang_lembur_libur, 0, ',', '.') : 0 }}/jam)
                                                                </small>
                                                            </td>
                                                            <td class="text-right">
                                                                <input type="hidden" name="tunjangan[3][nama]"
                                                                    value="Tunjangan Lembur Hari Libur">
                                                                <input type="text" name="tunjangan[3][nominal]"
                                                                    class="text-right form-control tunjangan-item"
                                                                    value="{{ $dataAbsensi['tunjangan_lembur_libur'] ?? 0 }}"
                                                                    min="0" readonly>
                                                            </td>
                                                        </tr>

                                                        <!-- Tunjangan Kehadiran -->
                                                        <tr>
                                                            <td>
                                                                Tunjangan Kehadiran
                                                                <small
                                                                    class="text-muted">({{ number_format(100000, 0, ',', '.') }}/bulan)</small>
                                                            </td>
                                                            <td class="text-right">
                                                                <input type="hidden" name="tunjangan[4][nama]"
                                                                    value="Tunjangan Kehadiran">
                                                                <input type="text" name="tunjangan[4][nominal]"
                                                                    class="text-right form-control tunjangan-item"
                                                                    value="{{ $dataAbsensi['tunjangan_kehadiran'] ?? 0 }}"
                                                                    min="0">
                                                            </td>
                                                        </tr>

                                                        <!-- Premi -->
                                                        <tr>
                                                            <td>
                                                                Premi
                                                                <small class="text-muted">(Tunjangan Tambahan)</small>
                                                            </td>
                                                            <td class="text-right">
                                                                <input type="hidden" name="tunjangan[5][nama]"
                                                                    value="Premi">
                                                                <input type="text" name="tunjangan[5][nominal]"
                                                                    class="text-right form-control tunjangan-item"
                                                                    value="0" min="0">
                                                            </td>
                                                        </tr>

                                                        <!-- Dynamic Tunjangan Container -->
                                                    <tbody id="tunjangan-tambahan">
                                                        <!-- Additional tunjangan items will be added here -->
                                                    </tbody>

                                                    <!-- Add Button -->
                                                    <tr>
                                                        <td colspan="2">
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                id="btnTambahTunjangan">
                                                                <i class="mr-1 fas fa-plus"></i> Tambah Tunjangan
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <!-- Total Tunjangan -->
                                                    <tr class="text-white bg-success">
                                                        <td class="font-weight-bold">Total Tunjangan</td>
                                                        <td class="text-right font-weight-bold">
                                                            <input type="text" id="total_tunjangan_display"
                                                                class="text-right text-white form-control-plaintext font-weight-bold"
                                                                value="Rp 0" readonly>
                                                            <input type="hidden" name="total_tunjangan"
                                                                id="total_tunjangan" value="0">
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Potongan -->
                                <div class="mt-3 col-md-12">
                                    <div class="card card-outline card-danger">
                                        <div class="card-header">
                                            <h3 class="card-title">Potongan</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="text-left" width="60%">Keterangan</th>
                                                            <th class="text-right" width="40%">Nominal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Divider for Potongan Absensi -->
                                                        <tr class="bg-light">
                                                            <td colspan="2" class="text-center font-weight-bold">
                                                                Potongan Absensi</td>
                                                        </tr>

                                                        <!-- Potongan Ketidakhadiran -->
                                                        <tr>
                                                            <td>
                                                                Potongan Ketidakhadiran
                                                                <small class="text-muted">
                                                                    ({{ isset($karyawan->jabatan) ? number_format($karyawan->jabatan->gaji_pokok / 30, 0, ',', '.') : 0 }}/hari)
                                                                </small>
                                                            </td>
                                                            <td class="text-right">
                                                                <input type="hidden" name="potongan[0][nama]"
                                                                    value="Potongan Ketidakhadiran">
                                                                <input type="text" name="potongan[0][nominal]"
                                                                    class="text-right form-control potongan-item"
                                                                    value="{{ $potonganAbsensi['tidak_hadir'] ?? 0 }}"
                                                                    min="0">
                                                            </td>
                                                        </tr>

                                                        <!-- Potongan Keterlambatan -->
                                                        <tr>
                                                            <td>
                                                                Potongan Keterlambatan
                                                                <small
                                                                    class="text-muted">({{ number_format(25000, 0, ',', '.') }}/30
                                                                    menit)</small>
                                                            </td>
                                                            <td class="text-right">
                                                                <input type="hidden" name="potongan[1][nama]"
                                                                    value="Potongan Keterlambatan">
                                                                <input type="text" name="potongan[1][nominal]"
                                                                    class="text-right form-control potongan-item"
                                                                    value="{{ $potonganAbsensi['keterlambatan'] ?? 0 }}"
                                                                    min="0">
                                                            </td>
                                                        </tr>

                                                        <!-- Potongan Master -->
                                                        @if (isset($dataPotongan) && count($dataPotongan) > 0)
                                                            <tr class="bg-light">
                                                                <td colspan="2" class="text-center font-weight-bold">
                                                                    Potongan Master</td>
                                                            </tr>

                                                            @foreach ($dataPotongan as $index => $potongan)
                                                                <tr>
                                                                    <td>{{ $potongan->nama_potongan }}</td>
                                                                    <td class="text-right">
                                                                        <input type="hidden"
                                                                            name="potongan[{{ 2 + $index }}][nama]"
                                                                            value="{{ $potongan->nama_potongan }}">
                                                                        <input type="text"
                                                                            name="potongan[{{ 2 + $index }}][nominal]"
                                                                            class="text-right form-control potongan-item"
                                                                            value="0" min="0">
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif

                                                        <!-- Dynamic Potongan Container -->
                                                    <tbody id="potongan-tambahan">
                                                        <!-- Additional potongan items will be added here -->
                                                    </tbody>

                                                    <!-- Add Button -->
                                                    <tr>
                                                        <td colspan="2">
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                id="btnTambahPotongan">
                                                                <i class="mr-1 fas fa-plus"></i> Tambah Potongan
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <!-- Total Potongan -->
                                                    <tr class="text-white bg-danger">
                                                        <td class="font-weight-bold">Total Potongan</td>
                                                        <td class="text-right font-weight-bold">
                                                            <input type="text" id="total_potongan_display"
                                                                class="text-right text-white form-control-plaintext font-weight-bold"
                                                                value="Rp 0" readonly>
                                                            <input type="hidden" name="total_potongan"
                                                                id="total_potongan" value="0">
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Gaji -->
                            <div class="mt-3 card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Total Gaji</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-left">Keterangan</th>
                                                    <th class="text-right">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Gaji Pokok</strong></td>
                                                    <td class="text-right"><span id="display_gaji_pokok">Rp
                                                            {{ isset($karyawan->jabatan) ? number_format($karyawan->jabatan->gaji_pokok, 0, ',', '.') : 0 }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Tunjangan</strong></td>
                                                    <td class="text-right"><span id="display_total_tunjangan"
                                                            class="text-success">Rp 0</span></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Potongan</strong></td>
                                                    <td class="text-right"><span id="display_total_potongan"
                                                            class="text-danger">Rp 0</span></td>
                                                </tr>
                                                <tr class="text-white bg-primary">
                                                    <td class="font-weight-bold">GAJI BERSIH</td>
                                                    <td class="text-right font-weight-bold">
                                                        <span id="gaji_bersih_display">Rp 0</span>
                                                        <input type="hidden" name="gaji_bersih" id="gaji_bersih"
                                                            value="0">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4 row">
                                        <div class="text-center col-md-12">
                                            <button type="button" class="mr-2 btn btn-secondary btn-lg"
                                                onclick="window.history.back();">
                                                <i class="mr-1 fas fa-arrow-left"></i> Kembali
                                            </button>
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="mr-1 fas fa-save"></i> Proses Penggajian
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box-number {
            font-size: 1.5rem;
        }

        #gaji_bersih_display {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            // Format currency function
            function formatRupiah(angka) {
                if (angka === null || angka === undefined || angka === '') {
                    return 'Rp 0';
                }

                var number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return 'Rp ' + rupiah;
            }

            // Make sure lembur values are properly loaded
            function updateLemburValues() {
                // Get lembur biasa value
                let lemburBiasa = $('input[name="tunjangan[2][nominal]"]');
                let lemburBiasaValue = lemburBiasa.val().replace(/[^\d]/g, '');
                lemburBiasa.attr('data-value', lemburBiasaValue);
                lemburBiasa.val(formatRupiah(lemburBiasaValue));

                // Get lembur libur value
                let lemburLibur = $('input[name="tunjangan[3][nominal]"]');
                let lemburLiburValue = lemburLibur.val().replace(/[^\d]/g, '');
                lemburLibur.attr('data-value', lemburLiburValue);
                lemburLibur.val(formatRupiah(lemburLiburValue));
            }

            // Apply currency format to all numeric inputs
            function applyRupiahFormat() {
                // Format tunjangan items
                $('.tunjangan-item').each(function() {
                    const value = $(this).val();
                    $(this).attr('data-value', value);
                    $(this).val(formatRupiah(value));
                });

                // Format potongan items
                $('.potongan-item').each(function() {
                    const value = $(this).val();
                    $(this).attr('data-value', value);
                    $(this).val(formatRupiah(value));
                });

                // Format gaji pokok
                const gajiPokok = $('#gaji_pokok').val();
                $('#gaji_pokok').attr('data-value', gajiPokok);
                $('#gaji_pokok').val(formatRupiah(gajiPokok));

                // Update lembur values specifically
                updateLemburValues();

                // Calculate totals after formatting
                calculateTotals();
            }

            // Handle focus and blur events for currency formatting
            $(document).on('focus', '.tunjangan-item, .potongan-item, #gaji_pokok', function() {
                // On focus, show the raw value for editing
                const value = $(this).attr('data-value') || $(this).val().replace(/[^\d]/g, '');
                $(this).val(value);
            });

            $(document).on('blur', '.tunjangan-item, .potongan-item, #gaji_pokok', function() {
                // On blur, format the value
                const value = $(this).val().replace(/[^\d]/g, '');
                $(this).attr('data-value', value);
                $(this).val(formatRupiah(value));
                calculateTotals();
            });

            // Calculate totals with the actual numeric values
            function calculateTotals() {
                let totalTunjangan = 0;
                $('.tunjangan-item').each(function() {
                    const value = $(this).attr('data-value') || $(this).val().replace(/[^\d]/g, '');
                    totalTunjangan += parseInt(value || 0);
                });

                let totalPotongan = 0;
                $('.potongan-item').each(function() {
                    const value = $(this).attr('data-value') || $(this).val().replace(/[^\d]/g, '');
                    totalPotongan += parseInt(value || 0);
                });

                const gajiPokok = parseInt($('#gaji_pokok').attr('data-value') || $('#gaji_pokok').val().replace(/[^\d]/g, '') || 0);
                const gajiBersih = gajiPokok + totalTunjangan - totalPotongan;

                // Update hidden fields with numeric values
                $('#total_tunjangan').val(totalTunjangan);
                $('#total_potongan').val(totalPotongan);
                $('#gaji_bersih').val(gajiBersih);

                // Update display fields with formatted values
                $('#total_tunjangan_display').val(formatRupiah(totalTunjangan));
                $('#total_potongan_display').val(formatRupiah(totalPotongan));
                $('#display_total_tunjangan').text(formatRupiah(totalTunjangan));
                $('#display_total_potongan').text(formatRupiah(totalPotongan));
                $('#display_gaji_pokok').text(formatRupiah(gajiPokok));
                $('#gaji_bersih_display').text(formatRupiah(gajiBersih));
            }

            // Handle form submission to convert back to numeric values
            $('form').on('submit', function() {
                $('.tunjangan-item, .potongan-item, #gaji_pokok').each(function() {
                    $(this).val($(this).attr('data-value') || $(this).val().replace(/[^\d]/g, ''));
                });
                return true;
            });

            // Apply Rupiah format on page load
            applyRupiahFormat();
            calculateTotals();

            // Inisialisasi counter untuk tunjangan dan potongan tambahan
            let tunjanganInitial = 6; // Updated to 6 tunjangan (2 tetap + 4 tambahan)
            if ($('input[name="tunjangan[0][nominal]"]').length > 0) tunjanganInitial = 1;
            if ($('input[name="tunjangan[1][nominal]"]').length > 0) tunjanganInitial++;
            if ($('input[name="tunjangan[2][nominal]"]').length > 0) tunjanganInitial++;
            if ($('input[name="tunjangan[3][nominal]"]').length > 0) tunjanganInitial++;
            if ($('input[name="tunjangan[4][nominal]"]').length > 0) tunjanganInitial++;
            if ($('input[name="tunjangan[5][nominal]"]').length > 0) tunjanganInitial++;

            let tunjanganCounter = tunjanganInitial;

            // Add dynamic tunjangan
            $('#btnTambahTunjangan').click(function() {
                $('#tunjangan-tambahan').append(`
                    <tr>
                        <td class="pl-2">
                            <div class="input-group">
                                <input type="text" name="tunjangan[${tunjanganCounter}][nama]"
                                    class="form-control" placeholder="Nama Tunjangan" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="pr-2">
                            <div class="input-group">
                                <input type="text" name="tunjangan[${tunjanganCounter}][nominal]"
                                    class="text-right form-control tunjangan-item" value="0" min="0">
                            </div>
                        </td>
                    </tr>
                `);

                // Apply currency format to the new input
                const newInput = $(`input[name="tunjangan[${tunjanganCounter}][nominal]"]`);
                newInput.attr('data-value', '0');
                newInput.val(formatRupiah('0'));

                tunjanganCounter++;
                calculateTotals();
            });

            // Menghitung jumlah potongan yang sudah ada
            let potonganInitial = 2; // Default: potongan ketidakhadiran dan keterlambatan

            // Tambahkan potongan master jika ada
            @if(isset($dataPotongan))
                potonganInitial += {{ count($dataPotongan) }};
            @endif

            let potonganCounter = potonganInitial;

            // Add dynamic potongan
            $('#btnTambahPotongan').click(function() {
                $('#potongan-tambahan').append(`
                    <tr>
                        <td class="pl-2">
                            <div class="input-group">
                                <input type="text" name="potongan[${potonganCounter}][nama]"
                                    class="form-control" placeholder="Nama Potongan" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="pr-2">
                            <div class="input-group">
                                <input type="text" name="potongan[${potonganCounter}][nominal]"
                                    class="text-right form-control potongan-item" value="0" min="0">
                            </div>
                        </td>
                    </tr>
                `);

                // Apply currency format to the new input
                const newInput = $(`input[name="potongan[${potonganCounter}][nominal]"]`);
                newInput.attr('data-value', '0');
                newInput.val(formatRupiah('0'));

                potonganCounter++;
                calculateTotals();
            });

            // Remove dynamic item
            $(document).on('click', '.btn-remove-item', function() {
                $(this).closest('tr').remove();
                calculateTotals();
            });
        });
    </script>
@stop
