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
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mr-1 fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mr-1 fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex mb-2">
                    <div class="btn-group mr-3">
                        <a href="{{ route('jadwalkerjas.index') }}" class="btn btn-default {{ !request('period') || request('period') == 'all' ? 'active' : '' }}">
                            Semua <span class="badge badge-light ml-1">{{ $totalCount }}</span>
                        </a>
                        <a href="{{ route('jadwalkerjas.index', ['period' => 'current']) }}" class="btn btn-default {{ request('period') == 'current' ? 'active' : '' }}">
                            Bulan Ini <span class="badge badge-info ml-1">{{ $thisMonthCount }}</span>
                        </a>
                        <a href="{{ route('jadwalkerjas.index', ['period' => 'next']) }}" class="btn btn-default {{ request('period') == 'next' ? 'active' : '' }}">
                            Bulan Depan <span class="badge badge-success ml-1">{{ $nextMonthCount }}</span>
                        </a>
                    </div>
                </div>
                <form action="{{ route('jadwalkerjas.index') }}" method="GET" class="d-flex flex-wrap">
                    @if(request('period'))
                        <input type="hidden" name="period" value="{{ request('period') }}">
                    @endif

                    <div class="d-flex mr-2 mb-2">
                        <div class="mr-2">
                            <label class="small text-muted d-block mb-1">Tanggal Mulai</label>
                            <div class="input-group" style="width: 200px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div>
                            <label class="small text-muted d-block mb-1">Tanggal Akhir</label>
                            <div class="input-group" style="width: 200px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mr-2 mb-2">
                        <label class="small text-muted d-block mb-1">Karyawan</label>
                        <select class="form-control select2" name="karyawan_id" style="width: 200px;">
                            <option value="">-- Semua Karyawan --</option>
                            @foreach ($karyawans as $karyawan)
                                <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                    {{ $karyawan->nama_karyawan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mr-2 mb-2">
                        <label class="small text-muted d-block mb-1">Shift</label>
                        <select class="form-control select2" name="shift_id" style="width: 200px;">
                            <option value="">-- Semua Shift --</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->kode_shift }} - {{ $shift->nama_shift }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="small text-muted d-block mb-1">Pencarian</label>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" name="search" class="form-control" placeholder="Cari jadwal..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-default" type="submit">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('jadwalkerjas.index') }}" class="btn btn-default">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Tanggal</th>
                            <th width="25%">Karyawan</th>
                            <th width="15%">Shift</th>
                            <th width="20%">Jam Kerja</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalkerjas as $index => $jadwalkerja)
                        <tr>
                            <td>{{ $jadwalkerjas->firstItem() + $index }}</td>
                            <td>{{ $jadwalkerja->tanggal->format('d-m-Y') }}</td>
                            <td>
                                <strong>{{ $jadwalkerja->karyawan->nama_karyawan ?? 'N/A' }}</strong>
                                @if($jadwalkerja->karyawan)
                                    <div class="small text-muted">{{ $jadwalkerja->karyawan->nik_karyawan }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $jadwalkerja->shift->kode_shift ?? 'N/A' }}</span>
                                {{ $jadwalkerja->shift->nama_shift ?? '' }}
                            </td>
                            <td>
                                @if ($jadwalkerja->shift)
                                    {{ $jadwalkerja->shift->jam_masuk->format('H:i') }} -
                                    {{ $jadwalkerja->shift->jam_pulang->format('H:i') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('jadwalkerjas.show', $jadwalkerja->id) }}" class="btn btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('jadwalkerjas.edit', $jadwalkerja->id) }}" class="btn btn-primary" title="Edit Jadwal">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('jadwalkerjas.destroy', $jadwalkerja->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Hapus Jadwal" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-3">
                                <i class="mr-1 fas fa-info-circle"></i> Tidak ada data jadwal kerja yang ditemukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Menampilkan {{ $jadwalkerjas->firstItem() ?? 0 }} sampai {{ $jadwalkerjas->lastItem() ?? 0 }} dari {{ $jadwalkerjas->total() }} data
                </div>
                <div>
                    {{ $jadwalkerjas->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .table td {
            border-top: none;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .badge {
            font-weight: 500;
            padding: 5px 8px;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .btn-group-sm > .btn {
            padding: .25rem .5rem;
        }

        .btn-default.active {
            background-color: #007bff;
            color: white;
        }

        .pagination {
            margin-bottom: 0;
        }

        .select2-container .select2-selection--single {
            height: 38px;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
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

            // Fade out alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Auto-submit form when select changes
            $('select[name="karyawan_id"], select[name="shift_id"]').change(function() {
                $(this).closest('form').submit();
            });
        });
    </script>
@stop
