<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickMessagesParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_quick_messages_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quick_message_id')->comment('Id da mensagem rápida');
            $table->unsignedBigInteger('type_parameter_id')->comment('Id do parâmetro associado a mensagem');
            $table->unsignedBigInteger('type_button_id')->nullable()->comment('Id do tipo de botão');
            $table->string('qui_content')->nullable()->comment('Conteúdo de um botão ou outro parâmetro');
            $table->string('qui_url', 500)->nullable()->comment('Url associada ao parâmetro');
            $table->string('qui_phone_number')->nullable()->comment('Telefone associada ao parâmetro');
            $table->string('qui_media_name')->nullable()->comment('Nome da mídia associada ao parâmetro');
            $table->string('qui_status')->default('A')->comment('Status do parâmetro');
            $table->timestamps();

            $table->foreign('quick_message_id')
                ->references('id')
                ->on('cha_quick_messages')
                ->onDelete('cascade');
            
            $table->foreign('type_parameter_id')
                ->references('id')
                ->on('cha_quick_messages_type_parameters')
                ->onDelete('cascade');

            $table->foreign('type_button_id')
                ->references('id')
                ->on('cha_templates_messages_type_buttons')
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
        Schema::dropIfExists('cha_quick_messages_parameters');
    }
}
