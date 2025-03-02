<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jabatan;

class JabatanController extends Controller
{
    public function index()
    {
        $jabatans = Jabatan::orderBy('name_jabatan')->get();
        return view('admin.jabatans.index', compact('jabatans'));
    }

    public function create()
    {
        return view('admin.jabatans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_jabatan' => 'required|string|max:255|unique:jabatans,name_jabatan',
            'gaji_pokok' => 'required|numeric|min:0',
            'premi' => 'required|numeric|min:0',
            'tunjangan_jabatan' => 'required|numeric|min:0',
            'uang_lembur_biasa' => 'required|numeric|min:0',
            'uang_lembur_libur' => 'required|numeric|min:0',
        ]);

        // Clean the monetary values to store as integers
        $data = $request->all();
        $data['gaji_pokok'] = $this->cleanMoneyFormat($request->gaji_pokok);
        $data['premi'] = $this->cleanMoneyFormat($request->premi);
        $data['tunjangan_jabatan'] = $this->cleanMoneyFormat($request->tunjangan_jabatan);
        $data['uang_lembur_biasa'] = $this->cleanMoneyFormat($request->uang_lembur_biasa);
        $data['uang_lembur_libur'] = $this->cleanMoneyFormat($request->uang_lembur_libur);

        Jabatan::create($data);

        return redirect()->route('jabatans.index')
            ->with('success', 'Jabatan berhasil ditambahkan');
    }

    public function show(Jabatan $jabatan)
    {
        return view('admin.jabatans.show', compact('jabatan'));
    }

    public function edit(Jabatan $jabatan)
    {
        return view('admin.jabatans.edit', compact('jabatan'));
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        $request->validate([
            'name_jabatan' => 'required|string|max:255|unique:jabatans,name_jabatan,'.$jabatan->id,
            'gaji_pokok' => 'required|numeric|min:0',
            'premi' => 'required|numeric|min:0',
            'tunjangan_jabatan' => 'required|numeric|min:0',
            'uang_lembur_biasa' => 'required|numeric|min:0',
            'uang_lembur_libur' => 'required|numeric|min:0',
        ]);

        // Clean the monetary values to store as integers
        $data = $request->all();
        $data['gaji_pokok'] = $this->cleanMoneyFormat($request->gaji_pokok);
        $data['premi'] = $this->cleanMoneyFormat($request->premi);
        $data['tunjangan_jabatan'] = $this->cleanMoneyFormat($request->tunjangan_jabatan);
        $data['uang_lembur_biasa'] = $this->cleanMoneyFormat($request->uang_lembur_biasa);
        $data['uang_lembur_libur'] = $this->cleanMoneyFormat($request->uang_lembur_libur);

        $jabatan->update($data);

        return redirect()->route('jabatans.index')
            ->with('success', 'Jabatan berhasil diupdate');
    }

    public function destroy(Jabatan $jabatan)
    {
        // Check for relationships before deleting
        // For example:
        // if($jabatan->karyawans()->exists()) {
        //     return redirect()->route('jabatans.index')
        //         ->with('error', 'Hapus semua karyawan dengan jabatan ini terlebih dahulu');
        // }

        $jabatan->delete();
        return redirect()->route('jabatans.index')
            ->with('success', 'Jabatan berhasil dihapus');
    }

    /**
     * Clean money format by removing non-numeric characters
     */
    private function cleanMoneyFormat($value)
    {
        // Remove any non-numeric characters except for the decimal point
        $cleanValue = preg_replace('/[^0-9.]/', '', $value);

        // Convert to integer (cents)
        return (int) $cleanValue;
    }
}