<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesTypeParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_type_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome do parâmetro do template');
            $table->string('tem_status')->default('A')->comment('Situação do parâmetro de um template');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_templates_messages_type_parameters')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Variável',
                    'tem_status' => 'A',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'Botão',
                    'tem_status' => 'A',
                ],
                [
                    'id' => 3,
                    'tem_name' => 'Imagem',
                    'tem_status' => 'A',
                ],
                [
                    'id' => 4,
                    'tem_name' => 'Vídeo',
                    'tem_status' => 'A',
                ],
                [
                    'id' => 5,
                    'tem_name' => 'Documento',
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
        Schema::dropIfExists('cha_templates_messages_type_parameters');
    }
}
