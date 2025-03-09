@extends('adminlte::page')

@section('title', 'Data Kehadiran')

@section('content_header')
<h1>Data Kehadiran</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Kehadiran</h3>
        <div class="card-tools">
            <a href="{{ route('jadwalkerjas.report') }}" class="btn btn-info btn-sm mr-1">
                <i class="fas fa-file-alt"></i> Laporan
            </a>
            <a href="{{ route('jadwalkerjas.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kehadiran
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('jadwalkerjas.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date">Tanggal</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="karyawan_id">Karyawan</label>
                        <select class="form-control select2" id="karyawan_id" name="karyawan_id">
                            <option value="">-- Semua Karyawan --</option>
                            @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ request('karyawan_id') == $karyawan->id ? 'selected' : '' }}>
                                {{ $karyawan->nama_karyawan }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('jadwalkerjas.index') }}" class="btn btn-default">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px">No</th>
                        <th>Tanggal</th>
                        <th>Nama Karyawan</th>
                        <th>Shift</th>
                        <th>Jam Kerja</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalkerjas as $key => $jadwalkerja)
                    <tr>
                        <td>{{ $jadwalkerjas->firstItem() + $key }}</td>
                        <td>{{ $jadwalkerja->tanggal->format('d-m-Y') }}</td>
                        <td>{{ $jadwalkerja->karyawan->nama_karyawan ?? 'N/A' }}</td>
                        <td>{{ $jadwalkerja->shift->jenis_shift ?? 'N/A' }}</td>
                        <td>
                            @if($jadwalkerja->shift)
                            {{ $jadwalkerja->shift->jam_masuk->translatedFormat('H i') }} - {{ $jadwalkerja->shift->jam_pulang->translatedFormat('H i') }}
                            @else
                            N/A
                            @endif
                        </td>
                        <td>
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
                        <td colspan="6" class="text-center">Tidak ada data kehadiran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $jadwalkerjas->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4'
            , width: '100%'
        });
    });

</script>
@stop
