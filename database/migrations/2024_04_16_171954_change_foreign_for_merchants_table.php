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
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropForeign('cinema_id_fk');
            $table->foreign('cinema_id', 'cinema_id_fk')->on('cinemas')->references('id');

        });
        Schema::table('cinemas', function (Blueprint $table) {
            $table->dropForeign('cinemas_company_title_fk');
            $table->foreign('company_title_id', 'cinemas_company_title_fk')->on('company_titles')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
