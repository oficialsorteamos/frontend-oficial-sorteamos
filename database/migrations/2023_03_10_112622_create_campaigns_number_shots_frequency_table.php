<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsNumberShotsFrequencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_number_shots_frequency', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('num_shots')->comment('Quantidade de tempo (minutos) entre cada operação da campanha');
            $table->string('num_status')->default('A')->comment('Status do intervalo de tempo');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cam_number_shots_frequency')->insert(
            array(
                [
                    'id' => 1,
                    'num_shots' => 1,
                ],
                [
                    'id' => 2,
                    'num_shots' => 2,
                ],
                [
                    'id' => 3,
                    'num_shots' => 3,
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
        Schema::dropIfExists('cam_number_shots_frequency');
    }
}
