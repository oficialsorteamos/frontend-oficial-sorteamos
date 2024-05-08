<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesTypeCallActionsButtonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_type_call_actions_buttons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome da chamada de ação de um botão');
            $table->string('tem_status')->default('A')->comment('Status de uma chamada de ação de um botão');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cha_templates_messages_type_call_actions_buttons')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Url',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'Número de Telefone',
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
        Schema::dropIfExists('cha_templates_messages_type_call_actions_buttons');
    }
}
