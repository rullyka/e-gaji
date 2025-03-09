<?php

namespace App\Models;

use App\Models\Bagian;
use App\Models\Absensi;
use App\Models\Jabatan;
use App\Models\Profesi;
use App\Models\Departemen;
use App\Models\Uangtunggu;
use App\Models\ProgramStudi;
use App\Models\MesinAbsensi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karyawan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nik_karyawan',
        'nama_karyawan',
        'foto_karyawan',
        'statuskaryawan',
        'id_departemen',
        'id_bagian',
        'tgl_awalmmasuk',
        'tahun_keluar',
        'id_jabatan',
        'id_profesi',
        'nik',
        'kk',
        'statuskawin',
        'pendidikan_terakhir',
        'id_programstudi',
        'no_hp',
        'ortu_bapak',
        'ortu_ibu',
        'ukuran_kemeja',
        'ukuran_celana',
        'ukuran_sepatu',
        'jml_anggotakk',
        'upload_ktp',
    ];

    protected $casts = [
        'tgl_awalmmasuk' => 'date',
        'tahun_keluar' => 'date',
    ];

    /**
     * Get the departemen that owns the karyawan.
     */
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }

    /**
     * Get the bagian that owns the karyawan.
     */
    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'id_bagian');
    }

    /**
     * Get the jabatan that owns the karyawan.
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }

    /**
     * Get the profesi that owns the karyawan.
     */
    public function profesi()
    {
        return $this->belongsTo(Profesi::class, 'id_profesi');
    }

    /**
     * Get the program studi that owns the karyawan.
     */
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_programstudi');
    }

    /**
     * Get the full path to the employee's photo
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto_karyawan) {
            return asset('storage/karyawan/foto/' . $this->foto_karyawan);
        }
        return asset('images/default-avatar.png');
    }

    /**
     * Get the full path to the employee's KTP scan
     */
    public function getKtpUrlAttribute()
    {
        if ($this->upload_ktp) {
            return asset('storage/karyawan/ktp/' . $this->upload_ktp);
        }
        return null;
    }

    /**
     * Get the absensis for the karyawan.
     */
    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'karyawan_id');
    }

    /**
     * Get the uang tunggus for the karyawan.
     */
    public function uangTunggus()
    {
        return $this->hasMany(Uangtunggu::class, 'karyawan_id');
    }

    /**
     * Get all mesin absensi where this employee is registered
     * This is done through the karyawan_mesinabsensi pivot table
     */
    public function mesinAbsensis()
    {
        return $this->belongsToMany(MesinAbsensi::class, 'karyawan_mesinabsensi', 'karyawan_id', 'mesinabsensi_id')
                    ->withPivot('status_sync', 'sync_at')
                    ->withTimestamps();
    }

    /**
     * Check if the employee is registered in the specific attendance machine
     *
     * @param MesinAbsensi|int $mesinAbsensi
     * @return bool
     */
    public function isRegisteredInMachine($mesinAbsensi)
    {
        $mesinAbsensiId = $mesinAbsensi instanceof MesinAbsensi ? $mesinAbsensi->id : $mesinAbsensi;
        return $this->mesinAbsensis()->where('mesinabsensi_id', $mesinAbsensiId)->exists();
    }

    /**
     * Register employee to an attendance machine
     *
     * @param MesinAbsensi|int $mesinAbsensi
     * @param bool $syncSuccess Status of synchronization (success/fail)
     * @return bool
     */
    public function registerToMachine($mesinAbsensi, $syncSuccess = true)
    {
        $mesinAbsensiId = $mesinAbsensi instanceof MesinAbsensi ? $mesinAbsensi->id : $mesinAbsensi;

        if ($this->isRegisteredInMachine($mesinAbsensiId)) {
            // Update sync status if already registered
            return $this->mesinAbsensis()->updateExistingPivot($mesinAbsensiId, [
                'status_sync' => $syncSuccess,
                'sync_at' => now()
            ]);
        } else {
            // Create new registration
            return $this->mesinAbsensis()->attach($mesinAbsensiId, [
                'status_sync' => $syncSuccess,
                'sync_at' => now()
            ]);
        }
    }

    /**
     * Unregister employee from an attendance machine
     *
     * @param MesinAbsensi|int $mesinAbsensi
     * @return int
     */
    public function unregisterFromMachine($mesinAbsensi)
    {
        $mesinAbsensiId = $mesinAbsensi instanceof MesinAbsensi ? $mesinAbsensi->id : $mesinAbsensi;
        return $this->mesinAbsensis()->detach($mesinAbsensiId);
    }
}
