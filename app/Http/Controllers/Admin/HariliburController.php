<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Harilibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\AbsensiController;

class HariliburController extends Controller
{
    /**
     * Menampilkan daftar semua hari libur
     */
    public function index()
    {
        // Ambil semua data hari libur dan urutkan dari tanggal terbaru
        $hariliburs = Harilibur::orderBy('tanggal', 'desc')->get();
        return view('admin.hariliburs.index', compact('hariliburs'));
    }

    /**
     * Menampilkan form untuk membuat hari libur baru
     */
    public function create()
    {
        return view('admin.hariliburs.create');
    }

    /**
     * Menyimpan data hari libur baru ke database
     */
    /**
 * Menyimpan data hari libur baru ke database
 */
public function store(Request $request)
{
    // Validasi input dari form
    $request->validate([
        'tanggal' => 'required|date|unique:hariliburs,tanggal',
        'nama_libur' => 'required|string|max:255',
        'keterangan' => 'nullable|string',
    ]);

    // Simpan data hari libur ke database
    $harilibur = Harilibur::create($request->all());

    // Buat absensi otomatis untuk semua karyawan pada hari libur ini
    $absensiController = app()->make('AbsensiController');
    $count = $absensiController->createAbsensiForHoliday($harilibur->id);

    // Redirect ke halaman index dengan pesan sukses
    return redirect()->route('hariliburs.index')
        ->with('success', 'Hari Libur berhasil ditambahkan dan ' . $count . ' absensi otomatis telah dibuat');
}

    /**
     * Menampilkan detail hari libur tertentu
     */
    public function show(Harilibur $harilibur)
    {
        return view('admin.hariliburs.show', compact('harilibur'));
    }

    /**
     * Menampilkan form untuk mengedit hari libur
     */
    public function edit(Harilibur $harilibur)
    {
        return view('admin.hariliburs.edit', compact('harilibur'));
    }

    /**
     * Memperbarui data hari libur di database
     */
    public function update(Request $request, Harilibur $harilibur)
    {
        // Validasi input dari form edit
        $request->validate([
            'tanggal' => 'required|date|unique:hariliburs,tanggal,'.$harilibur->id,
            'nama_libur' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        // Update data hari libur di database
        $harilibur->update($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('hariliburs.index')
            ->with('success', 'Hari Libur berhasil diupdate');
    }

    /**
     * Menghapus data hari libur dari database
     */
    public function destroy(Harilibur $harilibur)
    {
        // Hapus data hari libur
        $harilibur->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('hariliburs.index')
            ->with('success', 'Hari Libur berhasil dihapus');
    }

    /**
     * Menampilkan form untuk generate hari Minggu dalam satu tahun
     */
    public function generateSundaysForm()
    {
        $currentYear = date('Y');
        $yearOptions = [];

        // Buat opsi tahun (tahun sekarang dan 5 tahun ke depan)
        for ($i = 0; $i < 6; $i++) {
            $year = $currentYear + $i;
            $yearOptions[$year] = $year;
        }

        return view('admin.hariliburs.generate_sundays', compact('yearOptions', 'currentYear'));
    }

    /**
     * Generate semua hari Minggu untuk tahun yang dipilih
     */
    /**
 * Generate semua hari Minggu untuk tahun yang dipilih
 */
public function generateSundays(Request $request)
{
    // Validasi input dari form
    $request->validate([
        'year' => 'required|integer|min:2000|max:2099',
        'replace_existing' => 'nullable|boolean',
        'create_attendance' => 'nullable|boolean', // Tambahkan opsi untuk membuat absensi
    ]);

    $year = $request->input('year');
    $replaceExisting = $request->has('replace_existing');
    $createAttendance = $request->has('create_attendance');

    try {
        // Mulai transaksi database
        DB::beginTransaction();

        // Jika diminta untuk mengganti entri hari Minggu yang sudah ada
        if ($replaceExisting) {
            // Hapus semua hari libur Minggu untuk tahun yang dipilih
            Harilibur::where('nama_libur', 'Hari Minggu')
                ->whereYear('tanggal', $year)
                ->delete();
        }

        // Cari semua hari Minggu untuk tahun yang dipilih
        $startDate = Carbon::createFromDate($year, 1, 1);
        $endDate = Carbon::createFromDate($year, 12, 31);

        $sundays = [];
        $date = $startDate->copy()->startOfWeek(Carbon::SUNDAY);

        // Jika Minggu pertama sebelum awal tahun, tambah 7 hari
        if ($date->lt($startDate)) {
            $date->addWeek();
        }

        // Buat semua hari Minggu untuk tahun tersebut
        $createdHolidays = [];
        $absensiController = app()->make('App\Http\Controllers\Admin\AbsensiController');
        $attendanceCount = 0;

        // Trigger a reference to the AbsensiController
        while ($date->lte($endDate)) {
            $holiday = [
                'tanggal' => $date->format('Y-m-d'),
                'nama_libur' => 'Hari Minggu',
                'keterangan' => 'Hari Minggu - Generated Automatically',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Lewati jika tanggal sudah ada dan tidak diminta untuk mengganti
            if (!$replaceExisting && Harilibur::where('tanggal', $holiday['tanggal'])->exists()) {
                $date->addWeek();
                continue;
            }

            $createdHoliday = Harilibur::create($holiday);
            $createdHolidays[] = $createdHoliday;

            // Buat absensi otomatis jika opsi dipilih
            if ($createAttendance) {
                $attendanceCount += $absensiController->createAbsensiForHoliday($createdHoliday->id);
            }

            $date->addWeek();
        }

        // Commit transaksi database
        DB::commit();

        // Redirect ke halaman index dengan pesan sukses
        $message = count($createdHolidays) . ' Hari Minggu untuk tahun ' . $year . ' berhasil dibuat';
        if ($createAttendance) {
            $message .= ' dan ' . $attendanceCount . ' absensi otomatis telah dibuat';
        }

        return redirect()->route('hariliburs.index')
            ->with('success', $message);
    } catch (\Exception $e) {
        // Rollback transaksi jika terjadi error
        DB::rollBack();
        return redirect()->route('hariliburs.index')
            ->with('error', 'Gagal membuat Hari Minggu: ' . $e->getMessage());
    }
}
}
