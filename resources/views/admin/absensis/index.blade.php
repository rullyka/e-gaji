@extends('adminlte::page')

@section('title', 'Manajemen Absensi')

@section('content_header')
<h1>Manajemen Absensi</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Absensi</h3>
        <div class="card-tools">
            @can_show('absensis.create')
            <a href="{{ route('absensis.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Absensi Baru
            </a>
            @endcan_show
        </div>
    </div>
    <div class="card-body">
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
                    <tr data-id="{{ $absensi->id }}">
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
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(function() {
        $('#absensiTable').DataTable({
            "paging": true
            , "lengthChange": true
            , "searching": true
            , "ordering": true
            , "info": true
            , "autoWidth": false
            , "responsive": true
        , });
    });

</script>
@stop
