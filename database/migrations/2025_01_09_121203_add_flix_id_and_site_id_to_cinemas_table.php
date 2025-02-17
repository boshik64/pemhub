<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cinemas', function (Blueprint $table) {
            $table->unsignedBigInteger('flix_id')->nullable()->after('id'); 
            $table->unsignedBigInteger('site_id')->nullable()->after('flix_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cinemas', function (Blueprint $table) {
            $table->dropColumn(['flix_id', 'site_id']);
        });
    }
};
