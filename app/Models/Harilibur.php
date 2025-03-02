<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Harilibur extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'nama_libur',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}