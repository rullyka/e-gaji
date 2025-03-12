@extends('adminlte::page')

@section('title', 'Detail Karyawan')

@section('content_header')
<h1>Detail Karyawan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $karyawan->nama_karyawan }}</h3>
        <div class="card-tools">
            <a href="{{ route('karyawans.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @can_show('karyawan.edit')
            @if(!$karyawan->tahun_keluar)
            <a href="{{ route('karyawans.edit', $karyawan) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
            @endcan_show
        </div>
    </div>
    <div class="card-body position-relative">
        @if($karyawan->tahun_keluar)
        <div class="resign-watermark">RESIGN</div>
        @endif
        <div class="row">
            <div class="col-md-3">
                <div class="mb-4 text-center">
                    <img src="{{ $karyawan->foto_url }}" alt="{{ $karyawan->nama_karyawan }}" class="rounded img-fluid" style="max-height: 250px;">
                </div>
                @if($karyawan->ktp_url)
                <div class="text-center">
                    <h5>KTP</h5>
                    <img src="{{ $karyawan->ktp_url }}" alt="KTP {{ $karyawan->nama_karyawan }}" class="rounded img-fluid" style="max-height: 150px;">
                </div>
                @endif
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Informasi Pribadi</h4>
                        <table class="table table-striped">
                            <tr>
                                <th width="150">NIK Karyawan</th>
                                <td>{{ $karyawan->nik_karyawan }}</td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td>{{ $karyawan->nama_karyawan }}</td>
                            </tr>
                            <tr>
                                <th>Status Karyawan</th>
                                <td>{{ $karyawan->statuskaryawan }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Masuk</th>
                                <td>{{ $karyawan->tgl_awalmmasuk ? date('d-m-Y', strtotime($karyawan->tgl_awalmmasuk)) : '-' }}</td>
                            </tr>
                            @if($karyawan->tahun_keluar)
                            <tr>
                                <th>Tanggal Resign</th>
                                <td>{{ is_string($karyawan->tahun_keluar) ? date('d-m-Y', strtotime($karyawan->tahun_keluar)) : $karyawan->tahun_keluar->format('d-m-Y') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>NIK (KTP)</th>
                                <td>{{ $karyawan->nik }}</td>
                            </tr>
                            <tr>
                                <th>No. KK</th>
                                <td>{{ $karyawan->kk }}</td>
                            </tr>
                            <tr>
                                <th>Status Perkawinan</th>
                                <td>{{ $karyawan->statuskawin }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Anggota KK</th>
                                <td>{{ $karyawan->jml_anggotakk }}</td>
                            </tr>
                            <tr>
                                <th>Nama Ayah</th>
                                <td>{{ $karyawan->ortu_bapak }}</td>
                            </tr>
                            <tr>
                                <th>Nama Ibu</th>
                                <td>{{ $karyawan->ortu_ibu }}</td>
                            </tr>
                            <tr>
                                <th>No. HP</th>
                                <td>{{ $karyawan->no_hp }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Informasi Pekerjaan</h4>
                        <table class="table table-striped">
                            <tr>
                                <th width="150">Departemen</th>
                                <td>{{ $karyawan->departemen ? $karyawan->departemen->name_departemen : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Bagian</th>
                                <td>{{ $karyawan->bagian ? $karyawan->bagian->name_bagian : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jabatan</th>
                                <td>{{ $karyawan->jabatan ? $karyawan->jabatan->name_jabatan : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Profesi</th>
                                <td>{{ $karyawan->profesi ? $karyawan->profesi->name_profesi : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Pendidikan</th>
                                <td>{{ $karyawan->pendidikan_terakhir }}</td>
                            </tr>
                            <tr>
                                <th>Program Studi</th>
                                <td>{{ $karyawan->programStudi ? $karyawan->programStudi->name_programstudi : '-' }}</td>
                            </tr>
                        </table>

                        <h4 class="mt-4">Informasi Tambahan</h4>
                        <table class="table table-striped">
                            <tr>
                                <th width="150">Ukuran Kemeja</th>
                                <td>{{ $karyawan->ukuran_kemeja }}</td>
                            </tr>
                            <tr>
                                <th>Ukuran Celana</th>
                                <td>{{ $karyawan->ukuran_celana }}</td>
                            </tr>
                            <tr>
                                <th>Ukuran Sepatu</th>
                                <td>{{ $karyawan->ukuran_sepatu }}</td>
                            </tr>
                            <tr>
                                <th>Terdaftar Pada</th>
                                <td>{{ $karyawan->created_at->format('d-m-Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Diupdate</th>
                                <td>{{ $karyawan->updated_at->format('d-m-Y H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .position-relative {
        position: relative;
        overflow: hidden;
    }
    
    .resign-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 120px;
        font-weight: bold;
        color: rgba(255, 0, 0, 0.2);
        white-space: nowrap;
        pointer-events: none;
        z-index: 10;
        text-transform: uppercase;
        letter-spacing: 10px;
    }
</style>
@stop