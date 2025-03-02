<div>
    <!-- It is quality rather than quantity that matters. - Lucius Annaeus Seneca -->
</div>
@extends('adminlte::page')

@section('title', 'Program Studi Management')

@section('content_header')
<h1>Program Studi Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Program Studi List</h3>
        <div class="card-tools">
            @can_show('program_studi.create')
            <a href="{{ route('program_studis.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Program Studi
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

        <table class="table table-bordered" id="programStudiTable">
            <thead>
                <tr>
                    <th width="10">#</th>
                    <th>Nama Program Studi</th>
                    <th>Tanggal Dibuat</th>
                    @can_show('program_studi.edit')
                    <th width="150">Action</th>
                    @endcan_show
                </tr>
            </thead>
            <tbody id="programStudiTableBody">
                @foreach($programStudis as $index => $programStudi)
                <tr data-id="{{ $programStudi->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $programStudi->name_programstudi }}</td>
                    <td>{{ $programStudi->created_at->format('d-m-Y H:i:s') }}</td>
                    @can_show('program_studi.edit')
                    <td>
                        <a href="{{ route('program_studis.edit', $programStudi) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('program_studis.destroy', $programStudi) }}" method="POST" class="d-inline">
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
        $('#programStudiTable').DataTable({
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
