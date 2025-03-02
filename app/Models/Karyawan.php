<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
}
