<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCallWhatsappFeesToFinFeesTable extends Migration
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
                    'id' => 12,
                    'typ_description' => 'Cobrança por Ligação via WhatsApp',
                    'typ_status' => 'A',
                ],
            )
        );

        // Insert default values
        DB::table('fin_parameters')->insert(
            array(
                [
                    'id' => 16,
                    'type_parameter_id' => 12,
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
                    'id' => 4,
                    'typ_description' => 'Ligação via WhatsApp',
                    'typ_status' => 'A',
                ],
            )
        );

        // Insert default values
        DB::table('fin_type_fees')->insert(
            array(
                [
                    'id' => 8,
                    'typ_description' => 'Taxa por Ligação via WhatsApp',
                    'typ_status' => 'A',
                ],
            )
        );

        // Insert default values
        DB::table('fin_fees')->insert(
            array(
                [
                    'id' => 8,
                    'type_fee_id' => 8,
                    'fee_value' => 0.13,
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
