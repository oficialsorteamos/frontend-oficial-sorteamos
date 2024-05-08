<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsChannelsChatbotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_channels_chatbots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id')->comment('Id da campanha que o canal está inserido');
            $table->unsignedBigInteger('channel_id')->comment('Id do canal que possuirá um chatbot associado');
            $table->unsignedBigInteger('chatbot_id')->comment('Id do chatbot associado ao canal');
            $table->string('cha_status')->default('A')->comment('Status do parâmetro');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
                ->onDelete('cascade');
            
            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
                ->onDelete('cascade');

            $table->foreign('chatbot_id')
                ->references('id')
                ->on('cha_chatbots')
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
        Schema::dropIfExists('cam_channels_chatbots');
    }
}
