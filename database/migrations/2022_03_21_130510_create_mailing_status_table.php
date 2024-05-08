<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailingStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_mailing_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mai_description')->comment('Descrição da campanha.');
            $table->string('mai_status')->default('A')->comment('Status.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cam_mailing_status')->insert(
            array(
                [
                    'id' => 1,
                    'mai_description' => 'Aguardando Envio',
                ],
                [
                    'id' => 2,
                    'mai_description' => 'Enviado',
                ],
                [
                    'id' => 3,
                    'mai_description' => 'Falha ao Enviar',
                ],
                [
                    'id' => 4,
                    'mai_description' => 'Em Atendimento',
                ],
                [
                    'id' => 5,
                    'mai_description' => 'Blacklist',
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
        Schema::dropIfExists('cam_mailing_status');
    }
}
