<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmsChargeToFinTypeParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert default values
        DB::table('fin_type_parameters')->insert(
            array(
                [
                    'id' => 10,
                    'typ_description' => 'Cobrança por Envio de Mensagem de Campanha por SMS',
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
