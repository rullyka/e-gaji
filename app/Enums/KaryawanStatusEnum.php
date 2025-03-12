<?php

namespace App\Enums;

enum KaryawanStatusEnum: string
{
    case AKTIF = 'aktif';
    case NONAKTIF = 'nonaktif';
    case CUTI = 'cuti';
}
