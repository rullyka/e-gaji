@extends('adminlte::page')

@section('title', 'Menu Management')

@section('content_header')
<h1>Menu Management</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Menu List</h3>
        <div class="card-tools">
            @can_action
            <a href="{{ route('menu.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Menu
            </a>
            @endcan_action
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

        <table class="table table-bordered" id="menuTable">
            <thead>
                <tr>
                    <th width="10">#</th>
                    <th>Text</th>
                    <th>Type</th>
                    <th>Icon</th>
                    <th>Route</th>
                    <th>Parent</th>
                    <th>Permission</th>
                    <th>Order</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
            <tbody id="menuTableBody">
                @foreach($menus as $menu)
                <tr data-id="{{ $menu->id }}">
                    <td><i class="fas fa-arrows-alt handle" style="cursor: move"></i></td>
                    <td>{{ $menu->text }}</td>
                    <td>{{ ucfirst($menu->type) }}</td>
                    <td>
                        @if($menu->icon)
                        <i class="{{ $menu->icon }}"></i> {{ $menu->icon }}
                        @endif
                    </td>
                    <td>{{ $menu->route }}</td>
                    <td>{{ $menu->parent->text ?? '-' }}</td>
                    <td>{{ $menu->permission ?? '-' }}</td>
                    <td>{{ $menu->order }}</td>
                    <td>
                        <a href="{{ route('menu.edit', $menu) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('menu.destroy', $menu) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@stop

@section('js')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(function() {
        $("#menuTableBody").sortable({
            handle: '.handle'
            , update: function(event, ui) {
                let order = [];
                $('#menuTableBody tr').each(function(index) {
                    order.push({
                        id: $(this).data('id')
                        , order: index + 1
                    });
                });

                // Update order via AJAX
                $.ajax({
                    url: '{{ route("menu.update-order") }}'
                    , method: 'POST'
                    , data: {
                        _token: '{{ csrf_token() }}'
                        , order: order
                    }
                    , success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            }
        });
    });

</script>
@stop
