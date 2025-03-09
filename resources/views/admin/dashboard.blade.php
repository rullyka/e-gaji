@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Dashboard</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <p class="mb-0">Welcome to Admin Dashboard!</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
{{-- Add any additional CSS here --}}
@stop

@section('js')
<script>
    console.log('Hi!');

</script>
@stop
