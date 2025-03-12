@extends('adminlte::page')

@section('title', 'Persetujuan Cuti Karyawan')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-calendar-check mr-2 text-primary"></i>Persetujuan Cuti Karyawan</h1>
    <a href="{{ route('cuti_karyawans.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>
@stop

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Detail Pengajuan Cuti</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>NIK Karyawan</label>
                    <p class="form-control-static">{{ $cutiKaryawan->karyawan->nik_karyawan }}</p>
                </div>
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <p class="form-control-static">{{ $cutiKaryawan->karyawan->nama_karyawan }}</p>
                </div>
                <div class="form-group">
                    <label>Departemen</label>
                    <p class="form-control-static">{{ $cutiKaryawan->karyawan->departemen->name_departemen ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <label>Bagian</label>
                    <p class="form-control-static">{{ $cutiKaryawan->karyawan->bagian->name_bagian ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <label>Jenis Cuti</label>
                    <p class="form-control-static">{{ $cutiKaryawan->jenis_cuti }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tanggal Mulai Cuti</label>
                    <p class="form-control-static">{{ \Carbon\Carbon::parse($cutiKaryawan->tanggal_mulai_cuti)->format('d-m-Y') }}</p>
                </div>
                <div class="form-group">
                    <label>Tanggal Akhir Cuti</label>
                    <p class="form-control-static">{{ \Carbon\Carbon::parse($cutiKaryawan->tanggal_akhir_cuti)->format('d-m-Y') }}</p>
                </div>
                <div class="form-group">
                    <label>Jumlah Hari</label>
                    <p class="form-control-static">{{ $cutiKaryawan->jumlah_hari_cuti }} hari</p>
                </div>
                <div class="form-group">
                    <label>Supervisor</label>
                    <p class="form-control-static">{{ $cutiKaryawan->supervisor->nama_karyawan ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <label>Jenis Cuti</label>
                    <p class="form-control-static">
                        @if($cutiKaryawan->masterCuti)
                            {{ $cutiKaryawan->masterCuti->uraian }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Bukti Pendukung</label>
            <div>
                @if($cutiKaryawan->bukti)
                <a href="{{ asset('storage/cuti/bukti/' . $cutiKaryawan->bukti) }}"
                   class="btn btn-sm btn-info" target="_blank">
                    <i class="fas fa-file-download mr-1"></i> Lihat Dokumen
                </a>
                @else
                <a href="{{ asset('storage/cuti_karyawan/dokumen/' . $cuti_karyawan->dokumen_pendukung) }}"
                   class="btn btn-sm btn-info" target="_blank">
                    <i class="fas fa-file-download mr-1"></i> Lihat Dokumen
                </a>
            </div>
        </div>
        @endif

        <div class="form-group">
            <label>Status</label>
            <p class="form-control-static">
                @if($cuti_karyawan->status == 'pending')
                <span class="badge badge-warning">Menunggu Persetujuan</span>
                @elseif($cuti_karyawan->status == 'approved')
                <span class="badge badge-success">Disetujui</span>
                @elseif($cuti_karyawan->status == 'rejected')
                <span class="badge badge-danger">Ditolak</span>
                @endif
            </p>
        </div>

        @if($cuti_karyawan->status == 'pending')
        <form action="{{ route('cuti_karyawans.update_status', $cuti_karyawan->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="catatan">Catatan (opsional)</label>
                <textarea name="catatan" id="catatan" class="form-control" rows="3">{{ old('catatan') }}</textarea>
            </div>
            <div class="form-group">
                <button type="submit" name="status" value="approved" class="btn btn-success mr-2">
                    <i class="fas fa-check mr-1"></i> Setujui
                </button>
                <button type="submit" name="status" value="rejected" class="btn btn-danger">
                    <i class="fas fa-times mr-1"></i> Tolak
                </button>
            </div>
        </form>
        @else
        <div class="form-group">
            <label>Catatan</label>
            <p class="form-control-static">{{ $cuti_karyawan->catatan ?? '-' }}</p>
        </div>
        <div class="form-group">
            <label>Disetujui/Ditolak Oleh</label>
            <p class="form-control-static">{{ $cuti_karyawan->approved_by_user->name ?? '-' }}</p>
        </div>
        <div class="form-group">
            <label>Tanggal Persetujuan</label>
            <p class="form-control-static">
                {{ $cuti_karyawan->approved_at ? \Carbon\Carbon::parse($cuti_karyawan->approved_at)->format('d-m-Y H:i') : '-' }}
            </p>
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .form-control-static {
        font-weight: 500;
        padding: 7px 12px;
        background-color: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 0;
    }
    .badge {
        font-size: 90%;
        padding: 0.4em 0.6em;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Confirm before rejecting
        $('button[value="rejected"]').click(function(e) {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin menolak pengajuan cuti ini?')) {
                $(this).closest('form').submit();
            }
        });
    });
</script>
@stop
