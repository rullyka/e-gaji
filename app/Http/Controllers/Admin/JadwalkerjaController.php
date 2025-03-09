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
     * Display a listing of jadwal kerja.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = JadwalKerja::with(['karyawan', 'shift']);

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }

        // Filter by karyawan if provided
        if ($request->filled('karyawan_id')) {
            $query->where('karyawan_id', $request->karyawan_id);
        }

        // Filter by shift if provided
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        $jadwalkerjas = $query->orderBy('tanggal', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);

        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $shifts = Shift::orderBy('kode_shift')->get();

        return view('admin.jadwalkerjas.index', compact('jadwalkerjas', 'karyawans', 'shifts'));
    }

    /**
     * Show the form for creating a new jadwal kerja.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $shifts = Shift::orderBy('kode_shift')->get();

        return view('admin.jadwalkerjas.create', compact('karyawans', 'shifts'));
    }

    /**
     * Store a newly created jadwal kerja in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            'karyawan_id' => 'required|array',
            'karyawan_id.*' => 'exists:karyawans,id',
            'shift_id' => 'required|exists:shifts,id',
        ]);

        // Get date range
        $startDate = Carbon::parse($request->tanggal_mulai);
        $endDate = Carbon::parse($request->tanggal_akhir);

        // Get all dates in range
        $dateRange = CarbonPeriod::create($startDate, $endDate);

        // Begin transaction
        DB::beginTransaction();

        try {
            $inserted = 0;
            $skipped = 0;

            // For each date and each karyawan, create a jadwal kerja
            foreach ($dateRange as $date) {
                foreach ($request->karyawan_id as $karyawanId) {
                    // Check if jadwal already exists for this employee on this date
                    $exists = JadwalKerja::where('tanggal', $date->format('Y-m-d'))
                                ->where('karyawan_id', $karyawanId)
                                ->exists();

                    if (!$exists) {
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

            DB::commit();

            if ($skipped > 0) {
                $message = "Berhasil menambahkan $inserted jadwal kerja. $skipped jadwal dilewati karena sudah ada.";
                return redirect()->route('jadwalkerjas.index')->with('warning', $message);
            } else {
                return redirect()->route('jadwalkerjas.index')->with('success', "Berhasil menambahkan $inserted jadwal kerja.");
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified jadwal kerja.
     *
     * @param  \App\Models\JadwalKerja  $jadwalkerja
     * @return \Illuminate\Http\Response
     */
    public function show(JadwalKerja $jadwalkerja)
    {
        $jadwalkerja->load(['karyawan', 'shift']);

        return view('admin.jadwalkerjas.show', compact('jadwalkerja'));
    }

    /**
     * Show the form for editing the specified jadwal kerja.
     *
     * @param  \App\Models\JadwalKerja  $jadwalkerja
     * @return \Illuminate\Http\Response
     */
    public function edit(JadwalKerja $jadwalkerja)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $shifts = Shift::orderBy('kode_shift')->get();

        return view('admin.jadwalkerjas.edit', compact('jadwalkerja', 'karyawans', 'shifts'));
    }

    /**
     * Update the specified jadwal kerja in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JadwalKerja  $jadwalkerja
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JadwalKerja $jadwalkerja)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'karyawan_id' => 'required|exists:karyawans,id',
            'shift_id' => 'required|exists:shifts,id',
        ]);

        // Check if jadwal already exists for this employee on this date (excluding the current one)
        $exists = JadwalKerja::where('tanggal', $request->tanggal)
                        ->where('karyawan_id', $request->karyawan_id)
                        ->where('id', '!=', $jadwalkerja->id)
                        ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Jadwal kerja untuk karyawan ini pada tanggal tersebut sudah ada');
        }

        $jadwalkerja->update($request->all());

        return redirect()->route('jadwalkerjas.index')
            ->with('success', 'Jadwal kerja berhasil diupdate');
    }

    /**
     * Remove the specified jadwal kerja from storage.
     *
     * @param  \App\Models\JadwalKerja  $jadwalkerja
     * @return \Illuminate\Http\Response
     */
    public function destroy(JadwalKerja $jadwalkerja)
    {
        $jadwalkerja->delete();

        return redirect()->route('jadwalkerjas.index')
            ->with('success', 'Jadwal kerja berhasil dihapus');
    }

    /**
     * Display report view for jadwal kerja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $shifts = Shift::orderBy('kode_shift')->get();

        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $karyawanId = $request->karyawan_id;
        $shiftId = $request->shift_id;

        $query = JadwalKerja::with(['karyawan', 'shift'])
                    ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        }

        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }

        $jadwalkerjas = $query->orderBy('tanggal')
                            ->orderBy('karyawan_id')
                            ->get();

        // Group by karyawan for summary statistics
        $summary = $jadwalkerjas->groupBy('karyawan_id')
                    ->map(function ($items, $key) use ($shifts) {
                        $karyawan = $items->first()->karyawan;
                        $totalDays = $items->count();

                        // Count by shift
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