<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_channels_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id')->comment('Id da campanha que o canal irá operar');
            $table->unsignedBigInteger('channel_id')->comment('Id do canal que fará parte da campanha');
            $table->integer('cha_total_operations')->default(0)->comment('Total de operações que um canal já realizou');
            $table->string('cha_status')->default('A')->comment('Status da associação canal/campanha');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
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
        Schema::dropIfExists('cam_channels_campaigns');
    }
}
