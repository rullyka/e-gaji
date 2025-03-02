<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Departemen extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name_departemen',
    ];

    /**
     * Get the bagians associated with the departemen.
     */
    public function bagians()
    {
        return $this->hasMany(Bagian::class, 'id_departemen');
    }
}