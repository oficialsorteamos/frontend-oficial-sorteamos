<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNumberShotFrequencyIdToCamSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cam_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('number_shot_frequency_id')->nullable()->default(1)->comment('Id da quantidade de disparos a serem realizados a cada frequência')->after('operation_frequency_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cam_settings', function (Blueprint $table) {
            $table->dropColumn('number_shot_frequency_id');
        });
    }
}
