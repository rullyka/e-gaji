@extends('adminlte::page')

@section('title', 'Tambah Karyawan')

@section('content_header')
<h1>Tambah Karyawan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Karyawan</h3>
        <div class="card-tools">
            <a href="{{ route('karyawans.index') }}" class="btn btn-default btn-sm">
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

        <form action="{{ route('karyawans.store') }}" method="POST" enctype="multipart/form-data" id="karyawanForm">
            @csrf

            <!-- Smart Wizard -->
            <div id="smartwizard">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#step-1">
                            <div class="num">1</div>
                            Data Pribadi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#step-2">
                            <div class="num">2</div>
                            Data Pekerjaan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#step-3">
                            <div class="num">3</div>
                            Data Pendidikan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#step-4">
                            <div class="num">4</div>
                            Data Tambahan
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Step 1: Data Pribadi -->
                    <div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nik_karyawan">NIK Karyawan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('nik_karyawan') is-invalid @enderror" id="nik_karyawan" name="nik_karyawan" value="{{ old('nik_karyawan', $nikKaryawan) }}" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="refreshNik">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Format: YYYYMM### (otomatis digenerate, dapat diedit)</small>
                                    @error('nik_karyawan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="nama_karyawan">Nama Karyawan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_karyawan') is-invalid @enderror" id="nama_karyawan" name="nama_karyawan" value="{{ old('nama_karyawan') }}" required>
                                    @error('nama_karyawan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="foto_karyawan">Foto Karyawan</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('foto_karyawan') is-invalid @enderror" id="foto_karyawan" name="foto_karyawan">
                                            <label class="custom-file-label" for="foto_karyawan">Pilih file</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Format: JPG, PNG, JPEG. Max: 2MB.</small>
                                    @error('foto_karyawan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tgl_awalmmasuk">Tanggal Mulai Masuk <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tgl_awalmmasuk') is-invalid @enderror" id="tgl_awalmmasuk" name="tgl_awalmmasuk" value="{{ old('tgl_awalmmasuk', date('Y-m-d')) }}" required>
                                    @error('tgl_awalmmasuk')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="tahun_keluar">Tanggal Keluar</label>
                                    <input type="date" class="form-control @error('tahun_keluar') is-invalid @enderror" id="tahun_keluar" name="tahun_keluar" value="{{ old('tahun_keluar') }}">
                                    @error('tahun_keluar')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="statuskaryawan">Status Karyawan <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('statuskaryawan') is-invalid @enderror" id="statuskaryawan" name="statuskaryawan" required>
                                        <option value="">-- Pilih Status --</option>
                                        @foreach($statusKaryawan as $status)
                                        <option value="{{ $status }}" {{ old('statuskaryawan') == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('statuskaryawan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Data Pekerjaan -->
                    <div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_departemen">Departemen</label>
                                    <select class="form-control select2 @error('id_departemen') is-invalid @enderror" id="id_departemen" name="id_departemen">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach($departemens as $departemen)
                                        <option value="{{ $departemen->id }}" {{ old('id_departemen') == $departemen->id ? 'selected' : '' }}>
                                            {{ $departemen->name_departemen }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('id_departemen')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="id_bagian">Bagian</label>
                                    <select class="form-control select2 @error('id_bagian') is-invalid @enderror" id="id_bagian" name="id_bagian">
                                        <option value="">-- Pilih Bagian --</option>
                                        @foreach($bagians as $bagian)
                                        <option value="{{ $bagian->id }}" data-departemen="{{ $bagian->id_departemen }}" {{ old('id_bagian') == $bagian->id ? 'selected' : '' }}>
                                            {{ $bagian->name_bagian }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('id_bagian')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_jabatan">Jabatan</label>
                                    <select class="form-control select2 @error('id_jabatan') is-invalid @enderror" id="id_jabatan" name="id_jabatan">
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach($jabatans as $jabatan)
                                        <option value="{{ $jabatan->id }}" {{ old('id_jabatan') == $jabatan->id ? 'selected' : '' }}>
                                            {{ $jabatan->name_jabatan }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('id_jabatan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="id_profesi">Profesi</label>
                                    <select class="form-control select2 @error('id_profesi') is-invalid @enderror" id="id_profesi" name="id_profesi">
                                        <option value="">-- Pilih Profesi --</option>
                                        @foreach($profesis as $profesi)
                                        <option value="{{ $profesi->id }}" {{ old('id_profesi') == $profesi->id ? 'selected' : '' }}>
                                            {{ $profesi->name_profesi }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('id_profesi')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Data Pendidikan -->
                    <div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pendidikan_terakhir">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('pendidikan_terakhir') is-invalid @enderror" id="pendidikan_terakhir" name="pendidikan_terakhir" required>
                                        <option value="">-- Pilih Pendidikan --</option>
                                        @foreach($pendidikanTerakhir as $pendidikan)
                                        <option value="{{ $pendidikan }}" {{ old('pendidikan_terakhir') == $pendidikan ? 'selected' : '' }}>
                                            {{ $pendidikan }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('pendidikan_terakhir')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="id_programstudi">Program Studi</label>
                                    <select class="form-control select2 @error('id_programstudi') is-invalid @enderror" id="id_programstudi" name="id_programstudi">
                                        <option value="">-- Pilih Program Studi --</option>
                                        @foreach($programStudis as $programStudi)
                                        <option value="{{ $programStudi->id }}" {{ old('id_programstudi') == $programStudi->id ? 'selected' : '' }}>
                                            {{ $programStudi->name_programstudi }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('id_programstudi')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nik">NIK (KTP) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}" required>
                                    @error('nik')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="kk">Nomor KK <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kk') is-invalid @enderror" id="kk" name="kk" value="{{ old('kk') }}" required>
                                    @error('kk')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="upload_ktp">Upload KTP</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('upload_ktp') is-invalid @enderror" id="upload_ktp" name="upload_ktp">
                                            <label class="custom-file-label" for="upload_ktp">Pilih file</label>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Format: JPG, PNG, JPEG. Max: 2MB.</small>
                                    @error('upload_ktp')
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
                                    <label for="statuskawin">Status Perkawinan <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('statuskawin') is-invalid @enderror" id="statuskawin" name="statuskawin" required>
                                        <option value="">-- Pilih Status --</option>
                                        @foreach($statusKawin as $status)
                                        <option value="{{ $status }}" {{ old('statuskawin') == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('statuskawin')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="jml_anggotakk">Jumlah Anggota KK <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('jml_anggotakk') is-invalid @enderror" id="jml_anggotakk" name="jml_anggotakk" value="{{ old('jml_anggotakk') }}" required>
                                    @error('jml_anggotakk')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Data Tambahan -->
                    <div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ortu_bapak">Nama Ayah <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('ortu_bapak') is-invalid @enderror" id="ortu_bapak" name="ortu_bapak" value="{{ old('ortu_bapak') }}" required>
                                    @error('ortu_bapak')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="ortu_ibu">Nama Ibu <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('ortu_ibu') is-invalid @enderror" id="ortu_ibu" name="ortu_ibu" value="{{ old('ortu_ibu') }}" required>
                                    @error('ortu_ibu')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="no_hp">Nomor HP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" required>
                                    @error('no_hp')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ukuran_kemeja">Ukuran Kemeja <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('ukuran_kemeja') is-invalid @enderror" id="ukuran_kemeja" name="ukuran_kemeja" required>
                                        <option value="">-- Pilih Ukuran --</option>
                                        @foreach($ukuranKemeja as $ukuran)
                                        <option value="{{ $ukuran }}" {{ old('ukuran_kemeja') == $ukuran ? 'selected' : '' }}>
                                            {{ $ukuran }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('ukuran_kemeja')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="ukuran_celana">Ukuran Celana <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('ukuran_celana') is-invalid @enderror" id="ukuran_celana" name="ukuran_celana" value="{{ old('ukuran_celana') }}" required>
                                    @error('ukuran_celana')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="ukuran_sepatu">Ukuran Sepatu <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('ukuran_sepatu') is-invalid @enderror" id="ukuran_sepatu" name="ukuran_sepatu" value="{{ old('ukuran_sepatu') }}" required>
                                    @error('ukuran_sepatu')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 alert alert-info">
                            <i class="fas fa-info-circle"></i> Silakan periksa kembali semua data sebelum menyimpan.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<!-- WizardJS CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/css/smart_wizard_all.min.css">
<style>
    /* Custom wizard styles matching the CodePen example */
    .sw {
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .sw .tab-content {
        padding: 20px;
        border: 1px solid #eeeeee;
        background: #ffffff;
        min-height: 300px;
    }

    .sw .nav-tabs {
        border: none !important;
        background-color: #f8f9fa;
        padding: 10px 10px 0 10px;
    }

    .sw .nav-tabs .nav-link {
        border: none;
        background: transparent;
        padding: 10px 20px;
        border-radius: 0;
        margin-right: 5px;
        font-weight: 600;
        color: #666;
        border-bottom: 3px solid transparent;
    }

    .sw .nav-tabs .nav-link .num {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #ddd;
        margin-right: 10px;
        color: #666;
        font-weight: 600;
    }

    .sw .nav-tabs .nav-link.active {
        border-bottom-color: #007bff;
        color: #007bff;
    }

    .sw .nav-tabs .nav-link.active .num {
        background-color: #007bff;
        color: #fff;
    }

    .sw .nav-tabs .nav-link.done {
        border-bottom-color: #28a745;
        color: #28a745;
    }

    .sw .nav-tabs .nav-link.done .num {
        background-color: #28a745;
        color: #fff;
    }

    .sw .toolbar {
        padding: 10px;
        background-color: #ffffff;
        text-align: right;
        margin-top: 10px;
    }

    .sw .btn-toolbar-next,
    .sw .btn-toolbar-prev,
    .sw .btn-toolbar-reset {
        margin-left: 5px;
        border-radius: 3px;
    }

    .sw .btn-toolbar-next,
    .sw .btn-toolbar-reset {
        background-color: #007bff;
        color: #fff;
    }

    .sw .btn-toolbar-prev {
        border: 1px solid #007bff;
        color: #007bff;
        background-color: transparent;
    }

    /* Fix for the tabs display */
    .sw .nav-tabs {
        display: flex;
    }

    .sw .nav-item {
        flex: 1;
    }

    .sw .nav-link {
        display: flex;
        align-items: center;
        justify-content: center;
    }

</style>
@stop

@section('js')
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('vendor/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<!-- WizardJS Script -->
<script src="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/js/jquery.smartWizard.min.js"></script>
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4'
            , width: '100%'
        });

        // Initialize custom file input
        bsCustomFileInput.init();

        // Department-based filtering for bagian dropdown
        $('#id_departemen').on('change', function() {
            var departemenId = $(this).val();
            var bagianSelect = $('#id_bagian');

            // Reset bagian selection
            bagianSelect.val('').trigger('change');

            if (departemenId) {
                // Enable only bagian that belongs to the selected department
                bagianSelect.find('option').each(function() {
                    var bagianOption = $(this);
                    if (bagianOption.data('departemen') == departemenId || bagianOption.val() == '') {
                        bagianOption.prop('disabled', false);
                    } else {
                        bagianOption.prop('disabled', true);
                    }
                });
            } else {
                // If no department is selected, enable all bagian options
                bagianSelect.find('option').prop('disabled', false);
            }

            bagianSelect.select2('destroy').select2({
                theme: 'bootstrap4'
                , width: '100%'
            });
        });

        // Initialize SmartWizard
        $('#smartwizard').smartWizard({
            selected: 0, // Initial selected step
            theme: 'default', // Theme (default, arrows, dots, progress)
            justified: true, // Justifies the steps
            darkMode: false, // Dark mode
            autoAdjustHeight: true, // Automatically adjust content height
            cycleSteps: false, // Cycle steps
            backButtonSupport: true, // Back button support
            enableURLhash: false, // Enable URL hash navigation
            transition: {
                animation: 'fade', // Animation effect (none/fade/slide-horizontal/slide-vertical)
                speed: '400', // Animation speed
                easing: '' // Easing type
            }
            , toolbarSettings: {
                toolbarPosition: 'bottom', // Top or bottom
                toolbarButtonPosition: 'right', // Left or right
                showNextButton: true, // Show/hide next button
                showPreviousButton: true, // Show/hide previous button
                toolbarExtraButtons: [
                    $('<button type="submit" class="btn btn-success btn-finish">Simpan</button>')
                ]
            }
            , anchorSettings: {
                anchorClickable: true, // Enable/disable anchor clicking
                enableAllAnchors: false, // Enable/disable all anchors
                markDoneStep: true, // Mark done steps
                markAllPreviousStepsAsDone: true, // Mark all previous steps as done
                removeDoneStepOnNavigateBack: false, // Remove done step status on navigate back
                enableAnchorOnDoneStep: true // Enable/disable done steps navigation
            }
            , keyboardSettings: {
                keyNavigation: true, // Enable/disable keyboard navigation
                keyLeft: [37], // Left key code
                keyRight: [39], // Right key code
            }
            , lang: { // Language variables for button text
                next: 'Selanjutnya'
                , previous: 'Sebelumnya'
            }
        });

        // Get NIK Karyawan via Ajax
        $('#refreshNik').on('click', function() {
            $.ajax({
                url: "{{ route('karyawans.get-nik') }}"
                , type: "GET"
                , dataType: "json"
                , success: function(response) {
                    $('#nik_karyawan').val(response.nik_karyawan);
                }
                , error: function(xhr, status, error) {
                    console.error("Error getting NIK Karyawan:", error);
                    alert("Gagal mendapatkan NIK Karyawan baru. Silakan coba lagi.");
                }
            });
        });
    });

</script>
@stop
