<?php

namespace App\Models;

use App\Models\Jadwalkerja;
use App\Models\Mesinabsensi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absensi extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jadwalkerja_id',
        'jam_masuk',
        'jam_pulang',
        'total_jam',
        'keterlambatan',
        'pulang_awal',
        'status',
        'jenis_absensi_masuk',
        'mesinabsensi_masuk_id',
        'jenis_absensi_pulang',
        'mesinabsensi_pulang_id',
        'keterangan'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
        'keterlambatan' => 'integer',
        'pulang_awal' => 'integer',
    ];

    /**
     * Get the karyawan associated with the absensi.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Get the jadwal kerja associated with the absensi.
     */
    public function jadwalKerja()
    {
        return $this->belongsTo(Jadwalkerja::class, 'jadwalkerja_id');
    }

    /**
     * Get the mesin absensi for check-in.
     */
    public function mesinAbsensiMasuk()
    {
        return $this->belongsTo(Mesinabsensi::class, 'mesinabsensi_masuk_id');
    }

    /**
     * Get the mesin absensi for check-out.
     */
    public function mesinAbsensiPulang()
    {
        return $this->belongsTo(Mesinabsensi::class, 'mesinabsensi_pulang_id');
    }
}