<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignMailingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_mailings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id')->comment('Id da campanha');
            $table->unsignedBigInteger('contact_id')->comment('Id do contato que recebeu a mensagem');
            $table->unsignedBigInteger('message_id')->nullable()->comment('Id da mensagem enviada ao contato');
            $table->unsignedBigInteger('template_id')->nullable()->comment('Id do template enviado ao contato');
            $table->unsignedBigInteger('channel_id')->nullable()->comment('Id da mensagem enviada ao contato');
            $table->timestamp('mai_dt_sending')->nullable()->comment('Data/hora do envio da mensagem');
            $table->unsignedBigInteger('status_id')->default(1)->comment('Id do status do mailing');
            $table->boolean('mai_contact_returned')->default(0)->comment('Indica se o contato retornou (respondeu) a mensagem');
            $table->string('mai_additional_data_message', 2000)->nullable()->comment('Dados adicionais que podem ser incorporados a mensagem enviada ao contato');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
                ->onDelete('cascade');
            
            $table->foreign('contact_id')
                ->references('id')
                ->on('con_contacts')
                ->onDelete('cascade');
            
            $table->foreign('message_id')
                ->references('id')
                ->on('cam_messages')
                ->onDelete('cascade');
            
            $table->foreign('template_id')
                ->references('id')
                ->on('cha_templates_messages')
                ->onDelete('cascade');
            
            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
                ->onDelete('cascade');
            
            $table->foreign('status_id')
                ->references('id')
                ->on('cam_mailing_status')
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
        Schema::dropIfExists('cam_mailings');
    }
}
