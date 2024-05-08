<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_chats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contact_id')->comment('Contato que faz parte do chat');
            $table->integer('cha_unseen_messages')->comment('Quantidade de mensagens que ainda não foram visualizadas');
            $table->string('cha_status')->default('A');
            $table->timestamps();

            $table->foreign('contact_id')
                    ->references('id')
                    ->on('con_contacts')
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
        Schema::dropIfExists('chats');
    }
}
