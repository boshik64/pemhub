<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyTitle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'company_titles';
    protected $guarded = false;

    public function cinemas()
    {
        return $this->hasMany(Cinema::class, 'company_title_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            // Проверяем, есть ли связанные записи
            if ($model->cinemas()->exists()) {
                // Если есть, отменяем удаление
                return false;
            }
        });
    }
}
