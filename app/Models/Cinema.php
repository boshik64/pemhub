<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cinema extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cinemas';
    protected $guarded = false;

    public function company_title()
    {
        return $this->belongsTo(CompanyTitle::class, 'company_title_id', 'id');
    }

    public function merchants()
    {
        return $this->hasMany(Merchant::class, 'cinema_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            // Проверяем, есть ли связанные записи
            if ($model->merchants()->exists()) {
                // Если есть, отменяем удаление
                return false;
            }
        });
    }
}
