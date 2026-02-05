<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vista_offline_order_sync_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('last_processed_transaction_id')->default(0);
            // Верхняя граница (watermark) текущего запуска. Двигаем last_processed_transaction_id только
            // после успешной обработки всех заказов до этого значения.
            $table->unsignedBigInteger('target_transaction_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vista_offline_order_sync_states');
    }
};

