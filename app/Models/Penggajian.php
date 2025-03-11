<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penggajian extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_periode',
        'id_karyawan',
        'periode_awal',
        'periode_akhir',
        'gaji_pokok',
        'tunjangan',
        'detail_tunjangan',
        'potongan',
        'detail_potongan',
        'detail_departemen',
        'gaji_bersih',
    ];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'gaji_pokok' => 'decimal:2',
        'tunjangan' => 'decimal:2',
        'potongan' => 'decimal:2',
        'gaji_bersih' => 'decimal:2',
    ];

    // Disable auto-incrementing as we're using UUID
    public $incrementing = false;

    // Set the key type to string for UUID
    protected $keyType = 'string';

    // Relationships
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id');
    }

    public function periodeGaji()
    {
        return $this->belongsTo(PeriodeGaji::class, 'id_periode', 'id');
    }

    // Accessor for detail_tunjangan
    public function getDetailTunjanganAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    // Accessor for detail_potongan
    public function getDetailPotonganAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    // Accessor for detail_departemen
    public function getDetailDepartemenAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    // Mutator for detail_tunjangan
    public function setDetailTunjanganAttribute($value)
    {
        $this->attributes['detail_tunjangan'] = is_array($value) ? json_encode($value) : $value;
    }

    // Mutator for detail_potongan
    public function setDetailPotonganAttribute($value)
    {
        $this->attributes['detail_potongan'] = is_array($value) ? json_encode($value) : $value;
    }

    // Mutator for detail_departemen
    public function setDetailDepartemenAttribute($value)
    {
        $this->attributes['detail_departemen'] = is_array($value) ? json_encode($value) : $value;
    }

    // Calculate total allowances
    public function calculateTotalTunjangan()
    {
        $details = $this->detail_tunjangan ?: [];
        return array_sum(array_column($details, 'nominal'));
    }

    // Calculate total deductions
    public function calculateTotalPotongan()
    {
        $details = $this->detail_potongan ?: [];
        return array_sum(array_column($details, 'nominal'));
    }

    // Calculate net salary
    public function calculateGajiBersih()
    {
        return $this->gaji_pokok + $this->tunjangan - $this->potongan;
    }

    // Format currency
    public function formatCurrency($value)
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    // Get gaji pokok in Rupiah format
    public function getGajiPokokRupiahAttribute()
    {
        return $this->formatCurrency($this->gaji_pokok);
    }

    // Get tunjangan in Rupiah format
    public function getTunjanganRupiahAttribute()
    {
        return $this->formatCurrency($this->tunjangan);
    }

    // Get potongan in Rupiah format
    public function getPotonganRupiahAttribute()
    {
        return $this->formatCurrency($this->potongan);
    }

    // Get gaji bersih in Rupiah format
    public function getGajiBersihRupiahAttribute()
    {
        return $this->formatCurrency($this->gaji_bersih);
    }

    // Helper method to get departemen name
    public function getDepartemenNameAttribute()
    {
        $detailDept = $this->detail_departemen;
        return $detailDept && isset($detailDept['departemen']) ? $detailDept['departemen'] : '-';
    }

    // Helper method to get bagian name
    public function getBagianNameAttribute()
    {
        $detailDept = $this->detail_departemen;
        return $detailDept && isset($detailDept['bagian']) ? $detailDept['bagian'] : '-';
    }

    // Helper method to get jabatan name
    public function getJabatanNameAttribute()
    {
        $detailDept = $this->detail_departemen;
        return $detailDept && isset($detailDept['jabatan']) ? $detailDept['jabatan'] : '-';
    }

    // Helper method to get profesi name
    public function getProfesiNameAttribute()
    {
        $detailDept = $this->detail_departemen;
        return $detailDept && isset($detailDept['profesi']) ? $detailDept['profesi'] : '-';
    }
}