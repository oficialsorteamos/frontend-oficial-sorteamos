<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_quick_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Id do usuário cuja mensagem rápida pertence');
            $table->unsignedBigInteger('type_quick_message_id')->nullable()->comment('Id do tipo de mensagem rápida (usuário, chatbot, etc.)');
            $table->unsignedBigInteger('type_format_message_id')->nullable()->comment('Id do tipo de formato de mensagem (texto, áudio, etc)');
            $table->string('qui_title')->comment('Título da mensagem rápida');
            $table->text('qui_content', 10000)->comment('Conteúdo da mensagem rápida.');
            $table->string('qui_status')->default('A')->comment('Status da mensagem rápida.');
            $table->timestamps();

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
        Schema::dropIfExists('cha_quick_messages');
    }
}
