<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('parent')
            ->orderBy('parent_id')
            ->orderBy('order')
            ->get();
        return view('admin.menu.index', compact('menus'));
    }

    public function create()
    {
        $parentMenus = Menu::whereNull('parent_id')->get();
        $permissions = Permission::all();
        return view('admin.menu.create', compact('parentMenus', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required',
            'type' => 'required|in:header,menu',
            'icon' => 'nullable',
            'route' => 'nullable|required_if:type,menu',
            'parent_id' => 'nullable|exists:menus,id',
            'permission' => 'nullable|exists:permissions,name',
            'order' => 'required|integer'
        ]);

        Menu::create($request->all());

        return redirect()->route('menu.index')
            ->with('success', 'Menu berhasil ditambahkan');
    }

    public function edit(Menu $menu)
    {
        $parentMenus = Menu::whereNull('parent_id')
            ->where('id', '!=', $menu->id)
            ->get();
        $permissions = Permission::all();
        return view('admin.menu.edit', compact('menu', 'parentMenus', 'permissions'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'text' => 'required',
            'type' => 'required|in:header,menu',
            'icon' => 'nullable',
            'route' => 'nullable|required_if:type,menu',
            'parent_id' => 'nullable|exists:menus,id',
            'permission' => 'nullable|exists:permissions,name',
            'order' => 'required|integer'
        ]);

        $menu->update($request->all());

        return redirect()->route('menu.index')
            ->with('success', 'Menu berhasil diupdate');
    }

    public function destroy(Menu $menu)
    {
        if($menu->children()->exists()) {
            return redirect()->route('menu.index')
                ->with('error', 'Hapus submenu terlebih dahulu');
        }

        $menu->delete();
        return redirect()->route('menu.index')
            ->with('success', 'Menu berhasil dihapus');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:menus,id',
            'order.*.order' => 'required|integer'
        ]);

        foreach($request->order as $item) {
            Menu::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }


}
