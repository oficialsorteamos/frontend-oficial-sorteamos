<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesTypeVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_type_variables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome da variável contida em um template');
            $table->string('tem_description')->comment('Descrição da variável contida em um template');
            $table->string('tem_status')->default('A')->comment('Status de uma variável contida em um template');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cha_templates_messages_type_variables')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Nome do Contato',
                    'tem_description' => 'Nome do contato que receberá a mensagem template',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'CPF do Contato',
                    'tem_description' => 'CPF do contato que receberá a mensagem template',
                ],
                [
                    'id' => 3,
                    'tem_name' => 'CNPJ do Contato',
                    'tem_description' => 'CNPJ do contato que receberá a mensagem template',
                ],
                [
                    'id' => 4,
                    'tem_name' => 'Nome do usuário do sistema',
                    'tem_description' => 'Nome do usuário do sistema que enviará a mensagem template',
                ],
                [
                    'id' => 5,
                    'tem_name' => 'Saudação',
                    'tem_description' => 'Saudação (bom dia, boa tarde ou boa noite) que varia de acordo com o horário do dia',
                ],
                [
                    'id' => 6,
                    'tem_name' => 'Nº do Protocolo',
                    'tem_description' => 'Número do protocolo de atendimento',
                ],
                [
                    'id' => 7,
                    'tem_name' => 'Dados Adicionais',
                    'tem_description' => 'Dados adicionais associados ao usuário durante o upload do mailing',
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
        Schema::dropIfExists('cha_templates_messages_type_variables');
    }
}
