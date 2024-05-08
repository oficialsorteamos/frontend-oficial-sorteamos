<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmsChargeParameterToFinParametersTable extends Migration
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
                    'id' => 13,
                    'type_parameter_id' => 10,
                    'par_value' => '1',
                    'par_proportional_charge' => 0,
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
