@extends('adminlte::page')

@section('title', 'Manage Periode Gaji')

@section('content_header')
<h1>Manage Periode Gaji</h1>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-check"></i> Success!</h5>
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Periode Gaji List</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#generateModal">
                <i class="fas fa-calendar"></i> Generate Bulanan
            </button>
            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#generateWeeklyModal">
                <i class="fas fa-calendar-week"></i> Generate Mingguan
            </button>
            <a href="{{ route('periodegaji.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New
            </a>
        </div>
    </div>
    <div class="card-body">
        <form id="deleteMultipleForm" action="{{ url('/admin/periodegaji/delete-multiple') }}" method="POST">
            @csrf
            <div class="mb-3">
                <button type="button" class="btn btn-danger btn-sm" id="deleteSelected" disabled>
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>

            <table id="periodegaji-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50px">
                            <div class="icheck-primary">
                                <input type="checkbox" id="selectAll">
                                <label for="selectAll"></label>
                            </div>
                        </th>
                        <th>No</th>
                        <th>Nama Periode</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($periodeGajis as $index => $periode)
                    <tr>
                        <td>
                            <div class="icheck-primary">
                                <input type="checkbox" class="periode-checkbox" id="check{{ $periode->id }}" name="ids[]" value="{{ $periode->id }}" {{ $periode->status == 'aktif' ? 'disabled' : '' }}>
                                <label for="check{{ $periode->id }}"></label>
                            </div>
                        </td>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $periode->nama_periode }}</td>
                        <td>{{ $periode->tanggal_mulai->format('d-m-Y') }}</td>
                        <td>{{ $periode->tanggal_selesai->format('d-m-Y') }}</td>
                        <td>
                            @if($periode->status == 'aktif')
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('periodegaji.show', $periode->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('periodegaji.edit', $periode->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($periode->status != 'aktif')
                            <form action="{{ route('periodegaji.set-active', $periode->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i> Set Aktif
                                </button>
                            </form>
                            <form action="{{ route('periodegaji.destroy', $periode->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this period?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>
</div>

<!-- Generate Monthly Periods Modal -->
<div class="modal fade" id="generateModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Generate Periode Bulanan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('periodegaji.generate-monthly') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="year">Tahun</label>
                        <input type="number" class="form-control" id="year" name="year" value="{{ date('Y') }}" min="2000" max="2100" required>
                    </div>
                    <div class="form-group">
                        <label for="start_day">Tanggal Mulai (Hari)</label>
                        <input type="number" class="form-control" id="start_day" name="start_day" value="1" min="1" max="28" required>
                        <small class="form-text text-muted">Hari dalam bulan untuk tanggal mulai periode</small>
                    </div>
                    <div class="form-group">
                        <label for="end_day">Tanggal Selesai</label>
                        <select class="form-control" id="end_day" name="end_day" required>
                            <option value="end_of_month">Akhir Bulan</option>
                            <option value="25">Tanggal 25</option>
                            <option value="26">Tanggal 26</option>
                            <option value="27">Tanggal 27</option>
                            <option value="28">Tanggal 28</option>
                        </select>
                        <small class="form-text text-muted">Pilih tanggal selesai periode</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Generate Weekly Periods Modal -->
<div class="modal fade" id="generateWeeklyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Generate Periode Mingguan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('periodegaji.generate-weekly') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="year">Tahun</label>
                        <input type="number" class="form-control" id="year" name="year" value="{{ date('Y') }}" min="2000" max="2100" required>
                    </div>
                    <div class="form-group">
                        <label for="month">Bulan</label>
                        <select class="form-control" id="month" name="month" required>
                            <option value="1" {{ date('n') == 1 ? 'selected' : '' }}>Januari</option>
                            <option value="2" {{ date('n') == 2 ? 'selected' : '' }}>Februari</option>
                            <option value="3" {{ date('n') == 3 ? 'selected' : '' }}>Maret</option>
                            <option value="4" {{ date('n') == 4 ? 'selected' : '' }}>April</option>
                            <option value="5" {{ date('n') == 5 ? 'selected' : '' }}>Mei</option>
                            <option value="6" {{ date('n') == 6 ? 'selected' : '' }}>Juni</option>
                            <option value="7" {{ date('n') == 7 ? 'selected' : '' }}>Juli</option>
                            <option value="8" {{ date('n') == 8 ? 'selected' : '' }}>Agustus</option>
                            <option value="9" {{ date('n') == 9 ? 'selected' : '' }}>September</option>
                            <option value="10" {{ date('n') == 10 ? 'selected' : '' }}>Oktober</option>
                            <option value="11" {{ date('n') == 11 ? 'selected' : '' }}>November</option>
                            <option value="12" {{ date('n') == 12 ? 'selected' : '' }}>Desember</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start_day_of_week">Hari Mulai Minggu</label>
                        <select class="form-control" id="start_day_of_week" name="start_day_of_week" required>
                            <option value="monday">Senin</option>
                            <option value="tuesday">Selasa</option>
                            <option value="wednesday">Rabu</option>
                            <option value="thursday">Kamis</option>
                            <option value="friday">Jumat</option>
                            <option value="saturday">Sabtu</option>
                            <option value="sunday">Minggu</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('js')
<script>
    $(function () {
        $('#periodegaji-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });

        // Handle select all checkbox
        $('#selectAll').on('click', function() {
            $('.periode-checkbox:not(:disabled)').prop('checked', this.checked);
            updateDeleteButton();
        });

        // Handle individual checkboxes
        $('.periode-checkbox').on('click', function() {
            updateDeleteButton();

            // If any checkbox is unchecked, uncheck the "select all" checkbox
            if (!this.checked) {
                $('#selectAll').prop('checked', false);
            }

            // If all individual checkboxes are checked, check the "select all" checkbox
            if ($('.periode-checkbox:checked').length === $('.periode-checkbox:not(:disabled)').length) {
                $('#selectAll').prop('checked', true);
            }
        });

        // Handle delete selected button
        $('#deleteSelected').on('click', function() {
            if (confirm('Are you sure you want to delete all selected periods?')) {
                $('#deleteMultipleForm').submit();
            }
        });

        // Function to update delete button state
        function updateDeleteButton() {
            if ($('.periode-checkbox:checked').length > 0) {
                $('#deleteSelected').prop('disabled', false);
            } else {
                $('#deleteSelected').prop('disabled', true);
            }
        }
    });
</script>
@stop
