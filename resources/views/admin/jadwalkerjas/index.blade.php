@extends('adminlte::page')

@section('title', 'Data Jadwal Kerja')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="mr-2 fas fa-calendar-alt text-primary"></i>Data Jadwal Kerja</h1>
    <div>
        <a href="{{ route('jadwalkerjas.report') }}" class="btn btn-info">
            <i class="mr-1 fas fa-file-alt"></i> Laporan
        </a>
        <a href="{{ route('jadwalkerjas.create') }}" class="ml-2 btn btn-primary">
            <i class="mr-1 fas fa-plus"></i> Tambah Jadwal Kerja
        </a>
    </div>
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

<div class="card">
    <div class="card-header bg-light">
        <h3 class="card-title"><i class="mr-1 fas fa-list"></i> Daftar Jadwal Kerja</h3>
    </div>

    <div class="card-body">
        <div class="row">
            <!-- Left sidebar with filter options -->
            <div class="col-md-4 col-lg-3">
                <div class="p-3 mb-4 rounded bg-light">
                    <h5><i class="mr-1 fas fa-filter"></i> Filter Data</h5>
                    <form method="GET" action="{{ route('jadwalkerjas.index') }}">
                        <div class="form-group">
                            <label for="date"><i class="mr-1 far fa-calendar"></i> Tanggal</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="karyawan_id"><i class="mr-1 far fa-user"></i> Karyawan</label>
                            <select class="form-control select2" id="karyawan_id" name="karyawan_id">
                                <option value="">-- Semua Karyawan --</option>
                                @foreach($karyawans as $karyawan)
                                <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                    {{ $karyawan->nama_karyawan }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="mr-1 fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('jadwalkerjas.index') }}" class="mt-2 btn btn-default btn-block">
                                <i class="mr-1 fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right side with data table -->
            <div class="col-md-8 col-lg-9">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <h4 class="m-0"><i class="mr-1 fas fa-calendar-check"></i> Jadwal Kerja</h4>
                    <div>
                        <button type="button" class="btn btn-light" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px" class="text-center">No</th>
                                <th><i class="mr-1 far fa-calendar"></i> Tanggal</th>
                                <th><i class="mr-1 far fa-user"></i> Nama Karyawan</th>
                                <th><i class="mr-1 fas fa-clock"></i> Shift</th>
                                <th><i class="mr-1 fas fa-hourglass-half"></i> Jam Kerja</th>
                                <th style="width: 150px" class="text-center"><i class="mr-1 fas fa-cogs"></i> Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwalkerjas as $key => $jadwalkerja)
                            <tr>
                                <td class="text-center">{{ $jadwalkerjas->firstItem() + $key }}</td>
                                <td>{{ $jadwalkerja->tanggal->format('d-m-Y') }}</td>
                                <td>{{ $jadwalkerja->karyawan->nama_karyawan ?? 'N/A' }}</td>
                                <td>{{ $jadwalkerja->shift->jenis_shift ?? 'N/A' }}</td>
                                <td>
                                    @if($jadwalkerja->shift)
                                    {{ $jadwalkerja->shift->jam_masuk->translatedFormat('H:i') }} - {{ $jadwalkerja->shift->jam_pulang->translatedFormat('H:i') }}
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('jadwalkerjas.show', $jadwalkerja->id) }}" class="btn btn-info btn-sm" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('jadwalkerjas.edit', $jadwalkerja->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('jadwalkerjas.destroy', $jadwalkerja->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus data ini?');" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-3 text-center">
                                    <i class="mr-1 fas fa-info-circle"></i> Tidak ada data jadwal kerja
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Menampilkan {{ $jadwalkerjas->firstItem() ?? 0 }} - {{ $jadwalkerjas->lastItem() ?? 0 }} dari {{ $jadwalkerjas->total() }} data</span>
                    </div>
                    <div>
                        {{ $jadwalkerjas->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .table th, .table td {
        vertical-align: middle;
    }
    .pagination {
        margin-bottom: 0;
    }
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
    .card {
        border-radius: 8px;
        border: 1px solid #dadce0;
        box-shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
        margin-bottom: 20px;
    }
</style>
@stop

@section('js')
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Auto submit form when date or karyawan changes
        $('#date, #karyawan_id').change(function() {
            $(this).closest('form').submit();
        });

        // Fade out alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

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