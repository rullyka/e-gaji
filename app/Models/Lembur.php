<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Lembur extends Model
{
    use HasUuids;

    protected $fillable = [
        'karyawan_id',
        'supervisor_id',
        'tanggal_lembur',
        'jam_mulai',
        'jam_selesai',
        'total_lembur',
        'lembur_disetujui',
        'keterangan',
        'jenis_lembur',
        'status',
        'keterangan_tolak',
        'tanggal_approval',
        'approved_by',
    ];

    protected $casts = [
        'tanggal_lembur' => 'date',
        'tanggal_approval' => 'datetime',
    ];

    // Accessors
    public function getTanggalLemburFormattedAttribute()
    {
        return $this->tanggal_lembur ? $this->tanggal_lembur->format('d-m-Y') : '-';
    }

    public function getJamMulaiFormattedAttribute()
    {
        return $this->jam_mulai ? Carbon::parse($this->jam_mulai)->format('H:i') : '-';
    }

    public function getJamSelesaiFormattedAttribute()
    {
        return $this->jam_selesai ? Carbon::parse($this->jam_selesai)->format('H:i') : '-';
    }

    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'Disetujui':
                return 'badge-success';
            case 'Ditolak':
                return 'badge-danger';
            default:
                return 'badge-warning';
        }
    }

    // Relationships
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Karyawan::class, 'supervisor_id');
    }

    public function approver()
    {
        return $this->belongsTo(Karyawan::class, 'approved_by');
    }
}
