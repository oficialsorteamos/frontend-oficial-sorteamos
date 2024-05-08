<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesTypeButtonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_type_buttons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome do tipo de botão');
            $table->string('tem_status')->default('A')->comment('Situação do tipo de botão de parâmetro contido em um template');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_templates_messages_type_buttons')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Resposta Rápida',
                    'tem_status' => 'A',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'Chamada para Ação',
                    'tem_status' => 'A',
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
        Schema::dropIfExists('cha_templates_messages_type_buttons');
    }
}
