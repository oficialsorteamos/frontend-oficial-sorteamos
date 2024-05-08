<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvancedPaymentToFinParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Insert default values
        DB::table('fin_parameters')->insert(
            array(
                [
                    'type_parameter_id' => 13,
                    'par_value' => '0',
                    'par_proportional_charge' => NULL,
                    'par_status' => 'A',
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
