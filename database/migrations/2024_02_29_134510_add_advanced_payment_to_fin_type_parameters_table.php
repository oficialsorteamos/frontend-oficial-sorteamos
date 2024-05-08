<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvancedPaymentToFinTypeParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Cria o Perfil
        DB::table('fin_type_parameters')->insert(
            array(
                [
                    'id' => 13,
                    'typ_description' => 'Sinaliza se o cliente realizou pagamento antecipado para utilização do sistema',
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
