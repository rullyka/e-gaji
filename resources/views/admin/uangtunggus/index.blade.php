@extends('adminlte::page')

@section('title', 'Uang Tunggu Management')

@section('content_header')
<h1>Uang Tunggu Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Uang Tunggu</h3>
        <div class="card-tools">
            @can_show('uang_tunggu.create')
            <a href="{{ route('uangtunggus.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Uang Tunggu Baru
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

        <table class="table table-bordered" id="uangTungguTable">
            <thead>
                <tr>
                    <th width="10">#</th>
                    <th>Nama Karyawan</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Nominal</th>
                    @can_show('uang_tunggu.edit')
                    <th width="150">Action</th>
                    @endcan_show
                </tr>
            </thead>
            <tbody>
                @foreach($uangTunggus as $index => $uangTunggu)
                <tr data-id="{{ $uangTunggu->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $uangTunggu->karyawan->nama_karyawan ?? 'N/A' }}</td>
                    <td>{{ $uangTunggu->tanggal_mulai->format('d-m-Y') }}</td>
                    <td>{{ $uangTunggu->tanggal_selesai->format('d-m-Y') }}</td>
                    <td>Rp {{ number_format($uangTunggu->nominal, 0, ',', '.') }}</td>
                    @can_show('uang_tunggu.edit')
                    <td>
                        <a href="{{ route('uangtunggus.show', $uangTunggu) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('uangtunggus.edit', $uangTunggu) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('uangtunggus.destroy', $uangTunggu) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
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
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(function() {
        $('#uangTungguTable').DataTable({
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
