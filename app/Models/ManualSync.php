<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualSync extends Model
{

    protected $fillable = [
        'type',
        'status',
        'details',
        'output',
    ];
    const ACCESS = 0;
    const FAIL = 1;
}
