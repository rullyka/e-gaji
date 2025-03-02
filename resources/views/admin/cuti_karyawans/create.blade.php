@extends('adminlte::page')

@section('title', 'Ajukan Cuti')

@section('content_header')
<h1>Ajukan Cuti</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Pengajuan Cuti</h3>
        <div class="card-tools">
            <a href="{{ route('cuti_karyawans.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <form action="{{ route('cuti_karyawans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_karyawan">Karyawan <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('id_karyawan') is-invalid @enderror" id="id_karyawan" name="id_karyawan" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ old('id_karyawan') == $karyawan->id ? 'selected' : '' }}>
                                {{ $karyawan->nik_karyawan }} - {{ $karyawan->nama_karyawan }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_karyawan')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="jenis_cuti">Jenis Cuti <span class="text-danger">*</span></label>
                        <select class="form-control @error('jenis_cuti') is-invalid @enderror" id="jenis_cuti" name="jenis_cuti" required>
                            <option value="">-- Pilih Jenis Cuti --</option>
                            @foreach($jenisCuti as $jenis)
                            <option value="{{ $jenis }}" {{ old('jenis_cuti') == $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                            @endforeach
                        </select>
                        @error('jenis_cuti')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tanggal_mulai_cuti">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_mulai_cuti') is-invalid @enderror" id="tanggal_mulai_cuti" name="tanggal_mulai_cuti" value="{{ old('tanggal_mulai_cuti') }}" required>
                        @error('tanggal_mulai_cuti')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tanggal_akhir_cuti">Tanggal Akhir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_akhir_cuti') is-invalid @enderror" id="tanggal_akhir_cuti" name="tanggal_akhir_cuti" value="{{ old('tanggal_akhir_cuti') }}" required>
                        @error('tanggal_akhir_cuti')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Jumlah Hari</label>
                        <div class="alert alert-info" id="jumlah-hari">
                            <span id="jumlah-hari-text">-</span> hari
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="master_cuti_id">Jenis Master Cuti</label>
                        <select class="form-control select2 @error('master_cuti_id') is-invalid @enderror" id="master_cuti_id" name="master_cuti_id">
                            <option value="">-- Pilih Master Cuti --</option>
                            @foreach($masterCutis as $masterCuti)
                            <option value="{{ $masterCuti->id }}" {{ old('master_cuti_id') == $masterCuti->id ? 'selected' : '' }}>
                                {{ $masterCuti->uraian }} (Max: {{ $masterCuti->cuti_max }})
                            </option>
                            @endforeach
                        </select>
                        @error('master_cuti_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="id_supervisor">Supervisor <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('id_supervisor') is-invalid @enderror" id="id_supervisor" name="id_supervisor" required>
                            <option value="">-- Pilih Supervisor --</option>
                            @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ old('id_supervisor') == $karyawan->id ? 'selected' : '' }}>
                                {{ $karyawan->nik_karyawan }} - {{ $karyawan->nama_karyawan }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_supervisor')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="bukti">Bukti Pendukung <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('bukti') is-invalid @enderror" id="bukti" name="bukti" required>
                                <label class="custom-file-label" for="bukti">Pilih file</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">Format: JPG, PNG, PDF. Max: 2MB.</small>
                        @error('bukti')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group mt-4">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> Pastikan data yang diinput sudah benar sebelum mengajukan cuti.
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Ajukan Cuti
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('vendor/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4'
            , width: '100%'
        });

        // Initialize custom file input
        bsCustomFileInput.init();

        // Calculate jumlah hari when dates change
        $('#tanggal_mulai_cuti, #tanggal_akhir_cuti').on('change', function() {
            calculateDays();
        });

        function calculateDays() {
            var startDate = $('#tanggal_mulai_cuti').val();
            var endDate = $('#tanggal_akhir_cuti').val();

            if (startDate && endDate) {
                var start = new Date(startDate);
                var end = new Date(endDate);

                // Calculate difference in days
                var timeDiff = Math.abs(end.getTime() - start.getTime());
                var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Including both start and end days

                // Update the display
                $('#jumlah-hari-text').text(diffDays);
            } else {
                $('#jumlah-hari-text').text('-');
            }
        }
    });

</script>
@stop
