<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VistaOfflineOrderSyncLog extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $table = 'vista_offline_order_sync_logs';

    protected $guarded = false;

    protected $casts = [
        'transaction_id' => 'integer',
        'attempts' => 'integer',
        'source_data' => 'array',
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];
}

