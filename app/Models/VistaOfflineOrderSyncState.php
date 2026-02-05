<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VistaOfflineOrderSyncState extends Model
{
    protected $table = 'vista_offline_order_sync_states';

    protected $guarded = false;

    protected $casts = [
        'last_processed_transaction_id' => 'integer',
        'target_transaction_id' => 'integer',
    ];
}

