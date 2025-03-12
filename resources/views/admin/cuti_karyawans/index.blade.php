@extends('adminlte::page')

@section('title', 'Data Cuti Karyawan')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="mr-2 fas fa-calendar-alt text-primary"></i>Pengajuan Cuti Karyawan</h1>
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
                    @can_show('cuti_karyawan.create')
                    <a href="{{ route('cuti_karyawans.create') }}" class="btn btn-primary btn-block">
                        <i class="mr-1 fas fa-plus"></i> Ajukan Cuti
                    </a>
                    @endcan_show
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active filter-category" data-filter="all">
                        <i class="mr-2 fas fa-inbox"></i> Semua
                        <span class="float-right badge badge-light">{{ count($cutiKaryawans) }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Menunggu Persetujuan">
                        <i class="mr-2 fas fa-clock text-warning"></i> Menunggu
                        <span class="float-right badge badge-warning">{{ $cutiKaryawans->where('status_acc', 'Menunggu Persetujuan')->count() }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Disetujui">
                        <i class="mr-2 fas fa-check text-success"></i> Disetujui
                        <span class="float-right badge badge-success">{{ $cutiKaryawans->where('status_acc', 'Disetujui')->count() }}</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action filter-category" data-filter="Ditolak">
                        <i class="mr-2 fas fa-times text-danger"></i> Ditolak
                        <span class="float-right badge badge-danger">{{ $cutiKaryawans->where('status_acc', 'Ditolak')->count() }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right side with inbox -->
    <div class="col-md-9 col-lg-10">
        <div class="card">
            <div class="bg-white card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="mr-2 input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari pengajuan...">
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
                <div class="inbox-container">
                    @foreach($cutiKaryawans as $index => $cuti)
                    <div class="inbox-item {{ $cuti->status_acc == 'Menunggu Persetujuan' ? 'unread' : '' }}" data-status="{{ $cuti->status_acc }}">
                        <div class="inbox-item-checkbox">
                            <div class="icheck-primary">
                                <input type="checkbox" id="check{{ $cuti->id }}">
                                <label for="check{{ $cuti->id }}"></label>
                            </div>
                        </div>
                        <div class="inbox-item-star">
                            @if($cuti->status_acc == 'Menunggu Persetujuan')
                            <i class="fas fa-circle text-warning"></i>
                            @elseif($cuti->status_acc == 'Disetujui')
                            <i class="fas fa-check-circle text-success"></i>
                            @elseif($cuti->status_acc == 'Ditolak')
                            <i class="fas fa-times-circle text-danger"></i>
                            @endif
                        </div>
                        <div class="inbox-item-sender">
                            <span class="sender-name">{{ $cuti->karyawan ? $cuti->karyawan->nama_karyawan : '-' }}</span>
                        </div>
                        <div class="inbox-item-subject">
                            <span class="badge badge-{{ $cuti->jenis_cuti == 'Cuti' ? 'info' : 'secondary' }} mr-1">{{ $cuti->jenis_cuti }}</span>
                            {{ $cuti->masterCuti ? $cuti->masterCuti->uraian : 'Pengajuan Cuti' }}
                        </div>
                        <div class="inbox-item-date">
                            <span class="date">{{ $cuti->tanggal_mulai_formatted }} s/d {{ $cuti->tanggal_akhir_formatted }}</span>
                            <span class="ml-2 days">({{ $cuti->jumlah_hari_cuti }} hari)</span>
                        </div>
                        <div class="inbox-item-actions">
                            <a href="{{ route('cuti_karyawans.show', $cuti) }}" class="btn btn-sm btn-light" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>

                            @if($cuti->status_acc == 'Menunggu Persetujuan')
                                @can_show('cuti_karyawan.edit')
                                <a href="{{ route('cuti_karyawans.edit', $cuti) }}" class="btn btn-sm btn-light" title="Edit Pengajuan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan_show

                                @can_show('cuti_karyawan.approve')
                                <a href="{{ route('cuti_karyawans.approval', $cuti) }}" class="btn btn-sm btn-primary" title="Proses Pengajuan">
                                    <i class="fas fa-check"></i>
                                </a>
                                @endcan_show

                                @can_show('cuti_karyawan.delete')
                                <form action="{{ route('cuti_karyawans.destroy', $cuti) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light" title="Hapus Pengajuan" onclick="return confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan_show
                            @else
                                @can_show('cuti_karyawan.approve')
                                <a href="{{ route('cuti_karyawans.approval', $cuti) }}" class="btn btn-sm btn-light" title="Lihat Detail Persetujuan">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                @endcan_show
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @if(count($cutiKaryawans) == 0)
                    <div class="py-5 text-center">
                        <p class="text-muted">Tidak ada pengajuan cuti yang ditemukan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .inbox-container {
        border-top: 1px solid #e0e0e0;
    }

    .inbox-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
        transition: all 0.2s;
        cursor: pointer;
    }

    .inbox-item:hover {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        z-index: 1;
        position: relative;
    }

    .inbox-item.unread {
        background-color: #f2f6fc;
        font-weight: 500;
    }

    .inbox-item-checkbox {
        width: 30px;
    }

    .inbox-item-star {
        width: 30px;
        text-align: center;
    }

    .inbox-item-sender {
        width: 180px;
        padding-right: 15px;
    }

    .sender-name {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .inbox-item-subject {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 15px;
        color: #5f6368;
    }

    .inbox-item.unread .inbox-item-subject {
        color: #202124;
    }

    .inbox-item-date {
        width: 100px;
        text-align: right;
        color: #5f6368;
        font-size: 0.85rem;
    }

    .inbox-item-actions {
        width: 120px;
        text-align: right;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .inbox-item:hover .inbox-item-actions {
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

    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
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

    @media (max-width: 992px) {
        .inbox-item-date {
            width: 80px;
        }

        .inbox-item-sender {
            width: 150px;
        }
    }

    @media (max-width: 768px) {
        .inbox-item {
            flex-wrap: wrap;
        }

        .inbox-item-sender {
            width: calc(100% - 60px);
            order: 1;
        }

        .inbox-item-checkbox {
            order: 0;
        }

        .inbox-item-star {
            order: 2;
        }

        .inbox-item-subject {
            width: 100%;
            order: 3;
            padding-left: 30px;
            margin-top: 5px;
        }

        .inbox-item-date {
            width: 50%;
            order: 4;
            text-align: left;
            padding-left: 30px;
            margin-top: 5px;
        }

        .inbox-item-actions {
            width: 50%;
            order: 5;
            text-align: right;
            margin-top: 5px;
            opacity: 1;
        }
    }
</style>
@stop
@section('js')
<script>
    $(function() {
        // Filter functionality using left sidebar
        $('.filter-category').click(function(e) {
            e.preventDefault();
            $('.filter-category').removeClass('active');
            $(this).addClass('active');

            var filter = $(this).data('filter');

            if (filter === 'all') {
                $('.inbox-item').show();
            } else {
                $('.inbox-item').hide();
                $('.inbox-item[data-status="' + filter + '"]').show();
            }

            updateEmptyState();
        });

        // Search functionality
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var visibleCount = 0;

            $('.inbox-item').each(function() {
                var text = $(this).text().toLowerCase();
                var isVisible = text.indexOf(value) > -1;
                $(this).toggle(isVisible);

                if (isVisible) {
                    visibleCount++;
                }
            });

            updateEmptyState();
        });

        // Refresh button
        $('#refreshBtn').click(function() {
            $(this).find('i').addClass('fa-spin');
            setTimeout(function() {
                location.reload();
            }, 500);
        });

        // Click on row to view details
        $('.inbox-item').click(function(e) {
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
        $('.inbox-item-checkbox input').click(function(e) {
            e.stopPropagation();
        });

        // Function to show/hide empty state
        function updateEmptyState() {
            var visibleItems = $('.inbox-item:visible').length;

            if (visibleItems === 0) {
                if ($('.empty-state').length === 0) {
                    $('.inbox-container').append(`
                        <div class="py-5 text-center empty-state">
                            <p class="text-muted">Tidak ada pengajuan cuti yang sesuai dengan filter</p>
                        </div>
                    `);
                }
            } else {
                $('.empty-state').remove();
            }
        }

        // Set first filter as active by default
        $('.filter-btn[data-filter="all"]').addClass('active');
    });
</script>
@stop