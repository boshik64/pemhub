<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutputToManualSyncsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manual_syncs', function (Blueprint $table) {
            $table->longText('output')->nullable()->after('details'); // Добавляем поле output после поля details
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('manual_syncs', function (Blueprint $table) {
            $table->dropColumn('output'); // Удаляем поле output при откате
        });
    }
}
