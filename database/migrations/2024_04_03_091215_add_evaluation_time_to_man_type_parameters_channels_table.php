<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEvaluationTimeToManTypeParametersChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('man_type_parameters_channels')->insert(
            array(
                [
                    'id' => 5,
                    'category_id' => 1,
                    'typ_name' => 'Tempo de Avaliação de Atendimento',
                    'typ_description' => 'Tempo máximo que o contato terá para avaliar o atendimento',
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
        
    }
}
