@extends('adminlte::page')

@section('title', 'Laporan Kehadiran')

@section('content_header')
<h1>Laporan Kehadiran</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
        <div class="card-tools">
            <button type="button" id="printReport" class="btn btn-success btn-sm mr-1">
                <i class="fas fa-print"></i> Cetak
            </button>
            <a href="{{ route('jadwalkerjas.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('jadwalkerjas.report') }}" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_date">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="karyawan_id">Karyawan</label>
                        <select class="form-control select2" id="karyawan_id" name="karyawan_id">
                            <option value="">-- Semua Karyawan --</option>
                            @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ $karyawanId == $karyawan->id ? 'selected' : '' }}>
                                {{ $karyawan->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('jadwalkerjas.report') }}" class="btn btn-default">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="printableArea">
    <!-- Report Header -->
    <div class="text-center mb-4">
        <h4>LAPORAN KEHADIRAN KARYAWAN</h4>
        <h5>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</h5>
        @if($karyawanId && isset($summary[$karyawanId]['karyawan']))
        <h5>Karyawan: {{ $summary[$karyawanId]['karyawan']->name }}</h5>
        @endif
    </div>

    <!-- Summary Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ringkasan Kehadiran</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px">No</th>
                            <th>Nama Karyawan</th>
                            <th>Total Hari</th>
                            @foreach($shifts as $shift)
                            <th>{{ $shift->kode_shift }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary as $key => $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item['karyawan']->name }}</td>
                            <td>{{ $item['total_days'] }}</td>
                            @foreach($shifts as $shift)
                            <td>{{ $item['shift_counts'][$shift->id] ?? 0 }}</td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 3 + count($shifts) }}" class="text-center">Tidak ada data kehadiran</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Report -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Kehadiran</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px">No</th>
                            <th>Tanggal</th>
                            <th>Nama Karyawan</th>
                            <th>Kode Shift</th>
                            <th>Jenis Shift</th>
                            <th>Jam Kerja</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalkerjas as $key => $attendance)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $attendance->tanggal->format('d-m-Y') }}</td>
                            <td>{{ $attendance->karyawan->name ?? 'N/A' }}</td>
                            <td>{{ $attendance->shift->kode_shift ?? 'N/A' }}</td>
                            <td>{{ $attendance->shift->jenis_shift ?? 'N/A' }}</td>
                            <td>
                                @if($attendance->shift)
                                {{ $attendance->shift->jam_masuk }} - {{ $attendance->shift->jam_pulang }}
                                @else
                                N/A
                                @endif
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
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #printableArea,
        #printableArea * {
            visibility: visible;
        }

        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }

</style>
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

        // Print functionality
        $('#printReport').click(function() {
            window.print();
            return false;
        });
    });

</script>
@stop
