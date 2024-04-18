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
        Schema::create('cinemas', function (Blueprint $table) {
            $table->id();

            //Связь с наименованием юр.лица start
            $table->unsignedBigInteger('company_title_id')->nullable();
            $table->index('company_title_id', 'cinemas_company_title_idx');
            $table->foreign('company_title_id', 'cinemas_company_title_fk')->on('cinemas')->references('id');
            //Связь с наименованием юр.лица end

            $table->string('cinema_name');
            $table->string('country_name')->default('RU');
            $table->string('city_name')->default('Moscow');
            $table->string('subject_name')->default('Moscow');
            $table->string('contact_name')->default('i.shakirov@karofilm.ru');
            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cinemas');
    }
};
