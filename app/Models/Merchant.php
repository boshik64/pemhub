<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use mysql_xdevapi\Table;

class Merchant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'merchants';
    protected $guarded = false;

    public function cinema()
    {
        return $this->belongsTo(Cinema::class, 'cinema_id', 'id');
    }
}
