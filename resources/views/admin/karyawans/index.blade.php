@extends('adminlte::page')

@section('title', 'Karyawan Management')

@section('content_header')
<h1>Karyawan Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Karyawan</h3>
        <div class="card-tools">
            @can_show('karyawans.create')
            <a href="{{ route('karyawans.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Karyawan
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
            <table class="table table-bordered table-striped" id="karyawanTable">
                <thead>
                    <tr>
                        <th width="10">#</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Departemen</th>
                        <th>Bagian</th>
                        <th>Jabatan</th>
                        <th>Profesi</th>
                        <th>Status</th>
                        <th>Tgl Masuk</th>
                        @can_show('karyawans.edit')
                        <th width="150">Action</th>
                        @endcan_show
                    </tr>
                </thead>
                <tbody>
                    @foreach($karyawans as $index => $karyawan)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $karyawan->nik_karyawan }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-2">
                                    <img src="{{ $karyawan->foto_url }}" alt="{{ $karyawan->nama_karyawan }}" class="img-circle" width="40">
                                </div>
                                <div>{{ $karyawan->nama_karyawan }}</div>
                            </div>
                        </td>
                        <td>{{ $karyawan->departemen ? $karyawan->departemen->name_departemen : '-' }}</td>
                        <td>{{ $karyawan->bagian ? $karyawan->bagian->name_bagian : '-' }}</td>
                        <td>{{ $karyawan->jabatan ? $karyawan->jabatan->name_jabatan : '-' }}</td>
                        <td>{{ $karyawan->profesi ? $karyawan->profesi->name_profesi : '-' }}</td>
                        <td>{{ $karyawan->statuskaryawan }}</td>
                        <td>{{ $karyawan->tgl_awalmmasuk->format('d-m-Y') }}</td>
                        @can_show('karyawans.edit')
                        <td>
                            <a href="{{ route('karyawans.show', $karyawan) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('karyawans.edit', $karyawan) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('karyawans.destroy', $karyawan) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this employee?')">
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
        $('#karyawanTable').DataTable({
            "paging": true
            , "lengthChange": true
            , "searching": true
            , "ordering": true
            , "info": true
            , "autoWidth": false
            , "responsive": true
            , "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });
    });

</script>
@stop
