<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        // Group permissions by module (berdasarkan prefix sebelum tanda titik)
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name',
            'module' => 'required'
        ]);

        // Format permission name: module.action
        $permissionName = $request->module . '.' . $request->name;

        Permission::create(['name' => $permissionName]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil ditambahkan');
    }

    public function edit(Permission $permission)
    {
        // Split permission name into module and action
        [$module, $action] = explode('.', $permission->name);
        $permission->module = $module;
        $permission->action = $action;

        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
            'module' => 'required'
        ]);

        // Format permission name: module.action
        $permissionName = $request->module . '.' . $request->name;

        $permission->update(['name' => $permissionName]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil diupdate');
    }

    public function destroy(Permission $permission)
    {
        // Check if permission is being used by any roles
        if($permission->roles()->count() > 0) {
            return redirect()->route('permissions.index')
                ->with('error', 'Permission tidak bisa dihapus karena masih digunakan oleh role');
        }

        $permission->delete();
        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil dihapus');
    }
}