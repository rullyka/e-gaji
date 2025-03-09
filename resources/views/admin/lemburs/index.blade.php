@extends('adminlte::page')

@section('title', 'Data Lembur Karyawan')

@section('content_header')
<h1>Data Lembur Karyawan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pengajuan Lembur</h3>
        <div class="card-tools">
            @can_show('lemburs.create')
            <a href="{{ route('lemburs.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajukan Lembur
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
            <table class="table table-bordered table-striped" id="lemburTable">
                <thead>
                    <tr>
                        <th width="10">#</th>
                        <th>Nama Karyawan</th>
                        <th>Jenis Lembur</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Total</th>
                        <th>Supervisor</th>
                        <th>Status</th>
                        <th>Tanggal Pengajuan</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lemburs as $index => $lembur)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $lembur->karyawan ? $lembur->karyawan->nama_karyawan : '-' }}</td>
                        <td>{{ $lembur->jenis_lembur }}</td>
                        <td>{{ $lembur->tanggal_lembur_formatted }}</td>
                        <td>{{ $lembur->jam_mulai_formatted }} - {{ $lembur->jam_selesai_formatted }}</td>
                        <td>{{ $lembur->total_lembur }}</td>
                        <td>{{ $lembur->supervisor ? $lembur->supervisor->nama_karyawan : '-' }}</td>
                        <td>
                            <span class="badge {{ $lembur->status_badge_class }}">
                                {{ $lembur->status }}
                            </span>
                        </td>
                        <td>{{ $lembur->created_at->format('d-m-Y') }}</td>
                        <td>
                            <a href="{{ route('lemburs.show', $lembur) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>

                            @if($lembur->status == 'Menunggu Persetujuan')
                            @can_show('lemburs.edit')
                            <a href="{{ route('lemburs.edit', $lembur) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan_show

                            @can_show('lemburs.approve')
                            <a href="{{ route('lemburs.approval', $lembur) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-check"></i>
                            </a>
                            @endcan_show

                            @can_show('lemburs.delete')
                            <form action="{{ route('lemburs.destroy', $lembur) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan_show
                            @else
                            <!-- View only options for approved/rejected requests -->
                            @can_show('lemburs.approve')
                            <a href="{{ route('lemburs.approval', $lembur) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-info"></i>
                            </a>
                            @endcan_show
                            @endif
                        </td>
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
        $('#lemburTable').DataTable({
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
