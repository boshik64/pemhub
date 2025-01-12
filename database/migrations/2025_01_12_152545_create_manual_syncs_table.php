<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_syncs', function (Blueprint $table) {
            $table->id(); // Уникальный идентификатор
            $table->string('type')->default('manual'); // Тип синхронизации (manual, auto и т.д.)
            $table->string('status')->default('pending'); // Статус синхронизации
            $table->text('details')->nullable(); // Лог (успешно или ошибки)
            $table->timestamps(); // Время создания и обновления записи
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_syncs');
    }
};
