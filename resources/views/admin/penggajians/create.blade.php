@extends('adminlte::page')

@section('title', 'Tambah Penggajian')

@section('content_header')
    <h1>Tambah Penggajian</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Tambah Penggajian</h3>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form id="penggajianForm" action="{{ route('penggajian.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="periode_id">Periode Gaji <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('periode_id') is-invalid @enderror" id="periode_id" name="periode_id" required>
                                        <option value="">Pilih Periode</option>
                                        @foreach($periodeGajis as $periode)
                                            <option value="{{ $periode->id }}">{{ $periode->nama_periode }} ({{ $periode->tanggal_mulai->format('d/m/Y') }} - {{ $periode->tanggal_selesai->format('d/m/Y') }})</option>
                                        @endforeach
                                    </select>
                                    @error('periode_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="departemen_id">Filter Departemen</label>
                                    <select class="form-control select2" id="departemen_id">
                                        <option value="">Semua Departemen</option>
                                        @foreach($departemens as $departemen)
                                            <option value="{{ $departemen->id }}">{{ $departemen->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Filter Status</label>
                                    <select class="form-control select2" id="status">
                                        <option value="">Semua Status</option>
                                        @foreach($statusOptions as $status)
                                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" id="filterButton" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Filter Karyawan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 karyawan-list" style="display: none;">
                            <div class="card">
                                <div class="card-header bg-info">
                                    <h3 class="card-title">Daftar Karyawan</h3>
                                    <div class="card-tools">
                                        <span class="badge badge-light" id="karyawan-count">0 karyawan</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="karyawan-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">
                                                        <div class="icheck-primary">
                                                            <input type="checkbox" id="check-all">
                                                            <label for="check-all"></label>
                                                        </div>
                                                    </th>
                                                    <th>Nama</th>
                                                    <th>Departemen</th>
                                                    <th>Jabatan</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="karyawan-list-body">
                                                <!-- Karyawan list will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Proses Penggajian
                                    </button>
                                    <a href="{{ route('penggajian.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(function () {
            $('.select2').select2();

            $('#filterButton').click(function() {
                const periodeId = $('#periode_id').val();
                const departemenId = $('#departemen_id').val();
                const status = $('#status').val();

                if (!periodeId) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Silahkan pilih periode gaji terlebih dahulu',
                        icon: 'error'
                    });
                    return;
                }

                // Show loading
                Swal.fire({
                    title: 'Loading...',
                    text: 'Sedang memuat data karyawan',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Get filtered karyawan
                $.ajax({
                    url: "{{ route('penggajian.getFilteredKaryawans') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        periode_id: periodeId,
                        departemen_id: departemenId,
                        status: status
                    },
                    success: function(response) {
                        Swal.close();

                        if (response.success) {
                            const karyawans = response.data;
                            const count = response.count;

                            // Update count badge
                            $('#karyawan-count').text(count + ' karyawan');

                            // Clear previous list
                            $('#karyawan-list-body').empty();

                            if (count > 0) {
                                // Populate karyawan list
                                karyawans.forEach(function(karyawan) {
                                    const row = `
                                        <tr>
                                            <td>
                                                <div class="icheck-primary">
                                                    <input type="checkbox" name="karyawan_ids[]" value="${karyawan.id}" id="karyawan-${karyawan.id}">
                                                    <label for="karyawan-${karyawan.id}"></label>
                                                </div>
                                            </td>
                                            <td>${karyawan.nama}</td>
                                            <td>${karyawan.departemen ? karyawan.departemen.nama : '-'}</td>
                                            <td>${karyawan.jabatan ? karyawan.jabatan.nama : '-'}</td>
                                            <td>
                                                <span class="badge badge-${karyawan.status === 'aktif' ? 'success' : (karyawan.status === 'cuti' ? 'warning' : 'danger')}">
                                                    ${karyawan.status}
                                                </span>
                                            </td>
                                        </tr>
                                    `;
                                    $('#karyawan-list-body').append(row);
                                });

                                // Show karyawan list
                                $('.karyawan-list').show();
                            } else {
                                Swal.fire({
                                    title: 'Info',
                                    text: 'Tidak ada karyawan yang ditemukan atau semua karyawan sudah diproses untuk periode ini',
                                    icon: 'info'
                                });
                                $('.karyawan-list').show();
                            } else {
                                Swal.fire({
                                    title: 'Info',
                                    text: 'Tidak ada karyawan yang ditemukan atau semua karyawan sudah diproses untuk periode ini',
                                    icon: 'info'
                                });
                                $('.karyawan-list').hide();
                            }
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Gagal memuat data karyawan',
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan: ' + xhr.responseJSON.message,
                            icon: 'error'
                        });
                    }
                });
            });

            // Check/uncheck all
            $('#check-all').change(function() {
                $('input[name="karyawan_ids[]"]').prop('checked', $(this).prop('checked'));
            });

            // Form submission validation
            $('#penggajianForm').submit(function(e) {
                const checkedCount = $('input[name="karyawan_ids[]"]:checked').length;

                if (checkedCount === 0) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Silahkan pilih minimal satu karyawan',
                        icon: 'error'
                    });
                }
            });
        });
    </script>
@stop
