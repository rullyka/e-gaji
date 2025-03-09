<?php

namespace App\Models;

use App\Models\Departemen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bagian extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'bagians';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name_bagian',
        'id_departemen',
    ];

    /**
     * Relationship with Karyawan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function karyawans()
    {
        return $this->hasMany(Karyawan::class, 'id_bagian');
    }

    /**
     * Relationship with Departemen
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }
}