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
                $karyawan = Karyawan::with(['jabatan', 'tunjangans'])->findOrFail($karyawanId);

                // Check if payroll entry already exists for this employee and period
                $exists = Penggajian::where('id_periode', $periodeId)
                    ->where('id_karyawan', $karyawanId)
                    ->exists();

                if ($exists) {
                    $errors[] = "Penggajian untuk karyawan {$karyawan->nama} pada periode ini sudah ada.";
                    continue;
                }

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
}
