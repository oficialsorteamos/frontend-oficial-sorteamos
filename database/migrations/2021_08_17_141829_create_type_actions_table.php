<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_type_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição da ação a ser realizada pelo chatbot em um bloco.');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        DB::table('cha_type_actions')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Transferência',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Chamada de Bloco',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Rastreamento',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 4,
                    'typ_description' => 'Finalização de Autoatendimento',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 5,
                    'typ_description' => 'Comunicação Ativa',
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
        Schema::dropIfExists('cha_type_actions');
    }
}
