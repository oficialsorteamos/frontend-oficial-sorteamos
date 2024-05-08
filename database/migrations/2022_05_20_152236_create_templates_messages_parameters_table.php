<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('template_id')->comment('Id do template cujo parâmetro faz parte');
            $table->unsignedBigInteger('parameter_id')->nullable()->comment('Id do parâmetro associado (Ex: Id (chave estrangeira) do botão cuja variável está associada ao mesmo)');
            $table->unsignedBigInteger('type_parameter_id')->comment('Tipo de parâmetro (Variável, botão)');
            $table->unsignedBigInteger('location_parameter_id')->nullable()->comment('Tipo de parâmetro (Variável, botão)');
            $table->unsignedBigInteger('type_button_id')->nullable()->comment('Tipo de botão (Resposta rápida, chamada para ação)');
            $table->unsignedBigInteger('type_variable_id')->nullable()->comment('Tipo de variável (Nome do contato, Nome do usuário, etc.)');
            $table->string('tem_url', 300)->nullable()->comment('Url associada ao parâmetro');
            $table->string('tem_phone_number', 300)->nullable()->comment('Número de telefone para quando o botão for uma chamada para ação');
            $table->string('tem_content', 300)->nullable()->comment('Conteúdo associado ao parâmetro');
            $table->string('tem_variable_tag')->nullable()->comment('Tag no texto que identifica a variável ao qual será substituída pela mesma');
            $table->string('tem_media_name')->nullable()->comment('Nome da mídia associada ao cabeçalho da mensagem');
            $table->timestamps();


            $table->foreign('template_id')
                ->references('id')
                ->on('cha_templates_messages')
                ->onDelete('cascade');
            
            $table->foreign('type_parameter_id')
                ->references('id')
                ->on('cha_templates_messages_type_parameters')
                ->onDelete('cascade');
            
            $table->foreign('location_parameter_id')
                ->references('id')
                ->on('cha_templates_messages_locations_parameters')
                ->onDelete('cascade');
            
            $table->foreign('type_button_id')
                ->references('id')
                ->on('cha_templates_messages_type_buttons')
                ->onDelete('cascade');
            
            $table->foreign('parameter_id')
                ->references('id')
                ->on('cha_templates_messages_parameters')
                ->onDelete('cascade');
            
            $table->foreign('type_variable_id')
                ->references('id')
                ->on('cha_templates_messages_type_variables')
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
        Schema::dropIfExists('cha_templates_messages_parameters');
    }
}
