<?php

namespace App\Http\Controllers\Admin;

use App\Models\MesinAbsensi;
use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MesinAbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mesinAbsensis = MesinAbsensi::orderBy('nama')->get();
        return view('admin.mesinabsensis.index', compact('mesinAbsensis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.mesinabsensis.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'alamat_ip' => 'required|string|max:255',
            'kunci_komunikasi' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'status_aktif' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Set default value for status_aktif if not present
        if (!$request->has('status_aktif')) {
            $request->merge(['status_aktif' => 0]);
        }

        MesinAbsensi::create($request->all());

        return redirect()->route('mesinabsensis.index')
            ->with('success', 'Mesin Absensi berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(MesinAbsensi $mesinabsensi)
    {
        return view('admin.mesinabsensis.show', compact('mesinabsensi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MesinAbsensi $mesinabsensi)
    {
        return view('admin.mesinabsensis.edit', compact('mesinabsensi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MesinAbsensi $mesinabsensi)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'alamat_ip' => 'required|string|max:255',
            'kunci_komunikasi' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'status_aktif' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Set default value for status_aktif if not present
        if (!$request->has('status_aktif')) {
            $request->merge(['status_aktif' => 0]);
        }

        $mesinabsensi->update($request->all());

        return redirect()->route('mesinabsensis.index')
            ->with('success', 'Mesin Absensi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MesinAbsensi $mesinabsensi)
    {
        // Check if the machine is being used for attendance
        if ($mesinabsensi->absensisMasuk()->count() > 0 || $mesinabsensi->absensisPulang()->count() > 0) {
            return redirect()->route('mesinabsensis.index')
                ->with('error', 'Mesin Absensi tidak dapat dihapus karena sedang digunakan pada data absensi');
        }

        $mesinabsensi->delete();

        return redirect()->route('mesinabsensis.index')
            ->with('success', 'Mesin Absensi berhasil dihapus');
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleStatus(MesinAbsensi $mesinabsensi)
    {
        $mesinabsensi->status_aktif = !$mesinabsensi->status_aktif;
        $mesinabsensi->save();

        return redirect()->route('mesinabsensis.index')
            ->with('success', 'Status Mesin Absensi berhasil diperbarui');
    }

    /**
     * Test connection to the machine.
     */
    public function testConnection(MesinAbsensi $mesinabsensi)
    {
        $result = $mesinabsensi->testConnection();

        if ($result === true) {
            return redirect()->back()->with('success', 'Koneksi ke mesin absensi berhasil');
        } else {
            return redirect()->back()->with('error', 'Koneksi ke mesin absensi gagal: ' . $result);
        }
    }

    /**
     * Download attendance logs from the machine.
     */
    public function downloadLogs(MesinAbsensi $mesinabsensi)
    {
        $logs = $mesinabsensi->getAttendanceLogs();

        if (is_array($logs)) {
            return view('admin.mesinabsensis.logs', compact('mesinabsensi', 'logs'));
        } else {
            return redirect()->back()->with('error', 'Gagal mengambil log absensi: ' . $logs);
        }
    }

    /**
     * Process logs from attendance machine to create attendance records.
     */
    public function processLogs(Request $request, MesinAbsensi $mesinabsensi)
    {
        if (!$request->has('selected_logs') || !is_array($request->selected_logs) || count($request->selected_logs) === 0) {
            return redirect()->back()->with('error', 'Tidak ada log yang dipilih untuk diproses');
        }

        $processed = 0;
        $errors = 0;

        foreach ($request->selected_logs as $logString) {
            list($pin, $datetime) = explode('_', $logString);

            // Find karyawan by PIN (assuming NIK is used as PIN)
            $karyawan = Karyawan::where('nik', $pin)->first();

            if (!$karyawan) {
                $errors++;
                continue;
            }

            $dateTimeObj = Carbon::parse($datetime);
            $date = $dateTimeObj->format('Y-m-d');
            $time = $dateTimeObj->format('H:i:s');

            // Get default jadwal kerja for this day
            $jadwalKerja = \App\Models\JadwalKerja::where('status_aktif', 1)->first();

            if (!$jadwalKerja) {
                $errors++;
                continue;
            }

            // Check if this is check-in or check-out
            // Assumption: Before 12:00 is check-in, after 12:00 is check-out
            $isCheckIn = $dateTimeObj->hour < 12;

            if ($isCheckIn) {
                // Check if record for this date already exists
                $existingRecord = Absensi::where('karyawan_id', $karyawan->id)
                    ->where('tanggal', $date)
                    ->first();

                if ($existingRecord) {
                    // Update existing record's check-in time
                    $existingRecord->jam_masuk = $time;
                    $existingRecord->jenis_absensi_masuk = 'Mesin';
                    $existingRecord->mesinabsensi_masuk_id = $mesinabsensi->id;

                    // Calculate keterlambatan
                    $jadwalMasuk = Carbon::parse($jadwalKerja->jam_masuk);
                    $actualMasuk = Carbon::parse($time);

                    if ($actualMasuk->gt($jadwalMasuk)) {
                        $keterlambatan = $jadwalMasuk->diffInMinutes($actualMasuk);
                        $existingRecord->keterlambatan = $keterlambatan;
                        $existingRecord->status = 'Terlambat';
                    }

                    $existingRecord->save();
                } else {
                    // Create new attendance record
                    $attendanceData = [
                        'karyawan_id' => $karyawan->id,
                        'tanggal' => $date,
                        'jadwalkerja_id' => $jadwalKerja->id,
                        'jam_masuk' => $time,
                        'jam_pulang' => null,
                        'jenis_absensi_masuk' => 'Mesin',
                        'mesinabsensi_masuk_id' => $mesinabsensi->id,
                        'jenis_absensi_pulang' => 'Manual',
                        'mesinabsensi_pulang_id' => null,
                        'status' => 'Hadir',
                    ];

                    // Calculate keterlambatan
                    $jadwalMasuk = Carbon::parse($jadwalKerja->jam_masuk);
                    $actualMasuk = Carbon::parse($time);

                    if ($actualMasuk->gt($jadwalMasuk)) {
                        $keterlambatan = $jadwalMasuk->diffInMinutes($actualMasuk);
                        $attendanceData['keterlambatan'] = $keterlambatan;
                        $attendanceData['status'] = 'Terlambat';
                    } else {
                        $attendanceData['keterlambatan'] = 0;
                    }

                    Absensi::create($attendanceData);
                }
            } else {
                // This is a check-out
                // Find today's attendance record
                $existingRecord = Absensi::where('karyawan_id', $karyawan->id)
                    ->where('tanggal', $date)
                    ->first();

                if ($existingRecord) {
                    // Update existing record's check-out time
                    $existingRecord->jam_pulang = $time;
                    $existingRecord->jenis_absensi_pulang = 'Mesin';
                    $existingRecord->mesinabsensi_pulang_id = $mesinabsensi->id;

                    // Calculate pulang_awal
                    $jadwalPulang = Carbon::parse($jadwalKerja->jam_pulang);
                    $actualPulang = Carbon::parse($time);

                    if ($actualPulang->lt($jadwalPulang)) {
                        $pulangAwal = $actualPulang->diffInMinutes($jadwalPulang);
                        $existingRecord->pulang_awal = $pulangAwal;
                    }

                    // Calculate total_jam
                    if ($existingRecord->jam_masuk) {
                        $masuk = Carbon::parse($existingRecord->jam_masuk);
                        $pulang = Carbon::parse($time);

                        if ($pulang->lt($masuk)) {
                            $pulang->addDay(); // Handle overnight shifts
                        }

                        $totalJam = $pulang->diffForHumans($masuk, true);
                        $existingRecord->total_jam = $totalJam;
                    }

                    $existingRecord->save();
                } else {
                    // Create new attendance record with only checkout
                    $attendanceData = [
                        'karyawan_id' => $karyawan->id,
                        'tanggal' => $date,
                        'jadwalkerja_id' => $jadwalKerja->id,
                        'jam_masuk' => null,
                        'jam_pulang' => $time,
                        'jenis_absensi_masuk' => 'Manual',
                        'mesinabsensi_masuk_id' => null,
                        'jenis_absensi_pulang' => 'Mesin',
                        'mesinabsensi_pulang_id' => $mesinabsensi->id,
                        'status' => 'Hadir',
                        'keterlambatan' => 0,
                    ];

                    // Calculate pulang_awal
                    $jadwalPulang = Carbon::parse($jadwalKerja->jam_pulang);
                    $actualPulang = Carbon::parse($time);

                    if ($actualPulang->lt($jadwalPulang)) {
                        $pulangAwal = $actualPulang->diffInMinutes($jadwalPulang);
                        $attendanceData['pulang_awal'] = $pulangAwal;
                    } else {
                        $attendanceData['pulang_awal'] = 0;
                    }

                    Absensi::create($attendanceData);
                }
            }

            $processed++;
        }

        return redirect()->back()->with('success', "Berhasil memproses $processed data absensi" . ($errors > 0 ? " ($errors error)" : ""));
    }

    /**
     * Show form to upload user names to the machine.
     */
    public function showUploadNames(MesinAbsensi $mesinabsensi)
    {

        return view('admin.mesinabsensis.upload-names', compact('mesinabsensi'));
    }

    /**
     * Upload user names to the machine.
     */
    public function uploadNames(Request $request, MesinAbsensi $mesinabsensi)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:10',
            'name' => 'required|string|max:30',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $result = $mesinabsensi->uploadName($request->user_id, $request->name);

        return redirect()->back()->with('info', 'Hasil upload nama: ' . $result);
    }

    /**
     * Upload batch names to the attendance machine.
     */
    public function uploadNamesBatch(Request $request, MesinAbsensi $mesinabsensi)
    {
        if (!$request->has('batch_data') || empty($request->batch_data)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diupload.');
        }

        $batchData = json_decode($request->batch_data, true);

        if (!is_array($batchData) || count($batchData) === 0) {
            return redirect()->back()->with('error', 'Format data tidak valid.');
        }

        $success = 0;
        $failed = 0;
        $results = [];

        foreach ($batchData as $item) {
            $result = $mesinabsensi->uploadName($item['id'], $item['name']);

            if (strpos($result, 'successful') !== false || strpos($result, 'berhasil') !== false) {
                $success++;
            } else {
                $failed++;
            }

            $results[] = "ID: {$item['id']}, Nama: {$item['name']} - Hasil: {$result}";
        }

        // Create result message
        $resultMessage = "Upload selesai. Berhasil: $success, Gagal: $failed";

        // Store detailed results in session
        session()->flash('upload_results', $results);

        return redirect()->back()->with('info', $resultMessage);
    }
}