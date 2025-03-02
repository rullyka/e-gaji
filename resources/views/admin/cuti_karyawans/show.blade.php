@extends('adminlte::page')

@section('title', 'Detail Pengajuan Cuti')

@section('content_header')
<h1>Detail Pengajuan Cuti</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Pengajuan Cuti</h3>
        <div class="card-tools">
            <a href="{{ route('cuti_karyawans.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @if($cutiKaryawan->status_acc == 'Menunggu Persetujuan')
            @can_show('cuti_karyawan.edit')
            <a href="{{ route('cuti_karyawans.edit', $cutiKaryawan) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan_show

            @can_show('cuti_karyawan.approve')
            <a href="{{ route('cuti_karyawans.approval', $cutiKaryawan) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-check"></i> Proses
            </a>
            @endcan_show
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title">Data Karyawan</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">Nama Karyawan</th>
                                <td>{{ $cutiKaryawan->karyawan ? $cutiKaryawan->karyawan->nama_karyawan : '-' }}</td>
                            </tr>
                            <tr>
                                <th>NIK Karyawan</th>
                                <td>{{ $cutiKaryawan->karyawan ? $cutiKaryawan->karyawan->nik_karyawan : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Departemen</th>
                                <td>{{ $cutiKaryawan->karyawan && $cutiKaryawan->karyawan->departemen ? $cutiKaryawan->karyawan->departemen->name_departemen : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Bagian</th>
                                <td>{{ $cutiKaryawan->karyawan && $cutiKaryawan->karyawan->bagian ? $cutiKaryawan->karyawan->bagian->name_bagian : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jabatan</th>
                                <td>{{ $cutiKaryawan->karyawan && $cutiKaryawan->karyawan->jabatan ? $cutiKaryawan->karyawan->jabatan->name_jabatan : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title">Data Persetujuan</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">Supervisor</th>
                                <td>{{ $cutiKaryawan->supervisor ? $cutiKaryawan->supervisor->nama_karyawan : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge {{ $cutiKaryawan->status_badge_class }}">
                                        {{ $cutiKaryawan->status_acc }}
                                    </span>
                                </td>
                            </tr>
                            @if($cutiKaryawan->status_acc != 'Menunggu Persetujuan')
                            <tr>
                                <th>Tanggal Approval</th>
                                <td>{{ $cutiKaryawan->tanggal_approval ? $cutiKaryawan->tanggal_approval->format('d-m-Y H:i:s') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Diapprove Oleh</th>
                                <td>{{ $cutiKaryawan->approver ? $cutiKaryawan->approver->nama_karyawan : '-' }}</td>
                            </tr>
                            @if($cutiKaryawan->status_acc == 'Ditolak')
                            <tr>
                                <th>Alasan Penolakan</th>
                                <th>Alasan Penolakan</th>
                                <td>{{ $cutiKaryawan->alasan_penolakan ?? '-' }}</td>
                            </tr>
                            @endif
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title">Detail Pengajuan Cuti</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">No. Pengajuan</th>
                                <td>{{ $cutiKaryawan->no_pengajuan }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengajuan</th>
                                <td>{{ $cutiKaryawan->tanggal_pengajuan ? $cutiKaryawan->tanggal_pengajuan->format('d-m-Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Cuti</th>
                                <td>{{ $cutiKaryawan->jenisCuti ? $cutiKaryawan->jenisCuti->nama_jenis_cuti : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Mulai</th>
                                <td>{{ $cutiKaryawan->tanggal_mulai ? $cutiKaryawan->tanggal_mulai->format('d-m-Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Selesai</th>
                                <td>{{ $cutiKaryawan->tanggal_selesai ? $cutiKaryawan->tanggal_selesai->format('d-m-Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Hari</th>
                                <td>{{ $cutiKaryawan->jumlah_hari }} hari</td>
                            </tr>
                            <tr>
                                <th>Keterangan</th>
                                <td>{{ $cutiKaryawan->keterangan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Dokumen Pendukung</th>
                                <td>
                                    @if($cutiKaryawan->dokumen_pendukung)
                                    <a href="{{ asset('storage/dokumen_cuti/' . $cutiKaryawan->dokumen_pendukung) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-file"></i> Lihat Dokumen
                                    </a>
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title">Sisa Cuti</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">Kuota Cuti Tahunan</th>
                                <td>{{ $sisaCuti['kuota'] ?? 0 }} hari</td>
                            </tr>
                            <tr>
                                <th>Cuti Terpakai</th>
                                <td>{{ $sisaCuti['terpakai'] ?? 0 }} hari</td>
                            </tr>
                            <tr>
                                <th>Sisa Cuti</th>
                                <td>{{ $sisaCuti['sisa'] ?? 0 }} hari</td>
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
    .table th {
        background-color: #f4f6f9;
    }

</style>
@stop

@section('js')
<script>
    $(function() {
        // Additional JavaScript if needed
    });

</script>
@stop
