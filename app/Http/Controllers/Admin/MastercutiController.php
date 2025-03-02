<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mastercuti;

class MastercutiController extends Controller
{
    public function index()
    {
        $mastercutis = Mastercuti::orderBy('uraian')->get();
        return view('admin.mastercutis.index', compact('mastercutis'));
    }

    public function create()
    {
        return view('admin.mastercutis.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'uraian' => 'required|string|max:255|unique:mastercutis,uraian',
            'is_bulanan' => 'nullable|boolean',
            'cuti_max' => 'nullable|string|max:255',
            'izin_max' => 'nullable|string|max:255',
            'is_potonggaji' => 'nullable|boolean',
            'nominal' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();

        // Set default values for checkboxes if not present
        $data['is_bulanan'] = $request->has('is_bulanan') ? 1 : 0;
        $data['is_potonggaji'] = $request->has('is_potonggaji') ? 1 : 0;

        // Clean and format nominal if present
        if (!empty($data['nominal'])) {
            $data['nominal'] = preg_replace('/[^0-9]/', '', $data['nominal']);
        }

        Mastercuti::create($data);

        return redirect()->route('mastercutis.index')
            ->with('success', 'Master cuti berhasil ditambahkan');
    }

    public function show(Mastercuti $mastercuti)
    {
        return view('admin.mastercutis.show', compact('mastercuti'));
    }

    public function edit(Mastercuti $mastercuti)
    {
        return view('admin.mastercutis.edit', compact('mastercuti'));
    }

    public function update(Request $request, Mastercuti $mastercuti)
    {
        $request->validate([
            'uraian' => 'required|string|max:255|unique:mastercutis,uraian,' . $mastercuti->id,
            'is_bulanan' => 'nullable|boolean',
            'cuti_max' => 'nullable|string|max:255',
            'izin_max' => 'nullable|string|max:255',
            'is_potonggaji' => 'nullable|boolean',
            'nominal' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();

        // Set default values for checkboxes if not present
        $data['is_bulanan'] = $request->has('is_bulanan') ? 1 : 0;
        $data['is_potonggaji'] = $request->has('is_potonggaji') ? 1 : 0;

        // Clean and format nominal if present
        if (!empty($data['nominal'])) {
            $data['nominal'] = preg_replace('/[^0-9]/', '', $data['nominal']);
        }

        $mastercuti->update($data);

        return redirect()->route('mastercutis.index')
            ->with('success', 'Master cuti berhasil diupdate');
    }

    public function destroy(Mastercuti $mastercuti)
    {
        // Check if this mastercuti is used in other tables before deleting
        // For example:
        // if($mastercuti->related_models()->exists()) {
        //    return redirect()->route('mastercutis.index')
        //        ->with('error', 'Cannot delete this cuti because it is used in other records');
        // }

        $mastercuti->delete();

        return redirect()->route('mastercutis.index')
            ->with('success', 'Master cuti berhasil dihapus');
    }
}
