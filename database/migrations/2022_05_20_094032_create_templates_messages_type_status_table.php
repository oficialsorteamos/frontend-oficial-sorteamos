<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesTypeStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_type_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome do status do template');
            $table->string('tem_description', 1000)->comment('Descrição do significado do status do template');
            $table->string('tem_status')->default('A')->comment('Situação de um status de um template');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cha_templates_messages_type_status')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Enviado',
                    'tem_description' => 'Template submetido à análise pelo Facebook',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'Aprovado',
                    'tem_description' => 'Template aprovado e pronto para uso',
                ],
                [
                    'id' => 3,
                    'tem_name' => 'Reprovado',
                    'tem_description' => 'Template reprovado durante a análise pelo Facebook',
                ],
                [
                    'id' => 4,
                    'tem_name' => 'Pendente',
                    'tem_description' => 'Template aguardando a análise pelo Facebook',
                ],
                [
                    'id' => 5,
                    'tem_name' => 'Sinalizado',
                    'tem_description' => 'Este template está em estado de aviso. Quando a classificação de qualidade atinge um estado baixo (vermelho), o modelo é movido para o status Sinalizado. Se a classificação de qualidade melhorar para um estado alto (verde) ou médio (amarelo) em 7 dias, o modelo retornará ao status Aprovado',
                ],
                [
                    'id' => 6,
                    'tem_name' => 'Desativado',
                    'tem_description' => 'Este template foi desativado pelo Facebook pois recebeu uma classificação muito baixa durante o seu uso',
                ],
                [
                    'id' => 7,
                    'tem_name' => 'Erro ao Enviar',
                    'tem_description' => 'Ocorreu algum erro ao enviar o template. Pode ocorrer por indisponibilidade da internet ou da API',
                ],
                [
                    'id' => 8,
                    'tem_name' => 'Deletado',
                    'tem_description' => 'Template deletado do sistema',
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
        Schema::dropIfExists('cha_templates_messages_type_status');
    }
}
