<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use mysql_xdevapi\Table;

class Merchant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'merchants';
    protected $guarded = false;
    const CERT_VALID = 0;
    const CERT_EXPIRES = 1;
    const CERT_EXPIRED = 2;

    public $casts = [
        'next_update' => 'date'
    ];

    public function cinema()
    {
        return $this->belongsTo(Cinema::class, 'cinema_id', 'id');
    }

    public function workstation(): BelongsTo
    {
        return $this->belongsTo(Workstation::class);
    }

    public function getDistinguishedNames()
    {
        return [
            "countryName" => $this->cinema->country_name,
            "localityName" => $this->cinema->city_name,
            "stateOrProvinceName" => $this->cinema->subject_name,
            "organizationName" => $this->cinema->company_title->title,
            "organizationalUnitName" => $this->department_name,
            "commonName" => $this->mid,
            "emailAddress" => $this->cinema->contact_name,
        ];
    }

    public function getExpiryStatus()
    {
        $diff = Carbon::now()->diffInDays($this->next_update,false);

        if ($diff <= 0) {
            return self::CERT_EXPIRED;
        } elseif ($diff <= 31) {
            return self::CERT_EXPIRES;
        }

        return self::CERT_VALID;
    }

}

