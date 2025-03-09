<?php

namespace App\Http\Controllers\Admin;

use App\Models\Karyawan;
use App\Models\Uangtunggu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UangTungguController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $uangTunggus = Uangtunggu::with('karyawan')->latest()->get();
        return view('admin.uangtunggus.index', compact('uangTunggus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('admin.uangtunggus.create', compact('karyawans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'nominal' => 'required|numeric|min:0',
        ]);

        Uangtunggu::create($request->all());

        return redirect()->route('uangtunggus.index')
            ->with('success', 'Uang Tunggu berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Uangtunggu $uangtunggu)
    {
        $uangtunggu->load('karyawan');
        return view('admin.uangtunggus.show', compact('uangtunggu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Uangtunggu $uangtunggu)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        return view('admin.uangtunggus.edit', compact('uangtunggu', 'karyawans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Uangtunggu $uangtunggu)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'nominal' => 'required|numeric|min:0',
        ]);

        $uangtunggu->update($request->all());

        return redirect()->route('uangtunggus.index')
            ->with('success', 'Uang Tunggu berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Uangtunggu $uangtunggu)
    {
        $uangtunggu->delete();
        return redirect()->route('uangtunggus.index')
            ->with('success', 'Uang Tunggu berhasil dihapus');
    }
}