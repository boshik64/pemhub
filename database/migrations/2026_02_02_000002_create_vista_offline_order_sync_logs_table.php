<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vista_offline_order_sync_logs', function (Blueprint $table) {
            $table->id();

            // transaction_id из Vista (монотонно растущий)
            $table->unsignedBigInteger('transaction_id')->unique();

            // pending | success | failed
            $table->string('status', 16)->default('pending')->index();

            // Количество попыток отправки в Mindbox
            $table->unsignedInteger('attempts')->default(0);

            // Сохранение исходных агрегированных данных (для Retry из админки)
            $table->json('source_data')->nullable();

            // Минимальный payload, который реально был отправлен в Mindbox
            $table->json('request_payload')->nullable();

            // Ответ Mindbox (как есть)
            $table->json('response_payload')->nullable();

            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vista_offline_order_sync_logs');
    }
};

