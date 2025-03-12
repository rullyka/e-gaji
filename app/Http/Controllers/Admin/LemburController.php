<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Lembur;
use App\Models\Karyawan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LemburController extends Controller
{
    /**
     * Menampilkan daftar semua pengajuan lembur
     */
    public function index()
    {
        // Ambil semua data lembur dengan relasi terkait dan urutkan berdasarkan tanggal dibuat
        $lemburs = Lembur::with(['karyawan', 'supervisor', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.lemburs.index', compact('lemburs'));
    }

    /**
     * Menampilkan form untuk membuat pengajuan lembur baru
     */
    public function create()
    {
        // Siapkan data untuk dropdown di form
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $jenisLembur = ['Hari Kerja', 'Hari Libur'];

        return view('admin.lemburs.create', compact('karyawans', 'jenisLembur'));
    }

    /**
     * Menyimpan data pengajuan lembur baru ke database
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'jenis_lembur' => 'required|in:Hari Kerja,Hari Libur',
            'tanggal_lembur' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:karyawans,id',
        ]);

        // Hitung durasi lembur
        $startTime = Carbon::parse($request->jam_mulai);
        $endTime = Carbon::parse($request->jam_selesai);

        // Jika waktu selesai lebih kecil dari waktu mulai, diasumsikan hari berikutnya
        if ($endTime < $startTime) {
            $endTime->addDay();
        }

        // Format durasi dalam jam dan menit
        $durationInHours = $endTime->diffInHours($startTime);
        $durationInMinutes = $endTime->diffInMinutes($startTime) % 60;
        $totalDuration = $durationInHours . ' jam ' . $durationInMinutes . ' menit';

        // Siapkan data untuk disimpan
        $data = $request->all();
        $data['total_lembur'] = $totalDuration;
        $data['status'] = 'Menunggu Persetujuan';

        // Simpan data lembur ke database
        Lembur::create($data);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('lemburs.index')
            ->with('success', 'Pengajuan Lembur berhasil ditambahkan');
    }

    /**
     * Menampilkan detail pengajuan lembur tertentu
     */
    public function show(Lembur $lembur)
    {
        // Muat relasi yang dibutuhkan untuk detail lembur
        $lembur->load(['karyawan', 'supervisor', 'approver']);
        return view('admin.lemburs.show', compact('lembur'));
    }

    /**
     * Menampilkan form untuk mengedit pengajuan lembur
     */
    public function edit(Lembur $lembur)
    {
        // Siapkan data untuk dropdown di form edit
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $jenisLembur = ['Hari Kerja', 'Hari Libur'];

        // Muat relasi yang dibutuhkan
        $lembur->load(['karyawan', 'supervisor', 'approver']);

        return view('admin.lemburs.edit', compact('lembur', 'karyawans', 'jenisLembur'));
    }

    /**
     * Memperbarui data pengajuan lembur di database
     */
    public function update(Request $request, Lembur $lembur)
    {
        // Validasi input dari form edit
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'jenis_lembur' => 'required|in:Hari Kerja,Hari Libur',
            'tanggal_lembur' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:karyawans,id',
        ]);

        // Hanya boleh mengedit jika status masih "Menunggu Persetujuan"
        if ($lembur->status != 'Menunggu Persetujuan') {
            return redirect()->route('lemburs.index')
                ->with('error', 'Tidak dapat mengedit pengajuan yang sudah diproses');
        }

        // Hitung durasi lembur
        $startTime = Carbon::parse($request->jam_mulai);
        $endTime = Carbon::parse($request->jam_selesai);

        // Jika waktu selesai lebih kecil dari waktu mulai, diasumsikan hari berikutnya
        if ($endTime < $startTime) {
            $endTime->addDay();
        }

        // Format durasi dalam jam dan menit
        $durationInHours = $endTime->diffInHours($startTime);
        $durationInMinutes = $endTime->diffInMinutes($startTime) % 60;
        $totalDuration = $durationInHours . ' jam ' . $durationInMinutes . ' menit';

        // Siapkan data untuk diupdate
        $data = $request->all();
        $data['total_lembur'] = $totalDuration;

        // Update data lembur di database
        $lembur->update($data);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('lemburs.index')
            ->with('success', 'Pengajuan Lembur berhasil diupdate');
    }

    /**
     * Menghapus data pengajuan lembur dari database
     */
    public function destroy(Lembur $lembur)
    {
        // Hanya boleh menghapus jika status masih "Menunggu Persetujuan"
        if ($lembur->status != 'Menunggu Persetujuan') {
            return redirect()->route('lemburs.index')
                ->with('error', 'Tidak dapat menghapus pengajuan yang sudah diproses');
        }

        // Hapus data lembur
        $lembur->delete();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('lemburs.index')
            ->with('success', 'Pengajuan Lembur berhasil dihapus');
    }

    /**
     * Memproses persetujuan atau penolakan pengajuan lembur
     */
    public function approve(Request $request, Lembur $lembur)
    {
        // Validasi input dari form approval
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak',
            'keterangan_tolak' => 'required_if:status,Ditolak',
            'lembur_disetujui' => 'required_if:status,Disetujui',
        ]);

        // Update detail persetujuan
        $lembur->status = $request->status;
        $lembur->keterangan_tolak = $request->status == 'Ditolak' ? $request->keterangan_tolak : null;
        $lembur->lembur_disetujui = $request->status == 'Disetujui' ? $request->lembur_disetujui : null;
        $lembur->tanggal_approval = now();
        $lembur->approved_by = Auth::id();
        $lembur->save();

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('lemburs.index')
            ->with('success', 'Pengajuan Lembur berhasil ' . strtolower($request->status));
    }

    /**
     * Menampilkan form untuk persetujuan lembur
     */
    public function approvalForm(Lembur $lembur)
    {
        // Muat relasi yang dibutuhkan untuk form approval
        $lembur->load(['karyawan', 'supervisor']);
        return view('admin.lemburs.approval', compact('lembur'));
    }
}