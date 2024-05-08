<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsOperatingFrequencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_operating_frequency', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ope_description')->comment('Descrição do intervalo de tempo a cada operação da campanha');
            $table->integer('ope_minutes')->comment('Quantidade de tempo (minutos) entre cada operação da campanha');
            $table->string('ope_status')->default('A')->comment('Status do intervalo de tempo');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cam_operating_frequency')->insert(
            array(
                [
                    'id' => 1,
                    'ope_description' => '1 minuto',
                    'ope_minutes' => 1,
                ],
                [
                    'id' => 2,
                    'ope_description' => '2 minutos',
                    'ope_minutes' => 2,
                ],
                [
                    'id' => 3,
                    'ope_description' => '3 minutos',
                    'ope_minutes' => 3,
                ],
                [
                    'id' => 4,
                    'ope_description' => '4 minutos',
                    'ope_minutes' => 4,
                ],
                [
                    'id' => 5,
                    'ope_description' => '5 minutos',
                    'ope_minutes' => 5,
                ],
                [
                    'id' => 6,
                    'ope_description' => '10 minutos',
                    'ope_minutes' => 10,
                ],
                [
                    'id' => 7,
                    'ope_description' => '15 minutos',
                    'ope_minutes' => 15,
                ],
                [
                    'id' => 8,
                    'ope_description' => '30 minutos',
                    'ope_minutes' => 30,
                ],
                [
                    'id' => 9,
                    'ope_description' => '45 minutos',
                    'ope_minutes' => 45,
                ],
                [
                    'id' => 10,
                    'ope_description' => '60 minutos',
                    'ope_minutes' => 60,
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
        Schema::dropIfExists('cam_operating_frequency');
    }
}
