@extends('adminlte::page')

@section('title', 'Edit Mesin Absensi')

@section('content_header')
<h1>Edit Mesin Absensi</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Edit Mesin Absensi</h3>
        <div class="card-tools">
            <a href="{{ route('mesinabsensis.index') }}" class="btn btn-default btn-sm">
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

        <form action="{{ route('mesinabsensis.update', $cabang) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nama">Nama Mesin Absensi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $cabang->nama) }}" required>
                        @error('nama')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="alamat_ip">Alamat IP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('alamat_ip') is-invalid @enderror" id="alamat_ip" name="alamat_ip" value="{{ old('alamat_ip', $cabang->alamat_ip) }}" required>
                        @error('alamat_ip')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="kunci_komunikasi">Kunci Komunikasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kunci_komunikasi') is-invalid @enderror" id="kunci_komunikasi" name="kunci_komunikasi" value="{{ old('kunci_komunikasi', $cabang->kunci_komunikasi) }}" required>
                        @error('kunci_komunikasi')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="lokasi">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi" name="lokasi" value="{{ old('lokasi', $cabang->lokasi) }}" required>
                        @error('lokasi')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status_aktif">Status</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="status_aktif" name="status_aktif" value="1" {{ old('status_aktif', $cabang->status_aktif) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="status_aktif">Aktif</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $cabang->keterangan) }}</textarea>
                        @error('keterangan')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="{{ route('mesinabsensis.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<!-- Additional CSS -->
@stop

@section('js')
<script>
    $(function() {
        // Additional JavaScript if needed
    });

</script>
@stop
