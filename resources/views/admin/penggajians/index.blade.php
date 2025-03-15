@extends('adminlte::page')

@section('title', 'Data Penggajian')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="mr-2 fas fa-money-bill-wave text-primary"></i>Data Penggajian</h1>
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
        <!-- Left sidebar with filters -->
        <div class="col-md-4 col-lg-3">
            <div class="card">
                <div class="p-0 card-body">
                    <div class="p-3 compose-btn-container">
                        <a href="{{ route('penggajian.create') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-plus mr-1"></i> Tambah Penggajian
                        </a>
                        <div class="dropdown mt-2">
                            <button class="btn btn-info btn-block dropdown-toggle" type="button" id="reportDropdown" data-toggle="dropdown">
                                <i class="fas fa-file-export mr-1"></i> Laporan
                            </button>
                            <div class="dropdown-menu w-100" aria-labelledby="reportDropdown">
                                <a class="dropdown-item" href="{{ route('penggajian.reportByPeriod') }}">Laporan Per Periode</a>
                                <a class="dropdown-item" href="{{ route('penggajian.reportByDepartment') }}">Laporan Per Departemen</a>
                            </div>
                        </div>
                    </div>

                    <!-- Filter sections -->
<div class="list-group list-group-flush">
    <a href="#" class="list-group-item list-group-item-action active filter-all" data-filter="all">
        <i class="fas fa-list mr-2"></i> Semua Penggajian
        <span class="float-right badge badge-light">{{ count($penggajians) }}</span>
    </a>

    <!-- Year filter -->
    <div class="list-group-item list-group-item-secondary">
        <i class="fas fa-calendar-alt mr-2"></i> Filter Tahun
    </div>

    @php
        $years = $penggajians->groupBy(function($item) {
            return $item->periodeGaji->tanggal_mulai->format('Y');
        });

        $currentYear = date('Y');
    @endphp

    @foreach($years as $year => $items)
        <a href="#" class="list-group-item list-group-item-action filter-year" data-filter="{{ $year }}" data-toggle="collapse" data-target="#months-{{ $year }}">
            <i class="fas fa-calendar-alt mr-2"></i> {{ $year }}
            <span class="float-right badge badge-info">{{ count($items) }}</span>
        </a>

        <div id="months-{{ $year }}" class="collapse month-collapse {{ $year == $currentYear ? 'show' : '' }}">
            @php
                $months = $items->groupBy(function($item) {
                    return $item->periodeGaji->tanggal_mulai->format('m');
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
                <a href="#" class="list-group-item list-group-item-action filter-month pl-5" 
                   data-year="{{ $year }}" data-month="{{ $month }}"
                   data-toggle="collapse" data-target="#departments-{{ $year }}-{{ $month }}">
                    <i class="fas fa-calendar-day mr-2"></i> {{ $monthNames[$month] }}
                    <span class="float-right badge badge-secondary">{{ count($monthItems) }}</span>
                </a>
                
                <div id="departments-{{ $year }}-{{ $month }}" class="collapse department-collapse">
                    @php
                        $departments = $monthItems->groupBy(function($item) {
                            return $item->detail_departemen['departemen'] ?? 'Tidak Ada';
                        });
                    @endphp
                    
                    @foreach($departments as $department => $deptItems)
                        <a href="#" class="list-group-item list-group-item-action filter-department pl-5 ml-3" 
                           data-year="{{ $year }}" data-month="{{ $month }}" data-department="{{ $department }}"
                           data-toggle="collapse" data-target="#statuses-{{ $year }}-{{ $month }}-{{ Str::slug($department) }}">
                            <i class="fas fa-building mr-2"></i> {{ $department }}
                            <span class="float-right badge badge-secondary">{{ count($deptItems) }}</span>
                        </a>
                        
                        <div id="statuses-{{ $year }}-{{ $month }}-{{ Str::slug($department) }}" class="collapse status-collapse">
                            @php
                                $statuses = $deptItems->groupBy(function($item) {
                                    return $item->karyawan->statuskaryawan ?? 'Tidak Ada';
                                });
                            @endphp
                            
                            @foreach($statuses as $status => $statusItems)
                                <a href="#" class="list-group-item list-group-item-action filter-status pl-5 ml-5" 
                                   data-year="{{ $year }}" data-month="{{ $month }}" 
                                   data-department="{{ $department }}" data-status="{{ $status }}">
                                    <i class="fas fa-user-tag mr-2"></i> {{ $status }}
                                    <span class="float-right badge badge-secondary">{{ count($statusItems) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
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
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title" id="current-filter">Semua Data Penggajian</h3>
                        <div>
                            <button type="button" class="btn btn-light" id="refreshBtn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="penggajian-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Periode</th>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th>Gaji Bersih</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penggajians as $index => $penggajian)
                            <tr data-year="{{ $penggajian->periodeGaji->tanggal_mulai->format('Y') }}"
                                data-month="{{ $penggajian->periodeGaji->tanggal_mulai->format('m') }}"
                                data-department="{{ $penggajian->detail_departemen['departemen'] ?? 'Tidak Ada' }}"
                                data-status="{{ $penggajian->karyawan->statuskaryawan ?? 'Tidak Ada' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $penggajian->periodeGaji->nama_periode }}</td>
                                <td>{{ $penggajian->karyawan->nama }}</td>
                                <td>
                                    @php
                                        $departemen = $penggajian->detail_departemen['departemen'] ?? '-';
                                    @endphp
                                    {{ $departemen }}
                                </td>
                                
                                <td>{{ $penggajian->formatCurrency($penggajian->gaji_bersih) }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('penggajian.show', $penggajian->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('penggajian.edit', $penggajian->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('penggajian.payslip', $penggajian->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </a>
                                        <form action="{{ route('penggajian.destroy', $penggajian->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $penggajians->links() }}
                    </div>
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
            $('#penggajian-table').DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "responsive": true,
            });

            // Show all
            $('.filter-all').on('click', function(e) {
                e.preventDefault();

                // Remove active class from all filters and add to this one
                $('.filter-year, .filter-month, .filter-department, .filter-status, .filter-all').removeClass('active');
                $(this).addClass('active');

                // Reset the DataTable
                var table = $('#penggajian-table').DataTable();
                table.search('').draw();

                // Update filter title
                $('#current-filter').text('Semua Data Penggajian');

                // Show all rows
                $('tr').show();
            });
            
            // Filter by year - shows months for that year
            $('.filter-year').on('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all filters and add to this one
                $('.filter-year, .filter-month, .filter-department, .filter-status, .filter-all').removeClass('active');
                $(this).addClass('active');
                
                var year = $(this).data('filter');
                
                // Update filter title
                $('#current-filter').text('Penggajian Tahun ' + year);
                
                // Show only rows with matching year
                $('tr').show();
                $('tbody tr').not('[data-year="' + year + '"]').hide();
                
                // Hide all other month, department, and status collapses
                $('.month-collapse').not('#months-' + year).collapse('hide');
                $('.department-collapse, .status-collapse').collapse('hide');
            });
            
            // Filter by month - shows departments for that month
            $('.filter-month').on('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all filters and add to this one
                $('.filter-year, .filter-month, .filter-department, .filter-status, .filter-all').removeClass('active');
                $(this).addClass('active');
                
                var year = $(this).data('year');
                var month = $(this).data('month');
                
                // Get month name for display
                var monthNames = {
                    '01': 'Januari', '02': 'Februari', '03': 'Maret', '04': 'April',
                    '05': 'Mei', '06': 'Juni', '07': 'Juli', '08': 'Agustus',
                    '09': 'September', '10': 'Oktober', '11': 'November', '12': 'Desember'
                };
                
                // Update filter title
                $('#current-filter').text('Penggajian ' + monthNames[month] + ' ' + year);
                
                // Show only rows with matching year and month
                $('tr').show();
                $('tbody tr').not('[data-year="' + year + '"][data-month="' + month + '"]').hide();
                
                // Show departments for this month
                var monthId = year + '-' + month;
                $('.department-collapse').not('#departments-' + monthId).collapse('hide');
                $('#departments-' + monthId).collapse('show');
                
                // Hide all status collapses
                $('.status-collapse').collapse('hide');
            });
            
            // Filter by department - shows statuses for that department
            $('.filter-department').on('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all filters and add to this one
                $('.filter-year, .filter-month, .filter-department, .filter-status, .filter-all').removeClass('active');
                $(this).addClass('active');
                
                var year = $(this).data('year');
                var month = $(this).data('month');
                var department = $(this).data('department');
                
                // Update filter title
                $('#current-filter').text('Penggajian Departemen ' + department);
                
                // Show only rows with matching year, month, and department
                $('tr').show();
                $('tbody tr').not('[data-year="' + year + '"][data-month="' + month + '"][data-department="' + department + '"]').hide();
                
                // Show statuses for this department
                var deptId = year + '-' + month + '-' + department.replace(/\s+/g, '-').toLowerCase();
                $('.status-collapse').not('#statuses-' + deptId).collapse('hide');
                $('#statuses-' + deptId).collapse('show');
            });
            
            // Filter by status
            $('.filter-status').on('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all filters and add to this one
                $('.filter-year, .filter-month, .filter-department, .filter-status, .filter-all').removeClass('active');
                $(this).addClass('active');
                
                var year = $(this).data('year');
                var month = $(this).data('month');
                var department = $(this).data('department');
                var status = $(this).data('status');
                
                // Update filter title
                $('#current-filter').text('Penggajian Status ' + status);
                
                // Show only rows with matching year, month, department, and status
                $('tr').show();
                $('tbody tr').not('[data-year="' + year + '"][data-month="' + month + '"][data-department="' + department + '"][data-status="' + status + '"]').hide();
            });

            // Refresh button
            $('#refreshBtn').on('click', function() {
                location.reload();
            });
        });
    </script>
@stop