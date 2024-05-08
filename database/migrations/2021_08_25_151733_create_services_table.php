<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chat_id')->comment('Chat para qual o atendimento está sendo realizado.');
            $table->unsignedBigInteger('channel_id')->comment('Canal associado ao atendimento.');
            $table->unsignedBigInteger('campaign_id')->nullable()->comment('Campanha associada ao atendimento.');
            $table->unsignedBigInteger('type_status_service_id')->comment('Indica a situação do atendimento (Aberto, Fechado, Pendente, etc.).');
            $table->string('ser_protocol_number')->comment('Número do protocolo de atendimento.');
            $table->timestamp('ser_dt_end_service')->nullable()->comment('Data da finalização do antedimento');
            $table->timestamps();

            $table->foreign('chat_id')
                ->references('id')
                ->on('cha_chats')
                ->onDelete('cascade');
            
            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
                ->onDelete('cascade');
            
            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
                ->onDelete('cascade');

            $table->foreign('type_status_service_id')
                ->references('id')
                ->on('cha_type_status_services')
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
        Schema::dropIfExists('cha_services');
    }
}
