<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMesCaptionToChaMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_messages', function (Blueprint $table) {
            $table->text('mes_caption')->nullable()->comment('Texto que vem junto com uma mídia')->after('mes_content_name');
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
            $table->dropColumn('mes_caption');
        });
    }
}
