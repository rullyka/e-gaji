@extends('adminlte::page')

@section('title', 'Data Cuti Karyawan')

@section('content_header')
<h1>Data Cuti Karyawan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pengajuan Cuti</h3>
        <div class="card-tools">
            @can_show('cuti_karyawan.create')
            <a href="{{ route('cuti_karyawans.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajukan Cuti
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
            <table class="table table-bordered table-striped" id="cutiTable">
                <thead>
                    <tr>
                        <th width="10">#</th>
                        <th>Nama Karyawan</th>
                        <th>Jenis</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Akhir</th>
                        <th>Jumlah Hari</th>
                        <th>Supervisor</th>
                        <th>Status</th>
                        <th>Tanggal Pengajuan</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cutiKaryawans as $index => $cuti)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $cuti->karyawan ? $cuti->karyawan->nama_karyawan : '-' }}</td>
                        <td>{{ $cuti->jenis_cuti }}</td>
                        <td>{{ $cuti->tanggal_mulai_formatted }}</td>
                        <td>{{ $cuti->tanggal_akhir_formatted }}</td>
                        <td>{{ $cuti->jumlah_hari_cuti }} hari</td>
                        <td>{{ $cuti->supervisor ? $cuti->supervisor->nama_karyawan : '-' }}</td>
                        <td>
                            <span class="badge {{ $cuti->status_badge_class }}">
                                {{ $cuti->status_acc }}
                            </span>
                        </td>
                        <td>{{ $cuti->created_at->format('d-m-Y') }}</td>
                        <td>
                            <a href="{{ route('cuti_karyawans.show', $cuti) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>

                            @if($cuti->status_acc == 'Menunggu Persetujuan')
                            @can_show('cuti_karyawan.edit')
                            <a href="{{ route('cuti_karyawans.edit', $cuti) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan_show

                            @can_show('cuti_karyawan.approve')
                            <a href="{{ route('cuti_karyawans.approval', $cuti) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-check"></i>
                            </a>
                            @endcan_show

                            @can_show('cuti_karyawan.delete')
                            <form action="{{ route('cuti_karyawans.destroy', $cuti) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan_show
                            @else
                            <!-- View only options for approved/rejected requests -->
                            @can_show('cuti_karyawan.approve')
                            <a href="{{ route('cuti_karyawans.approval', $cuti) }}" class="btn btn-secondary btn-sm">
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
        $('#cutiTable').DataTable({
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
