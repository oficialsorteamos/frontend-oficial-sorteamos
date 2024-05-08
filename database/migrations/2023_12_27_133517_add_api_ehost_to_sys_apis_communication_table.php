<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApiEhostToSysApisCommunicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Cria o Perfil
        DB::table('sys_apis_communication')->insert(
            array(
                [
                    'id' => 8,
                    'api_name' => 'API eHost',
                    'api_description' => 'API Não Oficial para WhatsApp',
                    'api_official' => 0,
                    'api_status' => 'A',
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
