<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\JadwalKerja;
use App\Models\MesinAbsensi;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $absensis = Absensi::with(['karyawan', 'jadwalKerja', 'mesinAbsensiMasuk', 'mesinAbsensiPulang'])->latest()->get();
        return view('admin.absensis.index', compact('absensis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $jadwalKerjas = JadwalKerja::all();
        $mesinAbsensis = MesinAbsensi::where('status_aktif', 1)->get();
        $statusOptions = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti'];
        $jenisAbsensiOptions = ['Manual', 'Mesin'];

        return view('admin.absensis.create', compact('karyawans', 'jadwalKerjas', 'mesinAbsensis', 'statusOptions', 'jenisAbsensiOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'tanggal' => 'required|date',
            'jadwalkerja_id' => 'required|exists:jadwalkerjas,id',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:Hadir,Terlambat,Izin,Sakit,Cuti',
            'jenis_absensi_masuk' => 'required|in:Manual,Mesin',
            'mesinabsensi_masuk_id' => 'nullable|required_if:jenis_absensi_masuk,Mesin|exists:mesinabsensis,id',
            'jenis_absensi_pulang' => 'required|in:Manual,Mesin',
            'mesinabsensi_pulang_id' => 'nullable|required_if:jenis_absensi_pulang,Mesin|exists:mesinabsensis,id',
            'keterlambatan' => 'nullable|integer|min:0',
            'pulang_awal' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Calculate total hours if both check-in and check-out times are present
        $totalJam = null;
        if ($request->jam_masuk && $request->jam_pulang) {
            $masuk = \Carbon\Carbon::createFromFormat('H:i', $request->jam_masuk);
            $pulang = \Carbon\Carbon::createFromFormat('H:i', $request->jam_pulang);

            if ($pulang->lt($masuk)) {
                $pulang->addDay(); // Handle overnight shifts
            }

            $totalJam = $pulang->diffForHumans($masuk, true);
            $request->merge(['total_jam' => $totalJam]);
        }

        Absensi::create($request->all());

        return redirect()->route('absensis.index')
            ->with('success', 'Absensi berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Absensi $absensi)
    {
        $absensi->load(['karyawan', 'jadwalKerja', 'mesinAbsensiMasuk', 'mesinAbsensiPulang']);
        return view('admin.absensis.show', compact('absensi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Absensi $absensi)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $jadwalKerjas = JadwalKerja::all();
        $mesinAbsensis = MesinAbsensi::where('status_aktif', 1)->get();
        $statusOptions = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti'];
        $jenisAbsensiOptions = ['Manual', 'Mesin'];

        return view('admin.absensis.edit', compact('absensi', 'karyawans', 'jadwalKerjas', 'mesinAbsensis', 'statusOptions', 'jenisAbsensiOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Absensi $absensi)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'tanggal' => 'required|date',
            'jadwalkerja_id' => 'required|exists:jadwalkerjas,id',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:Hadir,Terlambat,Izin,Sakit,Cuti',
            'jenis_absensi_masuk' => 'required|in:Manual,Mesin',
            'mesinabsensi_masuk_id' => 'nullable|required_if:jenis_absensi_masuk,Mesin|exists:mesinabsensis,id',
            'jenis_absensi_pulang' => 'required|in:Manual,Mesin',
            'mesinabsensi_pulang_id' => 'nullable|required_if:jenis_absensi_pulang,Mesin|exists:mesinabsensis,id',
            'keterlambatan' => 'nullable|integer|min:0',
            'pulang_awal' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Calculate total hours if both check-in and check-out times are present
        $totalJam = null;
        if ($request->jam_masuk && $request->jam_pulang) {
            $masuk = \Carbon\Carbon::createFromFormat('H:i', $request->jam_masuk);
            $pulang = \Carbon\Carbon::createFromFormat('H:i', $request->jam_pulang);

            if ($pulang->lt($masuk)) {
                $pulang->addDay(); // Handle overnight shifts
            }

            $totalJam = $pulang->diffForHumans($masuk, true);
            $request->merge(['total_jam' => $totalJam]);
        }

        $absensi->update($request->all());

        return redirect()->route('absensis.index')
            ->with('success', 'Absensi berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Absensi $absensi)
    {
        $absensi->delete();
        return redirect()->route('absensis.index')
            ->with('success', 'Absensi berhasil dihapus');
    }
}