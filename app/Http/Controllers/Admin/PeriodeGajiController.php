<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PeriodeGajiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $periodeGajis = PeriodeGaji::orderBy('tanggal_mulai', 'asc')->get();
        return view('admin.periodegajis.index', compact('periodeGajis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.periodegajis.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_periode' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // If this period is set as active, deactivate all others
        if ($request->status == 'aktif') {
            PeriodeGaji::where('status', 'aktif')->update(['status' => 'nonaktif']);
        }

        PeriodeGaji::create($request->all());

        return redirect()->route('periodegaji.index')
            ->with('success', 'Periode Gaji berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(PeriodeGaji $periodegaji)
    {
        return view('admin.periodegajis.show', compact('periodegaji'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PeriodeGaji $periodegaji)
    {
        return view('admin.periodegajis.edit', compact('periodegaji'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PeriodeGaji $periodegaji)
    {
        $validator = Validator::make($request->all(), [
            'nama_periode' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,nonaktif',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // If this period is set as active, deactivate all others
        if ($request->status == 'aktif' && $periodegaji->status != 'aktif') {
            PeriodeGaji::where('status', 'aktif')->update(['status' => 'nonaktif']);
        }

        $periodegaji->update($request->all());

        return redirect()->route('periodegaji.index')
            ->with('success', 'Periode Gaji berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PeriodeGaji $periodegaji)
    {
        // Check if the period is being used for payroll
        if ($periodegaji->hasPayrollData()) {
            return redirect()->route('periodegaji.index')
                ->with('error', 'Periode Gaji tidak dapat dihapus karena sedang digunakan pada data penggajian');
        }

        // Don't allow deleting active period
        if ($periodegaji->status == 'aktif') {
            return redirect()->route('periodegaji.index')
                ->with('error', 'Periode Gaji aktif tidak dapat dihapus. Silakan nonaktifkan terlebih dahulu.');
        }

        $periodegaji->delete();

        return redirect()->route('periodegaji.index')
            ->with('success', 'Periode Gaji berhasil dihapus');
    }

    /**
     * Set a period as active
     */
    public function setActive(PeriodeGaji $periodegaji)
    {
        $periodegaji->setAsActive();

        return redirect()->route('periodegaji.index')
            ->with('success', 'Periode Gaji berhasil diaktifkan');
    }

    /**
     * Generate monthly periods for a year
     */
    public function generateMonthly(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:2100',
            'start_day' => 'required|integer|min:1|max:28',
            'end_day' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $year = $request->year;
        $startDay = $request->start_day;
        $endDay = $request->end_day;
        $count = 0;

        for ($month = 1; $month <= 12; $month++) {
            // Create start date with the specified day
            $startDate = Carbon::createFromDate($year, $month, $startDay);

            // Create end date based on selection
            if ($endDay === 'end_of_month') {
                $endDate = $startDate->copy()->endOfMonth();
            } else {
                // If the selected end day is less than the start day, move to next month
                if ((int)$endDay < $startDay) {
                    $endMonth = $month < 12 ? $month + 1 : 1;
                    $endYear = $month < 12 ? $year : $year + 1;
                    $endDate = Carbon::createFromDate($endYear, $endMonth, (int)$endDay);
                } else {
                    $endDate = Carbon::createFromDate($year, $month, (int)$endDay);
                }
            }

            $periodeName = 'Gaji Bulanan ' . $startDate->format('F Y');

            // Check if period already exists
            $exists = PeriodeGaji::where('nama_periode', $periodeName)->exists();

            if (!$exists) {
                PeriodeGaji::create([
                    'nama_periode' => $periodeName,
                    'tanggal_mulai' => $startDate->format('Y-m-d'),
                    'tanggal_selesai' => $endDate->format('Y-m-d'),
                    'status' => 'nonaktif',
                    'keterangan' => 'Periode gaji bulanan yang dibuat otomatis',
                ]);
                $count++;
            }
        }

        return redirect()->route('periodegaji.index')
            ->with('success', "Berhasil membuat $count periode gaji bulanan untuk tahun $year");
    }

    /**
     * Generate weekly periods for a month
     */
    public function generateWeekly(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'start_day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $year = $request->year;
        $month = $request->month;
        $startDayOfWeek = $request->start_day_of_week;
        $count = 0;

        // Get the first day of the month
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1);

        // Find the first occurrence of the selected day of week
        $currentDate = $firstDayOfMonth->copy();
        while (strtolower($currentDate->englishDayOfWeek) !== $startDayOfWeek) {
            $currentDate->addDay();
        }

        // Generate weekly periods until we're past the end of the month
        while (
            $currentDate->month == $month ||
            ($currentDate->month != $month && $currentDate->copy()->addDays(6)->month == $month)
        ) {

            $startDate = $currentDate->copy();
            $endDate = $startDate->copy()->addDays(6); // 7 days period (inclusive of start day)

            $periodeName = 'Gaji Mingguan ' . $startDate->format('d') . ' - ' . $endDate->format('d M Y');

            // Check if period already exists
            $exists = PeriodeGaji::where('nama_periode', $periodeName)->exists();

            if (!$exists) {
                PeriodeGaji::create([
                    'nama_periode' => $periodeName,
                    'tanggal_mulai' => $startDate->format('Y-m-d'),
                    'tanggal_selesai' => $endDate->format('Y-m-d'),
                    'status' => 'nonaktif',
                    'keterangan' => 'Periode gaji mingguan yang dibuat otomatis',
                ]);
                $count++;
            }

            // Move to next week
            $currentDate->addDays(7);
        }

        return redirect()->route('periodegaji.index')
            ->with('success', "Berhasil membuat $count periode gaji mingguan untuk bulan " . $firstDayOfMonth->format('F Y'));
    }

    /**
     * Delete multiple periods at once
     */
    public function deleteMultiple(Request $request)
    {
        $ids = $request->ids;

        if (empty($ids)) {
            return redirect()->route('periodegaji.index')
                ->with('error', 'Tidak ada periode yang dipilih');
        }

        // Check if any selected period is active
        $hasActive = PeriodeGaji::whereIn('id', $ids)
            ->where('status', 'aktif')
            ->exists();

        if ($hasActive) {
            return redirect()->route('periodegaji.index')
                ->with('error', 'Periode Gaji aktif tidak dapat dihapus. Silakan nonaktifkan terlebih dahulu.');
        }

        // Check if any selected period has payroll data
        $hasPayrollData = false;
        foreach ($ids as $id) {
            $periode = PeriodeGaji::find($id);
            if ($periode && $periode->hasPayrollData()) {
                $hasPayrollData = true;
                break;
            }
        }

        if ($hasPayrollData) {
            return redirect()->route('periodegaji.index')
                ->with('error', 'Beberapa periode tidak dapat dihapus karena sedang digunakan pada data penggajian');
        }

        // Delete the selected periods
        PeriodeGaji::whereIn('id', $ids)->delete();

        return redirect()->route('periodegaji.index')
            ->with('success', 'Periode Gaji yang dipilih berhasil dihapus');
    }
}
