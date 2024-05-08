<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmsReturnFeeToFinFeesTable extends Migration
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
                    'id' => 11,
                    'typ_description' => 'Cobrança por Retorno de Mensagem de Campanha por SMS',
                    'typ_status' => 'A',
                ],
            )
        );

        // Insert default values
        DB::table('fin_parameters')->insert(
            array(
                [
                    'id' => 14,
                    'type_parameter_id' => 11,
                    'par_value' => '1',
                    'par_proportional_charge' => 0,
                    'par_status' => 'A',
                ],
            )
        );

        // Insert default values
        DB::table('fin_type_costs')->insert(
            array(
                [
                    'id' => 3,
                    'typ_description' => 'Retorno de Mensagem de Campanha por SMS',
                    'typ_status' => 'A',
                ],
            )
        );

        // Insert default values
        DB::table('fin_type_fees')->insert(
            array(
                [
                    'id' => 7,
                    'typ_description' => 'Taxa por Retorno de Mensagem de Campanha via SMS',
                    'typ_status' => 'A',
                ],
            )
        );

        // Insert default values
        DB::table('fin_fees')->insert(
            array(
                [
                    'id' => 7,
                    'type_fee_id' => 7,
                    'fee_value' => 0.07,
                    'fee_status' => 'A',
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
