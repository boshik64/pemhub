<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vista_offline_sales_channels', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('channel_id')->unique();
            $table->string('name');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('vista_offline_sales_channels')->insert([
            [
                'channel_id' => 1,
                'name' => 'Point of Sale',
                'enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'channel_id' => 2,
                'name' => 'Kiosk',
                'enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'channel_id' => 8,
                'name' => 'Smartix(КСО)',
                'enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vista_offline_sales_channels');
    }
};
