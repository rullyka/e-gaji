@extends('adminlte::page')

@section('title', 'Persetujuan Cuti Karyawan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark"><i class="fas fa-calendar-check text-primary"></i> Persetujuan Cuti</h1>
        <a href="{{ route('cuti_karyawans.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <!-- Employee Information -->
                <div class="col-lg-4">
                    <div class="p-3 bg-light rounded">
                        <h5 class="border-bottom pb-2">Informasi Karyawan</h5>
                        <div class="mb-3">
                            <small class="text-muted d-block">Nama Karyawan</small>
                            <strong>{{ $cutiKaryawan->karyawan->nama_karyawan }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">NIK</small>
                            <strong>{{ $cutiKaryawan->karyawan->nik_karyawan }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Departemen</small>
                            <strong>{{ $cutiKaryawan->karyawan->departemen->name_departemen ?? '-' }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Bagian</small>
                            <strong>{{ $cutiKaryawan->karyawan->bagian->name_bagian ?? '-' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Leave Details -->
                <div class="col-lg-4">
                    <div class="p-3 bg-light rounded">
                        <h5 class="border-bottom pb-2">Detail Cuti</h5>
                        <div class="mb-3">
                            <small class="text-muted d-block">Jenis Cuti</small>
                            <strong>{{ $cutiKaryawan->masterCuti->uraian ?? $cutiKaryawan->jenis_cuti }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Periode Cuti</small>
                            <strong>{{ \Carbon\Carbon::parse($cutiKaryawan->tanggal_mulai_cuti)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($cutiKaryawan->tanggal_akhir_cuti)->format('d M Y') }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Jumlah Hari</small>
                            <strong>{{ $cutiKaryawan->jumlah_hari_cuti }} hari</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Status</small>
                            @if ($cutiKaryawan->status_acc == 'Menunggu Persetujuan')
                                <span class="badge badge-warning">Menunggu Persetujuan</span>
                            @elseif($cutiKaryawan->status_acc == 'Disetujui')
                                <span class="badge badge-success">Disetujui</span>
                            @else
                                <span class="badge badge-danger">Ditolak</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Approval Section -->
                <div class="col-lg-4">
                    <div class="p-3 bg-light rounded">
                        <h5 class="border-bottom pb-2">Persetujuan</h5>
                        @if ($cutiKaryawan->status_acc == 'Menunggu Persetujuan')
                            <form action="{{ route('cuti_karyawans.approve', $cutiKaryawan->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <select name="status_acc" id="status_acc" class="form-control form-control-sm">
                                        <option value="">-- Pilih Keputusan --</option>
                                        <option value="Disetujui">Disetujui</option>
                                        <option value="Ditolak">Ditolak</option>
                                    </select>
                                </div>

                                <div id="cuti_disetujui_group">
                                    <div class="form-group">
                                        <input type="number" name="cuti_disetujui" id="cuti_disetujui"
                                            class="form-control form-control-sm" placeholder="Jumlah hari disetujui"
                                            min="1" max="{{ $cutiKaryawan->jumlah_hari_cuti }}"
                                            value="{{ $cutiKaryawan->jumlah_hari_cuti }}">
                                    </div>
                                </div>

                                <div id="keterangan_tolak_group" style="display: none;">
                                    <div class="form-group">
                                        <textarea name="keterangan_tolak" id="keterangan_tolak" class="form-control form-control-sm" rows="3"
                                            placeholder="Alasan penolakan"></textarea>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-check-circle"></i> Simpan Keputusan
                                </button>
                            </form>
                        @else
                            <div class="mb-3">
                                <small class="text-muted d-block">Catatan</small>
                                <strong>{{ $cutiKaryawan->keterangan_tolak ?? '-' }}</strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Disetujui/Ditolak Oleh</small>
                                <strong>{{ $cutiKaryawan->approver->nama_karyawan ?? '-' }}</strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Tanggal Keputusan</small>
                                <strong>{{ $cutiKaryawan->tanggal_approval ? \Carbon\Carbon::parse($cutiKaryawan->tanggal_approval)->format('d M Y H:i') : '-' }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($cutiKaryawan->bukti || $cutiKaryawan->dokumen_pendukung)
                <div class="mt-4">
                    <h5 class="border-bottom pb-2">Dokumen Pendukung</h5>
                    @if ($cutiKaryawan->bukti)
                        <a href="{{ asset('storage/cuti/bukti/' . $cutiKaryawan->bukti) }}"
                            class="btn btn-outline-info btn-sm" target="_blank">
                            <i class="fas fa-file-download"></i> Lihat Dokumen
                        </a>
                    @else
                        <a href="{{ asset('storage/cuti_karyawan/dokumen/' . $cutiKaryawan->dokumen_pendukung) }}"
                            class="btn btn-outline-info btn-sm" target="_blank">
                            <i class="fas fa-file-download"></i> Lihat Dokumen
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            border: none;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        small.text-muted {
            font-size: 12px;
        }

        strong {
            display: block;
            margin-top: 4px;
        }

        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }

        .form-control-sm {
            height: calc(1.8125rem + 2px);
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }
    </style>
@stop
