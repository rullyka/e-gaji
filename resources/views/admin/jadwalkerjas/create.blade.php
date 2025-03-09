@extends('adminlte::page')

@section('title', 'Tambah Jadwal Kerja')

@section('content_header')
<h1>Tambah Jadwal Kerja</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Jadwal Kerja</h3>
        <div class="card-tools">
            <a href="{{ route('jadwalkerjas.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

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

        <form action="{{ route('jadwalkerjas.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                        @error('tanggal_mulai')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tanggal_akhir">Tanggal Akhir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_akhir') is-invalid @enderror" id="tanggal_akhir" name="tanggal_akhir" value="{{ old('tanggal_akhir', date('Y-m-d')) }}" required>
                        @error('tanggal_akhir')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="karyawan_id">Karyawan <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('karyawan_id') is-invalid @enderror" id="karyawan_id" name="karyawan_id[]" multiple required>
                            @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ in_array($karyawan->id, old('karyawan_id', [])) ? 'selected' : '' }}>
                                {{ $karyawan->nama_karyawan }}
                            </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Bisa pilih lebih dari satu karyawan (multiple)</small>
                        @error('karyawan_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="shift_id">Shift/Jadwal <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('shift_id') is-invalid @enderror" id="shift_id" name="shift_id" required>
                            <option value="">-- Pilih Shift --</option>
                            @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                {{ $shift->jenis_shift }}: {{ $shift->jam_masuk->translatedFormat('H i') }} - {{ $shift->jam_pulang->translatedFormat('H i') }}
                            </option>
                            @endforeach
                        </select>
                        @error('shift_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Jadwal akan dibuat untuk setiap karyawan terpilih di setiap hari dalam rentang tanggal yang dipilih. Jadwal yang sudah ada tidak akan ditimpa.
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
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
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4'
            , width: '100%'
        });

        // Validate date range
        $('#tanggal_akhir').on('change', function() {
            var startDate = new Date($('#tanggal_mulai').val());
            var endDate = new Date($(this).val());

            if (endDate < startDate) {
                alert('Tanggal akhir harus sama dengan atau setelah tanggal mulai');
                $(this).val($('#tanggal_mulai').val());
            }
        });

        $('#tanggal_mulai').on('change', function() {
            var startDate = new Date($(this).val());
            var endDate = new Date($('#tanggal_akhir').val());

            if (endDate < startDate) {
                $('#tanggal_akhir').val($(this).val());
            }
        });
    });

</script>
@stop
