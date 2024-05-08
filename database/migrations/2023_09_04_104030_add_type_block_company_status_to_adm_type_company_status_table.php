<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeBlockCompanyStatusToAdmTypeCompanyStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        // Insert default values
        DB::table('adm_type_company_status')->insert(
            array(
                [
                    'id' => 4,
                    'com_description' => 'Bloqueada Pela Devsky',
                ],
                [
                    'id' => 5,
                    'com_description' => 'Bloqueada Pelo Parceiro White Label',
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
