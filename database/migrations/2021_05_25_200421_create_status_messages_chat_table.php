<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusMessagesChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_status_messages_chat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do status de mensagem em um chat. (Enviada, entregue, em fila.)');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });


        // Insert default values
        DB::table('sys_status_messages_chat')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Enfileirado',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Enviado',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Entregue',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 4,
                    'typ_description' => 'Erro ao Enviar',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 5,
                    'typ_description' => 'Lido',
                    'typ_status' => 'A',
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
        Schema::dropIfExists('sys_status_messages_chat');
    }
}
