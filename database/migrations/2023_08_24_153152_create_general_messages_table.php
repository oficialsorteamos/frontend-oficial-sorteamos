<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_general_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_general_message_id')->comment('Id do tipo de mensagem geral');
            $table->text('gen_content', 1024)->comment('Conteúdo da mensagem');
            $table->string('gen_status')->default('A');
            $table->timestamps();

            $table->foreign('type_general_message_id')
                ->references('id')
                ->on('man_type_general_messages')
                ->onDelete('cascade');
        });

        // Insert default values
        DB::table('man_general_messages')->insert(
            array(
                [
                    'id' => 1,
                    'type_general_message_id' => 1,
                    'gen_content' => 'Olá! Aguarde um momento pois um dos nossos operadores irá lhe atender...',
                ],
                [
                    'id' => 2,
                    'type_general_message_id' => 2,
                    'gen_content' => 'Olá! Aguarde um momento pois um dos nossos operadores irá lhe atender...',
                ],
            )
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('man_general_messages');
    }
}
