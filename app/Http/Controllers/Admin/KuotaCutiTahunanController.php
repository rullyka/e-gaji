<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KuotaCutiTahunan;
use App\Models\Karyawan;
use App\Models\CutiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KuotaCutiTahunanController extends Controller
{
    public function index()
    {
        $kuotaCuti = KuotaCutiTahunan::with('cutiKaryawan')
            ->join('karyawans', 'kuota_cuti_tahunans.karyawan_id', '=', 'karyawans.id')
            ->select('kuota_cuti_tahunans.*', 'karyawans.nama_karyawan as nama_karyawan')
            ->orderBy('tahun', 'desc')
            ->paginate(10);

        $allKaryawan = Karyawan::orderBy('nama_karyawan')->get();

        return view('admin.kuota-cuti.index', compact('kuotaCuti', 'allKaryawan'));
    }

    public function create()
    {
        $karyawan = Karyawan::orderBy('nama_karyawan')->get();
        $tahun = date('Y');

        return view('admin.kuota-cuti.create', compact('karyawan', 'tahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'kuota_awal' => 'required|integer|min:0|max:12',
        ]);

        // Cek apakah sudah ada kuota untuk karyawan dan tahun yang sama
        $exists = KuotaCutiTahunan::where('karyawan_id', $request->karyawan_id)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kuota cuti untuk karyawan dan tahun tersebut sudah ada');
        }

        // Buat kuota cuti baru
        KuotaCutiTahunan::create([
            'karyawan_id' => $request->karyawan_id,
            'tahun' => $request->tahun,
            'kuota_awal' => $request->kuota_awal,
            'kuota_digunakan' => 0,
            'kuota_sisa' => $request->kuota_awal,
        ]);

        return redirect()->route('kuota-cuti.index')
            ->with('success', 'Kuota cuti tahunan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $kuotaCuti = KuotaCutiTahunan::findOrFail($id);
        $karyawan = Karyawan::orderBy('nama_karyawan')->get();

        return view('admin.kuota-cuti.edit', compact('kuotaCuti', 'karyawan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kuota_awal' => 'required|integer|min:0|max:12',
        ]);

        $kuotaCuti = KuotaCutiTahunan::findOrFail($id);

        // Hitung selisih kuota awal baru dengan yang lama
        $selisih = $request->kuota_awal - $kuotaCuti->kuota_awal;

        // Update kuota awal dan kuota sisa
        $kuotaCuti->kuota_awal = $request->kuota_awal;
        $kuotaCuti->kuota_sisa = $kuotaCuti->kuota_sisa + $selisih;
        $kuotaCuti->save();

        return redirect()->route('kuota-cuti.index')
            ->with('success', 'Kuota cuti tahunan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kuotaCuti = KuotaCutiTahunan::findOrFail($id);

        // Cek apakah kuota cuti sudah digunakan
        if ($kuotaCuti->kuota_digunakan > 0) {
            return redirect()->route('kuota-cuti.index')
                ->with('error', 'Tidak dapat menghapus kuota cuti yang sudah digunakan');
        }

        // Cek apakah ada pengajuan cuti yang terkait
        // Perbaikan query untuk mencari cuti terkait
        $cutiTerkait = CutiKaryawan::where('id_karyawan', $kuotaCuti->karyawan_id)
            ->whereYear('tanggal_mulai_cuti', $kuotaCuti->tahun)
            ->exists();

        if ($cutiTerkait) {
            return redirect()->route('kuota-cuti.index')
                ->with('error', 'Tidak dapat menghapus kuota cuti yang memiliki pengajuan cuti terkait');
        }

        $kuotaCuti->delete();

        return redirect()->route('kuota-cuti.index')
            ->with('success', 'Kuota cuti tahunan berhasil dihapus');
    }

    public function report()
    {
        $tahunIni = date('Y');
        $tahunList = range($tahunIni - 5, $tahunIni + 1);
        $selectedTahun = request('tahun', $tahunIni);

        // Get all departments for the filter
        $departemens = \App\Models\Departemen::orderBy('name_departemen')->get();

        // Get status filter (default to 'Bulanan')
        $statusKaryawan = request('statuskaryawan', 'Bulanan');

        // Build the query
        $query = KuotaCutiTahunan::join('karyawans', 'kuota_cuti_tahunans.karyawan_id', '=', 'karyawans.id')
            ->join('departemens', 'karyawans.id_departemen', '=', 'departemens.id')
            ->where('tahun', $selectedTahun);

        // Apply department filter if selected
        if (request('id_departemen')) {
            $query->where('karyawans.id_departemen', request('id_departemen'));
        }

        // Apply status filter
        if ($statusKaryawan !== 'all') {
            $query->where('karyawans.statuskaryawan', $statusKaryawan);
        }

        // Get the data
        $kuotaReport = $query->select(
            'karyawans.nama_karyawan as nama_karyawan',
            'departemens.name_departemen as name_departemen',
            'kuota_cuti_tahunans.kuota_awal',
            'kuota_cuti_tahunans.kuota_digunakan',
            'kuota_cuti_tahunans.kuota_sisa',
            DB::raw('(kuota_cuti_tahunans.kuota_digunakan / NULLIF(kuota_cuti_tahunans.kuota_awal, 0) * 100) as persentase_penggunaan')
        )
            ->orderBy('karyawans.nama_karyawan')
            ->get();

        return view('admin.kuota-cuti.report', compact('kuotaReport', 'tahunList', 'selectedTahun', 'departemens'));
    }

    // Add this new method to your KuotaCutiTahunanController

    public function generateMassal(Request $request)
    {
        $request->validate([
            'tahun_generate' => 'required|integer|min:2000|max:2100',
            'kuota_awal_generate' => 'required|integer|min:0|max:12',
            'karyawan_ids' => 'required|array',
            'karyawan_ids.*' => 'exists:karyawans,id',
        ]);

        $tahun = $request->tahun_generate;
        $kuotaAwal = $request->kuota_awal_generate;
        $karyawanIds = $request->karyawan_ids;

        $berhasil = 0;
        $gagal = 0;

        foreach ($karyawanIds as $karyawanId) {
            // Cek apakah sudah ada kuota untuk karyawan dan tahun yang sama
            $exists = KuotaCutiTahunan::where('karyawan_id', $karyawanId)
                ->where('tahun', $tahun)
                ->exists();

            if (!$exists) {
                // Buat kuota cuti baru
                KuotaCutiTahunan::create([
                    'karyawan_id' => $karyawanId,
                    'tahun' => $tahun,
                    'kuota_awal' => $kuotaAwal,
                    'kuota_digunakan' => 0,
                    'kuota_sisa' => $kuotaAwal,
                ]);
                $berhasil++;
            } else {
                $gagal++;
            }
        }

        if ($berhasil > 0 && $gagal == 0) {
            return redirect()->route('kuota-cuti.index')
                ->with('success', "Berhasil generate kuota cuti untuk {$berhasil} karyawan.");
        } elseif ($berhasil > 0 && $gagal > 0) {
            return redirect()->route('kuota-cuti.index')
                ->with('success', "Berhasil generate kuota cuti untuk {$berhasil} karyawan. {$gagal} karyawan sudah memiliki kuota untuk tahun {$tahun}.");
        } else {
            return redirect()->route('kuota-cuti.index')
                ->with('error', "Gagal generate kuota cuti. Semua karyawan yang dipilih sudah memiliki kuota untuk tahun {$tahun}.");
        }
    }
}
