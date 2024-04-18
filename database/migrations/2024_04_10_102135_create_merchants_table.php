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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            //Связь с кинотеатрами один ко многим
            $table->unsignedBigInteger('cinema_id')->nullable();
            $table->index('cinema_id', 'cinema_id_idx');
            $table->foreign('cinema_id', 'cinema_id_fk')->on('merchants')->references('id');

            $table->unsignedBigInteger('mid');
            $table->string('merchant_type');
            $table->string('workstation');
            $table->string('department_name');
            $table->timestamp('next_update')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
