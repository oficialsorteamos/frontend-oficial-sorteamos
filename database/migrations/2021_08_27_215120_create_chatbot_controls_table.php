<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotControlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_chatbot_controls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chat_id')->comment('Tipo de ação realizada. (Transferência, rastreamento, etc.)');
            $table->unsignedBigInteger('bloc_id')->nullable()->comment('Departamento para onde o cliente foi transferido');
            $table->timestamps();

            $table->foreign('chat_id')
                ->references('id')
                ->on('cha_chats')
                ->onDelete('cascade');
            
            $table->foreign('bloc_id')
                ->references('id')
                ->on('cha_chatbot_blocs')
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
        Schema::dropIfExists('cha_chatbot_controls');
    }
}
