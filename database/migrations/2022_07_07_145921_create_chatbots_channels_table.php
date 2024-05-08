<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotsChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_chatbots_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chatbot_id')->comment('Id do do chatbot');
            $table->unsignedBigInteger('channel_id')->comment('Id do canal associado ao chatbot');
            $table->string('cha_status')->default('A')->comment('Status do canal associado ao chatbot');
            $table->timestamps();

            $table->foreign('chatbot_id')
                ->references('id')
                ->on('cha_chatbots')
                ->onDelete('cascade');

            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cha_chatbots_channels');
    }
}
