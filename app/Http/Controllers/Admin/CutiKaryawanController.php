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
    public function index()
    {
        $cutiKaryawans = CutiKaryawan::with(['karyawan', 'masterCuti', 'supervisor', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.cuti_karyawans.index', compact('cutiKaryawans'));
    }

    public function create()
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $masterCutis = Mastercuti::where('cuti_max', '!=', null)->orderBy('uraian')->get();
        $jenisCuti = ['Izin', 'Cuti'];

        return view('admin.cuti_karyawans.create', compact('karyawans', 'masterCutis', 'jenisCuti'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id',
            'jenis_cuti' => 'required|in:Izin,Cuti',
            'tanggal_mulai_cuti' => 'required|date',
            'tanggal_akhir_cuti' => 'required|date|after_or_equal:tanggal_mulai_cuti',
            'master_cuti_id' => 'nullable|exists:Mastercutis,id',
            'bukti' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'id_supervisor' => 'required|exists:karyawans,id',
        ]);

        // Calculate number of days
        $startDate = Carbon::parse($request->tanggal_mulai_cuti);
        $endDate = Carbon::parse($request->tanggal_akhir_cuti);
        $jumlahHari = $startDate->diffInDays($endDate) + 1; // Including both start and end days

        $data = $request->all();
        $data['jumlah_hari_cuti'] = $jumlahHari;
        $data['status_acc'] = 'Menunggu Persetujuan';

        // Handle bukti upload
        if ($request->hasFile('bukti')) {
            $bukti = $request->file('bukti');
            $buktiName = 'cuti-' . Str::slug($request->jenis_cuti) . '-' . time() . '.' . $bukti->getClientOriginalExtension();
            $bukti->storeAs('public/cuti/bukti', $buktiName);
            $data['bukti'] = $buktiName;
        }

        CutiKaryawan::create($data);

        return redirect()->route('cuti_karyawans.index')
            ->with('success', 'Pengajuan Cuti berhasil ditambahkan');
    }

    public function show(CutiKaryawan $cutiKaryawan)
    {
        $cutiKaryawan->load(['karyawan', 'masterCuti', 'supervisor', 'approver']);
        return view('admin.cuti_karyawans.show', compact('cutiKaryawan'));
    }

    public function edit(CutiKaryawan $cutiKaryawan)
    {
        $karyawans = Karyawan::orderBy('nama_karyawan')->get();
        $masterCutis = Mastercuti::where('cuti_max', '!=', null)->orderBy('uraian')->get();
        $jenisCuti = ['Izin', 'Cuti'];

        $cutiKaryawan->load(['karyawan', 'masterCuti', 'supervisor', 'approver']);

        return view('admin.cuti_karyawans.edit', compact('cutiKaryawan', 'karyawans', 'masterCutis', 'jenisCuti'));
    }

    public function update(Request $request, CutiKaryawan $cutiKaryawan)
    {
        $request->validate([
            'id_karyawan' => 'required|exists:karyawans,id',
            'jenis_cuti' => 'required|in:Izin,Cuti',
            'tanggal_mulai_cuti' => 'required|date',
            'tanggal_akhir_cuti' => 'required|date|after_or_equal:tanggal_mulai_cuti',
            'master_cuti_id' => 'nullable|exists:Mastercutis,id',
            'bukti' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'id_supervisor' => 'required|exists:karyawans,id',
        ]);

        // Calculate number of days
        $startDate = Carbon::parse($request->tanggal_mulai_cuti);
        $endDate = Carbon::parse($request->tanggal_akhir_cuti);
        $jumlahHari = $startDate->diffInDays($endDate) + 1; // Including both start and end days

        $data = $request->all();
        $data['jumlah_hari_cuti'] = $jumlahHari;

        // Only allow editing if the status is still "Menunggu Persetujuan"
        if ($cutiKaryawan->status_acc != 'Menunggu Persetujuan') {
            return redirect()->route('cuti_karyawans.index')
                ->with('error', 'Tidak dapat mengedit pengajuan yang sudah diproses');
        }

        // Handle bukti upload
        if ($request->hasFile('bukti')) {
            // Delete old bukti if exists
            if ($cutiKaryawan->bukti) {
                Storage::delete('public/cuti/bukti/' . $cutiKaryawan->bukti);
            }

            $bukti = $request->file('bukti');
            $buktiName = 'cuti-' . Str::slug($request->jenis_cuti) . '-' . time() . '.' . $bukti->getClientOriginalExtension();
            $bukti->storeAs('public/cuti/bukti', $buktiName);
            $data['bukti'] = $buktiName;
        }

        $cutiKaryawan->update($data);

        return redirect()->route('cuti_karyawans.index')
            ->with('success', 'Pengajuan Cuti berhasil diupdate');
    }

    public function destroy(CutiKaryawan $cutiKaryawan)
    {
        // Only allow deleting if the status is still "Menunggu Persetujuan"
        if ($cutiKaryawan->status_acc != 'Menunggu Persetujuan') {
            return redirect()->route('cuti_karyawans.index')
                ->with('error', 'Tidak dapat menghapus pengajuan yang sudah diproses');
        }

        // Delete the bukti file
        if ($cutiKaryawan->bukti) {
            Storage::delete('public/cuti/bukti/' . $cutiKaryawan->bukti);
        }

        $cutiKaryawan->delete();

        return redirect()->route('cuti_karyawans.index')
            ->with('success', 'Pengajuan Cuti berhasil dihapus');
    }

    public function approve(Request $request, CutiKaryawan $cutiKaryawan)
    {
        $request->validate([
            'status_acc' => 'required|in:Disetujui,Ditolak',
            'keterangan_tolak' => 'required_if:status_acc,Ditolak',
            'cuti_disetujui' => 'required_if:status_acc,Disetujui',
        ]);

        // Update approval details
        $cutiKaryawan->status_acc = $request->status_acc;
        $cutiKaryawan->keterangan_tolak = $request->status_acc == 'Ditolak' ? $request->keterangan_tolak : null;
        $cutiKaryawan->cuti_disetujui = $request->status_acc == 'Disetujui' ? $request->cuti_disetujui : null;
        $cutiKaryawan->tanggal_approval = now();
        $cutiKaryawan->approved_by = Auth::id();
        $cutiKaryawan->save();

        return redirect()->route('cuti_karyawans.index')
            ->with('success', 'Pengajuan Cuti berhasil ' . strtolower($request->status_acc));
    }

    public function approvalForm(CutiKaryawan $cutiKaryawan)
    {
        $cutiKaryawan->load(['karyawan', 'masterCuti', 'supervisor']);
        return view('admin.cuti_karyawans.approval', compact('cutiKaryawan'));
    }
}