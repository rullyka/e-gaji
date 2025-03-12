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
     * Menampilkan daftar semua absensi
     */
    public function index()
    {
        // Ambil semua data absensi dengan relasi terkait dan urutkan dari yang terbaru
        $absensis = Absensi::with(['karyawan', 'jadwalKerja', 'mesinAbsensiMasuk', 'mesinAbsensiPulang'])->latest()->get();
        return view('admin.absensis.index', compact('absensis'));
    }

    /**
     * Menampilkan form untuk membuat absensi baru
     */
    public function create()
    {
        // Siapkan data untuk dropdown di form
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $jadwalKerjas = JadwalKerja::all();
        $mesinAbsensis = MesinAbsensi::where('status_aktif', 1)->get();
        $statusOptions = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti'];
        $jenisAbsensiOptions = ['Manual', 'Mesin'];

        return view('admin.absensis.create', compact('karyawans', 'jadwalKerjas', 'mesinAbsensis', 'statusOptions', 'jenisAbsensiOptions'));
    }

    /**
     * Menyimpan data absensi baru ke database
     */
    public function store(Request $request)
    {
        // Validasi input dari form
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

        // Hitung total jam kerja jika ada jam masuk dan jam pulang
        $totalJam = null;
        if ($request->jam_masuk && $request->jam_pulang) {
            // Konversi string jam ke objek Carbon
            $masuk = \Carbon\Carbon::createFromFormat('H:i', $request->jam_masuk);
            $pulang = \Carbon\Carbon::createFromFormat('H:i', $request->jam_pulang);

            // Jika jam pulang lebih kecil dari jam masuk, berarti shift malam
            if ($pulang->lt($masuk)) {
                $pulang->addDay(); // Tambah 1 hari untuk shift yang melewati tengah malam
            }

            // Hitung selisih waktu dalam format yang mudah dibaca
            $totalJam = $pulang->diffForHumans($masuk, true);
            $request->merge(['total_jam' => $totalJam]);
        }

        // Simpan data absensi ke database
        Absensi::create($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('absensis.index')
            ->with('success', 'Absensi berhasil ditambahkan');
    }

    /**
     * Menampilkan detail absensi tertentu
     */
    public function show(Absensi $absensi)
    {
        // Muat relasi yang dibutuhkan untuk detail absensi
        $absensi->load(['karyawan', 'jadwalKerja', 'mesinAbsensiMasuk', 'mesinAbsensiPulang']);
        return view('admin.absensis.show', compact('absensi'));
    }

    /**
     * Menampilkan form untuk mengedit absensi
     */
    public function edit(Absensi $absensi)
    {
        // Siapkan data untuk dropdown di form edit
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $jadwalKerjas = JadwalKerja::all();
        $mesinAbsensis = MesinAbsensi::where('status_aktif', 1)->get();
        $statusOptions = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti'];
        $jenisAbsensiOptions = ['Manual', 'Mesin'];

        return view('admin.absensis.edit', compact('absensi', 'karyawans', 'jadwalKerjas', 'mesinAbsensis', 'statusOptions', 'jenisAbsensiOptions'));
    }

    /**
     * Memperbarui data absensi di database
     */
    public function update(Request $request, Absensi $absensi)
    {
        // Validasi input dari form edit
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

        // Hitung total jam kerja jika ada jam masuk dan jam pulang
        $totalJam = null;
        if ($request->jam_masuk && $request->jam_pulang) {
            // Konversi string jam ke objek Carbon
            $masuk = \Carbon\Carbon::createFromFormat('H:i', $request->jam_masuk);
            $pulang = \Carbon\Carbon::createFromFormat('H:i', $request->jam_pulang);

            // Jika jam pulang lebih kecil dari jam masuk, berarti shift malam
            if ($pulang->lt($masuk)) {
                $pulang->addDay(); // Tambah 1 hari untuk shift yang melewati tengah malam
            }

            // Hitung selisih waktu dalam format yang mudah dibaca
            $totalJam = $pulang->diffForHumans($masuk, true);
            $request->merge(['total_jam' => $totalJam]);
        }

        // Update data absensi di database
        $absensi->update($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('absensis.index')
            ->with('success', 'Absensi berhasil diupdate');
    }

    /**
     * Menghapus data absensi dari database
     */
    public function destroy(Absensi $absensi)
    {
        // Hapus data absensi
        $absensi->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('absensis.index')
            ->with('success', 'Absensi berhasil dihapus');
    }

    /**
     * Check if an employee has a work schedule for the given date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSchedule(Request $request)
    {
        $karyawanId = $request->input('karyawan_id');
        $tanggal = $request->input('tanggal');

        // Check if the employee has a schedule for this date
        // This query will need to be adjusted based on your database structure
        $jadwal = Jadwalkerja::where('karyawan_id', $karyawanId)
            ->where('tanggal', $tanggal)
            ->first();

        if ($jadwal) {
            return response()->json([
                'has_schedule' => true,
                'jadwal_id' => $jadwal->jadwalkerja_id
            ]);
        }

        return response()->json([
            'has_schedule' => false
        ]);
    }
}
