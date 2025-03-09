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
    public function index()
    {
        $lemburs = Lembur::with(['karyawan', 'supervisor', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.lemburs.index', compact('lemburs'));
    }

    public function create()
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $jenisLembur = ['Hari Kerja', 'Hari Libur'];

        return view('admin.lemburs.create', compact('karyawans', 'jenisLembur'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'jenis_lembur' => 'required|in:Hari Kerja,Hari Libur',
            'tanggal_lembur' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:karyawans,id',
        ]);

        // Calculate duration
        $startTime = Carbon::parse($request->jam_mulai);
        $endTime = Carbon::parse($request->jam_selesai);

        // If end time is less than start time, assume it's the next day
        if ($endTime < $startTime) {
            $endTime->addDay();
        }

        $durationInHours = $endTime->diffInHours($startTime);
        $durationInMinutes = $endTime->diffInMinutes($startTime) % 60;
        $totalDuration = $durationInHours . ' jam ' . $durationInMinutes . ' menit';

        $data = $request->all();
        $data['total_lembur'] = $totalDuration;
        $data['status'] = 'Menunggu Persetujuan';

        Lembur::create($data);

        return redirect()->route('lemburs.index')
            ->with('success', 'Pengajuan Lembur berhasil ditambahkan');
    }

    public function show(Lembur $lembur)
    {
        $lembur->load(['karyawan', 'supervisor', 'approver']);
        return view('admin.lemburs.show', compact('lembur'));
    }

    public function edit(Lembur $lembur)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $jenisLembur = ['Hari Kerja', 'Hari Libur'];

        $lembur->load(['karyawan', 'supervisor', 'approver']);

        return view('admin.lemburs.edit', compact('lembur', 'karyawans', 'jenisLembur'));
    }

    public function update(Request $request, Lembur $lembur)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'jenis_lembur' => 'required|in:Hari Kerja,Hari Libur',
            'tanggal_lembur' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'keterangan' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:karyawans,id',
        ]);

        // Only allow editing if the status is still "Menunggu Persetujuan"
        if ($lembur->status != 'Menunggu Persetujuan') {
            return redirect()->route('lemburs.index')
                ->with('error', 'Tidak dapat mengedit pengajuan yang sudah diproses');
        }

        // Calculate duration
        $startTime = Carbon::parse($request->jam_mulai);
        $endTime = Carbon::parse($request->jam_selesai);

        // If end time is less than start time, assume it's the next day
        if ($endTime < $startTime) {
            $endTime->addDay();
        }

        $durationInHours = $endTime->diffInHours($startTime);
        $durationInMinutes = $endTime->diffInMinutes($startTime) % 60;
        $totalDuration = $durationInHours . ' jam ' . $durationInMinutes . ' menit';

        $data = $request->all();
        $data['total_lembur'] = $totalDuration;

        $lembur->update($data);

        return redirect()->route('lemburs.index')
            ->with('success', 'Pengajuan Lembur berhasil diupdate');
    }

    public function destroy(Lembur $lembur)
    {
        // Only allow deleting if the status is still "Menunggu Persetujuan"
        if ($lembur->status != 'Menunggu Persetujuan') {
            return redirect()->route('lemburs.index')
                ->with('error', 'Tidak dapat menghapus pengajuan yang sudah diproses');
        }

        $lembur->delete();

        return redirect()->route('lemburs.index')
            ->with('success', 'Pengajuan Lembur berhasil dihapus');
    }

    public function approve(Request $request, Lembur $lembur)
    {
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak',
            'keterangan_tolak' => 'required_if:status,Ditolak',
            'lembur_disetujui' => 'required_if:status,Disetujui',
        ]);

        // Update approval details
        $lembur->status = $request->status;
        $lembur->keterangan_tolak = $request->status == 'Ditolak' ? $request->keterangan_tolak : null;
        $lembur->lembur_disetujui = $request->status == 'Disetujui' ? $request->lembur_disetujui : null;
        $lembur->tanggal_approval = now();
        $lembur->approved_by = Auth::id();
        $lembur->save();

        return redirect()->route('lemburs.index')
            ->with('success', 'Pengajuan Lembur berhasil ' . strtolower($request->status));
    }

    public function approvalForm(Lembur $lembur)
    {
        $lembur->load(['karyawan', 'supervisor']);
        return view('admin.lemburs.approval', compact('lembur'));
    }
}