@extends('adminlte::page')

@section('title', 'Persetujuan Cuti Karyawan')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="mr-2 fas fa-calendar-check text-primary"></i>Persetujuan Cuti Karyawan</h1>
    <a href="{{ route('cuti_karyawans.index') }}" class="btn btn-secondary">
        <i class="mr-1 fas fa-arrow-left"></i> Kembali
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
                    <i class="mr-1 fas fa-file-download"></i> Lihat Dokumen
                </a>
                @else
                <a href="{{ asset('storage/cuti_karyawan/dokumen/' . $cutiKaryawan->dokumen_pendukung) }}"
                   class="btn btn-sm btn-info" target="_blank">
                    <i class="mr-1 fas fa-file-download"></i> Lihat Dokumen
                </a>
                </div>
                </div>
                @endif

                <div class="form-group">
                    <label>Status</label>
                    <p class="form-control-static">
                        @if($cutiKaryawan->status_acc == 'Menunggu Persetujuan')
                        <span class="badge badge-warning">Menunggu Persetujuan</span>
                        @elseif($cutiKaryawan->status_acc == 'Disetujui')
                        <span class="badge badge-success">Disetujui</span>
                        @elseif($cutiKaryawan->status_acc == 'Ditolak')
                        <span class="badge badge-danger">Ditolak</span>
                        @endif
                    </p>
                </div>

                @if($cutiKaryawan->status_acc == 'Menunggu Persetujuan')
                <form action="{{ route('cuti_karyawans.approve', $cutiKaryawan->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="status_acc">Status Persetujuan</label>
                        <select name="status_acc" id="status_acc" class="form-control" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>

                    <div class="form-group" id="cuti_disetujui_group">
                        <label for="cuti_disetujui">Jumlah Hari Disetujui</label>
                        <input type="number" name="cuti_disetujui" id="cuti_disetujui" class="form-control"
                               min="1" max="{{ $cutiKaryawan->jumlah_hari_cuti }}" value="{{ $cutiKaryawan->jumlah_hari_cuti }}">
                    </div>

                    <div class="form-group" id="keterangan_tolak_group" style="display: none;">
                        <label for="keterangan_tolak">Alasan Penolakan</label>
                        <textarea name="keterangan_tolak" id="keterangan_tolak" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="mr-1 fas fa-save"></i> Simpan Keputusan
                        </button>
                    </div>
                </form>
                @else
                <div class="form-group">
                    <label>Catatan</label>
                    <p class="form-control-static">{{ $cutiKaryawan->keterangan_tolak ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <label>Disetujui/Ditolak Oleh</label>
                    <p class="form-control-static">{{ $cutiKaryawan->approver->nama_karyawan ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <label>Tanggal Persetujuan</label>
                    <p class="form-control-static">
                        {{ $cutiKaryawan->tanggal_approval ? \Carbon\Carbon::parse($cutiKaryawan->tanggal_approval)->format('d-m-Y H:i') : '-' }}
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
        // Toggle fields based on approval status
        $('#status_acc').change(function() {
            if ($(this).val() == 'Disetujui') {
                $('#cuti_disetujui_group').show();
                $('#keterangan_tolak_group').hide();
                $('#keterangan_tolak').removeAttr('required');
                $('#cuti_disetujui').attr('required', 'required');
            } else if ($(this).val() == 'Ditolak') {
                $('#cuti_disetujui_group').hide();
                $('#keterangan_tolak_group').show();
                $('#cuti_disetujui').removeAttr('required');
                $('#keterangan_tolak').attr('required', 'required');
            } else {
                $('#cuti_disetujui_group').hide();
                $('#keterangan_tolak_group').hide();
            }
        });

        // Trigger the change event on page load to set initial state
        $('#status_acc').trigger('change');
    });
</script>
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
