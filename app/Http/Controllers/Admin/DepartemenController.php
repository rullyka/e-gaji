<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Departemen;

class DepartemenController extends Controller
{
    public function index()
    {
        $departemens = Departemen::orderBy('name_departemen')->get();
        return view('admin.departemens.index', compact('departemens'));
    }

    public function create()
    {
        return view('admin.departemens.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_departemen' => 'required|string|max:255|unique:departemens,name_departemen',
        ]);

        Departemen::create($request->all());

        return redirect()->route('departemens.index')
            ->with('success', 'Departemen berhasil ditambahkan');
    }

    public function show(Departemen $departemen)
    {
        $departemen->load('bagians');
        return view('admin.departemens.show', compact('departemen'));
    }

    public function edit(Departemen $departemen)
    {
        return view('admin.departemens.edit', compact('departemen'));
    }

    public function update(Request $request, Departemen $departemen)
    {
        $request->validate([
            'name_departemen' => 'required|string|max:255|unique:departemens,name_departemen,'.$departemen->id,
        ]);

        $departemen->update($request->all());

        return redirect()->route('departemens.index')
            ->with('success', 'Departemen berhasil diupdate');
    }

    public function destroy(Departemen $departemen)
    {
        if($departemen->bagians()->exists()) {
            return redirect()->route('departemens.index')
                ->with('error', 'Hapus semua bagian pada departemen ini terlebih dahulu');
        }

        $departemen->delete();
        return redirect()->route('departemens.index')
            ->with('success', 'Departemen berhasil dihapus');
    }
}