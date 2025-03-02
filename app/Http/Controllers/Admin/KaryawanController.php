<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Departemen;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\Profesi;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::with(['departemen', 'bagian', 'jabatan', 'profesi', 'programStudi'])
            ->orderBy('nama_karyawan')
            ->get();
        return view('admin.karyawans.index', compact('karyawans'));
    }

    public function create()
    {
        $departemens = Departemen::orderBy('name_departemen')->get();
        $bagians = Bagian::orderBy('name_bagian')->get();
        $jabatans = Jabatan::orderBy('name_jabatan')->get();
        $profesis = Profesi::orderBy('name_profesi')->get();
        $programStudis = ProgramStudi::orderBy('name_programstudi')->get();

        $statusKaryawan = ['Bulanan', 'Harian', 'Borongan'];
        $statusKawin = ['Lajang', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];
        $pendidikanTerakhir = ['SD/MI', 'SMP/MTS', 'SMA/SMK/MA', 'S1', 'S2', 'S3', 'Lainnya'];
        $ukuranKemeja = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

        // Generate NIK Karyawan
        $nikKaryawan = $this->generateNikKaryawan();

        return view('admin.karyawans.create', compact(
            'departemens',
            'bagians',
            'jabatans',
            'profesis',
            'programStudis',
            'statusKaryawan',
            'statusKawin',
            'pendidikanTerakhir',
            'ukuranKemeja',
            'nikKaryawan'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik_karyawan' => 'required|string|max:255|unique:karyawans,nik_karyawan',
            'nama_karyawan' => 'required|string|max:255',
            'foto_karyawan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'statuskaryawan' => 'required|in:Bulanan,Harian,Borongan',
            'id_departemen' => 'nullable|exists:departemens,id',
            'id_bagian' => 'nullable|exists:bagians,id',
            'tgl_awalmmasuk' => 'required|date',
            'tahun_keluar' => 'nullable|date',
            'id_jabatan' => 'nullable|exists:jabatans,id',
            'id_profesi' => 'nullable|exists:profesis,id',
            'nik' => 'required|string|max:16',
            'kk' => 'required|string|max:16',
            'statuskawin' => 'required|in:Lajang,Kawin,Cerai Hidup,Cerai Mati',
            'pendidikan_terakhir' => 'required|in:SD/MI,SMP/MTS,SMA/SMK/MA,S1,S2,S3,Lainnya',
            'id_programstudi' => 'nullable|exists:program_studis,id',
            'no_hp' => 'required|string|max:16',
            'ortu_bapak' => 'required|string|max:255',
            'ortu_ibu' => 'required|string|max:255',
            'ukuran_kemeja' => 'required|in:S,M,L,XL,XXL,XXXL',
            'ukuran_celana' => 'required|string|max:5',
            'ukuran_sepatu' => 'required|string|max:5',
            'jml_anggotakk' => 'required|string|max:5',
            'upload_ktp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle foto_karyawan upload
        if ($request->hasFile('foto_karyawan')) {
            $fotoKaryawan = $request->file('foto_karyawan');
            $fotoName = Str::slug($request->nama_karyawan) . '-' . time() . '.' . $fotoKaryawan->getClientOriginalExtension();
            $fotoKaryawan->storeAs('public/karyawan/foto', $fotoName);
            $data['foto_karyawan'] = $fotoName;
        }

        // Handle upload_ktp upload
        if ($request->hasFile('upload_ktp')) {
            $uploadKtp = $request->file('upload_ktp');
            $ktpName = 'ktp-' . Str::slug($request->nama_karyawan) . '-' . time() . '.' . $uploadKtp->getClientOriginalExtension();
            $uploadKtp->storeAs('public/karyawan/ktp', $ktpName);
            $data['upload_ktp'] = $ktpName;
        }

        Karyawan::create($data);

        return redirect()->route('karyawans.index')
            ->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function show(Karyawan $karyawan)
    {
        $karyawan->load(['departemen', 'bagian', 'jabatan', 'profesi', 'programStudi']);
        return view('admin.karyawans.show', compact('karyawan'));
    }

    public function edit(Karyawan $karyawan)
    {
        $departemens = Departemen::orderBy('name_departemen')->get();
        $bagians = Bagian::orderBy('name_bagian')->get();
        $jabatans = Jabatan::orderBy('name_jabatan')->get();
        $profesis = Profesi::orderBy('name_profesi')->get();
        $programStudis = ProgramStudi::orderBy('name_programstudi')->get();

        $statusKaryawan = ['Bulanan', 'Harian', 'Borongan'];
        $statusKawin = ['Lajang', 'Kawin', 'Cerai Hidup', 'Cerai Mati'];
        $pendidikanTerakhir = ['SD/MI', 'SMP/MTS', 'SMA/SMK/MA', 'S1', 'S2', 'S3', 'Lainnya'];
        $ukuranKemeja = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

        return view('admin.karyawans.edit', compact(
            'karyawan',
            'departemens',
            'bagians',
            'jabatans',
            'profesis',
            'programStudis',
            'statusKaryawan',
            'statusKawin',
            'pendidikanTerakhir',
            'ukuranKemeja'
        ));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nik_karyawan' => 'required|string|max:255|unique:karyawans,nik_karyawan,' . $karyawan->id,
            'nama_karyawan' => 'required|string|max:255',
            'foto_karyawan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'statuskaryawan' => 'required|in:Bulanan,Harian,Borongan',
            'id_departemen' => 'nullable|exists:departemens,id',
            'id_bagian' => 'nullable|exists:bagians,id',
            'tgl_awalmmasuk' => 'required|date',
            'tahun_keluar' => 'nullable|date',
            'id_jabatan' => 'nullable|exists:jabatans,id',
            'id_profesi' => 'nullable|exists:profesis,id',
            'nik' => 'required|string|max:16',
            'kk' => 'required|string|max:16',
            'statuskawin' => 'required|in:Lajang,Kawin,Cerai Hidup,Cerai Mati',
            'pendidikan_terakhir' => 'required|in:SD/MI,SMP/MTS,SMA/SMK/MA,S1,S2,S3,Lainnya',
            'id_programstudi' => 'nullable|exists:program_studis,id',
            'no_hp' => 'required|string|max:16',
            'ortu_bapak' => 'required|string|max:255',
            'ortu_ibu' => 'required|string|max:255',
            'ukuran_kemeja' => 'required|in:S,M,L,XL,XXL,XXXL',
            'ukuran_celana' => 'required|string|max:5',
            'ukuran_sepatu' => 'required|string|max:5',
            'jml_anggotakk' => 'required|string|max:5',
            'upload_ktp' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle foto_karyawan upload
        if ($request->hasFile('foto_karyawan')) {
            // Delete old photo if exists
            if ($karyawan->foto_karyawan) {
                Storage::delete('public/karyawan/foto/' . $karyawan->foto_karyawan);
            }

            $fotoKaryawan = $request->file('foto_karyawan');
            $fotoName = Str::slug($request->nama_karyawan) . '-' . time() . '.' . $fotoKaryawan->getClientOriginalExtension();
            $fotoKaryawan->storeAs('public/karyawan/foto', $fotoName);
            $data['foto_karyawan'] = $fotoName;
        }

        // Handle upload_ktp upload
        if ($request->hasFile('upload_ktp')) {
            // Delete old KTP if exists
            if ($karyawan->upload_ktp) {
                Storage::delete('public/karyawan/ktp/' . $karyawan->upload_ktp);
            }

            $uploadKtp = $request->file('upload_ktp');
            $ktpName = 'ktp-' . Str::slug($request->nama_karyawan) . '-' . time() . '.' . $uploadKtp->getClientOriginalExtension();
            $uploadKtp->storeAs('public/karyawan/ktp', $ktpName);
            $data['upload_ktp'] = $ktpName;
        }

        $karyawan->update($data);

        return redirect()->route('karyawans.index')
            ->with('success', 'Karyawan berhasil diupdate');
    }

    public function destroy(Karyawan $karyawan)
    {
        // Delete associated files
        if ($karyawan->foto_karyawan) {
            Storage::delete('public/karyawan/foto/' . $karyawan->foto_karyawan);
        }

        if ($karyawan->upload_ktp) {
            Storage::delete('public/karyawan/ktp/' . $karyawan->upload_ktp);
        }

        $karyawan->delete();

        return redirect()->route('karyawans.index')
            ->with('success', 'Karyawan berhasil dihapus');
    }

    /**
     * Generate a new unique NIK Karyawan with format YYYYMM###
     * If there's a conflict, it will find the next available number
     */
    protected function generateNikKaryawan()
    {
        // Get current year and month
        $prefix = Carbon::now()->format('Ym'); // Format: YYYYMM

        try {
            // Start a database transaction to ensure uniqueness
            return DB::transaction(function () use ($prefix) {
                // Find the highest sequence number for the current year/month
                $lastSequence = Karyawan::where('nik_karyawan', 'like', $prefix . '%')
                    ->orderByRaw('LENGTH(nik_karyawan) DESC, nik_karyawan DESC')
                    ->value('nik_karyawan');

                if ($lastSequence) {
                    // Extract the sequence part (last 3 digits)
                    $sequence = (int) substr($lastSequence, -3);
                    $sequence++;
                } else {
                    $sequence = 1;
                }

                // Find next available sequence by checking if numbers are taken
                $isUnique = false;
                while (!$isUnique) {
                    $newNik = $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);

                    // Check if this NIK already exists
                    $exists = Karyawan::where('nik_karyawan', $newNik)->exists();

                    if (!$exists) {
                        $isUnique = true;
                    } else {
                        $sequence++;
                    }
                }

                return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            });
        } catch (\Exception $e) {
            // In case of error, return a fallback
            return $prefix . '001';
        }
    }

    /**
     * API endpoint to get a new NIK Karyawan
     */
    public function getNikKaryawan()
    {
        $nikKaryawan = $this->generateNikKaryawan();
        return response()->json(['nik_karyawan' => $nikKaryawan]);
    }
}
