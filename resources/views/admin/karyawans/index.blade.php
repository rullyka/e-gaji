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
                        <span class="float-right badge badge-light">{{ count($karyawans) }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Bulanan">
                        <i class="mr-2 fas fa-calendar-alt text-info"></i> Bulanan
                        <span class="float-right badge badge-info">{{ $karyawans->where('statuskaryawan', 'Bulanan')->count() }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Harian">
                        <i class="mr-2 fas fa-calendar-day text-success"></i> Harian
                        <span class="float-right badge badge-success">{{ $karyawans->where('statuskaryawan', 'Harian')->count() }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Borong">
                        <i class="mr-2 fas fa-briefcase text-warning"></i> Borong
                        <span class="float-right badge badge-warning">{{ $karyawans->where('statuskaryawan', 'Borongan')->count() }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Resign">
                        <i class="mr-2 fas fa-user-slash text-danger"></i> Resign
                        <span class="float-right badge badge-danger">{{ $karyawans->whereNotNull('tahun_keluar')->count() }}</span>
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
            </div>
            <div class="p-0 card-body">
                <div class="employee-container">
                    @foreach($karyawans as $index => $karyawan)
                    <div class="employee-item" data-status="{{ $karyawan->tahun_keluar ? 'Resign' : $karyawan->statuskaryawan }}">
                        <div class="employee-item-checkbox">
                            <div class="icheck-primary">
                                <input type="checkbox" id="check{{ $karyawan->id }}">
                                <label for="check{{ $karyawan->id }}"></label>
                            </div>
                        </div>
                        <div class="employee-item-avatar">
                            <img src="{{ $karyawan->foto_url }}" alt="{{ $karyawan->nama_karyawan }}" class="img-circle">
                        </div>
                        <div class="employee-item-info">
                            <div class="employee-name">{{ $karyawan->nama_karyawan }}</div>
                            <div class="employee-nik">{{ $karyawan->nik_karyawan }}</div>
                        </div>
                        <div class="employee-item-department">
                            <div>{{ $karyawan->departemen ? $karyawan->departemen->name_departemen : '-' }}</div>
                            <div class="text-muted">{{ $karyawan->bagian ? $karyawan->bagian->name_bagian : '-' }}</div>
                        </div>
                        <div class="employee-item-position">
                            <div>{{ $karyawan->jabatan ? $karyawan->jabatan->name_jabatan : '-' }}</div>
                            <div class="text-muted">{{ $karyawan->profesi ? $karyawan->profesi->name_profesi : '-' }}</div>
                        </div>
                        <div class="employee-item-status">
                            <span class="badge badge-{{
                                $karyawan->tahun_keluar ? 'danger' :
                                ($karyawan->statuskaryawan == 'Bulanan' ? 'info' :
                                ($karyawan->statuskaryawan == 'Harian' ? 'success' :
                                ($karyawan->statuskaryawan == 'Borongan' ? 'warning' : 'secondary')))
                            }}">
                                {{ $karyawan->tahun_keluar ? 'Resign' : $karyawan->statuskaryawan }}
                            </span>
                        </div>
                        <div class="employee-item-date">
                            <span>{{ $karyawan->tgl_awalmasuk }}</span>
                        </div>
                        <div class="employee-item-actions">
                            <a href="{{ route('karyawans.show', $karyawan) }}" class="btn btn-sm btn-light" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can_show('karyawans.edit')
                            @if(!$karyawan->tahun_keluar)
                            <a href="{{ route('karyawans.edit', $karyawan) }}" class="btn btn-sm btn-light" title="Edit Karyawan">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-light resign-btn" data-id="{{ $karyawan->id }}" data-name="{{ $karyawan->nama_karyawan }}" title="Resign Karyawan">
                                <i class="fas fa-user-slash text-danger"></i>
                            </a>
                            <form action="{{ route('karyawans.destroy', $karyawan) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light" title="Hapus Karyawan" onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                            @endcan_show
                        </div>
                    </div>
                    @endforeach

                    @if(count($karyawans) == 0)
                    <div class="py-5 text-center">
                        <p class="text-muted">Tidak ada karyawan yang ditemukan</p>
                    </div>
                    @endif
                </div>
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
    .employee-container {
        border-top: 1px solid #e0e0e0;
    }

    .employee-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
        transition: all 0.2s;
        cursor: pointer;
    }

    .employee-item:hover {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        z-index: 1;
        position: relative;
    }

    .employee-item-checkbox {
        width: 30px;
    }

    .employee-item-avatar {
        width: 50px;
        text-align: center;
    }

    .employee-item-avatar img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border: 1px solid #e0e0e0;
    }

    .employee-item-info {
        width: 200px;
        padding-right: 15px;
    }

    .employee-name {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .employee-nik {
        font-size: 0.85rem;
        color: #5f6368;
    }

    .employee-item-department {
        width: 180px;
        padding-right: 15px;
    }

    .employee-item-position {
        width: 180px;
        padding-right: 15px;
    }

    .employee-item-status {
        width: 100px;
        text-align: center;
    }

    .employee-item-date {
        width: 100px;
        text-align: right;
        color: #5f6368;
        font-size: 0.85rem;
    }

    .employee-item-actions {
        width: 120px;
        text-align: right;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .employee-item:hover .employee-item-actions {
        opacity: 1;
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
        box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
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

    @media (max-width: 1200px) {
        .employee-item-department, .employee-item-position {
            width: 150px;
        }
    }

    @media (max-width: 992px) {
        .employee-item-date {
            width: 80px;
        }

        .employee-item-department, .employee-item-position {
            width: 120px;
        }
    }

    @media (max-width: 768px) {
        .employee-item {
            flex-wrap: wrap;
        }

        .employee-item-info {
            width: calc(100% - 80px);
            order: 1;
        }

        .employee-item-checkbox {
            order: 0;
        }

        .employee-item-avatar {
            order: 2;
        }

        .employee-item-department, .employee-item-position {
            width: 50%;
            order: 3;
            padding-left: 30px;
            margin-top: 10px;
        }

        .employee-item-status, .employee-item-date {
            width: 50%;
            order: 4;
            text-align: left;
            padding-left: 30px;
            margin-top: 5px;
        }

        .employee-item-actions {
            width: 100%;
            order: 5;
            text-align: right;
            margin-top: 10px;
            opacity: 1;
        }
    }
</style>
@stop

@section('js')
<script>
    $(function() {
        // Resign functionality
        $('.resign-btn').click(function(e) {
            e.preventDefault();
            e.stopPropagation();

            var id = $(this).data('id');
            var name = $(this).data('name');

            $('#resignEmployeeName').text(name);
            // Use the proper route for the form action
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
    });
</script>
<script>
    $(function() {
        // Filter functionality using left sidebar
        $('.filter-category').click(function(e) {
            e.preventDefault();
            $('.filter-category').removeClass('active');
            $(this).addClass('active');

            var filter = $(this).data('filter');

            if (filter === 'all') {
                $('.employee-item').show();
            } else {
                $('.employee-item').hide();
                $('.employee-item[data-status="' + filter + '"]').show();
            }

            updateEmptyState();
        });

        // Search functionality
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var currentFilter = $('.filter-category.active').data('filter');

            $('.employee-item').each(function() {
                var text = $(this).text().toLowerCase();
                var matchesSearch = text.indexOf(value) > -1;
                var matchesFilter = currentFilter === 'all' || $(this).data('status') === currentFilter;

                $(this).toggle(matchesSearch && matchesFilter);
            });

            updateEmptyState();
        });

        // Refresh button
        $('#refreshBtn').click(function() {
            $(this).find('i').addClass('fa-spin');
            $('#searchInput').val('');
            $('.filter-category[data-filter="all"]').click();

            setTimeout(function() {
                location.reload();
            }, 500);
        });

        // Click on row to view details
        $('.employee-item').click(function(e) {
            if (!$(e.target).is('input[type="checkbox"]') &&
                !$(e.target).is('button') &&
                !$(e.target).is('a') &&
                !$(e.target).is('i') &&
                !$(e.target).closest('button').length &&
                !$(e.target).closest('a').length) {
                var detailUrl = $(this).find('a[title="Lihat Detail"]').attr('href');
                if (detailUrl) {
                    window.location.href = detailUrl;
                }
            }
        });

        // Prevent checkbox from triggering row click
        $('.employee-item-checkbox input').click(function(e) {
            e.stopPropagation();
        });

        // Function to show/hide empty state
        function updateEmptyState() {
            var visibleItems = $('.employee-item:visible').length;

            if (visibleItems === 0) {
                if ($('.empty-state').length === 0) {
                    $('.employee-container').append(`
                        <div class="py-5 text-center empty-state">
                            <p class="text-muted">Tidak ada karyawan yang sesuai dengan filter</p>
                        </div>
                    `);
                }
            } else {
                $('.empty-state').remove();
            }
        }

        // Initialize
        $('.filter-category[data-filter="all"]').addClass('active');

        // Add hover effect to rows
        $('.employee-item').hover(
            function() {
                $(this).css('box-shadow', '0 1px 3px rgba(0,0,0,0.1)');
                $(this).css('z-index', '1');
                $(this).css('position', 'relative');
            },
            function() {
                $(this).css('box-shadow', 'none');
                $(this).css('z-index', 'auto');
            }
        );

        // Keyboard shortcuts for navigation
        $(document).keydown(function(e) {
            // 'j' key - move down
            if (e.keyCode === 74) {
                var $visible = $('.employee-item:visible');
                var $selected = $('.employee-item.selected');

                if ($selected.length) {
                    var index = $visible.index($selected);
                    if (index < $visible.length - 1) {
                        $selected.removeClass('selected');
                        $visible.eq(index + 1).addClass('selected');
                    }
                } else {
                    $visible.first().addClass('selected');
                }

                return false;
            }

            // 'k' key - move up
            if (e.keyCode === 75) {
                var $visible = $('.employee-item:visible');
                var $selected = $('.employee-item.selected');

                if ($selected.length) {
                    var index = $visible.index($selected);
                    if (index > 0) {
                        $selected.removeClass('selected');
                        $visible.eq(index - 1).addClass('selected');
                    }
                } else {
                    $visible.first().addClass('selected');
                }

                return false;
            }

            // 'o' or Enter key - open selected
            if (e.keyCode === 79 || e.keyCode === 13) {
                var $selected = $('.employee-item.selected');
                if ($selected.length) {
                    var detailUrl = $selected.find('a[title="Lihat Detail"]').attr('href');
                    if (detailUrl) {
                        window.location.href = detailUrl;
                    }
                }

                return false;
            }
        });
    });
</script>
@stop