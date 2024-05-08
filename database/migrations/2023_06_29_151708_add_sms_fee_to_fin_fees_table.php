<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmsFeeToFinFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Insert default values
        DB::table('fin_type_fees')->insert(
            array(
                [
                    'id' => 6,
                    'typ_description' => 'Taxa por Envio de Mensagem de Campanha via SMS',
                    'typ_status' => 'A',
                ],
            )
        );

        // Insert default values
        DB::table('fin_fees')->insert(
            array(
                [
                    'id' => 6,
                    'type_fee_id' => 6,
                    'fee_value' => 0.09,
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
