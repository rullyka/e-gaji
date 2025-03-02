@extends('adminlte::page')

@section('title', 'Edit Program Studi')

@section('content_header')
<h1>Edit Program Studi</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Program Studi Details</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('program_studis.update', $programStudi) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name_programstudi">Nama Program Studi</label>
                <input type="text" class="form-control @error('name_programstudi') is-invalid @enderror" id="name_programstudi" name="name_programstudi" value="{{ old('name_programstudi', $programStudi->name_programstudi) }}" required>
                @error('name_programstudi')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="form-group">
                <a href="{{ route('program_studis.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@stop
