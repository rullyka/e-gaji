<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bagian;
use App\Models\Departemen;

class BagianController extends Controller
{
    /**
     * Menampilkan daftar semua bagian
     */
    public function index()
    {
        // Ambil semua data bagian dengan relasi departemen dan urutkan berdasarkan nama
        $bagians = Bagian::with('departemen')->orderBy('name_bagian')->get();
        return view('admin.bagians.index', compact('bagians'));
    }

    /**
     * Menampilkan form untuk membuat bagian baru
     */
    public function create()
    {
        // Siapkan data departemen untuk dropdown di form
        $departemens = Departemen::orderBy('name_departemen')->get();
        return view('admin.bagians.create', compact('departemens'));
    }

    /**
     * Menyimpan data bagian baru ke database
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'name_bagian' => 'required|string|max:255|unique:bagians,name_bagian',
            'id_departemen' => 'nullable|exists:departemens,id',
        ]);

        // Simpan data bagian ke database
        Bagian::create($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('bagians.index')
            ->with('success', 'Bagian berhasil ditambahkan');
    }

    /**
     * Menampilkan detail bagian tertentu
     */
    public function show(Bagian $bagian)
    {
        // Muat relasi departemen dan karyawan beserta jabatannya
        $bagian->load(['departemen', 'karyawans.jabatan']);
        return view('admin.bagians.show', compact('bagian'));
    }

    /**
     * Menampilkan form untuk mengedit bagian
     */
    public function edit(Bagian $bagian)
    {
        // Siapkan data departemen untuk dropdown di form edit
        $departemens = Departemen::orderBy('name_departemen')->get();
        return view('admin.bagians.edit', compact('bagian', 'departemens'));
    }

    /**
     * Memperbarui data bagian di database
     */
    public function update(Request $request, Bagian $bagian)
    {
        // Validasi input dari form edit
        $request->validate([
            'name_bagian' => 'required|string|max:255|unique:bagians,name_bagian,'.$bagian->id,
            'id_departemen' => 'nullable|exists:departemens,id',
        ]);

        // Update data bagian di database
        $bagian->update($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('bagians.index')
            ->with('success', 'Bagian berhasil diupdate');
    }

    /**
     * Menghapus data bagian dari database
     */
    public function destroy(Bagian $bagian)
    {
        // Cek apakah masih ada karyawan di bagian ini
        if($bagian->karyawans()->exists()) {
            // Jika masih ada karyawan, tampilkan pesan error
            return redirect()->route('bagians.index')
                ->with('error', 'Hapus semua karyawan pada bagian ini terlebih dahulu');
        }

        // Hapus data bagian
        $bagian->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('bagians.index')
            ->with('success', 'Bagian berhasil dihapus');
    }
}
