<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeChatbotIdToChaChatbotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_chatbots', function (Blueprint $table) {
            $table->unsignedBigInteger('type_chatbot_id')->nullable()->default(1)->comment('Id do tipo de um chatbot')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cha_chatbots', function (Blueprint $table) {
            $table->dropColumn('type_chatbot_id');
        });
    }
}
