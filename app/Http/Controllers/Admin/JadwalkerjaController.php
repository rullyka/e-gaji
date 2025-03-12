<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalKerja;
use App\Models\Karyawan;
use App\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalKerjaController extends Controller
{
    /**
     * Menampilkan daftar jadwal kerja dengan filter
     */
    public function index(Request $request)
    {
        $query = JadwalKerja::with(['karyawan', 'shift']);

        // Filter berdasarkan rentang tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }

        // Filter berdasarkan karyawan jika ada
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        // Filter berdasarkan shift jika ada
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        // Ambil data jadwal kerja dengan paginasi
        $jadwalkerjas = $query->orderBy('tanggal', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);

        // Siapkan data untuk dropdown filter
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $shifts = Shift::orderBy('kode_shift')->get();

        return view('admin.jadwalkerjas.index', compact('jadwalkerjas', 'karyawans', 'shifts'));
    }

    /**
     * Menampilkan form untuk membuat jadwal kerja baru
     */
    public function create()
    {
        // Siapkan data untuk dropdown di form
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $shifts = Shift::orderBy('kode_shift')->get();

        return view('admin.jadwalkerjas.create', compact('karyawans', 'shifts'));
    }

    /**
     * Menyimpan data jadwal kerja baru ke database
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'karyawan_id' => 'required|array',
            'karyawan_id.*' => 'exists:karyawans,id',
            'shift_id' => 'required|exists:shifts,id',
        ]);

        // Dapatkan rentang tanggal
        $startDate = Carbon::parse($request->tanggal_mulai);
        $endDate = Carbon::parse($request->tanggal_akhir);

        // Dapatkan semua tanggal dalam rentang
        $dateRange = CarbonPeriod::create($startDate, $endDate);

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            $inserted = 0;
            $skipped = 0;

            // Untuk setiap tanggal dan setiap karyawan, buat jadwal kerja
            foreach ($dateRange as $date) {
                foreach ($request->karyawan_id as $karyawanId) {
                    // Cek apakah jadwal sudah ada untuk karyawan ini pada tanggal ini
                    $exists = JadwalKerja::where('tanggal', $date->format('Y-m-d'))
                                ->where('karyawan_id', $karyawanId)
                                ->exists();

                    if (!$exists) {
                        // Buat jadwal baru jika belum ada
                        JadwalKerja::create([
                            'id' => Str::uuid(),
                            'tanggal' => $date->format('Y-m-d'),
                            'karyawan_id' => $karyawanId,
                            'shift_id' => $request->shift_id,
                        ]);
                        $inserted++;
                    } else {
                        $skipped++;
                    }
                }
            }

            // Commit transaksi database
            DB::commit();

            // Tampilkan pesan sesuai hasil
            if ($skipped > 0) {
                $message = "Berhasil menambahkan $inserted jadwal kerja. $skipped jadwal dilewati karena sudah ada.";
                return redirect()->route('jadwalkerjas.index')->with('warning', $message);
            } else {
                return redirect()->route('jadwalkerjas.index')->with('success', "Berhasil menambahkan $inserted jadwal kerja.");
            }
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail jadwal kerja tertentu
     */
    public function show(JadwalKerja $jadwalkerja)
    {
        // Muat relasi yang dibutuhkan
        $jadwalkerja->load(['karyawan', 'shift']);

        return view('admin.jadwalkerjas.show', compact('jadwalkerja'));
    }

    /**
     * Menampilkan form untuk mengedit jadwal kerja
     */
    public function edit(JadwalKerja $jadwalkerja)
    {
        // Siapkan data untuk dropdown di form edit
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $shifts = Shift::orderBy('kode_shift')->get();

        return view('admin.jadwalkerjas.edit', compact('jadwalkerja', 'karyawans', 'shifts'));
    }

    /**
     * Memperbarui data jadwal kerja di database
     */
    public function update(Request $request, JadwalKerja $jadwalkerja)
    {
        // Validasi input dari form edit
        $request->validate([
            'tanggal' => 'required|date',
            'karyawan_id' => 'required|exists:karyawans,id',
            'shift_id' => 'required|exists:shifts,id',
        ]);

        // Cek apakah jadwal sudah ada untuk karyawan ini pada tanggal ini (kecuali jadwal saat ini)
        $exists = JadwalKerja::where('tanggal', $request->tanggal)
                        ->where('karyawan_id', $request->karyawan_id)
                        ->where('id', '!=', $jadwalkerja->id)
                        ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Jadwal kerja untuk karyawan ini pada tanggal tersebut sudah ada');
        }

        // Update data jadwal kerja
        $jadwalkerja->update($request->all());

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('jadwalkerjas.index')
            ->with('success', 'Jadwal kerja berhasil diupdate');
    }

    /**
     * Menghapus data jadwal kerja dari database
     */
    public function destroy(JadwalKerja $jadwalkerja)
    {
        // Hapus data jadwal kerja
        $jadwalkerja->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('jadwalkerjas.index')
            ->with('success', 'Jadwal kerja berhasil dihapus');
    }

    /**
     * Menampilkan laporan jadwal kerja dengan filter dan ringkasan
     */
    public function report(Request $request)
    {
        // Siapkan data untuk dropdown filter
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $shifts = Shift::orderBy('kode_shift')->get();

        // Set nilai default untuk filter tanggal
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $karyawanId = $request->karyawan_id;
        $shiftId = $request->shift_id;

        // Query jadwal kerja dengan filter
        $query = JadwalKerja::with(['karyawan', 'shift'])
                    ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        }

        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }

        // Ambil data jadwal kerja
        $jadwalkerjas = $query->orderBy('tanggal')
                            ->orderBy('karyawan_id')
                            ->get();

        // Buat ringkasan statistik berdasarkan karyawan
        $summary = $jadwalkerjas->groupBy('karyawan_id')
                    ->map(function ($items, $key) use ($shifts) {
                        $karyawan = $items->first()->karyawan;
                        $totalDays = $items->count();

                        // Hitung berdasarkan shift
                        $shiftCounts = $items->groupBy('shift_id')
                                        ->map(function ($shiftItems) {
                                            return $shiftItems->count();
                                        });

                        return [
                            'karyawan' => $karyawan,
                            'total_days' => $totalDays,
                            'shift_counts' => $shiftCounts
                        ];
                    });

        // Tampilkan view laporan dengan data
        return view('admin.jadwalkerjas.report', compact(
            'jadwalkerjas',
            'karyawans',
            'shifts',
            'startDate',
            'endDate',
            'karyawanId',
            'shiftId',
            'summary'
        ));
    }
}