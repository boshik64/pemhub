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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('workstation_id')->unsigned()->index();
        });

        Schema::table('merchants', function (Blueprint $table) {
            $table->integer('workstation_id')->unsigned()->index();
            $table->dropColumn('workstation');
        });

        Schema::create('workstations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('user_workstation', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->index();
            $table->integer('workstation_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
