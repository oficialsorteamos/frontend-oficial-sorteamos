<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFairDistributionToManTypeParametersChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Cria o Perfil
        DB::table('man_type_parameters_channels')->insert(
            array(
                [
                    'id' => 4,
                    'category_id' => 2, //Opção
                    'typ_name' => 'Transferência Igualitária',
                    'typ_description' => 'Configuração de transferência Igualitária que será acionada em caso de inatividade do contato durante o autoatendimento ou em caso de inexistência de chatbot',
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
