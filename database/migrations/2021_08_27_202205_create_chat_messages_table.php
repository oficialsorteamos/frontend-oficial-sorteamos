<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chat_id')->comment('Chat da qual as mensagens fazem parte');
            $table->unsignedBigInteger('type_user_id')->comment('Tipo de usuário que enviou a mensagem. (contato, operador)');
            $table->integer('sender_id')->comment('Usuário que enviou a mensagem');
            $table->unsignedBigInteger('action_id')->nullable()->comment('Tipo de ação realizada durante interação com o chat. (transferência, etc.)');
            $table->unsignedBigInteger('service_id')->nullable()->comment('Dados do atendimento ao contato');
            $table->unsignedBigInteger('campaign_id')->nullable()->comment('Id da campanha associada a mensagem');
            $table->unsignedBigInteger('template_id')->nullable()->comment('Id do template associado a mensagem');
            $table->unsignedBigInteger('quick_message_id')->nullable()->comment('Id da mensagem rápida associada a mensagem');
            $table->string('api_message_id')->nullable()->comment('Id da mensagem na Api do Whatsapp. (Gupshup)');
            $table->text('mes_message', 20000)->nullable()->comment('Contém o texto da mensagem');
            $table->unsignedBigInteger('type_message_chat_id')->comment('Descrição do tipo de mensagem em um chat. (Texto, áudio, imagem, etc.)');
            $table->string('mes_url')->nullable()->comment('Url que aponta para a mídia enviada pelo chat. (Arquivo, áudio, imagem, etc.)');
            $table->string('mes_caption')->nullable()->comment('Legenda da imagem ou vídeo');
            $table->string('mes_content_name')->nullable()->comment('Nome do conteúdo que enviado/recebido pelo chat');
            $table->string('mes_content_type')->nullable()->comment('Tipo do conteúdo da mensagem (Pdf, jpg, doc, etc.)');
            $table->string('mes_contact_name')->nullable()->comment('Nome do contato compartilhado');
            $table->string('mes_contact_phone_number')->nullable()->comment('Número de telefone do contato compartilhado');
            $table->string('mes_lat')->nullable()->comment('Latitude de uma localização');
            $table->string('mes_long')->nullable()->comment('Longitude de uma localização');
            $table->unsignedBigInteger('status_message_chat_id')->nullable()->comment('Descrição do status de mensagem em um chat. (Enviada, entregue, em fila, etc)');
            $table->string('mes_private')->default(0)->comment('Indica se a mensagem é privada ou não');
            $table->boolean('mes_waiting_message')->nullable()->comment('Indica se a API ainda está aguardando o conteúdo da mensagem (o conteúdo ainda não veio)');
            $table->timestamps();

            $table->foreign('type_user_id')
                    ->references('id')
                    ->on('sys_type_users')
                    ->onDelete('cascade');
            
            $table->foreign('type_message_chat_id')
                    ->references('id')
                    ->on('sys_type_messages_chat')
                    ->onDelete('cascade');
            
            $table->foreign('status_message_chat_id')
                    ->references('id')
                    ->on('sys_status_messages_chat')
                    ->onDelete('cascade');
            
            $table->foreign('chat_id')
                    ->references('id')
                    ->on('cha_chats')
                    ->onDelete('cascade');
            
            $table->foreign('action_id')
                    ->references('id')
                    ->on('cha_actions')
                    ->onDelete('cascade');

            $table->foreign('service_id')
                    ->references('id')
                    ->on('cha_services')
                    ->onDelete('cascade');

            $table->foreign('campaign_id')
                    ->references('id')
                    ->on('cam_campaigns')
                    ->onDelete('cascade');
            
            $table->foreign('template_id')
                    ->references('id')
                    ->on('cha_templates_messages')
                    ->onDelete('cascade');
        
           $table->foreign('quick_message_id')
                    ->references('id')
                    ->on('cha_quick_messages')
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
        Schema::dropIfExists('chat_messages');
    }
}
