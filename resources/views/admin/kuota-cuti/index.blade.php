@extends('adminlte::page')

@section('title', 'Daftar Kuota Cuti Tahunan')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="mr-2 fas fa-calendar-check text-primary"></i>Daftar Kuota Cuti Tahunan</h1>
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

<div class="row">
    <!-- Left sidebar with years -->
    <div class="col-md-4 col-lg-3">
        <div class="card">
            <div class="p-0 card-body">
                <div class="p-3 compose-btn-container">
                    <a href="{{ route('kuota-cuti.create') }}" class="btn btn-primary btn-block">
                        <i class="mr-1 fas fa-plus"></i> Tambah Kuota
                    </a>
                    <button type="button" class="mt-2 btn btn-success btn-block" data-toggle="modal" data-target="#generateMassalModal">
                        <i class="mr-1 fas fa-users"></i> Generate Massal
                    </button>
                    <a href="{{ route('kuota-cuti.report') }}" class="mt-2 btn btn-info btn-block">
                        <i class="mr-1 fas fa-chart-bar"></i> Laporan Kuota
                    </a>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active filter-year" data-filter="all">
                        <i class="mr-2 fas fa-calendar"></i> Semua Tahun
                        <span class="float-right badge badge-light">{{ count($kuotaCuti) }}</span>
                    </a>

                    @php
                        $years = $kuotaCuti->groupBy('tahun');
                        $currentYear = date('Y');
                    @endphp

                    @foreach($years as $year => $items)
                        <a href="#" class="list-group-item list-group-item-action filter-year" data-filter="{{ $year }}">
                            <i class="mr-2 fas fa-calendar-alt"></i> {{ $year }}
                            <span class="float-right badge badge-info">{{ count($items) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right side with data table -->
    <div class="col-md-8 col-lg-9">
        <div class="card">
            <div class="bg-white card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title" id="current-filter">Semua Kuota Cuti Tahunan</h3>
                    <div>
                        <button type="button" class="btn btn-light" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="kuota-cuti-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Karyawan</th>
                            <th>Tahun</th>
                            <th>Kuota Awal</th>
                            <th>Kuota Digunakan</th>
                            <th>Kuota Sisa</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kuotaCuti as $index => $kuota)
                        <tr data-year="{{ $kuota->tahun }}">
                            <td>{{ $index + $kuotaCuti->firstItem() }}</td>
                            <td>{{ $kuota->nama_karyawan }}</td>
                            <td>{{ $kuota->tahun }}</td>
                            <td>{{ $kuota->kuota_awal }} hari</td>
                            <td>{{ $kuota->kuota_digunakan }} hari</td>
                            <td>
                                <span class="badge {{ $kuota->kuota_sisa > 3 ? 'bg-success' : ($kuota->kuota_sisa > 0 ? 'bg-warning' : 'bg-danger') }}">
                                    {{ $kuota->kuota_sisa }} hari
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('kuota-cuti.edit', $kuota->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('kuota-cuti.destroy', $kuota->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $kuotaCuti->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Generate Massal -->
<div class="modal fade" id="generateMassalModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Generate Kuota Cuti Massal</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('kuota-cuti.generate-massal') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tahun_generate">Tahun</label>
                        <input type="number" name="tahun_generate" id="tahun_generate" class="form-control"
                               value="{{ date('Y') }}" min="2000" max="2100" required>
                        <small class="text-muted">Tahun untuk kuota cuti yang akan digenerate</small>
                    </div>

                    <div class="form-group">
                        <label for="kuota_awal_generate">Kuota Awal (Hari)</label>
                        <input type="number" name="kuota_awal_generate" id="kuota_awal_generate" class="form-control"
                               value="12" min="0" max="12" required>
                        <small class="text-muted">Maksimal 12 hari per tahun</small>
                    </div>

                    <div class="form-group">
                        <label>Pilih Karyawan</label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-primary" id="selectAllKaryawan">Pilih Semua</button>
                            <button type="button" class="btn btn-sm btn-secondary" id="deselectAllKaryawan">Batal Pilih Semua</button>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                            <div class="row">
                                @foreach($allKaryawan ?? [] as $k)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input karyawan-checkbox" type="checkbox" name="karyawan_ids[]" value="{{ $k->id }}" id="karyawan{{ $k->id }}">
                                        <label class="form-check-label" for="karyawan{{ $k->id }}">
                                            {{ $k->nama_karyawan }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Generate Kuota Cuti</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
<style>
    .badge.bg-success {
        background-color: #28a745;
        color: white;
    }
    .badge.bg-warning {
        background-color: #ffc107;
        color: black;
    }
    .badge.bg-danger {
        background-color: #dc3545;
        color: white;
    }
</style>
@stop

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(function() {
        // Initialize DataTable
        var table = $('#kuota-cuti-table').DataTable({
            "paging": false,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "order": [[2, 'desc'], [1, 'asc']], // Sort by year desc, then by employee name
        });

        // Filter by year
        $('.filter-year').click(function(e) {
            e.preventDefault();

            $('.filter-year').removeClass('active');
            $(this).addClass('active');

            var filter = $(this).data('filter');

            if (filter === 'all') {
                // Show all data
                table.search('').draw();
                $('#current-filter').text('Semua Kuota Cuti Tahunan');
            } else {
                // Filter by year
                table.search(filter).draw();
                $('#current-filter').text('Kuota Cuti Tahun ' + filter);
            }
        });

        // Refresh button
        $('#refreshBtn').click(function() {
            location.reload();
        });

        // Select/Deselect All Karyawan
        $('#selectAllKaryawan').click(function() {
            $('.karyawan-checkbox').prop('checked', true);
        });

        $('#deselectAllKaryawan').click(function() {
            $('.karyawan-checkbox').prop('checked', false);
        });

        // Form validation before submission
        $('form[action="{{ route('kuota-cuti.generate-massal') }}"]').on('submit', function(e) {
            const selectedKaryawan = $('.karyawan-checkbox:checked').length;
            if (selectedKaryawan === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal satu karyawan untuk generate kuota cuti.');
                return false;
            }
            return true;
        });
    });
</script>
@stop