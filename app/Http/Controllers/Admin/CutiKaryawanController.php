<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Karyawan;
use App\Models\Mastercuti;
use Illuminate\Support\Str;
use App\Models\CutiKaryawan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CutiKaryawanController extends Controller
{
    /**
     * Menampilkan daftar semua pengajuan cuti karyawan
     */
    public function index()
    {
        // Ambil semua data cuti dengan relasi terkait dan urutkan dari yang terbaru
        $cutiKaryawans = CutiKaryawan::with(['karyawan', 'masterCuti', 'supervisor', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.cuti_karyawans.index', compact('cutiKaryawans'));
    }

    /**
     * Menampilkan form untuk membuat pengajuan cuti baru
     */
    public function create()
    {
        // Siapkan data untuk dropdown di form
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $masterCutis = Mastercuti::where('cuti_max', '!=', null)->orderBy('uraian')->get();
        $jenisCuti = ['Izin', 'Cuti'];

        return view('admin.cuti_karyawans.create', compact('karyawans', 'masterCutis', 'jenisCuti'));
    }

    /**
     * Menyimpan data pengajuan cuti baru ke database
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id',
            'jenis_cuti' => 'required|in:Izin,Cuti',
            'tanggal_mulai_cuti' => 'required|date',
            'tanggal_akhir_cuti' => 'required|date|after_or_equal:tanggal_mulai_cuti',
            'master_cuti_id' => 'nullable|exists:Mastercutis,id',
            'bukti' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'id_supervisor' => 'required|exists:karyawans,id',
        ]);

        // Hitung jumlah hari cuti
        $startDate = Carbon::parse($request->tanggal_mulai_cuti);
        $endDate = Carbon::parse($request->tanggal_akhir_cuti);
        $jumlahHari = $startDate->diffInDays($endDate) + 1; // Termasuk hari awal dan akhir

        $data = $request->all();
        $data['jumlah_hari_cuti'] = $jumlahHari;
        $data['status_acc'] = 'Menunggu Persetujuan';

        // Proses upload file bukti
        if ($request->hasFile('bukti')) {
            $bukti = $request->file('bukti');
            $buktiName = 'cuti-' . Str::slug($request->jenis_cuti) . '-' . time() . '.' . $bukti->getClientOriginalExtension();
            $bukti->storeAs('public/cuti/bukti', $buktiName);
            $data['bukti'] = $buktiName;
        }

        // Simpan data cuti ke database
        CutiKaryawan::create($data);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('cuti_karyawans.index')
            ->with('success', 'Pengajuan Cuti berhasil ditambahkan');
    }

    /**
     * Menampilkan detail pengajuan cuti tertentu
     */
    public function show(CutiKaryawan $cutiKaryawan)
    {
        // Muat relasi yang dibutuhkan untuk detail cuti
        $cutiKaryawan->load(['karyawan', 'masterCuti', 'supervisor', 'approver']);
        return view('admin.cuti_karyawans.show', compact('cutiKaryawan'));
    }

    /**
     * Menampilkan form untuk mengedit pengajuan cuti
     */
    public function edit(CutiKaryawan $cutiKaryawan)
    {
        // Siapkan data untuk dropdown di form edit
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $masterCutis = Mastercuti::where('cuti_max', '!=', null)->orderBy('uraian')->get();
        $jenisCuti = ['Izin', 'Cuti'];

        // Muat relasi yang dibutuhkan
        $cutiKaryawan->load(['karyawan', 'masterCuti', 'supervisor', 'approver']);

        return view('admin.cuti_karyawans.edit', compact('cutiKaryawan', 'karyawans', 'masterCutis', 'jenisCuti'));
    }

    /**
     * Memperbarui data pengajuan cuti di database
     */
    public function update(Request $request, CutiKaryawan $cutiKaryawan)
    {
        // Validasi input dari form edit
        $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id',
            'jenis_cuti' => 'required|in:Izin,Cuti',
            'tanggal_mulai_cuti' => 'required|date',
            'tanggal_akhir_cuti' => 'required|date|after_or_equal:tanggal_mulai_cuti',
            'master_cuti_id' => 'nullable|exists:Mastercutis,id',
            'bukti' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'id_supervisor' => 'required|exists:karyawans,id',
        ]);

        // Hitung jumlah hari cuti
        $startDate = Carbon::parse($request->tanggal_mulai_cuti);
        $endDate = Carbon::parse($request->tanggal_akhir_cuti);
        $jumlahHari = $startDate->diffInDays($endDate) + 1; // Termasuk hari awal dan akhir

        $data = $request->all();
        $data['jumlah_hari_cuti'] = $jumlahHari;

        // Cek apakah pengajuan masih bisa diedit (status masih menunggu)
        if ($cutiKaryawan->status_acc != 'Menunggu Persetujuan') {
            return redirect()->route('cuti_karyawans.index')
                ->with('error', 'Tidak dapat mengedit pengajuan yang sudah diproses');
        }

        // Proses upload file bukti baru jika ada
        if ($request->hasFile('bukti')) {
            // Hapus file bukti lama jika ada
            if ($cutiKaryawan->bukti) {
                Storage::delete('public/cuti/bukti/' . $cutiKaryawan->bukti);
            }

            $bukti = $request->file('bukti');
            $buktiName = 'cuti-' . Str::slug($request->jenis_cuti) . '-' . time() . '.' . $bukti->getClientOriginalExtension();
            $bukti->storeAs('public/cuti/bukti', $buktiName);
            $data['bukti'] = $buktiName;
        }

        // Update data cuti di database
        $cutiKaryawan->update($data);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('cuti_karyawans.index')
            ->with('success', 'Pengajuan Cuti berhasil diupdate');
    }

    /**
     * Menghapus data pengajuan cuti dari database
     */
    public function destroy(CutiKaryawan $cutiKaryawan)
    {
        // Cek apakah pengajuan masih bisa dihapus (status masih menunggu)
        if ($cutiKaryawan->status_acc != 'Menunggu Persetujuan') {
            return redirect()->route('cuti_karyawans.index')
                ->with('error', 'Tidak dapat menghapus pengajuan yang sudah diproses');
        }

        // Hapus file bukti jika ada
        if ($cutiKaryawan->bukti) {
            Storage::delete('public/cuti/bukti/' . $cutiKaryawan->bukti);
        }

        // Hapus data cuti
        $cutiKaryawan->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('cuti_karyawans.index')
            ->with('success', 'Pengajuan Cuti berhasil dihapus');
    }

    /**
     * Memproses persetujuan atau penolakan pengajuan cuti
     */
    public function approve(Request $request, CutiKaryawan $cutiKaryawan)
    {
        // Validasi input dari form approval
        $request->validate([
            'status_acc' => 'required|in:Disetujui,Ditolak',
            'keterangan_tolak' => 'required_if:status_acc,Ditolak',
            'cuti_disetujui' => 'required_if:status_acc,Disetujui',
        ]);
    
        // Get the authenticated user's karyawan record
        $karyawanId = null;
        $user = Auth::user();
        if ($user) {
            // Find the karyawan record associated with the authenticated user
            $karyawan = Karyawan::where('user_id', $user->id)->first();
            if ($karyawan) {
                $karyawanId = $karyawan->id;
            }
        }
    
        // Update detail persetujuan
        $cutiKaryawan->status_acc = $request->status_acc;
        $cutiKaryawan->keterangan_tolak = $request->status_acc == 'Ditolak' ? $request->keterangan_tolak : null;
        $cutiKaryawan->cuti_disetujui = $request->status_acc == 'Disetujui' ? $request->cuti_disetujui : null;
        $cutiKaryawan->tanggal_approval = now();
        $cutiKaryawan->approved_by = $karyawanId; // Use the karyawan ID instead of user ID
        $cutiKaryawan->save();
    
        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('cuti_karyawans.index')
            ->with('success', 'Pengajuan Cuti berhasil ' . strtolower($request->status_acc));
    }

    /**
     * Menampilkan form untuk persetujuan pengajuan cuti
     */
    public function approvalForm(CutiKaryawan $cutiKaryawan)
    {
        // Muat relasi yang dibutuhkan untuk form approval
        $cutiKaryawan->load(['karyawan', 'masterCuti', 'supervisor']);
        return view('admin.cuti_karyawans.approval', compact('cutiKaryawan'));
    }
}
