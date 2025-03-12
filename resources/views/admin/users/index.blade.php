@extends('adminlte::page')
@section('title', 'Users Management')
@section('content_header')
<h1>Users Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Users List</h3>
        <div class="card-tools">
            @can_show('users.create')
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New User
            </a>
            @endcan_show
        </div>
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

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Linked To</th>
                    <th>Roles</th>
                    @can_show('users.create')
                    <th style="width: 150px">Action</th>
                    @endcan_show
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->karyawan)
                        <span class="badge badge-primary">Karyawan</span>
                        @else
                        <span class="badge badge-warning">Owner/Admin</span>
                        @endif
                    </td>
                    <td>
                        @if($user->karyawan)
                        <a href="{{ route('karyawans.show', $user->karyawan->id) }}" class="text-primary">
                            {{ $user->karyawan->nama_karyawan }}
                            ({{ $user->karyawan->nik_karyawan }})
                            <br>
                            <small class="text-muted">{{ $user->karyawan->departemen->name_departemen ?? 'No Department' }}</small>
                        </a>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @foreach($user->roles as $role)
                        <span class="badge badge-info">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    @can_show('users.create')
                    <td>
                        @can_show('users.edit')
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <form action="{{ route('users.reset-password', $user) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm" onclick="return confirm('Reset password to 12345678?')">
                                <i class="fas fa-key"></i>
                            </button>
                        </form>
                        @endcan_show
                    </td>
                    @endcan_show
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@stop
