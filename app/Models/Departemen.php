<?php

namespace App\Models;

use App\Models\Bagian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    public function hasBagians()
{
    return $this->bagians()->count() > 0;
}

}
