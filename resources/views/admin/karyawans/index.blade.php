@extends('adminlte::page')

@section('title', 'Karyawan Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="mr-2 fas fa-users text-primary"></i>Karyawan Management</h1>
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
    <!-- Left sidebar with categories -->
    <div class="col-md-3 col-lg-2">
        <div class="card">
            <div class="p-0 card-body">
                <div class="p-3 compose-btn-container">
                    @can_show('karyawans.create')
                    <a href="{{ route('karyawans.create') }}" class="btn btn-primary btn-block">
                        <i class="mr-1 fas fa-plus"></i> Tambah Karyawan
                    </a>
                    @endcan_show
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active filter-category" data-filter="all">
                        <i class="mr-2 fas fa-users"></i> Semua
                        <span class="float-right badge badge-light">{{ $allCount }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Bulanan">
                        <i class="mr-2 fas fa-calendar-alt text-info"></i> Bulanan
                        <span class="float-right badge badge-info">{{ $bulananCount }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Harian">
                        <i class="mr-2 fas fa-calendar-day text-success"></i> Harian
                        <span class="float-right badge badge-success">{{ $harianCount }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Borongan">
                        <i class="mr-2 fas fa-briefcase text-warning"></i> Borong
                        <span class="float-right badge badge-warning">{{ $boronganCount }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Resign">
                        <i class="mr-2 fas fa-user-slash text-danger"></i> Resign
                        <span class="float-right badge badge-danger">{{ $resignCount }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right side with employee list -->
    <div class="col-md-9 col-lg-10">
        <div class="card">
            <div class="bg-white card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="mr-2 input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari karyawan...">
                            <div class="input-group-append">
                                <span class="bg-transparent input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-light" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <!-- Add pagination controls at the bottom of the employee list -->
                <div class="card-footer bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="text-muted">
                                Showing {{ $karyawans->firstItem() ?? 0 }} to {{ $karyawans->lastItem() ?? 0 }} of {{ $karyawans->total() }} entries
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="d-flex justify-content-center">
                                <nav aria-label="Page navigation">
                                    {{ $karyawans->appends(request()->except('page'))->onEachSide(1)->links('pagination::bootstrap-4') }}
                                </nav>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-end align-items-center">
                                <span class="mr-2">Show</span>
                                <select id="perPageSelect" class="form-control form-control-sm d-inline-block" style="width: auto;">
                                    <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="ml-2">entries</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th width="60">Foto</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Bagian</th>
                                <th>Jabatan</th>
                                <th width="100">Status</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $currentDepartemenName = null;
                                $counter = 0;
                            @endphp

                            @foreach($karyawans as $index => $karyawan)
                                @php
                                    $departemenName = $karyawan->departemen ? $karyawan->departemen->name_departemen : 'Tanpa Departemen';
                                @endphp

                                @if($currentDepartemenName != $departemenName)
                                    @php
                                        $currentDepartemenName = $departemenName;
                                        $counter = 0; // Reset counter for each department
                                    @endphp
                                    <tr class="department-header" id="dept-{{ Str::slug($departemenName) }}">
                                        <td colspan="8" class="font-weight-bold bg-light">
                                            {{ $departemenName }}
                                        </td>
                                    </tr>
                                @endif
                                <tr class="employee-row" data-department="{{ Str::slug($departemenName) }}">
                                    <td>{{ ++$counter }}</td>
                                    <td>
                                        <img src="{{ $karyawan->foto_url }}" alt="{{ $karyawan->nama_karyawan }}">
                                    </td>
                                    <td>{{ $karyawan->nik_karyawan }}</td>

                                    <td>
                                        <div class="font-weight-bold">{{ $karyawan->nama_karyawan }}</div>
                                        <small class="text-muted">
                                            @if($karyawan->tahun_keluar)
                                                Resign: {{ \Carbon\Carbon::parse($karyawan->tahun_keluar)->format('d/m/Y') }}
                                            @else
                                                Masuk: {{ \Carbon\Carbon::parse($karyawan->tgl_awalmasuk)->format('d/m/Y') }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>{{ $karyawan->bagian ? $karyawan->bagian->name_bagian : '-' }}</td>
                                    <td>{{ $karyawan->jabatan ? $karyawan->jabatan->name_jabatan : '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{
                                            $karyawan->tahun_keluar ? 'danger' :
                                            ($karyawan->statuskaryawan == 'Bulanan' ? 'info' :
                                            ($karyawan->statuskaryawan == 'Harian' ? 'success' :
                                            ($karyawan->statuskaryawan == 'Borongan' ? 'warning' : 'secondary')))
                                        }}">
                                            {{ $karyawan->tahun_keluar ? 'Resign' : $karyawan->statuskaryawan }}
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="{{ route('karyawans.show', $karyawan) }}" class="btn btn-sm btn-info" data-toggle="tooltip" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can_show('karyawans.edit')
                                        @if(!$karyawan->tahun_keluar)
                                        <a href="{{ route('karyawans.edit', $karyawan) }}" class="btn btn-sm btn-warning" data-toggle="tooltip" title="Edit Karyawan">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger resign-btn" data-id="{{ $karyawan->id }}" data-name="{{ $karyawan->nama_karyawan }}" data-toggle="tooltip" title="Resign Karyawan">
                                            <i class="fas fa-user-slash"></i>
                                        </a>
                                        <form action="{{ route('karyawans.destroy', $karyawan) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Hapus Karyawan" onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @endcan_show
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(count($karyawans) == 0)
                <div class="py-5 text-center">
                    <p class="text-muted">Tidak ada karyawan yang ditemukan</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Resign Modal -->
<div class="modal fade" id="resignModal" tabindex="-1" role="dialog" aria-labelledby="resignModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resignModalLabel">Konfirmasi Resign Karyawan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengubah status <strong id="resignEmployeeName"></strong> menjadi resign?</p>
                <form id="resignForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label for="tanggal_resign">Tanggal Resign</label>
                        <input type="date" class="form-control" id="tanggal_resign" name="tanggal_resign" required value="{{ date('Y-m-d') }}">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmResign">Konfirmasi Resign</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    /* Table styles */
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        position: relative;
    }

    .table {
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        border-top: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        border-left: none;
        border-right: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table td {
        vertical-align: middle;
        border-left: none;
        border-right: none;
        border-top: none;
        border-bottom: 1px solid #f0f0f0;
    }

    .table tr:last-child td {
        border-bottom: 1px solid #dee2e6;
    }

    .department-header {
        width: 100%;
    }

    .department-header td {
        padding: 10px 15px;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        font-size: 14px;
        color: #495057;
        font-weight: bold;
    }

    .department-header.sticky {
        position: sticky;
        top: 53px; /* Height of the table header */
        z-index: 9;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .department-header.sticky td {
        background-color: #f0f0f0;
    }

    .table img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border: 1px solid #e0e0e0;
        border-radius: 4px; /* Slightly rounded corners */
    }

    /* Left sidebar styles */
    .list-group-item {
        border-radius: 0;
        border-left: none;
        border-right: none;
        padding: 12px 15px;
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

    .compose-btn-container {
        border-bottom: 1px solid #e0e0e0;
    }

    .btn-light {
        background-color: #f8f9fa;
        border-color: #dadce0;
    }

    .btn-light:hover {
        background-color: #f1f3f4;
    }

    .card {
        border-radius: 8px;
        border: 1px solid #dadce0;
        box-shadow: none;
        margin-bottom: 20px;
    }

    .card-header {
        border-bottom: 1px solid #dadce0;
        padding: 12px 16px;
    }

    #searchInput {
        border-radius: 24px;
        padding-left: 15px;
        background-color: #f1f3f4;
        border: none;
        height: 40px;
    }

    #searchInput:focus {
        background-color: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .input-group-text {
        border: none;
        background-color: transparent;
    }

    /* Action buttons */
    .action-buttons .btn {
        margin-right: 3px;
    }

    .action-buttons .btn:last-child {
        margin-right: 0;
    }

    /* Remove these styles as they're for vertical layout and hover effects
    .action-text {
        display: none;
        margin-left: 3px;
        font-size: 0.8rem;
    }

    .action-btn:hover .action-text {
        display: inline-block;
    }

    .btn-group-vertical {
        display: flex;
        flex-direction: column;
    }

    .btn-group-vertical form {
        margin-bottom: 3px;
    }

    .btn-group-vertical form:last-child {
        margin-bottom: 0;
    }
    */
</style>
@stop

@section('js')
<script>
    $(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Make department headers sticky - disabled as requested
        function setupStickyHeaders() {
            // Get table header height
            const tableHeaderHeight = $('.table thead tr').outerHeight();

            // Remove sticky behavior for department headers
            $('.department-header').removeClass('sticky');

            // No need for scroll event handling for department headers
            /*
            $(window).scroll(function() {
                // Previous scroll handling code removed
            });
            */
        }

        // Initialize headers after page is fully loaded
        $(window).on('load', function() {
            setTimeout(setupStickyHeaders, 200);
        });

        // Handle search button click
        $('#searchBtn').on('click', function() {
            performSearch();
        });

        // Handle Enter key in search input
        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                performSearch();
            }
        });

        // Handle per page change
        $('#perPageSelect').on('change', function() {
            const filter = $('.filter-category.active').data('filter') || 'all';
            window.location.href = '{{ route("karyawans.index") }}?per_page=' + $(this).val() +
                '&search=' + $('#searchInput').val() +
                '&filter=' + filter;
        });

        // Set search input value from URL
        $('#searchInput').val('{{ request("search") }}');

        // Filter functionality using left sidebar
        $('.filter-category').click(function(e) {
            e.preventDefault();
            $('.filter-category').removeClass('active');
            $(this).addClass('active');

            const filter = $(this).data('filter');
            window.location.href = '{{ route("karyawans.index") }}?filter=' + filter +
                '&search=' + $('#searchInput').val() +
                '&per_page=' + $('#perPageSelect').val();
        });

        // Set active filter from URL
        const urlFilter = '{{ request("filter", "all") }}';
        $('.filter-category').removeClass('active');
        $('.filter-category[data-filter="' + urlFilter + '"]').addClass('active');

        // Refresh button
        $('#refreshBtn').click(function() {
            window.location.href = '{{ route("karyawans.index") }}';
        });

        // Resign functionality
        $('.resign-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();

            var id = $(this).data('id');
            var name = $(this).data('name');

            $('#resignEmployeeName').text(name);
            $('#resignForm').attr('action', '{{ route("karyawans.resign", "__ID__") }}'.replace('__ID__', id));
            $('#resignModal').modal('show');
        });

        $('#confirmResign').click(function() {
            if ($('#tanggal_resign').val()) {
                $('#resignForm').submit();
            } else {
                alert('Mohon lengkapi tanggal resign');
            }
        });

        function performSearch() {
            const filter = $('.filter-category.active').data('filter') || 'all';
            window.location.href = '{{ route("karyawans.index") }}?search=' +
                $('#searchInput').val() +
                '&filter=' + filter +
                '&per_page=' + $('#perPageSelect').val();
        }
    });
</script>
@stop