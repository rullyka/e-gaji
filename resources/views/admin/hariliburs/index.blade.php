@extends('adminlte::page')

@section('title', 'Hari Libur Management')

@section('content_header')
<h1>Hari Libur Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Hari Libur List</h3>
        <div class="card-tools">
            @can_show('hariliburs.create')
            <a href="{{ route('hariliburs.generate-sundays-form') }}" class="mr-1 btn btn-info btn-sm">
                <i class="fas fa-calendar-plus"></i> Generate Hari Minggu
            </a>
            <a href="{{ route('hariliburs.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Hari Libur
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

        <table class="table table-bordered" id="hariliburTable">
            <thead>
                <tr>
                    <th width="10">#</th>
                    <th>Tanggal</th>
                    <th>Nama Libur</th>
                    <th>Keterangan</th>
                    @can_show('hariliburs.edit')
                    <th width="150">Action</th>
                    @endcan_show
                </tr>
            </thead>
            <tbody id="hariliburTableBody">
                @foreach($hariliburs as $index => $harilibur)
                <tr data-id="{{ $harilibur->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $harilibur->tanggal->format('d-m-Y') }}</td>
                    <td>{{ $harilibur->nama_libur }}</td>
                    <td>{{ $harilibur->keterangan ?? '-' }}</td>
                    @can_show('hariliburs.edit')
                    <td>
                        <a href="{{ route('hariliburs.edit', $harilibur) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('hariliburs.destroy', $harilibur) }}" method="POST" class="d-inline">
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
        $('#hariliburTable').DataTable({
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
