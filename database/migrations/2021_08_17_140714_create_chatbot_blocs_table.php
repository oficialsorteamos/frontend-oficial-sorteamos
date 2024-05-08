<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotBlocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_chatbot_blocs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chatbot_id')->comment('Chat da qual as mensagens fazem parte');
            $table->unsignedBigInteger('type_bloc_id')->comment('Tipo de bloco de mensagem. (Bloco Inicial, Bloco Final, etc.)');
            $table->unsignedBigInteger('template_id')->nullable()->comment('Id do template que compõe o bloco');
            $table->unsignedBigInteger('quick_message_id')->nullable()->comment('Id da mensagem rápida que compõe o bloco');
            $table->boolean('cha_send_option_error_message')->default(1)->comment('Indica se o chatbot irá enviar a mensagem de erro quando o contato digita uma opção inválida');
            $table->string('cha_title')->comment('Título do bloco de mensagens.');
            $table->text('cha_content', 10000)->comment('Conteúdo referente ao bloco.');
            $table->timestamps();

            $table->foreign('chatbot_id')
                ->references('id')
                ->on('cha_chatbots')
                ->onDelete('cascade');
            
            $table->foreign('type_bloc_id')
                ->references('id')
                ->on('cha_type_blocs')
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
        Schema::dropIfExists('cha_chatbot_blocs');
    }
}
