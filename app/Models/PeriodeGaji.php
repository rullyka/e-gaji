<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeGaji extends Model
{
    use HasFactory;

    protected $table = 'periodegajis';

    protected $fillable = [
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Relationship with penggajian if needed
    public function penggajian()
    {
        return $this->hasMany(Penggajian::class, 'periode_id');
    }

    // Check if this period has any associated payroll data
    /**
     * Check if this period has payroll data
     */
    public function hasPayrollData()
    {
        // Implement this based on your relationships
        // For example, if you have a Payroll model with a periode_gaji_id column:
        // return $this->payrolls()->exists();

        // Temporary implementation (replace with actual check)
        return false;
    }

    /**
     * Set this period as active and deactivate all others
     */
    public function setAsActive()
    {
        // Deactivate all other periods
        static::where('id', '!=', $this->id)
            ->where('status', 'aktif')
            ->update(['status' => 'nonaktif']);

        // Set this period as active
        $this->status = 'aktif';
        $this->save();

        return $this;
    }
}
