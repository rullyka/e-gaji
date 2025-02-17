<?php

namespace App\Models;

use App\Models\PeriodeGaji;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penggajian extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [
        'id'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }

    public function periodeGaji()
    {
        return $this->belongsTo(PeriodeGaji::class, 'id_periode');
    }

    // Helper method to safely get tanggal_mulai
    public function getTanggalMulai()
    {
        return $this->periodeGaji ? $this->periodeGaji->tanggal_mulai : null;
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verifikasi_oleh');
    }

    public function verifikasiPenggajian()
    {
        return $this->hasMany(VerifikasiPenggajian::class, 'id_penggajian');
    }

    /**
     * Format a currency value with Indonesian Rupiah format
     *
     * @param float $value The value to format
     * @return string Formatted currency string
     */
    public function formatCurrency($value)
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
