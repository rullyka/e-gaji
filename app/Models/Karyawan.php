<?php

namespace App\Models;

use App\Enums\KaryawanStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karyawan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'tgl_awalmasuk' => 'date',
        'tahun_keluar' => 'date', // Perbaikan
        'status' => 'string', // Gunakan Enum
    ];

    /**
     * Relasi ke departemen
     */
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }

    /**
     * Relasi ke bagian
     */
    public function bagian()
    {
        return $this->belongsTo(Bagian::class, 'id_bagian');
    }

    /**
     * Relasi ke jabatan
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan');
    }

    /**
     * Relasi ke profesi
     */
    public function profesi()
    {
        return $this->belongsTo(Profesi::class, 'id_profesi');
    }

    /**
     * Relasi ke program studi
     */
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_programstudi');
    }

    /**
     * Relasi ke penggajian
     */
    public function penggajians()
    {
        return $this->hasMany(Penggajian::class, 'id_karyawan');
    }

    /**
     * Relasi ke absensi
     */
    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'karyawan_id');
    }

    /**
     * Relasi ke uang tunggu
     */
    public function uangTunggus()
    {
        return $this->hasMany(Uangtunggu::class, 'karyawan_id');
    }

    /**
     * Relasi ke mesin absensi (pivot table)
     */
    public function mesinAbsensis()
    {
        return $this->belongsToMany(MesinAbsensi::class, 'karyawan_mesinabsensi', 'karyawan_id', 'mesinabsensi_id')
            ->withPivot('status_sync', 'sync_at')
            ->withTimestamps();
    }

    /**
     * Scope karyawan dengan status aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', KaryawanStatusEnum::AKTIF);
    }

    /**
     * Scope karyawan dengan status nonaktif
     */
    public function scopeInactive($query)
    {
        return $query->where('status', KaryawanStatusEnum::NONAKTIF);
    }

    /**
     * Scope karyawan dengan status cuti
     */
    public function scopeOnLeave($query)
    {
        return $query->where('status', KaryawanStatusEnum::CUTI);
    }

    /**
     * Scope karyawan berdasarkan departemen
     */
    public function scopeByDepartment($query, $departemenId)
    {
        return $query->where('id_departemen', $departemenId);
    }

    /**
     * Cek apakah karyawan punya payroll di periode tertentu
     */
    public function hasPayrollInPeriod($periodeId)
    {
        return $this->penggajians()->where('id_periode', $periodeId)->exists();
    }

    /**
     * Hitung total gaji yang diterima
     */
    public function getTotalSalaryAmount()
    {
        return $this->penggajians()->sum('gaji_bersih');
    }

    /**
     * Get nama lengkap
     */
    public function getNamaAttribute()
    {
        return $this->nama_karyawan;
    }

    /**
     * Get status badge class untuk tampilan UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            KaryawanStatusEnum::AKTIF => 'badge-success',
            KaryawanStatusEnum::NONAKTIF => 'badge-danger',
            KaryawanStatusEnum::CUTI => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Get URL foto karyawan
     */
    public function getFotoUrlAttribute()
    {
        return $this->foto_karyawan
            ? asset("storage/karyawan/foto/{$this->foto_karyawan}")
            : asset('images/default-avatar.png');
    }

    /**
     * Get URL scan KTP karyawan
     */
    public function getKtpUrlAttribute()
    {
        return $this->upload_ktp
            ? asset("storage/karyawan/ktp/{$this->upload_ktp}")
            : null;
    }
}
