<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Departemen;

class DepartemenController extends Controller
{
    /**
     * Menampilkan daftar semua departemen
     */
    public function index()
    {
        // Ambil semua data departemen dan urutkan berdasarkan nama
        $departemens = Departemen::orderBy('name_departemen')->get();
        return view('admin.departemens.index', compact('departemens'));
    }

    /**
     * Menampilkan form untuk membuat departemen baru
     */
    public function create()
    {
        return view('admin.departemens.create');
    }

    /**
     * Menyimpan data departemen baru ke database
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'name_departemen' => 'required|string|max:255|unique:departemens,name_departemen',
        ]);

        // Simpan data departemen ke database
        Departemen::create($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('departemens.index')
            ->with('success', 'Departemen berhasil ditambahkan');
    }

    /**
     * Menampilkan detail departemen tertentu
     */
    public function show(Departemen $departemen)
    {
        // Muat relasi bagian untuk ditampilkan
        $departemen->load('bagians');
        return view('admin.departemens.show', compact('departemen'));
    }

    /**
     * Menampilkan form untuk mengedit departemen
     */
    public function edit(Departemen $departemen)
    {
        return view('admin.departemens.edit', compact('departemen'));
    }

    /**
     * Memperbarui data departemen di database
     */
    public function update(Request $request, Departemen $departemen)
    {
        // Validasi input dari form edit
        $request->validate([
            'name_departemen' => 'required|string|max:255|unique:departemens,name_departemen,'.$departemen->id,
        ]);

        // Update data departemen di database
        $departemen->update($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('departemens.index')
            ->with('success', 'Departemen berhasil diupdate');
    }

    /**
     * Menghapus data departemen dari database
     */
    public function destroy(Departemen $departemen)
    {
        // Cek apakah masih ada bagian di departemen ini
        if($departemen->bagians()->exists()) {
            // Jika masih ada bagian, tampilkan pesan error
            return redirect()->route('departemens.index')
                ->with('error', 'Hapus semua bagian pada departemen ini terlebih dahulu');
        }

        // Hapus data departemen
        $departemen->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('departemens.index')
            ->with('success', 'Departemen berhasil dihapus');
    }
}