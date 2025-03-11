<?php

namespace App\Models;

use App\Models\Bagian;
use App\Models\Absensi;
use App\Models\Jabatan;
use App\Models\Profesi;
use App\Models\Departemen;
use App\Models\Uangtunggu;
use App\Models\Penggajian;
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
        'status', // aktif, nonaktif, cuti
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
     * Get the penggajians for the karyawan.
     */
    public function penggajians()
    {
        return $this->hasMany(Penggajian::class, 'id_karyawan', 'id');
    }

    /**
     * Get tunjangans for this karyawan
     */
    public function tunjangans()
    {
        // Implementasi sesuai dengan struktur database
        // Jika menggunakan model Tunjangan, maka:
        // return $this->hasMany(Tunjangan::class, 'karyawan_id', 'id');

        // Jika tidak ada model Tunjangan, maka bisa dibuat collection dari data jabatan/profesi
        return collect([
            [
                'nama' => 'Tunjangan Jabatan',
                'nominal' => $this->jabatan ? $this->jabatan->tunjangan_jabatan : 0
            ],
            [
                'nama' => 'Tunjangan Profesi',
                'nominal' => $this->profesi ? $this->profesi->tunjangan_profesi : 0
            ]
        ])->filter(function ($item) {
            return $item['nominal'] > 0;
        });
    }

    /**
     * Check if karyawan has payroll data in the specified period
     */
    public function hasPayrollInPeriod($periodeId)
    {
        return $this->penggajians()
            ->where('id_periode', $periodeId)
            ->exists();
    }

    /**
     * Calculate total salary amount (summing all periods)
     */
    public function getTotalSalaryAmount()
    {
        return $this->penggajians()->sum('gaji_bersih');
    }

    /**
     * Get the full name
     */
    public function getNamaAttribute()
    {
        return $this->nama_karyawan;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'aktif':
                return 'badge-success';
            case 'nonaktif':
                return 'badge-danger';
            case 'cuti':
                return 'badge-warning';
            default:
                return 'badge-secondary';
        }
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

    // Scope untuk get karyawan dengan status aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    // Scope untuk get karyawan dengan status nonaktif
    public function scopeInactive($query)
    {
        return $query->where('status', 'nonaktif');
    }

    // Scope untuk get karyawan dengan status cuti
    public function scopeOnLeave($query)
    {
        return $query->where('status', 'cuti');
    }

    // Scope untuk get karyawan dari departemen tertentu
    public function scopeByDepartment($query, $departemenId)
    {
        return $query->where('id_departemen', $departemenId);
    }
}