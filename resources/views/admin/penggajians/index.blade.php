@extends('adminlte::page')

@section('title', 'Data Penggajian')

@section('content_header')
    <h1>Data Penggajian</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Penggajian</h3>
                    <div class="card-tools">
                        <a href="{{ route('penggajian.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Penggajian
                        </a>
                        <div class="ml-2 btn-group">
                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-file-export"></i> Laporan
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('penggajian.reportByPeriod') }}">Laporan Per Periode</a>
                                <a class="dropdown-item" href="{{ route('penggajian.reportByDepartment') }}">Laporan Per Departemen</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error!</h5>
                            {{ session('error') }}
                        </div>
                    @endif

                    <table id="penggajian-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Periode</th>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th>Gaji Pokok</th>
                                <th>Tunjangan</th>
                                <th>Potongan</th>
                                <th>Gaji Bersih</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penggajians as $index => $penggajian)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $penggajian->periodeGaji->nama_periode }}</td>
                                <td>{{ $penggajian->karyawan->nama }}</td>
                                <td>
                                    @php
                                        $departemen = $penggajian->detail_departemen['departemen'] ?? '-';
                                    @endphp
                                    {{ $departemen }}
                                </td>
                                <td>{{ $penggajian->formatCurrency($penggajian->gaji_pokok) }}</td>
                                <td>{{ $penggajian->formatCurrency($penggajian->tunjangan) }}</td>
                                <td>{{ $penggajian->formatCurrency($penggajian->potongan) }}</td>
                                <td>{{ $penggajian->formatCurrency($penggajian->gaji_bersih) }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('penggajian.show', $penggajian->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('penggajian.edit', $penggajian->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('penggajian.payslip', $penggajian->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </a>
                                        <form action="{{ route('penggajian.destroy', $penggajian->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $penggajians->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(function () {
            $('#penggajian-table').DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
@stop
