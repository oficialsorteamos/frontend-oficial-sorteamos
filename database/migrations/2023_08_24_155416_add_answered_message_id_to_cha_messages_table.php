<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnsweredMessageIdToChaMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_messages', function (Blueprint $table) {
            $table->string('answered_message_id')->nullable()->comment('Id da mensagem que foi respondida')->after('api_message_id');
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
            $table->dropColumn('answered_message_id');
        });
    }
}
