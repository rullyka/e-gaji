<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Lembur;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\Potongan;
use App\Models\Harilibur;
use App\Models\Departemen;
use App\Models\Penggajian;
use App\Models\PeriodeGaji;
use App\Models\CutiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class PenggajianController extends Controller
{
    /**
     * Generate payslip for multiple employees by period
     *
     * @param  int  $periodeId
     * @return \Illuminate\Http\Response
     */
    public function generatePayslips($periodeId)
    {
        // Verify period exists
        $periode = \App\Models\PeriodeGaji::findOrFail($periodeId);

        // Get all penggajian for the specified period with necessary relationships
        $penggajians = \App\Models\Penggajian::with([
            'karyawan.departemen',
            'karyawan.bagian',
            'karyawan.jabatan',
            'karyawan.profesi',
            'periodeGaji'  // Changed from 'periode' to 'periodeGaji'
        ])
            ->where('id_periode', $periodeId)
            ->orderBy('id_karyawan')
            ->get();

        // If no penggajian found for this period
        if ($penggajians->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada data penggajian untuk periode ini.');
        }

        // Prepare additional data for each penggajian
        foreach ($penggajians as $penggajian) {
            $this->processAttendanceData($penggajian, $periode);
        }

        // Pass to view
        return view('admin.penggajians.slip', compact('penggajians', 'periode'));
    }

    /**
     * Generate individual payslip for a single employee
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function generatePayslip($id)
    {
        // Get single penggajian with related data
        $penggajian = \App\Models\Penggajian::with([
            'karyawan.departemen',
            'karyawan.bagian',
            'karyawan.jabatan',
            'karyawan.profesi',
            'periodeGaji'  // Changed from 'periode' to 'periodeGaji'
        ])->findOrFail($id);

        // Get the period
        $periode = $penggajian->periodeGaji;

        // Process attendance data
        $this->processAttendanceData($penggajian, $periode);

        // Create a collection with single item and pass variables needed for the view
        $penggajians = collect([$penggajian]);

        // Pass all necessary variables to the view
        return view('admin.penggajians.slip', compact('penggajians', 'periode'));
    }

    /**
     * Process attendance data for a specific penggajian
     *
     * @param Penggajian $penggajian
     * @param PeriodeGaji $periode
     * @return Penggajian
     */
    protected function processAttendanceData(Penggajian $penggajian, PeriodeGaji $periode)
    {
        // Load attendance data
        $absensi = Absensi::where('karyawan_id', $penggajian->id_karyawan)
            ->whereBetween('tanggal', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->orderBy('tanggal', 'asc')
            ->get();

        // Load overtime data
        $lembur = Lembur::where('karyawan_id', $penggajian->id_karyawan)
            ->where('status', 'Disetujui')
            ->whereBetween('tanggal_lembur', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->get();

        // Load leave data
        $cuti = CutiKaryawan::where('id_karyawan', $penggajian->id_karyawan)
            ->where('status_acc', 'Disetujui')
            ->where(function ($query) use ($periode) {
                $query->whereBetween('tanggal_mulai_cuti', [$periode->tanggal_mulai, $periode->tanggal_selesai])
                    ->orWhereBetween('tanggal_akhir_cuti', [$periode->tanggal_mulai, $periode->tanggal_selesai]);
            })
            ->get();

        // Calculate total days in period
        $totalHari = $periode->tanggal_mulai->diffInDays($periode->tanggal_selesai) + 1;

        // Get holidays
        $hariLibur = Harilibur::whereBetween('tanggal', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->pluck('tanggal')
            ->toArray();

        // Format holidays for comparison
        $hariLiburFormatted = array_map(function ($date) {
            return date('Y-m-d', strtotime($date));
        }, $hariLibur);

        // Calculate working days
        $hariKerja = $totalHari;
        $currentDate = clone $periode->tanggal_mulai;

        while ($currentDate <= $periode->tanggal_selesai) {
            $currentDateFormatted = $currentDate->format('Y-m-d');

            // If Sunday or holiday
            if ($currentDate->dayOfWeek === 0 || in_array($currentDateFormatted, $hariLiburFormatted)) {
                $hariKerja--;
            }

            $currentDate->addDay();
        }

        // Calculate attendance statistics
        $hariHadir = $absensi->where('status', 'Hadir')->count();
        $hariIzin = $absensi->where('status', 'Izin')->count();
        $hariCuti = $absensi->where('status', 'Cuti')->count();

        // Add leave days from leave table
        $izinCuti = 0;
        foreach ($cuti as $c) {
            $startDate = max($c->tanggal_mulai_cuti, $periode->tanggal_mulai);
            $endDate = min($c->tanggal_akhir_cuti, $periode->tanggal_selesai);
            $izinCuti += $startDate->diffInDays($endDate) + 1;
        }

        // Calculate absences
        $hariTidakHadir = $hariKerja - $hariHadir - $hariIzin - $hariCuti - $izinCuti;
        $hariTidakHadir = max(0, $hariTidakHadir);

        // Calculate attendance rate
        $kehadiranRate = $hariKerja > 0 ? round(($hariHadir / $hariKerja) * 100, 2) : 0;

        // Calculate total lateness in minutes
        $totalKeterlambatan = $absensi->sum('keterlambatan');

        // Format keterlambatan in days and minutes
        $hariKeterlambatan = floor($totalKeterlambatan / (60 * 8)); // Assuming 8 hours = 1 work day
        $menitKeterlambatan = $totalKeterlambatan % (60 * 8);

        // Format keterlambatan for display
        $keterlambatanFormatted = '';
        if ($hariKeterlambatan > 0) {
            $keterlambatanFormatted = $hariKeterlambatan . ' hari / ';
        }
        $keterlambatanFormatted .= $totalKeterlambatan . ' menit';

        // Calculate total early departure in minutes
        $totalPulangAwal = $absensi->sum('pulang_awal');

        // Format pulang awal in days and minutes
        $hariPulangAwal = floor($totalPulangAwal / (60 * 8)); // Assuming 8 hours = 1 work day
        $menitPulangAwal = $totalPulangAwal % (60 * 8);

        // Format pulang awal for display
        $pulangAwalFormatted = '';
        if ($hariPulangAwal > 0) {
            $pulangAwalFormatted = $hariPulangAwal . ' hari / ';
        }
        $pulangAwalFormatted .= $totalPulangAwal . ' menit';

        // Calculate overtime hours
        $totalLembur = 0;
        $lemburHariBiasa = 0;
        $lemburHariLibur = 0;

        foreach ($lembur as $item) {
            $jamMulai = \Carbon\Carbon::parse($item->jam_mulai);
            $jamSelesai = \Carbon\Carbon::parse($item->jam_selesai);
            $durasiJam = $jamSelesai->diffInHours($jamMulai);

            $totalLembur += $durasiJam;

            if ($item->jenis_lembur == 'Hari Kerja') {
                $lemburHariBiasa += $durasiJam;
            } else { // Hari Libur
                $lemburHariLibur += $durasiJam;
            }
        }

        // Store all calculated data with the penggajian object
        $penggajian->dataAbsensi = [
            'absensi' => $absensi,
            'total_hari' => $totalHari,
            'total_hari_kerja' => $hariKerja,
            'hadir' => $hariHadir,
            'izin' => $hariIzin,
            'cuti' => $hariCuti,
            'izin_cuti' => $izinCuti,
            'tidak_hadir' => $hariTidakHadir,
            'total_keterlambatan' => $totalKeterlambatan,
            'total_pulang_awal' => $totalPulangAwal,
            'total_lembur' => $totalLembur,
            'lembur_hari_biasa' => $lemburHariBiasa,
            'lembur_hari_libur' => $lemburHariLibur,
            'lembur_disetujui' => $lembur
        ];

        // Add variables needed for the slip view
        $penggajian->hariKerja = $hariKerja;
        $penggajian->hariHadir = $hariHadir;
        $penggajian->hariIzin = $hariIzin;
        $penggajian->hariCuti = $hariCuti;
        $penggajian->izinCuti = $izinCuti;
        $penggajian->hariTidakHadir = $hariTidakHadir;
        $penggajian->kehadiranRate = $kehadiranRate;
        $penggajian->keterlambatanFormatted = $keterlambatanFormatted;
        $penggajian->pulangAwalFormatted = $pulangAwalFormatted;
        $penggajian->totalLembur = $totalLembur;
        $penggajian->lemburHariBiasa = $lemburHariBiasa;
        $penggajian->lemburHariLibur = $lemburHariLibur;

        return $penggajian;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $penggajians = Penggajian::with(['karyawan', 'periodeGaji'])->latest()->paginate(10);

        // Ensure JSON fields are properly decoded for each penggajian
        foreach ($penggajians as $penggajian) {
            if (is_string($penggajian->detail_tunjangan)) {
                $penggajian->detail_tunjangan = json_decode($penggajian->detail_tunjangan, true) ?: [];
            }

            if (is_string($penggajian->detail_potongan)) {
                $penggajian->detail_potongan = json_decode($penggajian->detail_potongan, true) ?: [];
            }

            if (is_string($penggajian->detail_departemen)) {
                $penggajian->detail_departemen = json_decode($penggajian->detail_departemen, true) ?: [];
            }
        }

        return view('admin.penggajians.index', compact('penggajians'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $periodeGajis  = PeriodeGaji::where('status', 'aktif')->get();
        $departemens   = Departemen::all();
        $statusOptions = ['aktif', 'nonaktif', 'cuti'];

        return view('admin.penggajians.create', compact('periodeGajis', 'departemens', 'statusOptions'));
    }

    /**
     * Get employees based on filters that don't have payroll entries for the selected period
     */
    public function getFilteredKaryawans(Request $request)
    {
        $request->validate([
            'periode_id'    => 'required|exists:periodegajis,id',
            'departemen_id' => 'nullable|exists:departemens,id',
            'status'        => 'nullable|in:aktif,nonaktif,cuti',
        ]);

        $periodeId    = $request->periode_id;
        $departemenId = $request->departemen_id;
        $status       = $request->status;

        // Get IDs of employees who already have payroll entries for this period
        $processedKaryawanIds = Penggajian::where('id_periode', $periodeId)
            ->pluck('id_karyawan')
            ->toArray();

        // Query to get filtered employees
        $query = Karyawan::query();

        // Filter by department if specified
        if ($departemenId) {
            $query->where('id_departemen', $departemenId);
        }

        // Filter by status if specified
        if ($status) {
            $query->where('statuskaryawan', $status);
        }

        // Exclude employees who already have payroll entries for this period
        if (!empty($processedKaryawanIds)) {
            $query->whereNotIn('id', $processedKaryawanIds);
        }

        // Get employees with their related data
        $karyawans = $query->with(['departemen', 'bagian', 'jabatan', 'profesi'])->get();

        return response()->json([
            'success' => true,
            'data'    => $karyawans,
            'count'   => $karyawans->count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'periode_id'     => 'required|exists:periodegajis,id',
            'karyawan_ids'   => 'required|array',
            'karyawan_ids.*' => 'exists:karyawans,id',
        ]);

        $periodeId   = $request->periode_id;
        $karyawanIds = $request->karyawan_ids;
        $periode     = PeriodeGaji::findOrFail($periodeId);

        $count  = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($karyawanIds as $karyawanId) {
                // Load karyawan dengan relasi yang ada
                $karyawan = Karyawan::with(['jabatan', 'profesi', 'departemen', 'bagian'])->findOrFail($karyawanId);

                // Check if payroll entry already exists for this employee and period
                $exists = Penggajian::where('id_periode', $periodeId)
                    ->where('id_karyawan', $karyawanId)
                    ->exists();

                if ($exists) {
                    $errors[] = "Penggajian untuk karyawan {$karyawan->nama_karyawan} pada periode ini sudah ada.";
                    continue;
                }

                // Calculate basic salary
                $gajiPokok = $karyawan->jabatan ? $karyawan->jabatan->gaji_pokok : 0;

                // Calculate allowances
                $tunjangan       = 0;
                $detailTunjangan = [];

                // Tunjangan dari jabatan
                if ($karyawan->jabatan && $karyawan->jabatan->tunjangan_jabatan > 0) {
                    $tunjangan         += $karyawan->jabatan->tunjangan_jabatan;
                    $detailTunjangan[]  = [
                        'nama'    => 'Tunjangan Jabatan',
                        'nominal' => $karyawan->jabatan->tunjangan_jabatan
                    ];
                }

                // Tunjangan dari profesi
                if ($karyawan->profesi && $karyawan->profesi->tunjangan_profesi > 0) {
                    $tunjangan         += $karyawan->profesi->tunjangan_profesi;
                    $detailTunjangan[]  = [
                        'nama'    => 'Tunjangan Profesi',
                        'nominal' => $karyawan->profesi->tunjangan_profesi
                    ];
                }

                // For now, no deductions
                $potongan       = 0;
                $detailPotongan = [];

                // Calculate net salary
                $gajiBersih = $gajiPokok + $tunjangan - $potongan;

                // Department details
                $detailDepartemen = [
                    'departemen' => $karyawan->departemen ? $karyawan->departemen->name_departemen : null,
                    'bagian'     => $karyawan->bagian ? $karyawan->bagian->name_bagian : null,
                    'jabatan'    => $karyawan->jabatan ? $karyawan->jabatan->name_jabatan : null,
                    'profesi'    => $karyawan->profesi ? $karyawan->profesi->name_profesi : null,
                ];

                // Create payroll entry
                Penggajian::create([
                    'id'                => Str::uuid(),
                    'id_periode'        => $periodeId,
                    'id_karyawan'       => $karyawanId,
                    'periode_awal'      => $periode->tanggal_mulai,
                    'periode_akhir'     => $periode->tanggal_selesai,
                    'gaji_pokok'        => $gajiPokok,
                    'tunjangan'         => $tunjangan,
                    'detail_tunjangan'  => json_encode($detailTunjangan),
                    'potongan'          => $potongan,
                    'detail_potongan'   => json_encode($detailPotongan),
                    'detail_departemen' => json_encode($detailDepartemen),
                    'gaji_bersih'       => $gajiBersih,
                ]);

                $count++;
            }

            DB::commit();

            if ($count > 0) {
                return redirect()->route('penggajian.index')
                    ->with('success', "Berhasil membuat {$count} data penggajian.")
                    ->with('errors', $errors);
            } else {
                return redirect()->back()
                    ->with('error', "Tidak ada data penggajian yang dibuat.")
                    ->with('errors', $errors);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Penggajian $penggajian)
    {
        $penggajian->load(['karyawan', 'periodeGaji']);

        // Ensure JSON fields are properly decoded
        if (is_string($penggajian->detail_tunjangan)) {
            $penggajian->detail_tunjangan = json_decode($penggajian->detail_tunjangan, true) ?: [];
        }

        if (is_string($penggajian->detail_potongan)) {
            $penggajian->detail_potongan = json_decode($penggajian->detail_potongan, true) ?: [];
        }

        if (is_string($penggajian->detail_departemen)) {
            $penggajian->detail_departemen = json_decode($penggajian->detail_departemen, true) ?: [];
        }

        return view('admin.penggajians.show', compact('penggajian'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penggajian $penggajian)
    {
        $penggajian->load(['karyawan', 'periodeGaji']);

        // Ensure JSON fields are properly decoded
        if (is_string($penggajian->detail_tunjangan)) {
            $penggajian->detail_tunjangan = json_decode($penggajian->detail_tunjangan, true) ?: [];
        }

        if (is_string($penggajian->detail_potongan)) {
            $penggajian->detail_potongan = json_decode($penggajian->detail_potongan, true) ?: [];
        }

        if (is_string($penggajian->detail_departemen)) {
            $penggajian->detail_departemen = json_decode($penggajian->detail_departemen, true) ?: [];
        }

        return view('admin.penggajians.edit', compact('penggajian'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penggajian $penggajian)
    {
        $request->validate([
            'gaji_pokok'       => 'required|numeric|min:0',
            'tunjangan'        => 'nullable|numeric|min:0',
            'potongan'         => 'nullable|numeric|min:0',
            'detail_tunjangan' => 'nullable|array',
            'detail_potongan'  => 'nullable|array',
        ]);

        // Process allowance details
        $totalTunjangan  = 0;
        $detailTunjangan = [];

        if ($request->has('detail_tunjangan')) {
            foreach ($request->detail_tunjangan as $tunjangan) {
                if (!empty($tunjangan['nama']) && !empty($tunjangan['nominal'])) {
                    $nominal            = floatval($tunjangan['nominal']);
                    $totalTunjangan    += $nominal;
                    $detailTunjangan[]  = [
                        'nama'    => $tunjangan['nama'],
                        'nominal' => $nominal
                    ];
                }
            }
        }

        // Process deduction details
        $totalPotongan  = 0;
        $detailPotongan = [];

        if ($request->has('detail_potongan')) {
            foreach ($request->detail_potongan as $potongan) {
                if (!empty($potongan['nama']) && !empty($potongan['nominal'])) {
                    $nominal           = floatval($potongan['nominal']);
                    $totalPotongan    += $nominal;
                    $detailPotongan[]  = [
                        'nama'    => $potongan['nama'],
                        'nominal' => $nominal
                    ];
                }
            }
        }

        // Calculate net salary
        $gajiBersih = $request->gaji_pokok + $totalTunjangan - $totalPotongan;

        $penggajian->update([
            'gaji_pokok'       => $request->gaji_pokok,
            'tunjangan'        => $totalTunjangan,
            'detail_tunjangan' => json_encode($detailTunjangan),
            'potongan'         => $totalPotongan,
            'detail_potongan'  => json_encode($detailPotongan),
            'gaji_bersih'      => $gajiBersih,
        ]);

        return redirect()->route('penggajian.index')
            ->with('success', 'Data penggajian berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penggajian $penggajian)
    {
        $penggajian->delete();
        return redirect()->route('penggajian.index')
            ->with('success', 'Data penggajian berhasil dihapus');
    }

    /**
     * Generate payroll report by period
     */
    public function reportByPeriod(Request $request)
    {
        $periodeGajis = PeriodeGaji::orderBy('tanggal_mulai', 'desc')->get();
        $periodeId    = $request->periode_id;

        $penggajians     = [];
        $selectedPeriode = null;

        if ($periodeId) {
            $selectedPeriode = PeriodeGaji::findOrFail($periodeId);
            $penggajians     = Penggajian::with(['karyawan', 'periodeGaji'])
                ->where('id_periode', $periodeId)
                ->get();
        }

        return view('admin.penggajians.report-by-period', compact('periodeGajis', 'penggajians', 'selectedPeriode'));
    }

    /**
     * Generate payroll report by department
     */
    public function reportByDepartment(Request $request)
    {
        $departemens  = Departemen::all();
        $periodeGajis = PeriodeGaji::orderBy('tanggal_mulai', 'desc')->get();

        $departemenId = $request->departemen_id;
        $periodeId    = $request->periode_id;

        $penggajians        = [];
        $selectedDepartemen = null;
        $selectedPeriode    = null;

        if ($departemenId && $periodeId) {
            $selectedDepartemen = Departemen::findOrFail($departemenId);
            $selectedPeriode    = PeriodeGaji::findOrFail($periodeId);

            // Get all employees in the department
            $karyawanIds = Karyawan::where('id_departemen', $departemenId)->pluck('id')->toArray();

            // Get payroll data for these employees in the selected period
            $penggajians = Penggajian::with(['karyawan', 'periodeGaji'])
                ->where('id_periode', $periodeId)
                ->whereIn('id_karyawan', $karyawanIds)
                ->get();
        }

        return view('admin.penggajians.report-by-department', compact(
            'departemens',
            'periodeGajis',
            'penggajians',
            'selectedDepartemen',
            'selectedPeriode'
        ));
    }

    /**
     * Add dynamic allowance or deduction to payroll
     */
    public function addComponent(Request $request, Penggajian $penggajian)
    {
        $request->validate([
            'type'    => 'required|in:tunjangan,potongan',
            'nama'    => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
        ]);

        $type      = $request->type;
        $component = [
            'nama'    => $request->nama,
            'nominal' => floatval($request->nominal)
        ];

        if ($type === 'tunjangan') {
            $details        = $penggajian->detail_tunjangan ?: [];
            $details[]      = $component;
            $totalTunjangan = array_sum(array_column($details, 'nominal'));

            $penggajian->update([
                'detail_tunjangan' => json_encode($details),
                'tunjangan'        => $totalTunjangan,
                'gaji_bersih'      => $penggajian->gaji_pokok + $totalTunjangan - $penggajian->potongan
            ]);

            return redirect()->back()->with('success', 'Tunjangan berhasil ditambahkan');
        } else {
            $details       = $penggajian->detail_potongan ?: [];
            $details[]     = $component;
            $totalPotongan = array_sum(array_column($details, 'nominal'));

            $penggajian->update([
                'detail_potongan' => json_encode($details),
                'potongan'        => $totalPotongan,
                'gaji_bersih'     => $penggajian->gaji_pokok + $penggajian->tunjangan - $totalPotongan
            ]);

            return redirect()->back()->with('success', 'Potongan berhasil ditambahkan');
        }
    }

    /**
     * Remove dynamic allowance or deduction from payroll
     */
    public function removeComponent(Request $request, Penggajian $penggajian)
    {
        $request->validate([
            'type'  => 'required|in:tunjangan,potongan',
            'index' => 'required|integer|min:0',
        ]);

        $type  = $request->type;
        $index = $request->index;

        if ($type === 'tunjangan') {
            $details = $penggajian->detail_tunjangan ?: [];

            if (isset($details[$index])) {
                $nominal = $details[$index]['nominal'];
                array_splice($details, $index, 1);

                $totalTunjangan = array_sum(array_column($details, 'nominal'));

                $penggajian->update([
                    'detail_tunjangan' => json_encode($details),
                    'tunjangan'        => $totalTunjangan,
                    'gaji_bersih'      => $penggajian->gaji_pokok + $totalTunjangan - $penggajian->potongan
                ]);

                return redirect()->back()->with('success', 'Tunjangan berhasil dihapus');
            }
        } else {
            $details = $penggajian->detail_potongan ?: [];

            if (isset($details[$index])) {
                $nominal = $details[$index]['nominal'];
                array_splice($details, $index, 1);

                $totalPotongan = array_sum(array_column($details, 'nominal'));

                $penggajian->update([
                    'detail_potongan' => json_encode($details),
                    'potongan'        => $totalPotongan,
                    'gaji_bersih'     => $penggajian->gaji_pokok + $penggajian->tunjangan - $totalPotongan
                ]);

                return redirect()->back()->with('success', 'Potongan berhasil dihapus');
            }
        }

        return redirect()->back()->with('error', 'Komponen tidak ditemukan');
    }


    /**
     * Export payroll data to Excel
     */
    public function exportExcel(Request $request)
    {
        $periodeId    = $request->periode_id;
        $departemenId = $request->departemen_id;

        if (!$periodeId) {
            return redirect()->back()->with('error', 'Periode gaji harus dipilih');
        }

        $query = Penggajian::with(['karyawan', 'periodeGaji'])
            ->where('id_periode', $periodeId);

        if ($departemenId) {
            $karyawanIds = Karyawan::where('id_departemen', $departemenId)->pluck('id')->toArray();
            $query->whereIn('id_karyawan', $karyawanIds);
        }

        $penggajians = $query->get();

        if ($penggajians->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data penggajian untuk diekspor');
        }

        $periode  = $penggajians->first()->periodeGaji;
        $fileName = 'Penggajian_' . str_replace(' ', '_', $periode->nama_periode) . '.xlsx';

        // Here you would implement the Excel export using a library like Laravel Excel
        // For now, we'll just return a message
        return redirect()->back()->with('success', 'Fitur ekspor Excel akan segera tersedia');
    }

    /**
     * Batch process payroll for multiple employees
     */
    public function batchProcess(Request $request)
    {
        $request->validate([
            'periode_id'    => 'required|exists:periodegajis,id',
            'departemen_id' => 'nullable|exists:departemens,id',
            'status'        => 'nullable|in:aktif,nonaktif,cuti',
        ]);

        $periodeId    = $request->periode_id;
        $departemenId = $request->departemen_id;
        $status       = $request->status;

        // Get employees who haven't been processed yet
        $query = Karyawan::with(['jabatan', 'tunjangans', 'departemen', 'bagian', 'profesi']);

        // Filter by department if specified
        if ($departemenId) {
            $query->where('id_departemen', $departemenId);
        }

        // Filter by status if specified
        if ($status) {
            $query->where('status', $status);
        }

        // Get IDs of employees who already have payroll entries for this period
        $processedKaryawanIds = Penggajian::where('id_periode', $periodeId)
            ->pluck('id_karyawan')
            ->toArray();

        // Exclude employees who already have payroll entries
        if (!empty($processedKaryawanIds)) {
            $query->whereNotIn('id', $processedKaryawanIds);
        }

        $karyawans = $query->get();

        if ($karyawans->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada karyawan yang belum diproses untuk periode ini');
        }

        $periode = PeriodeGaji::findOrFail($periodeId);
        $count   = 0;

        DB::beginTransaction();
        try {
            foreach ($karyawans as $karyawan) {
                // Calculate basic salary
                $gajiPokok = $karyawan->jabatan ? $karyawan->jabatan->gaji_pokok : 0;

                // Calculate allowances
                $tunjangan       = 0;
                $detailTunjangan = [];

                foreach ($karyawan->tunjangans as $tunjangan_item) {
                    $tunjangan         += $tunjangan_item->nominal;
                    $detailTunjangan[]  = [
                        'nama'    => $tunjangan_item->nama,
                        'nominal' => $tunjangan_item->nominal
                    ];
                }

                // Tunjangan dari jabatan
                if ($karyawan->jabatan && $karyawan->jabatan->tunjangan_jabatan > 0) {
                    $tunjangan         += $karyawan->jabatan->tunjangan_jabatan;
                    $detailTunjangan[]  = [
                        'nama'    => 'Tunjangan Jabatan',
                        'nominal' => $karyawan->jabatan->tunjangan_jabatan
                    ];
                }

                // Tunjangan dari profesi
                if ($karyawan->profesi && $karyawan->profesi->tunjangan_profesi > 0) {
                    $tunjangan         += $karyawan->profesi->tunjangan_profesi;
                    $detailTunjangan[]  = [
                        'nama'    => 'Tunjangan Profesi',
                        'nominal' => $karyawan->profesi->tunjangan_profesi
                    ];
                }

                // For now, no deductions
                $potongan       = 0;
                $detailPotongan = [];

                // Calculate net salary
                $gajiBersih = $gajiPokok + $tunjangan - $potongan;

                // Department details
                $detailDepartemen = [
                    'departemen' => $karyawan->departemen ? $karyawan->departemen->name_departemen : null,
                    'bagian'     => $karyawan->bagian ? $karyawan->bagian->name_bagian : null,
                    'jabatan'    => $karyawan->jabatan ? $karyawan->jabatan->name_jabatan : null,
                    'profesi'    => $karyawan->profesi ? $karyawan->profesi->name_profesi : null,
                ];

                // Create payroll entry
                Penggajian::create([
                    'id'                => Str::uuid(),
                    'id_periode'        => $periodeId,
                    'id_karyawan'       => $karyawan->id,
                    'periode_awal'      => $periode->tanggal_mulai,
                    'periode_akhir'     => $periode->tanggal_selesai,
                    'gaji_pokok'        => $gajiPokok,
                    'tunjangan'         => $tunjangan,
                    'detail_tunjangan'  => json_encode($detailTunjangan),
                    'potongan'          => $potongan,
                    'detail_potongan'   => json_encode($detailPotongan),
                    'detail_departemen' => json_encode($detailDepartemen),
                    'gaji_bersih'       => $gajiBersih,
                ]);

                $count++;
            }

            DB::commit();

            return redirect()->route('penggajian.index')
                ->with('success', "Berhasil memproses {$count} data penggajian secara batch");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function review(Request $request)
    {
        $request->validate([
            'karyawan_ids' => 'required|array',
            'karyawan_ids.*' => 'exists:karyawans,id',
            'periode_id' => 'required|exists:periodegajis,id',
        ]);

        $karyawanId = $request->karyawan_ids[0];
        $periodeId = $request->periode_id;

        // Ambil data karyawan dengan relasinya
        $karyawan = Karyawan::with(['jabatan', 'profesi', 'departemen', 'bagian'])
            ->findOrFail($karyawanId);

        // Ambil data periode
        $periode = PeriodeGaji::findOrFail($periodeId);

        // Cek apakah sudah ada penggajian untuk karyawan & periode ini
        $exists = Penggajian::where('id_periode', $periodeId)
            ->where('id_karyawan', $karyawanId)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', "Penggajian untuk karyawan {$karyawan->nama_karyawan} pada periode ini sudah ada.");
        }

        // Ambil data absensi selama periode
        $absensi = Absensi::where('karyawan_id', $karyawanId)
            ->whereBetween('tanggal', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->orderBy('tanggal', 'asc')
            ->get();

        // Ambil data lembur selama periode
        $lembur = Lembur::where('karyawan_id', $karyawanId)
            ->where('status', 'Disetujui')
            ->whereBetween('tanggal_lembur', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->get();

        // Ambil data cuti selama periode
        $cuti = CutiKaryawan::where('id_karyawan', $karyawanId)
            ->where('status_acc', 'Disetujui')
            ->where(function ($query) use ($periode) {
                $query->whereBetween('tanggal_mulai_cuti', [$periode->tanggal_mulai, $periode->tanggal_selesai])
                    ->orWhereBetween('tanggal_akhir_cuti', [$periode->tanggal_mulai, $periode->tanggal_selesai]);
            })
            ->get();

        // Hitung jumlah hari dalam periode
        $totalHari = $periode->tanggal_mulai->diffInDays($periode->tanggal_selesai) + 1;

        // Hitung jumlah hari kerja (exclude hari libur)
        $hariLibur = Harilibur::whereBetween('tanggal', [$periode->tanggal_mulai, $periode->tanggal_selesai])
            ->pluck('tanggal')
            ->toArray();

        // Konversi tanggal ke format yang sama untuk perbandingan
        $hariLiburFormatted = array_map(function ($date) {
            return date('Y-m-d', strtotime($date));
        }, $hariLibur);

        // Hitung jumlah hari kerja (total hari dikurangi hari libur)
        $totalHariKerja = $totalHari;

        // Kurangi hari libur
        $currentDate = clone $periode->tanggal_mulai;
        while ($currentDate <= $periode->tanggal_selesai) {
            $currentDateFormatted = $currentDate->format('Y-m-d');

            // Jika hari minggu atau hari libur
            if ($currentDate->dayOfWeek === 0 || in_array($currentDateFormatted, $hariLiburFormatted)) {
                $totalHariKerja--;
            }

            $currentDate->addDay();
        }

        // Hitung total kehadiran berdasarkan status
        $hadirCount = $absensi->where('status', 'Hadir')->count();
        $izinCount = $absensi->where('status', 'Izin')->count();
        $cutiCount = $absensi->where('status', 'Cuti')->count();

        // Tambahkan izin dari tabel cuti
        $izinCutiCount = 0;
        foreach ($cuti as $c) {
            // Hitung jumlah hari cuti yang jatuh dalam periode
            $startDate = max($c->tanggal_mulai_cuti, $periode->tanggal_mulai);
            $endDate = min($c->tanggal_akhir_cuti, $periode->tanggal_selesai);
            $izinCutiCount += $startDate->diffInDays($endDate) + 1;
        }

        // Hitung tidak hadir (hari kerja - hadir - izin - cuti)
        $tidakHadirCount = $totalHariKerja - $hadirCount - $izinCount - $cutiCount - $izinCutiCount;
        $tidakHadirCount = max(0, $tidakHadirCount); // Pastikan tidak negatif

        // Hitung total keterlambatan dalam menit
        $totalKeterlambatan = $absensi->sum('keterlambatan');

        // Format keterlambatan dalam hari dan menit
        $hariKeterlambatan = floor($totalKeterlambatan / (60 * 8)); // Asumsi 8 jam = 1 hari kerja
        $menitKeterlambatan = $totalKeterlambatan % (60 * 8);

        // Format keterlambatan untuk display
        $keterlambatanDisplay = '';
        if ($hariKeterlambatan > 0) {
            $keterlambatanDisplay = $hariKeterlambatan . ' hari / ';
        }
        $keterlambatanDisplay .= $totalKeterlambatan . ' menit';

        // Hitung total pulang awal dalam menit
        $totalPulangAwal = $absensi->sum('pulang_awal');

        // Format pulang awal dalam hari dan menit
        $hariPulangAwal = floor($totalPulangAwal / (60 * 8)); // Asumsi 8 jam = 1 hari kerja
        $menitPulangAwal = $totalPulangAwal % (60 * 8);

        // Format pulang awal untuk display
        $pulangAwalDisplay = '';
        if ($hariPulangAwal > 0) {
            $pulangAwalDisplay = $hariPulangAwal . ' hari / ';
        }
        $pulangAwalDisplay .= $totalPulangAwal . ' menit';

        // Hitung total lembur dan lembur berdasarkan jenisnya
        $totalLembur = 0;
        $lemburHariBiasa = 0;
        $lemburHariLibur = 0;

        foreach ($lembur as $item) {
            // Calculate duration in hours
            $jamMulai = \Carbon\Carbon::parse($item->jam_mulai);
            $jamSelesai = \Carbon\Carbon::parse($item->jam_selesai);
            $durasiJam = $jamSelesai->diffInHours($jamMulai);

            // Store duration for display
            $item->durasi = $durasiJam;

            $totalLembur += $durasiJam;

            if ($item->jenis_lembur == 'Hari Kerja') {
                $lemburHariBiasa += $durasiJam;
            } else { // Hari Libur
                $lemburHariLibur += $durasiJam;
            }
        }

        // Log for debugging
        Log::info('Lembur calculation', [
            'periode' => [$periode->tanggal_mulai->format('Y-m-d'), $periode->tanggal_selesai->format('Y-m-d')],
            'total_lembur' => $totalLembur,
            'lembur_biasa' => $lemburHariBiasa,
            'lembur_libur' => $lemburHariLibur,
            'lembur_count' => $lembur->count(),
            'lembur_data' => $lembur->toArray()
        ]);

        // Hitung potongan ketidakhadiran (jika tidak hadir, potong gaji pokok / total hari kerja)
        $gajiPokok = $karyawan->jabatan ? $karyawan->jabatan->gaji_pokok : 0;
        $potonganPerHari = $gajiPokok / 30; // Asumsi 30 hari kerja per bulan

        $potonganTidakHadir = round($potonganPerHari * $tidakHadirCount);

        // Hitung potongan keterlambatan (asumsi: per 30 menit keterlambatan = potong 25.000)
        $potonganPerTerlambat = 25000; // Rp 25.000 per 30 menit
        $potonganKeterlambatan = round($potonganPerTerlambat * ($totalKeterlambatan / 30));

        // Hitung tunjangan kehadiran (bonus kehadiran - potong berdasarkan ketidakhadiran)
        $tunjanganKehadiranPenuh = 100000; // Rp 100.000 per bulan
        $tunjanganKehadiran = $tunjanganKehadiranPenuh;

        if ($tidakHadirCount > 0) {
            $tunjanganKehadiran = 0; // Tidak dapat tunjangan kehadiran jika ada ketidakhadiran
        } elseif ($totalKeterlambatan > 60) { // Jika terlambat lebih dari 1 jam total
            $tunjanganKehadiran = 0;
        }

        // Hitung tunjangan lembur
        $tarifLemburBiasa = $karyawan->jabatan ? $karyawan->jabatan->uang_lembur_biasa : 0;
        $tarifLemburLibur = $karyawan->jabatan ? $karyawan->jabatan->uang_lembur_libur : 0;

        $tunjanganLemburBiasa = $lemburHariBiasa * $tarifLemburBiasa;
        $tunjanganLemburLibur = $lemburHariLibur * $tarifLemburLibur;
        $totalTunjanganLembur = $tunjanganLemburBiasa + $tunjanganLemburLibur;

        // Hitung potongan BPJS
        // Asumsi: BPJS Kesehatan 1% dari gaji pokok, BPJS Ketenagakerjaan 2% dari gaji pokok
        $potonganBPJSKesehatan = round($gajiPokok * 0.01);
        $potonganBPJSKetenagakerjaan = round($gajiPokok * 0.02);

        // Data absensi untuk view
        $dataAbsensi = [
            'absensi' => $absensi,
            'total_hari' => $totalHari,
            'total_hari_kerja' => $totalHariKerja,
            'hadir' => $hadirCount,
            'izin' => $izinCount,
            'cuti' => $cutiCount,
            'izin_cuti' => $izinCutiCount,
            'tidak_hadir' => $tidakHadirCount,
            'total_keterlambatan' => $totalKeterlambatan,
            'keterlambatan_display' => $keterlambatanDisplay,
            'total_pulang_awal' => $totalPulangAwal,
            'pulang_awal_display' => $pulangAwalDisplay,
            'total_lembur' => $totalLembur,
            'lembur_hari_biasa' => $lemburHariBiasa,
            'lembur_hari_libur' => $lemburHariLibur,
            'tunjangan_lembur_biasa' => $tunjanganLemburBiasa,
            'tunjangan_lembur_libur' => $tunjanganLemburLibur,
            'total_tunjangan_lembur' => $totalTunjanganLembur,
            'tunjangan_kehadiran' => $tunjanganKehadiran,
            'lembur_disetujui' => $lembur
        ];

        // Data potongan BPJS
        $potonganBPJS = [
            'kesehatan' => $potonganBPJSKesehatan,
            'ketenagakerjaan' => $potonganBPJSKetenagakerjaan
        ];

        // Data potongan absensi
        $potonganAbsensi = [
            'tidak_hadir' => $potonganTidakHadir,
            'keterlambatan' => $potonganKeterlambatan
        ];

        // Ambil data master potongan
        $dataPotongan = Potongan::all();

        return view('admin.penggajians.review', compact(
            'karyawan',
            'periode',
            'dataAbsensi',
            'potonganBPJS',
            'potonganAbsensi',
            'dataPotongan'
        ));
    }

    /**
     * Process payroll data and create entry
     * This method is modified to handle proper JSON encoding for detail fields
     */
    public function process(Request $request)
    {
        Log::info('Process method called', ['request' => $request->all()]);

        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'periode_id' => 'required|exists:periodegajis,id',
            'gaji_pokok' => 'required|numeric|min:0',
            'total_tunjangan' => 'required|numeric|min:0',
            'total_potongan' => 'required|numeric|min:0',
            'gaji_bersih' => 'required|numeric',
            'tunjangan' => 'array',
            'tunjangan.*.nama' => 'required|string',
            'tunjangan.*.nominal' => 'required|numeric|min:0',
            'potongan' => 'array',
            'potongan.*.nama' => 'required|string',
            'potongan.*.nominal' => 'required|numeric|min:0',
        ]);

        $karyawanId = $request->karyawan_id;
        $periodeId = $request->periode_id;

        // Ambil data karyawan dan periode
        $karyawan = Karyawan::with(['departemen', 'bagian', 'jabatan', 'profesi'])->findOrFail($karyawanId);
        $periode = PeriodeGaji::findOrFail($periodeId);

        // Validasi bahwa belum ada penggajian untuk karyawan dan periode ini
        $exists = Penggajian::where('id_periode', $periodeId)
            ->where('id_karyawan', $karyawanId)
            ->exists();

        if ($exists) {
            return redirect()->route('penggajian.index')
                ->with('error', "Penggajian untuk karyawan {$karyawan->nama_karyawan} pada periode ini sudah ada.");
        }

        // Ambil data tunjangan dan potongan dari form
        $gajiPokok = $request->gaji_pokok;
        $totalTunjangan = $request->total_tunjangan;
        $totalPotongan = $request->total_potongan;
        $gajiBersih = $request->gaji_bersih;

        // Filter out empty tunjangan
        $detailTunjangan = collect($request->tunjangan)->filter(function ($tunjangan) {
            return !empty($tunjangan['nama']) && isset($tunjangan['nominal']) && $tunjangan['nominal'] > 0;
        })->values()->toArray();

        // Filter out empty potongan
        $detailPotongan = collect($request->potongan)->filter(function ($potongan) {
            return !empty($potongan['nama']) && isset($potongan['nominal']) && $potongan['nominal'] > 0;
        })->values()->toArray();

        // Department details
        $detailDepartemen = [
            'departemen' => $karyawan->departemen ? $karyawan->departemen->name_departemen : null,
            'bagian' => $karyawan->bagian ? $karyawan->bagian->name_bagian : null,
            'jabatan' => $karyawan->jabatan ? $karyawan->jabatan->name_jabatan : null,
            'profesi' => $karyawan->profesi ? $karyawan->profesi->name_profesi : null,
        ];

        // Create payroll entry
        DB::beginTransaction();
        try {
            Penggajian::create([
                'id' => Str::uuid(),
                'id_periode' => $periodeId,
                'id_karyawan' => $karyawanId,
                'periode_awal' => $periode->tanggal_mulai,
                'periode_akhir' => $periode->tanggal_selesai,
                'gaji_pokok' => $gajiPokok,
                'tunjangan' => $totalTunjangan,
                'detail_tunjangan' => json_encode($detailTunjangan), // Explicitly encode as JSON
                'potongan' => $totalPotongan,
                'detail_potongan' => json_encode($detailPotongan), // Explicitly encode as JSON
                'detail_departemen' => json_encode($detailDepartemen), // Explicitly encode as JSON
                'gaji_bersih' => $gajiBersih,
            ]);

            DB::commit();

            return redirect()->route('penggajian.index')
                ->with('success', "Penggajian untuk karyawan {$karyawan->nama_karyawan} berhasil diproses.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payroll', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    /**
     * Generate detailed payslips with 3 per page
     */
    public function printDetailedSlips(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodegajis,id',
            'departemen_id' => 'nullable|exists:departemens,id',
            'karyawan_id' => 'nullable|exists:karyawans,id',
            'slips_per_page' => 'nullable|integer|min:1|max:3',
        ]);

        // Default to 3 slips per page if not specified
        $slipsPerPage = $request->slips_per_page ?? 3;

        // Get the period
        $periode = PeriodeGaji::findOrFail($request->periode_id);

        // Build query
        $query = Penggajian::with([
            'karyawan.departemen',
            'karyawan.bagian',
            'karyawan.jabatan',
            'karyawan.profesi',
            'periodeGaji'
        ])->where('id_periode', $request->periode_id);

        // Filter by department if specified
        if ($request->filled('departemen_id')) {
            $karyawanIds = Karyawan::where('id_departemen', $request->departemen_id)->pluck('id')->toArray();
            $query->whereIn('id_karyawan', $karyawanIds);
        }

        // Filter by specific karyawan if specified
        if ($request->filled('karyawan_id')) {
            $query->where('id_karyawan', $request->karyawan_id);
        }

        // Get penggajian data
        $penggajians = $query->orderBy('id_karyawan')->get();

        // If no data found
        if ($penggajians->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data penggajian yang sesuai dengan kriteria tersebut.');
        }

        // Process each penggajian to add attendance data
        foreach ($penggajians as $penggajian) {
            $this->processAttendanceData($penggajian, $periode);
        }

        // Pass to view - use our detailed payslip view
        return view('admin.penggajians.detailed-slip', compact('penggajians', 'periode', 'slipsPerPage'));
    }
}
