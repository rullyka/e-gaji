<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\PeriodeGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PenggajianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $penggajians = Penggajian::with(['karyawan', 'periodeGaji'])->latest()->paginate(10);
        return view('admin.penggajians.index', compact('penggajians'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $periodeGajis = PeriodeGaji::where('status', 'aktif')->get();
        $departemens = Departemen::all();
        $statusOptions = ['aktif', 'nonaktif', 'cuti'];

        return view('admin.penggajians.create', compact('periodeGajis', 'departemens', 'statusOptions'));
    }

    /**
     * Get employees based on filters that don't have payroll entries for the selected period
     */
    public function getFilteredKaryawans(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodegajis,id',
            'departemen_id' => 'nullable|exists:departemens,id',
            'status' => 'nullable|in:aktif,nonaktif,cuti',
        ]);

        $periodeId = $request->periode_id;
        $departemenId = $request->departemen_id;
        $status = $request->status;

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
            $query->where('status', $status);
        }

        // Exclude employees who already have payroll entries for this period
        if (!empty($processedKaryawanIds)) {
            $query->whereNotIn('id', $processedKaryawanIds);
        }

        // Get employees with their related data
        $karyawans = $query->with(['departemen', 'bagian', 'jabatan', 'profesi'])->get();

        return response()->json([
            'success' => true,
            'data' => $karyawans,
            'count' => $karyawans->count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'periode_id' => 'required|exists:periodegajis,id',
        'karyawan_ids' => 'required|array',
        'karyawan_ids.*' => 'exists:karyawans,id',
    ]);

    $periodeId = $request->periode_id;
    $karyawanIds = $request->karyawan_ids;
    $periode = PeriodeGaji::findOrFail($periodeId);

    $count = 0;
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
            $tunjangan = 0;
            $detailTunjangan = [];

            // Tunjangan dari jabatan
            if ($karyawan->jabatan && $karyawan->jabatan->tunjangan_jabatan > 0) {
                $tunjangan += $karyawan->jabatan->tunjangan_jabatan;
                $detailTunjangan[] = [
                    'nama' => 'Tunjangan Jabatan',
                    'nominal' => $karyawan->jabatan->tunjangan_jabatan
                ];
            }

            // Tunjangan dari profesi
            if ($karyawan->profesi && $karyawan->profesi->tunjangan_profesi > 0) {
                $tunjangan += $karyawan->profesi->tunjangan_profesi;
                $detailTunjangan[] = [
                    'nama' => 'Tunjangan Profesi',
                    'nominal' => $karyawan->profesi->tunjangan_profesi
                ];
            }

            // For now, no deductions
            $potongan = 0;
            $detailPotongan = [];

            // Calculate net salary
            $gajiBersih = $gajiPokok + $tunjangan - $potongan;

            // Department details
            $detailDepartemen = [
                'departemen' => $karyawan->departemen ? $karyawan->departemen->name_departemen : null,
                'bagian' => $karyawan->bagian ? $karyawan->bagian->name_bagian : null,
                'jabatan' => $karyawan->jabatan ? $karyawan->jabatan->name_jabatan : null,
                'profesi' => $karyawan->profesi ? $karyawan->profesi->name_profesi : null,
            ];

            // Create payroll entry
            Penggajian::create([
                'id' => Str::uuid(),
                'id_periode' => $periodeId,
                'id_karyawan' => $karyawanId,
                'periode_awal' => $periode->tanggal_mulai,
                'periode_akhir' => $periode->tanggal_selesai,
                'gaji_pokok' => $gajiPokok,
                'tunjangan' => $tunjangan,
                'detail_tunjangan' => json_encode($detailTunjangan),
                'potongan' => $potongan,
                'detail_potongan' => json_encode($detailPotongan),
                'detail_departemen' => json_encode($detailDepartemen),
                'gaji_bersih' => $gajiBersih,
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
        return view('admin.penggajians.show', compact('penggajian'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penggajian $penggajian)
    {
        $penggajian->load(['karyawan', 'periodeGaji']);
        return view('admin.penggajians.edit', compact('penggajian'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penggajian $penggajian)
    {
        $request->validate([
            'gaji_pokok' => 'required|numeric|min:0',
            'tunjangan' => 'nullable|numeric|min:0',
            'potongan' => 'nullable|numeric|min:0',
            'detail_tunjangan' => 'nullable|array',
            'detail_potongan' => 'nullable|array',
        ]);

        // Process allowance details
        $totalTunjangan = 0;
        $detailTunjangan = [];

        if ($request->has('detail_tunjangan')) {
            foreach ($request->detail_tunjangan as $tunjangan) {
                if (!empty($tunjangan['nama']) && !empty($tunjangan['nominal'])) {
                    $nominal = floatval($tunjangan['nominal']);
                    $totalTunjangan += $nominal;
                    $detailTunjangan[] = [
                        'nama' => $tunjangan['nama'],
                        'nominal' => $nominal
                    ];
                }
            }
        }

        // Process deduction details
        $totalPotongan = 0;
        $detailPotongan = [];

        if ($request->has('detail_potongan')) {
            foreach ($request->detail_potongan as $potongan) {
                if (!empty($potongan['nama']) && !empty($potongan['nominal'])) {
                    $nominal = floatval($potongan['nominal']);
                    $totalPotongan += $nominal;
                    $detailPotongan[] = [
                        'nama' => $potongan['nama'],
                        'nominal' => $nominal
                    ];
                }
            }
        }

        // Calculate net salary
        $gajiBersih = $request->gaji_pokok + $totalTunjangan - $totalPotongan;

        $penggajian->update([
            'gaji_pokok' => $request->gaji_pokok,
            'tunjangan' => $totalTunjangan,
            'detail_tunjangan' => json_encode($detailTunjangan),
            'potongan' => $totalPotongan,
            'detail_potongan' => json_encode($detailPotongan),
            'gaji_bersih' => $gajiBersih,
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
        $periodeId = $request->periode_id;

        $penggajians = [];
        $selectedPeriode = null;

        if ($periodeId) {
            $selectedPeriode = PeriodeGaji::findOrFail($periodeId);
            $penggajians = Penggajian::with(['karyawan', 'periodeGaji'])
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
        $departemens = Departemen::all();
        $periodeGajis = PeriodeGaji::orderBy('tanggal_mulai', 'desc')->get();

        $departemenId = $request->departemen_id;
        $periodeId = $request->periode_id;

        $penggajians = [];
        $selectedDepartemen = null;
        $selectedPeriode = null;

        if ($departemenId && $periodeId) {
            $selectedDepartemen = Departemen::findOrFail($departemenId);
            $selectedPeriode = PeriodeGaji::findOrFail($periodeId);

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
            'type' => 'required|in:tunjangan,potongan',
            'nama' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
        ]);

        $type = $request->type;
        $component = [
            'nama' => $request->nama,
            'nominal' => floatval($request->nominal)
        ];

        if ($type === 'tunjangan') {
            $details = $penggajian->detail_tunjangan ?: [];
            $details[] = $component;
            $totalTunjangan = array_sum(array_column($details, 'nominal'));

            $penggajian->update([
                'detail_tunjangan' => json_encode($details),
                'tunjangan' => $totalTunjangan,
                'gaji_bersih' => $penggajian->gaji_pokok + $totalTunjangan - $penggajian->potongan
            ]);

            return redirect()->back()->with('success', 'Tunjangan berhasil ditambahkan');
        } else {
            $details = $penggajian->detail_potongan ?: [];
            $details[] = $component;
            $totalPotongan = array_sum(array_column($details, 'nominal'));

            $penggajian->update([
                'detail_potongan' => json_encode($details),
                'potongan' => $totalPotongan,
                'gaji_bersih' => $penggajian->gaji_pokok + $penggajian->tunjangan - $totalPotongan
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
            'type' => 'required|in:tunjangan,potongan',
            'index' => 'required|integer|min:0',
        ]);

        $type = $request->type;
        $index = $request->index;

        if ($type === 'tunjangan') {
            $details = $penggajian->detail_tunjangan ?: [];

            if (isset($details[$index])) {
                $nominal = $details[$index]['nominal'];
                array_splice($details, $index, 1);

                $totalTunjangan = array_sum(array_column($details, 'nominal'));

                $penggajian->update([
                    'detail_tunjangan' => json_encode($details),
                    'tunjangan' => $totalTunjangan,
                    'gaji_bersih' => $penggajian->gaji_pokok + $totalTunjangan - $penggajian->potongan
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
                    'potongan' => $totalPotongan,
                    'gaji_bersih' => $penggajian->gaji_pokok + $penggajian->tunjangan - $totalPotongan
                ]);

                return redirect()->back()->with('success', 'Potongan berhasil dihapus');
            }
        }

        return redirect()->back()->with('error', 'Komponen tidak ditemukan');
    }

    /**
     * Generate payslip for an employee
     */
    public function generatePayslip(Penggajian $penggajian)
    {
        $penggajian->load(['karyawan', 'periodeGaji']);

        return view('admin.penggajians.payslip', compact('penggajian'));
    }

    /**
     * Export payroll data to Excel
     */
    public function exportExcel(Request $request)
    {
        $periodeId = $request->periode_id;
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

        $periode = $penggajians->first()->periodeGaji;
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
            'periode_id' => 'required|exists:periodegajis,id',
            'departemen_id' => 'nullable|exists:departemens,id',
            'status' => 'nullable|in:aktif,nonaktif,cuti',
        ]);

        $periodeId = $request->periode_id;
        $departemenId = $request->departemen_id;
        $status = $request->status;

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
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($karyawans as $karyawan) {
                // Calculate basic salary
                $gajiPokok = $karyawan->jabatan ? $karyawan->jabatan->gaji_pokok : 0;

                // Calculate allowances
                $tunjangan = 0;
                $detailTunjangan = [];

                foreach ($karyawan->tunjangans as $tunjangan_item) {
                    $tunjangan += $tunjangan_item->nominal;
                    $detailTunjangan[] = [
                        'nama' => $tunjangan_item->nama,
                        'nominal' => $tunjangan_item->nominal
                    ];
                }

                // For now, no deductions
                $potongan = 0;
                $detailPotongan = [];

                // Calculate net salary
                $gajiBersih = $gajiPokok + $tunjangan - $potongan;

                // Department details
                $detailDepartemen = [
                    'departemen' => $karyawan->departemen ? $karyawan->departemen->nama : null,
                    'bagian' => $karyawan->bagian ? $karyawan->bagian->nama : null,
                    'jabatan' => $karyawan->jabatan ? $karyawan->jabatan->nama : null,
                    'profesi' => $karyawan->profesi ? $karyawan->profesi->nama : null,
                ];

                // Create payroll entry
                Penggajian::create([
                    'id' => Str::uuid(),
                    'id_periode' => $periodeId,
                    'id_karyawan' => $karyawan->id,
                    'periode_awal' => $periode->tanggal_mulai,
                    'periode_akhir' => $periode->tanggal_selesai,
                    'gaji_pokok' => $gajiPokok,
                    'tunjangan' => $tunjangan,
                    'detail_tunjangan' => json_encode($detailTunjangan),
                    'potongan' => $potongan,
                    'detail_potongan' => json_encode($detailPotongan),
                    'detail_departemen' => json_encode($detailDepartemen),
                    'gaji_bersih' => $gajiBersih,
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
        'karyawan_id' => 'required|exists:karyawans,id',
        'periode_id' => 'required|exists:periodegajis,id',
    ]);

    $karyawanId = $request->karyawan_id;
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
        ->where(function($query) use ($periode) {
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
    $hariLiburFormatted = array_map(function($date) {
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

    // Hitung total kehadiran
    $hadirCount = $absensi->where('status', 'Hadir')->count();
    $izinCount = $absensi->whereIn('status', ['Izin', 'Cuti'])->count();

    // Tambahkan izin dari tabel cuti
    $izinCutiCount = 0;
    foreach ($cuti as $c) {
        // Hitung jumlah hari cuti yang jatuh dalam periode
        $startDate = max($c->tanggal_mulai_cuti, $periode->tanggal_mulai);
        $endDate = min($c->tanggal_akhir_cuti, $periode->tanggal_selesai);
        $izinCutiCount += $startDate->diffInDays($endDate) + 1;
    }

    $izinCount += $izinCutiCount;

    // Hitung tidak hadir (hari kerja - hadir - izin)
    $tidakHadirCount = $totalHariKerja - $hadirCount - $izinCount;
    $tidakHadirCount = max(0, $tidakHadirCount); // Pastikan tidak negatif

    // Hitung total keterlambatan dalam menit
    $totalKeterlambatan = $absensi->sum('keterlambatan');

    // Hitung total pulang awal dalam menit
    $totalPulangAwal = $absensi->sum('pulang_awal');

    // Hitung total lembur dalam jam
    $totalLembur = $lembur->sum('lembur_disetujui');

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
    $tarifLemburPerJam = $karyawan->jabatan ? $karyawan->jabatan->uang_lembur_biasa : 0;
    $totalTunjanganLembur = $totalLembur * $tarifLemburPerJam;

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
        'tidak_hadir' => $tidakHadirCount,
        'total_keterlambatan' => $totalKeterlambatan,
        'total_pulang_awal' => $totalPulangAwal,
        'total_lembur' => $totalLembur,
        'tunjangan_kehadiran' => $tunjanganKehadiran
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
 * Process reviewed payroll and store it
 */
public function process(Request $request)
{
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

    // Ambil data tunjangan dan potongan
    $gajiPokok = $request->gaji_pokok;
    $totalTunjangan = $request->total_tunjangan;
    $totalPotongan = $request->total_potongan;
    $gajiBersih = $request->gaji_bersih;

    // Filter out empty tunjangan
    $detailTunjangan = collect($request->tunjangan)->filter(function($tunjangan) {
        return !empty($tunjangan['nama']) && $tunjangan['nominal'] > 0;
    })->values()->toArray();

    // Filter out empty potongan
    $detailPotongan = collect($request->potongan)->filter(function($potongan) {
        return !empty($potongan['nama']) && $potongan['nominal'] > 0;
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
            'detail_tunjangan' => json_encode($detailTunjangan),
            'potongan' => $totalPotongan,
            'detail_potongan' => json_encode($detailPotongan),
            'detail_departemen' => json_encode($detailDepartemen),
            'gaji_bersih' => $gajiBersih,
        ]);

        DB::commit();

        return redirect()->route('penggajian.index')
            ->with('success', "Penggajian untuk karyawan {$karyawan->nama_karyawan} berhasil diproses.");
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', "Terjadi kesalahan: " . $e->getMessage());
    }
}
}