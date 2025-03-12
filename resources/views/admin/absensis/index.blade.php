@extends('adminlte::page')

@section('title', 'Manajemen Absensi')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="mr-2 fas fa-clipboard-check text-primary"></i>Manajemen Absensi</h1>
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

<!-- Info Boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Hadir</span>
                <span class="info-box-number">{{ $absensis->where('status', 'Hadir')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Terlambat</span>
                <span class="info-box-number">{{ $absensis->where('status', 'Terlambat')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-procedures"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sakit</span>
                <span class="info-box-number">{{ $absensis->where('status', 'Sakit')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tidak Hadir</span>
                <span class="info-box-number">{{ $absensis->whereIn('status', ['Izin', 'Cuti'])->count() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left sidebar with years and months -->
    <div class="col-md-4 col-lg-3">
        <div class="card">
            <div class="p-0 card-body">
                <div class="p-3 compose-btn-container">
                    @can_show('absensis.create')
                    <a href="{{ route('absensis.create') }}" class="btn btn-primary btn-block">
                        <i class="mr-1 fas fa-plus"></i> Tambah Absensi Baru
                    </a>
                    @endcan_show
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active filter-year" data-filter="all">
                        <i class="mr-2 fas fa-calendar"></i> Semua Absensi
                        <span class="float-right badge badge-light">{{ count($absensis) }}</span>
                    </a>

                    @php
                        $years = $absensis->groupBy(function($date) {
                            return $date->tanggal->format('Y');
                        });

                        $currentYear = date('Y');
                    @endphp

                    @foreach($years as $year => $items)
                        <a href="#" class="list-group-item list-group-item-action filter-year" data-filter="{{ $year }}" data-toggle="collapse" data-target="#months-{{ $year }}">
                            <i class="mr-2 fas fa-calendar-alt"></i> {{ $year }}
                            <span class="float-right badge badge-info">{{ count($items) }}</span>
                        </a>

                        <div id="months-{{ $year }}" class="collapse {{ $year == $currentYear ? 'show' : '' }}">
                            @php
                                $months = $items->groupBy(function($date) {
                                    return $date->tanggal->format('m');
                                });

                                $monthNames = [
                                    '01' => 'Januari',
                                    '02' => 'Februari',
                                    '03' => 'Maret',
                                    '04' => 'April',
                                    '05' => 'Mei',
                                    '06' => 'Juni',
                                    '07' => 'Juli',
                                    '08' => 'Agustus',
                                    '09' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember'
                                ];
                            @endphp

                            @foreach($months as $month => $monthItems)
                                <a href="#" class="pl-5 list-group-item list-group-item-action filter-month" data-year="{{ $year }}" data-month="{{ $month }}">
                                    <i class="mr-2 fas fa-calendar-day"></i> {{ $monthNames[$month] }}
                                    <span class="float-right badge badge-secondary">{{ count($monthItems) }}</span>
                                </a>
                            @endforeach
                        </div>
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
                    <h3 class="card-title" id="current-filter">Semua Absensi</h3>
                    <div>
                        <button type="button" class="btn btn-light" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="absensiTable">
                        <thead>
                            <tr>
                                <th width="10">#</th>
                                <th>Karyawan</th>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Total Jam</th>
                                <th>Status</th>
                                <th>Absensi Masuk</th>
                                <th>Absensi Pulang</th>
                                @can_show('absensis.edit')
                                <th width="150">Action</th>
                                @endcan_show
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($absensis as $index => $absensi)
                            <tr data-id="{{ $absensi->id }}" data-year="{{ $absensi->tanggal->format('Y') }}" data-month="{{ $absensi->tanggal->format('m') }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $absensi->karyawan->nama_karyawan ?? 'N/A' }}</td>
                                <td>{{ $absensi->tanggal->format('d-m-Y') }}</td>
                                <td>{{ $absensi->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>{{ $absensi->jam_pulang ? \Carbon\Carbon::parse($absensi->jam_pulang)->format('H:i') : '-' }}</td>
                                <td>{{ $absensi->total_jam ?? '-' }}</td>
                                <td>
                                    @if($absensi->status == 'Hadir')
                                    <span class="badge badge-success">{{ $absensi->status }}</span>
                                    @elseif($absensi->status == 'Terlambat')
                                    <span class="badge badge-warning">{{ $absensi->status }}</span>
                                    @elseif($absensi->status == 'Izin')
                                    <span class="badge badge-info">{{ $absensi->status }}</span>
                                    @elseif($absensi->status == 'Sakit')
                                    <span class="badge badge-primary">{{ $absensi->status }}</span>
                                    @elseif($absensi->status == 'Cuti')
                                    <span class="badge badge-secondary">{{ $absensi->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $absensi->jenis_absensi_masuk }}
                                    @if($absensi->jenis_absensi_masuk == 'Mesin')
                                    <br><small>({{ $absensi->mesinAbsensiMasuk->nama ?? 'N/A' }})</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $absensi->jenis_absensi_pulang }}
                                    @if($absensi->jenis_absensi_pulang == 'Mesin')
                                    <br><small>({{ $absensi->mesinAbsensiPulang->nama ?? 'N/A' }})</small>
                                    @endif
                                </td>
                                @can_show('absensis.edit')
                                <td>
                                    <a href="{{ route('absensis.show', $absensi) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($absensi->jam_pulang == null && $absensi->status != 'Izin' && $absensi->status != 'Sakit' && $absensi->status != 'Cuti')
                                    <a href="{{ route('absensis.checkout', $absensi) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-sign-out-alt"></i> Absen Pulang
                                    </a>
                                    @else
                                    <a href="{{ route('absensis.edit', $absensi) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif

                                    <form action="{{ route('absensis.destroy', $absensi) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                @endcan_show
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
<style>
    /* Left sidebar styles */
    .list-group-item {
        border-radius: 0;
        border-left: none;
        border-right: none;
        padding: 12px 15px;
        display: flex;
        align-items: center;
    }

    .list-group-item.active {
        background-color: #e8f0fe;
        color: #1a73e8;
        border-color: #e0e0e0;
        font-weight: 600;
    }

    .list-group-item:first-child {
        border-top: none;
    }

    .list-group-item:hover:not(.active) {
        background-color: #f8f9fa;
    }

    .list-group-item i {
        margin-right: 8px;
        min-width: 16px;
    }

    .list-group-item .badge {
        margin-left: auto;
        min-width: 28px;
        text-align: center;
        padding: 4px 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-light {
        background-color: #f8f9fa;
        color: #212529;
    }

    .badge-info {
        background-color: #e1f5fe;
        color: #0288d1;
    }

    .badge-secondary {
        background-color: #eceff1;
        color: #455a64;
    }

    .compose-btn-container {
        border-bottom: 1px solid #e0e0e0;
    }

    .card {
        border-radius: 8px;
        border: 1px solid #dadce0;
        box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        margin-bottom: 20px;
    }

    .card-header {
        border-bottom: 1px solid #dadce0;
        padding: 12px 16px;
    }

    #refreshBtn {
        background-color: #f8f9fa;
        border-color: #dadce0;
    }

    #refreshBtn:hover {
        background-color: #f1f3f4;
    }

    .info-box {
        box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        border-radius: 8px;
    }
</style>
@stop
@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(function() {
        // Initialize DataTable
        var table = $('#absensiTable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "order": [[2, 'desc']], // Urutkan berdasarkan tanggal
            "columnDefs": [
                {
                    "targets": 0,
                    "render": function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                }
            ]
        });

        // Filter by year
        $('.filter-year').click(function(e) {
            if (!$(e.target).hasClass('badge')) {
                e.preventDefault();

                // Only apply filter if not clicking on collapse toggle
                if (!$(this).data('toggle') || $(this).data('filter') === 'all') {
                    $('.filter-year, .filter-month').removeClass('active');
                    $(this).addClass('active');

                    var filter = $(this).data('filter');

                    if (filter === 'all') {
                        // Tampilkan semua data
                        table.search('').draw();
                        $('#current-filter').text('Semua Absensi');
                    } else {
                        // Cari berdasarkan tahun
                        table.search(filter).draw();
                        $('#current-filter').text('Absensi Tahun ' + filter);
                    }
                }
            }
        });

        // Filter by month
        $('.filter-month').click(function(e) {
            e.preventDefault();

            $('.filter-year, .filter-month').removeClass('active');
            $(this).addClass('active');

            var year = $(this).data('year');
            var month = $(this).data('month');
            var monthName = $(this).text().trim().split(' ')[0];

            // Cari berdasarkan bulan dan tahun
            table.search(month + '-' + year).draw();
            $('#current-filter').text('Absensi ' + monthName + ' ' + year);
        });

        // Refresh button
        $('#refreshBtn').click(function() {
            $(this).find('i').addClass('fa-spin');
            setTimeout(function() {
                location.reload();
            }, 500);
        });
    });
</script>
@stop