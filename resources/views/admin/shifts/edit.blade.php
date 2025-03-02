@extends('adminlte::page')

@section('title', 'Edit Shift')

@section('content_header')
<h1>Edit Shift</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Shift Form</h3>
        <div class="card-tools">
            <a href="{{ route('shifts.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <form action="{{ route('shifts.update', $shift) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="kode_shift">Kode Shift <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('kode_shift') is-invalid @enderror" id="kode_shift" name="kode_shift" value="{{ old('kode_shift', $shift->kode_shift) }}" required>
                @error('kode_shift')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="jenis_shift">Jenis Shift <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('jenis_shift') is-invalid @enderror" id="jenis_shift" name="jenis_shift" value="{{ old('jenis_shift', $shift->jenis_shift) }}" required>
                @error('jenis_shift')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="jam_masuk">Jam Masuk <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('jam_masuk') is-invalid @enderror" id="jam_masuk" name="jam_masuk" value="{{ old('jam_masuk', $shift->jam_masuk ? date('H:i', strtotime($shift->jam_masuk)) : '') }}" required>
                        @error('jam_masuk')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="jam_pulang">Jam Pulang <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('jam_pulang') is-invalid @enderror" id="jam_pulang" name="jam_pulang" value="{{ old('jam_pulang', $shift->jam_pulang ? date('H:i', strtotime($shift->jam_pulang)) : '') }}" required>
                        @error('jam_pulang')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="alert alert-info">
                    <strong>Durasi Shift:</strong> <span id="duration-display">{{ $shift->duration }}</span>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="{{ route('shifts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<!-- Add CSS if needed -->
@stop

@section('js')
<script>
    $(function() {
        // Calculate duration when time changes
        $('#jam_masuk, #jam_pulang').on('change', function() {
            calculateDuration();
        });

        function calculateDuration() {
            var start = $('#jam_masuk').val();
            var end = $('#jam_pulang').val();

            if (start && end) {
                // Convert to Date objects for comparison
                var startDate = new Date('2000-01-01T' + start + ':00');
                var endDate = new Date('2000-01-01T' + end + ':00');

                // If end time is before start time, assume it's the next day
                if (endDate < startDate) {
                    endDate.setDate(endDate.getDate() + 1);
                }

                // Calculate duration in minutes
                var durationMinutes = (endDate - startDate) / (1000 * 60);
                var hours = Math.floor(durationMinutes / 60);
                var minutes = durationMinutes % 60;

                // Format the duration
                var durationText = hours + ' jam ' + minutes + ' menit';

                // Display the duration
                $('#duration-display').text(durationText);
            }
        }
    });

</script>
@stop
