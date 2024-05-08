<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_chatbots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cha_name')->comment('Nome do chatbot.');
            $table->string('cha_description')->comment('Descrição da finalidade do chatbot.');
            $table->boolean('cha_only_official_channel')->nullable()->comment('Indica se o chatbot irá utilizar apenas canais oficiais. Informação necessária para saber se será permitido mensagem modelo no chatbot');
            $table->string('cha_status')->default('A')->comment('Indica se o chatbot está ativo ou não.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_chatbots')->insert(
            array(
                [
                    'id' => 1,
                    'cha_name' => 'Chatbot Padrão',
                    'cha_description' => 'Chatbot para atendimentos recorrentes da empresa',
                    'cha_status' => 'A',
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
        Schema::dropIfExists('cha_chatbots');
    }
}
