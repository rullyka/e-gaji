@extends('adminlte::page')

@section('title', 'Tambah User Baru')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="mr-2 fas fa-user-plus text-primary"></i>Tambah User Baru</h1>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="mr-1 fas fa-arrow-left"></i> Kembali ke Daftar User
    </a>
</div>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="mr-1 fas fa-check-circle"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="mr-1 fas fa-exclamation-circle"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="mr-1 fas fa-user-edit"></i> Informasi User</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="mr-1 fas fa-id-card"></i> Informasi Dasar</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name"><i class="mr-1 fas fa-user"></i> Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required>
                                <small class="form-text text-muted">Masukkan nama lengkap user yang akan ditampilkan dalam sistem.</small>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email"><i class="mr-1 fas fa-envelope"></i> Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    </div>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="contoh@perusahaan.com" required>
                                </div>
                                <small class="form-text text-muted">Email ini akan digunakan untuk login dan pemberitahuan.</small>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password"><i class="mr-1 fas fa-lock"></i> Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan password yang aman" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Password harus minimal 8 karakter dan termasuk huruf dan angka.</small>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="mr-1 fas fa-user-tag"></i> Peran & Tipe User</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><i class="mr-1 fas fa-user-shield"></i> Peran <span class="text-danger">*</span></label>
                                <div class="select2-purple">
                                    <select name="roles[]" class="select2 @error('roles') is-invalid @enderror" multiple="multiple" data-placeholder="Pilih peran" style="width: 100%;" required>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="form-text text-muted">Pilih satu atau lebih peran untuk diberikan pada user ini. Peran menentukan izin akses.</small>
                                @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="user_type"><i class="mr-1 fas fa-users-cog"></i> Tipe User <span class="text-danger">*</span></label>
                                <select class="form-control @error('user_type') is-invalid @enderror" id="user_type" name="user_type" required>
                                    <option value="">-- Pilih Tipe User --</option>
                                    <option value="owner" {{ old('user_type') == 'owner' ? 'selected' : '' }}>Pemilik / Admin</option>
                                    <option value="karyawan" {{ old('user_type') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                                </select>
                                <small class="form-text text-muted">Pemilik/Admin dapat mengakses semua fitur. Karyawan memiliki akses terbatas.</small>
                                @error('user_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="karyawan_section" style="display: none;">
                <div class="mt-3 card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="mr-1 fas fa-id-badge"></i> Informasi Karyawan</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="departemen_id"><i class="mr-1 fas fa-building"></i> Departemen <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('departemen_id') is-invalid @enderror" id="departemen_id" name="departemen_id">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach($departemen as $dept)
                                        <option value="{{ $dept->id }}" {{ old('departemen_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name_departemen }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Pilih departemen tempat karyawan ini bekerja.</small>
                                    @error('departemen_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="karyawan_id"><i class="mr-1 fas fa-user-tie"></i> Pilih Karyawan <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('karyawan_id') is-invalid @enderror" id="karyawan_id" name="karyawan_id" disabled>
                                        <option value="">-- Pilih Departemen Terlebih Dahulu --</option>
                                    </select>
                                    <small class="form-text text-muted">Hubungkan akun user ini ke data karyawan yang sudah ada.</small>
                                    @error('karyawan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <button type="submit" class="px-5 btn btn-primary btn-lg">
                    <i class="mr-1 fas fa-save"></i> Simpan User
                </button>
                <a href="{{ route('users.index') }}" class="px-5 ml-2 btn btn-secondary btn-lg">
                    <i class="mr-1 fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="/vendor/select2/css/select2.min.css">
<link rel="stylesheet" href="/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<style>
    .card {
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.125);
        background-color: rgba(0,0,0,0.03);
    }
    .form-control {
        border-radius: 4px;
    }
    .select2-container--bootstrap4 .select2-selection {
        border-radius: 4px;
    }
    .text-danger {
        font-weight: bold;
    }
    .form-text {
        font-size: 0.85rem;
    }
</style>
@stop


@section('js')
<script src="/vendor/select2/js/select2.full.min.js"></script>
<script>
    $(function() {
        // Initialize select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Toggle password visibility
        $('#togglePassword').click(function() {
            const passwordField = $('#password');
            const passwordFieldType = passwordField.attr('type');
            const newType = passwordFieldType === 'password' ? 'text' : 'password';
            passwordField.attr('type', newType);

            // Toggle icon
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        });

        // Toggle karyawan section based on user type
        $('#user_type').on('change', function() {
            var type = $(this).val();
            if (type === 'karyawan') {
                $('#karyawan_section').slideDown(300);
                $('#departemen_id').prop('required', true);
            } else {
                $('#karyawan_section').slideUp(300);
                $('#departemen_id').prop('required', false);
                $('#karyawan_id').prop('required', false);
            }
        });

        // Load karyawan based on selected departemen
        $('#departemen_id').on('change', function() {
            var departemenId = $(this).val();
            if (departemenId) {
                // Enable karyawan dropdown
                $('#karyawan_id').prop('disabled', false);

                // Clear current options
                $('#karyawan_id').empty().append('<option value="">-- Pilih Karyawan --</option>');

                // Tampilkan karyawan dari data yang sudah disiapkan
                var karyawanList = @json($karyawanByDepartemen);

                if (karyawanList[departemenId] && karyawanList[departemenId].length > 0) {
                    // Populate karyawan dropdown
                    $.each(karyawanList[departemenId], function(key, value) {
                        $('#karyawan_id').append('<option value="' + value.id + '">' +
                            value.nik_karyawan + ' - ' + value.nama_karyawan + '</option>');
                    });
                    $('#karyawan_id').prop('required', true);
                } else {
                    $('#karyawan_id').append('<option value="">Tidak ada karyawan di departemen ini</option>');
                }
            } else {
                $('#karyawan_id').prop('disabled', true);
                $('#karyawan_id').empty().append('<option value="">-- Pilih Departemen Terlebih Dahulu --</option>');
                $('#karyawan_id').prop('required', false);
            }
        });

        // Trigger change event on page load
        $('#user_type').trigger('change');

        // If departemen_id has a value on page load, trigger its change event
        var selectedDepartemen = "{{ old('departemen_id') }}";
        if (selectedDepartemen) {
            $('#departemen_id').trigger('change');
        }
    });
</script>
@stop