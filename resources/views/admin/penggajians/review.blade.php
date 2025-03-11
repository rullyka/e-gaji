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
                @if(!isset($karyawan) || !isset($periode))
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
                                        @if(isset($karyawan->foto_karyawan))
                                        <img src="{{ asset('storage/karyawan/foto/'.$karyawan->foto_karyawan) }}" alt="Foto {{ $karyawan->nama_karyawan }}" class="rounded img-fluid" style="max-height: 150px;">
                                        @else
                                        <img src="{{ asset('images/default-avatar.png') }}" alt="Foto Default" class="rounded img-fluid" style="max-height: 150px;">
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
                                                    @if(isset($karyawan->status))
                                                    <span class="badge badge-{{ $karyawan->status == 'aktif' ? 'success' : ($karyawan->status == 'nonaktif' ? 'danger' : 'warning') }}">
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
                                        <td>{{ isset($periode->tanggal_mulai) ? $periode->tanggal_mulai->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Selesai</th>
                                        <td>{{ isset($periode->tanggal_selesai) ? $periode->tanggal_selesai->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Jumlah Hari</th>
                                        <td>{{ (isset($periode->tanggal_mulai) && isset($periode->tanggal_selesai)) ? $periode->tanggal_mulai->diffInDays($periode->tanggal_selesai) + 1 : 0 }} hari</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if(isset($periode->status))
                                            <span class="badge badge-{{ $periode->status == 'aktif' ? 'success' : 'secondary' }}">
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
                @if(isset($dataAbsensi))
                <div class="mt-3 card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Data Absensi ({{ $dataAbsensi['total_hari_kerja'] ?? 0 }} Hari Kerja)</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Hadir</span>
                                        <span class="info-box-number">{{ $dataAbsensi['hadir'] ?? 0 }} hari</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-calendar-minus"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Izin/Cuti</span>
                                        <span class="info-box-number">{{ $dataAbsensi['izin'] ?? 0 }} hari</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-danger">
                                    <span class="info-box-icon"><i class="fas fa-calendar-times"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tidak Hadir</span>
                                        <span class="info-box-number">{{ $dataAbsensi['tidak_hadir'] ?? 0 }} hari</span>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                    @if(isset($dataAbsensi['absensi']) && count($dataAbsensi['absensi']) > 0)
                                    @foreach($dataAbsensi['absensi'] as $absensi)
                                    <tr>
                                        <td>{{ isset($absensi->tanggal) ? $absensi->tanggal->format('d-m-Y') : '-' }}</td>
                                        <td>{{ isset($absensi->jam_masuk) ? $absensi->jam_masuk->format('H:i') : '-' }}</td>
                                        <td>{{ isset($absensi->jam_pulang) ? $absensi->jam_pulang->format('H:i') : '-' }}</td>
                                        <td>{{ $absensi->total_jam ?? '-' }}</td>
                                        <td>
                                            @if(isset($absensi->keterlambatan) && $absensi->keterlambatan > 0)
                                            <span class="badge badge-warning">{{ $absensi->keterlambatan }} menit</span>
                                            @else
                                            -
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($absensi->pulang_awal) && $absensi->pulang_awal > 0)
                                            <span class="badge badge-warning">{{ $absensi->pulang_awal }} menit</span>
                                            @else
                                            -
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($absensi->status))
                                            @if($absensi->status == 'Hadir')
                                            <span class="badge badge-success">Hadir</span>
                                            @elseif($absensi->status == 'Izin' || $absensi->status == 'Cuti')
                                            <span class="badge badge-warning">{{ $absensi->status }}</span>
                                            @else
                                            <span class="badge badge-danger">{{ $absensi->status }}</span>
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
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Gaji Pokok & Tunjangan</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Gaji Pokok</th>
                                            <td>
                                                <input type="number" name="gaji_pokok" id="gaji_pokok" class="form-control" value="{{ isset($karyawan->jabatan) ? $karyawan->jabatan->gaji_pokok : 0 }}" min="0" required readonly>
                                            </td>
                                        </tr>

                                        <!-- Tunjangan Tetap -->
                                        <tr class="bg-light">
                                            <th colspan="2" class="text-center">Tunjangan Tetap</th>
                                        </tr>

                                        @if(isset($karyawan->jabatan) && isset($karyawan->jabatan->tunjangan_jabatan) && $karyawan->jabatan->tunjangan_jabatan > 0)
                                        <tr>
                                            <th>Tunjangan Jabatan</th>
                                            <td>
                                                <input type="hidden" name="tunjangan[0][nama]" value="Tunjangan Jabatan">
                                                <input type="number" name="tunjangan[0][nominal]" class="form-control tunjangan-item" value="{{ $karyawan->jabatan->tunjangan_jabatan }}" min="0" required readonly>
                                            </td>
                                        </tr>
                                        @endif

                                        @if(isset($karyawan->profesi) && isset($karyawan->profesi->tunjangan_profesi) && $karyawan->profesi->tunjangan_profesi > 0)
                                        <tr>
                                            <th>Tunjangan Profesi</th>
                                            <td>
                                                <input type="hidden" name="tunjangan[1][nama]" value="Tunjangan Profesi">
                                                <input type="number" name="tunjangan[1][nominal]" class="form-control tunjangan-item" value="{{ $karyawan->profesi->tunjangan_profesi }}" min="0" required readonly>
                                            </td>
                                        </tr>
                                        @endif

                                        <!-- Tunjangan Tambahan -->
                                        <tr class="bg-light">
                                            <th colspan="2" class="text-center">Tunjangan Tambahan</th>
                                        </tr>

                                        <tr>
                                            <th>Lembur</th>
                                            <td>
                                                <input type="hidden" name="tunjangan[2][nama]" value="Tunjangan Lembur">
                                                <input type="number" name="tunjangan[2][nominal]" class="form-control tunjangan-item" value="{{ $dataAbsensi['total_lembur'] ?? 0 }}" min="0">
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Tunjangan Kehadiran</th>
                                            <td>
                                                <input type="hidden" name="tunjangan[3][nama]" value="Tunjangan Kehadiran">
                                                <input type="number" name="tunjangan[3][nominal]" class="form-control tunjangan-item" value="{{ $dataAbsensi['tunjangan_kehadiran'] ?? 0 }}" min="0">
                                            </td>
                                        </tr>

                                        <!-- Tunjangan Tambahan Dinamis -->
                                        <tr>
                                            <td colspan="2">
                                                <button type="button" class="btn btn-sm btn-primary" id="btnTambahTunjangan">+ Tambah Tunjangan</button>
                                            </td>
                                        </tr>

                                        <tbody id="tunjangan-tambahan">
                                            <!-- Tunjangan tambahan akan ditambahkan di sini -->
                                        </tbody>

                                        <tr class="bg-success">
                                            <th>Total Tunjangan</th>
                                            <td>
                                                <input type="text" id="total_tunjangan_display" class="form-control-plaintext font-weight-bold" value="Rp 0" readonly>
                                                <input type="hidden" name="total_tunjangan" id="total_tunjangan" value="0">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Potongan -->
                        <div class="col-md-6">
                            <div class="card card-outline card-danger">
                                <div class="card-header">
                                    <h3 class="card-title">Potongan</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr class="bg-light">
                                            <th colspan="2" class="text-center">Potongan Wajib</th>
                                        </tr>

                                        <tr>
                                            <th>BPJS Kesehatan (1%)</th>
                                            <td>
                                                <input type="hidden" name="potongan[0][nama]" value="BPJS Kesehatan">
                                                <input type="number" name="potongan[0][nominal]" class="form-control potongan-item" value="{{ $potonganBPJS['kesehatan'] ?? 0 }}" min="0" required>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>BPJS Ketenagakerjaan (2%)</th>
                                            <td>
                                                <input type="hidden" name="potongan[1][nama]" value="BPJS Ketenagakerjaan">
                                                <input type="number" name="potongan[1][nominal]" class="form-control potongan-item" value="{{ $potonganBPJS['ketenagakerjaan'] ?? 0 }}" min="0" required>
                                            </td>
                                        </tr>

                                        <tr class="bg-light">
                                            <th colspan="2" class="text-center">Potongan Absensi</th>
                                        </tr>

                                        <tr>
                                            <th>Potongan Ketidakhadiran</th>
                                            <td>
                                                <input type="hidden" name="potongan[2][nama]" value="Potongan Ketidakhadiran">
                                                <input type="number" name="potongan[2][nominal]" class="form-control potongan-item" value="{{ $potonganAbsensi['tidak_hadir'] ?? 0 }}" min="0">
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Potongan Keterlambatan</th>
                                            <td>
                                                <input type="hidden" name="potongan[3][nama]" value="Potongan Keterlambatan">
                                                <input type="number" name="potongan[3][nominal]" class="form-control potongan-item" value="{{ $potonganAbsensi['keterlambatan'] ?? 0 }}" min="0">
                                            </td>
                                        </tr>

                                        <!-- Potongan Tambahan -->
                                        <tr class="bg-light">
                                            <th colspan="2" class="text-center">Potongan Tambahan</th>
                                        </tr>

                                        @if(isset($dataPotongan) && count($dataPotongan) > 0)
                                        @foreach($dataPotongan as $index => $potongan)
                                        <tr>
                                            <th>{{ $potongan->nama_potongan }}</th>
                                            <td>
                                                <input type="hidden" name="potongan[{{ 4 + $index }}][nama]" value="{{ $potongan->nama_potongan }}">
                                                <input type="number" name="potongan[{{ 4 + $index }}][nominal]" class="form-control potongan-item" value="0" min="0">
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endif

                                        <!-- Potongan Tambahan Dinamis -->
                                        <tr>
                                            <td colspan="2">
                                                <button type="button" class="btn btn-sm btn-danger" id="btnTambahPotongan">+ Tambah Potongan</button>
                                            </td>
                                        </tr>

                                        <tbody id="potongan-tambahan">
                                            <!-- Potongan tambahan akan ditambahkan di sini -->
                                        </tbody>

                                        <tr class="bg-danger">
                                            <th>Total Potongan</th>
                                            <td>
                                                <input type="text" id="total_potongan_display" class="form-control-plaintext font-weight-bold" value="Rp 0" readonly>
                                                <input type="hidden" name="total_potongan" id="total_potongan" value="0">
                                            </td>
                                        </tr>
                                    </table>
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
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Gaji Pokok</span>
                                            <span class="info-box-number" id="display_gaji_pokok">Rp {{ isset($karyawan->jabatan) ? number_format($karyawan->jabatan->gaji_pokok, 0, ',', '.') : 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-plus"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Tunjangan</span>
                                            <span class="info-box-number" id="display_total_tunjangan">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-danger">
                                        <span class="info-box-icon"><i class="fas fa-minus"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Potongan</span>
                                            <span class="info-box-number" id="display_total_potongan">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 row">
                                <div class="col-md-12">
                                    <div class="alert alert-success">
                                        <h4 class="text-center">Total Gaji Bersih</h4>
                                        <h2 class="text-center" id="gaji_bersih_display">Rp 0</h2>
                                        <input type="hidden" name="gaji_bersih" id="gaji_bersih" value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 row">
                                <div class="text-center col-md-12">
                                    <button type="button" class="mr-2 btn btn-secondary btn-lg" onclick="window.history.back();">
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
                // Inisialisasi counter untuk tunjangan dan potongan tambahan
                let tunjanganCounter = {
                    {
                        isset($karyawan) ?
                            ((isset($karyawan - > jabatan) && isset($karyawan - > jabatan - > tunjangan_jabatan) && $karyawan - > jabatan - > tunjangan_jabatan > 0) ? 1 : 0) +
                            ((isset($karyawan - > profesi) && isset($karyawan - > profesi - > tunjangan_profesi) && $karyawan - > profesi - > tunjangan_profesi > 0) ? 1 : 0) +
                            2 : 4
                    }
                };

                let potonganCounter = {
                    {
                        isset($dataPotongan) ? 4 + count($dataPotongan) : 4
                    }
                };

                // Fungsi untuk menambah tunjangan
                $('#btnTambahTunjangan').click(function() {
                    const html = `
                <tr class="tunjangan-row">
                    <td>
                        <input type="text" name="tunjangan[${tunjanganCounter}][nama]" class="form-control" placeholder="Nama Tunjangan" required>
                    </td>
                    <td class="d-flex">
                        <input type="number" name="tunjangan[${tunjanganCounter}][nominal]" class="form-control tunjangan-item" value="0" min="0" required>
                        <button type="button" class="ml-2 btn btn-sm btn-danger btn-hapus-tunjangan">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
                    $('#tunjangan-tambahan').append(html);
                    tunjanganCounter++;
                    hitungTotal();
                });

                // Fungsi untuk menambah potongan
                $('#btnTambahPotongan').click(function() {
                    const html = `
                <tr class="potongan-row">
                    <td>
                        <input type="text" name="potongan[${potonganCounter}][nama]" class="form-control" placeholder="Nama Potongan" required>
                    </td>
                    <td class="d-flex">
                        <input type="number" name="potongan[${potonganCounter}][nominal]" class="form-control potongan-item" value="0" min="0" required>
                        <button type="button" class="ml-2 btn btn-sm btn-danger btn-hapus-potongan">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
                    $('#potongan-tambahan').append(html);
                    potonganCounter++;
                    hitungTotal();
                });

                // Hapus tunjangan
                $(document).on('click', '.btn-hapus-tunjangan', function() {
                    $(this).closest('tr').remove();
                    hitungTotal();
                });

                // Hapus potongan
                $(document).on('click', '.btn-hapus-potongan', function() {
                    $(this).closest('tr').remove();
                    hitungTotal();
                });

                // Hitung total saat nilai berubah
                $(document).on('input', '.tunjangan-item, .potongan-item, #gaji_pokok', function() {
                    hitungTotal();
                });

                // Fungsi untuk menghitung total
                function hitungTotal() {
                    let totalTunjangan = 0;
                    let totalPotongan = 0;

                    // Hitung total tunjangan
                    $('.tunjangan-item').each(function() {
                        const nilai = parseInt($(this).val()) || 0;
                        totalTunjangan += nilai;
                    });

                    // Hitung total potongan
                    $('.potongan-item').each(function() {
                        const nilai = parseInt($(this).val()) || 0;
                        totalPotongan += nilai;
                    });

                    // Ambil gaji pokok
                    const gajiPokok = parseInt($('#gaji_pokok').val()) || 0;

                    // Hitung gaji bersih
                    const gajiBersih = gajiPokok + totalTunjangan - totalPotongan;

                    // Update display dan hidden inputs
                    $('#total_tunjangan').val(totalTunjangan);
                    $('#total_potongan').val(totalPotongan);
                    $('#gaji_bersih').val(gajiBersih);

                    // Format currency
                    // Format currency
                    $('#total_tunjangan_display').val(formatRupiah(totalTunjangan));
                    $('#total_potongan_display').val(formatRupiah(totalPotongan));
                    $('#display_gaji_pokok').text(formatRupiah(gajiPokok));
                    $('#display_total_tunjangan').text(formatRupiah(totalTunjangan));
                    $('#display_total_potongan').text(formatRupiah(totalPotongan));
                    $('#gaji_bersih_display').text(formatRupiah(gajiBersih));
