<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bagian;
use App\Models\Departemen;

class BagianController extends Controller
{
    public function index()
    {
        $bagians = Bagian::with('departemen')->orderBy('name_bagian')->get();
        return view('admin.bagians.index', compact('bagians'));
    }

    public function create()
    {
        $departemens = Departemen::orderBy('name_departemen')->get();
        return view('admin.bagians.create', compact('departemens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_bagian' => 'required|string|max:255|unique:bagians,name_bagian',
            'id_departemen' => 'nullable|exists:departemens,id',
        ]);

        Bagian::create($request->all());

        return redirect()->route('bagians.index')
            ->with('success', 'Bagian berhasil ditambahkan');
    }

    public function show(Bagian $bagian)
    {
        $bagian->load(['departemen', 'karyawans.jabatan']);
        return view('admin.bagians.show', compact('bagian'));
    }

    public function edit(Bagian $bagian)
    {
        $departemens = Departemen::orderBy('name_departemen')->get();
        return view('admin.bagians.edit', compact('bagian', 'departemens'));
    }

    public function update(Request $request, Bagian $bagian)
    {
        $request->validate([
            'name_bagian' => 'required|string|max:255|unique:bagians,name_bagian,'.$bagian->id,
            'id_departemen' => 'nullable|exists:departemens,id',
        ]);

        $bagian->update($request->all());

        return redirect()->route('bagians.index')
            ->with('success', 'Bagian berhasil diupdate');
    }

    public function destroy(Bagian $bagian)
    {
        if($bagian->karyawans()->exists()) {
            return redirect()->route('bagians.index')
                ->with('error', 'Hapus semua karyawan pada bagian ini terlebih dahulu');
        }

        $bagian->delete();
        return redirect()->route('bagians.index')
            ->with('success', 'Bagian berhasil dihapus');
    }
}
