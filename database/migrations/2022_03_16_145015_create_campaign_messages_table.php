<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id')->comment('Id da campanha');
            $table->unsignedBigInteger('quick_message_id')->nullable()->comment('Id da mensagem rápida a associada a campanha');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que está gravando a mensagem');
            $table->text('mes_content', 10000)->nullable()->comment('Conteúdo da mensagem da campanha.');
            $table->string('mes_status')->default('A')->comment('Status da mensagem.');            
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
                ->onDelete('cascade');
            
            $table->foreign('quick_message_id')
                ->references('id')
                ->on('cha_quick_messages')
                ->onDelete('cascade');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('cam_messages');
    }
}
