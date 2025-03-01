@extends('adminlte::page')

@section('title', 'Permissions Management')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Permissions List</h1>
    <a href="{{ route('permissions.update-db') }}" class="btn btn-primary">
        <i class="mr-1 fas fa-sync-alt"></i> Update to Database
    </a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">System Permissions</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="row">
            @foreach($controllerPermissions as $module => $controller)
            <div class="mb-4 col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="mr-2 fas fa-file-code text-primary"></i>{{ $controller['name'] }}
                        </h5>
                        <small class="text-muted">
                            <code>{{ Str::after($controller['path'], base_path('/')) }}</code>
                        </small>
                    </div>
                    <div class="p-0 card-body">
                        <div class="list-group list-group-flush">
                            @foreach($controller['permissions'] as $methodName => $permissionType)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="mr-2 badge badge-info">{{ $methodName }}()</span>
                                    <i class="fas fa-arrow-right text-muted"></i>
                                </div>
                                @if($permissionType == 'view')
                                <span class="badge badge-success">{{ $module }}.{{ $permissionType }}</span>
                                @elseif($permissionType == 'create')
                                <span class="badge badge-primary">{{ $module }}.{{ $permissionType }}</span>
                                @elseif($permissionType == 'edit')
                                <span class="badge badge-warning">{{ $module }}.{{ $permissionType }}</span>
                                @elseif($permissionType == 'delete')
                                <span class="badge badge-danger">{{ $module }}.{{ $permissionType }}</span>
                                @else
                                <span class="badge badge-secondary">{{ $module }}.{{ $permissionType }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="bg-white card-footer">
        <small class="text-muted">
            <i class="mr-1 fas fa-info-circle"></i>
            Click "Update to Database" to save these permissions to the database for use in roles
        </small>
    </div>
</div>
@stop

@section('css')
<style>
    code {
        font-size: 80%;
    }

</style>
@stop
