<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Harilibur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HariliburController extends Controller
{
    public function index()
    {
        $hariliburs = Harilibur::orderBy('tanggal', 'desc')->get();
        return view('admin.hariliburs.index', compact('hariliburs'));
    }

    public function create()
    {
        return view('admin.hariliburs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hariliburs,tanggal',
            'nama_libur' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        Harilibur::create($request->all());

        return redirect()->route('hariliburs.index')
            ->with('success', 'Hari Libur berhasil ditambahkan');
    }

    public function show(Harilibur $harilibur)
    {
        return view('admin.hariliburs.show', compact('harilibur'));
    }

    public function edit(Harilibur $harilibur)
    {
        return view('admin.hariliburs.edit', compact('harilibur'));
    }

    public function update(Request $request, Harilibur $harilibur)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:hariliburs,tanggal,'.$harilibur->id,
            'nama_libur' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $harilibur->update($request->all());

        return redirect()->route('hariliburs.index')
            ->with('success', 'Hari Libur berhasil diupdate');
    }

    public function destroy(Harilibur $harilibur)
    {
        $harilibur->delete();
        return redirect()->route('hariliburs.index')
            ->with('success', 'Hari Libur berhasil dihapus');
    }

    /**
     * Show form to generate Sundays for a specific year
     */
    public function generateSundaysForm()
    {
        $currentYear = date('Y');
        $yearOptions = [];

        // Generate year options (current year and next 5 years)
        for ($i = 0; $i < 6; $i++) {
            $year = $currentYear + $i;
            $yearOptions[$year] = $year;
        }

        return view('admin.hariliburs.generate_sundays', compact('yearOptions', 'currentYear'));
    }

    /**
     * Generate all Sundays for the specified year
     */
    public function generateSundays(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2099',
            'replace_existing' => 'nullable|boolean',
        ]);

        $year = $request->input('year');
        $replaceExisting = $request->has('replace_existing');

        try {
            DB::beginTransaction();

            // If requested to replace existing Sunday entries for the year
            if ($replaceExisting) {
                // Delete all Sunday holidays for the selected year
                Harilibur::where('nama_libur', 'Hari Minggu')
                    ->whereYear('tanggal', $year)
                    ->delete();
            }

            // Find all Sundays for the given year
            $startDate = Carbon::createFromDate($year, 1, 1);
            $endDate = Carbon::createFromDate($year, 12, 31);

            $sundays = [];
            $date = $startDate->copy()->startOfWeek(Carbon::SUNDAY);

            // If the first Sunday is before the start of the year, add 7 days
            if ($date->lt($startDate)) {
                $date->addWeek();
            }

            // Create all Sundays for the year
            while ($date->lte($endDate)) {
                $sundays[] = [
                    'tanggal' => $date->format('Y-m-d'),
                    'nama_libur' => 'Hari Minggu',
                    'keterangan' => 'Hari Minggu - Generated Automatically',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $date->addWeek();
            }

            // Insert all sundays that don't exist yet
            foreach ($sundays as $sunday) {
                // Skip if the date already exists and we're not replacing
                if (!$replaceExisting && Harilibur::where('tanggal', $sunday['tanggal'])->exists()) {
                    continue;
                }

                Harilibur::create($sunday);
            }

            DB::commit();

            return redirect()->route('hariliburs.index')
                ->with('success', count($sundays) . ' Hari Minggu untuk tahun ' . $year . ' berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('hariliburs.index')
                ->with('error', 'Gagal membuat Hari Minggu: ' . $e->getMessage());
        }
    }
}