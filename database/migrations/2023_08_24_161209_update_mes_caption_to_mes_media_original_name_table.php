<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMesCaptionToMesMediaOriginalNameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_messages', function (Blueprint $table) {
            $table->renameColumn('mes_caption', 'mes_media_original_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cha_messages', function (Blueprint $table) {
            $table->renameColumn('mes_media_original_name', 'mes_caption');
        });
    }
}
