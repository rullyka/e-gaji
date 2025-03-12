@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-user-edit text-primary mr-2"></i>Edit User</h1>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Back to Users List
    </a>
</div>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-cog mr-1"></i> User Information</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-id-card mr-1"></i> Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name"><i class="fas fa-user mr-1"></i> Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Enter full name" required>
                                <small class="form-text text-muted">Enter the user's full name as it will appear in the system.</small>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope mr-1"></i> Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    </div>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="example@company.com" required>
                                </div>
                                <small class="form-text text-muted">This email will be used for login and notifications.</small>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock mr-1"></i> Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Leave blank to keep current password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Only fill this if you want to change the password.</small>
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
                            <h3 class="card-title"><i class="fas fa-user-tag mr-1"></i> User Role & Type</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><i class="fas fa-user-shield mr-1"></i> Roles <span class="text-danger">*</span></label>
                                <div class="select2-purple">
                                    <select name="roles[]" class="select2 @error('roles') is-invalid @enderror" multiple="multiple" data-placeholder="Select roles" style="width: 100%;" required>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ in_array($role->name, $user->roles->pluck('name')->toArray()) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="form-text text-muted">Select one or more roles to assign to this user. Roles determine permissions.</small>
                                @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="user_type"><i class="fas fa-users-cog mr-1"></i> User Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('user_type') is-invalid @enderror" id="user_type" name="user_type" required>
                                    <option value="">-- Select User Type --</option>
                                    <option value="owner" {{ old('user_type', $userType) == 'owner' ? 'selected' : '' }}>Owner / Admin</option>
                                    <option value="karyawan" {{ old('user_type', $userType) == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                                </select>
                                <small class="form-text text-muted">Owner/Admin can access all features. Karyawan has limited access.</small>
                                @error('user_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="karyawan_section" style="display: none;">
                <div class="card card-outline card-warning mt-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-badge mr-1"></i> Employee Information</h3>
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
                                    <label for="departemen_id"><i class="fas fa-building mr-1"></i> Department <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('departemen_id') is-invalid @enderror" id="departemen_id" name="departemen_id">
                                        <option value="">-- Select Department --</option>
                                        @foreach($departemen as $dept)
                                        <option value="{{ $dept->id }}" {{ old('departemen_id', isset($karyawan) && $karyawan->departemen_id ? $karyawan->departemen_id : '') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name_departemen }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select the department where this employee works.</small>
                                    @error('departemen_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="karyawan_id"><i class="fas fa-user-tie mr-1"></i> Select Employee <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('karyawan_id') is-invalid @enderror" id="karyawan_id" name="karyawan_id" {{ old('departemen_id', isset($karyawan) && $karyawan->departemen_id ? $karyawan->departemen_id : '') ? '' : 'disabled' }}>
                                        <option value="">-- {{ old('departemen_id', isset($karyawan) && $karyawan->departemen_id ? $karyawan->departemen_id : '') ? 'Select Employee' : 'Select Department First' }} --</option>
                                        @if(isset($karyawan) && $karyawan)
                                        <option value="{{ $karyawan->id }}" selected>{{ $karyawan->nik_karyawan }} - {{ $karyawan->nama_karyawan }}</option>
                                        @endif
                                    </select>
                                    <small class="form-text text-muted">Link this user account to an existing employee record.</small>
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
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save mr-1"></i> Update User
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-lg px-5 ml-2">
                    <i class="fas fa-times mr-1"></i> Cancel
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

                // Clear current options except the selected one
                var selectedKaryawan = $('#karyawan_id').val();
                $('#karyawan_id').empty().append('<option value="">-- Select Employee --</option>');

                // Tampilkan karyawan dari data yang sudah disiapkan
                var karyawanList = @json($karyawanByDepartemen);

                if (karyawanList[departemenId] && karyawanList[departemenId].length > 0) {
                    // Populate karyawan dropdown
                    $.each(karyawanList[departemenId], function(key, value) {
                        $('#karyawan_id').append('<option value="' + value.id + '"' +
                            (selectedKaryawan == value.id ? ' selected' : '') + '>' +
                            value.nik_karyawan + ' - ' + value.nama_karyawan + '</option>');
                    });
                    $('#karyawan_id').prop('required', true);
                } else {
                    $('#karyawan_id').append('<option value="">No employees in this department</option>');
                }
            } else {
                $('#karyawan_id').prop('disabled', true);
                $('#karyawan_id').empty().append('<option value="">-- Select Department First --</option>');
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

        // Add animation to form submission
        $('form').on('submit', function() {
            // Validate form before animation
            if (this.checkValidity()) {
                // Add loading state to submit button
                $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...').attr('disabled', true);

                // Add fade effect to form
                $(this).find('.card').css('opacity', '0.7');

                return true;
            }
        });

        // Add tooltip to all buttons with title attribute
        $('[title]').tooltip({
            placement: 'top',
            trigger: 'hover'
        });

        // Highlight fields on focus
        $('.form-control').on('focus', function() {
            $(this).closest('.form-group').addClass('border-left border-primary pl-2');
        }).on('blur', function() {
            $(this).closest('.form-group').removeClass('border-left border-primary pl-2');
        });

        // Password strength indicator
        $('#password').on('keyup', function() {
            var password = $(this).val();
            var strength = 0;

            // Check password length
            if (password.length >= 8) strength += 1;

            // Check for mixed case
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;

            // Check for numbers
            if (password.match(/\d/)) strength += 1;

            // Check for special characters
            if (password.match(/[^a-zA-Z\d]/)) strength += 1;

            // Update strength indicator
            var strengthBar = '';
            for (var i = 0; i < strength; i++) {
                strengthBar += '<span class="text-success"><i class="fas fa-star"></i></span>';
            }
            for (var i = strength; i < 4; i++) {
                strengthBar += '<span class="text-muted"><i class="far fa-star"></i></span>';
            }

            // Show strength indicator
            if (password.length > 0) {
                $(this).closest('.form-group').find('.form-text').html(
                    'Password strength: ' + strengthBar +
                    (strength < 3 ? ' <small class="text-danger">(Make it stronger!)</small>' : ' <small class="text-success">(Good!)</small>')
                );
            } else {
                $(this).closest('.form-group').find('.form-text').html(
                    'Password should be at least 8 characters and include letters and numbers.'
                );
            }
        });
    });
</script>
@stop
        // Trigger change event on page load
