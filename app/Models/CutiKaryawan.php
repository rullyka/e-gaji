<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class CutiKaryawan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id_karyawan',
        'jenis_cuti',
        'tanggal_mulai_cuti',
        'tanggal_akhir_cuti',
        'jumlah_hari_cuti',
        'cuti_disetujui',
        'master_cuti_id',
        'bukti',
        'id_supervisor',
        'status_acc',
        'keterangan_tolak',
        'tanggal_approval',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_mulai_cuti' => 'date',
        'tanggal_akhir_cuti' => 'date',
        'tanggal_approval' => 'datetime',
    ];

    /**
     * Get the karyawan that owns the cuti.
     */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }

    /**
     * Get the master cuti that owns the cuti karyawan.
     */
    public function masterCuti()
    {
        return $this->belongsTo(Mastershift::class, 'master_cuti_id');
    }

    /**
     * Get the supervisor that approves the cuti.
     */
    public function supervisor()
    {
        return $this->belongsTo(Karyawan::class, 'id_supervisor');
    }

    /**
     * Get the approver that approves the cuti.
     */
    public function approver()
    {
        return $this->belongsTo(Karyawan::class, 'approved_by');
    }

    /**
     * Get the full path to the bukti file
     */
    public function getBuktiUrlAttribute()
    {
        if ($this->bukti) {
            return asset('storage/cuti/bukti/' . $this->bukti);
        }
        return null;
    }

    /**
     * Get the formatted tanggal_mulai_cuti
     */
    public function getTanggalMulaiFormattedAttribute()
    {
        return $this->tanggal_mulai_cuti ? $this->tanggal_mulai_cuti->format('d-m-Y') : '-';
    }

    /**
     * Get the formatted tanggal_akhir_cuti
     */
    public function getTanggalAkhirFormattedAttribute()
    {
        return $this->tanggal_akhir_cuti ? $this->tanggal_akhir_cuti->format('d-m-Y') : '-';
    }

    /**
     * Get class for status badge
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status_acc) {
            case 'Disetujui':
                return 'badge-success';
            case 'Ditolak':
                return 'badge-danger';
            default:
                return 'badge-warning';
        }
    }
}