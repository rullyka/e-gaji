@extends('adminlte::page')

@section('title', 'Departemen Management')

@section('content_header')
<h1>Departemen Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Departemen List</h3>
        <div class="card-tools">
            @can_show('departemens.create')
            <a href="{{ route('departemens.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Departemen
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

        <table class="table table-bordered" id="departemenTable">
            <thead>
                <tr>
                    <th width="10">#</th>
                    <th>Nama Departemen</th>
                    <th>Tanggal Dibuat</th>
                    @can_show('departemens.edit')
                    <th width="150">Action</th>
                    @endcan_show
                </tr>
            </thead>
            <tbody id="departemenTableBody">
                @foreach($departemens as $index => $departemen)
                <tr data-id="{{ $departemen->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $departemen->name_departemen }}</td>
                    <td>{{ $departemen->created_at->format('d-m-Y H:i:s') }}</td>
                    @can_show('departemens.edit')
                    <td>
                        <a href="{{ route('departemens.edit', $departemen) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('departemens.destroy', $departemen) }}" method="POST" class="d-inline">
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
        $('#departemenTable').DataTable({
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
