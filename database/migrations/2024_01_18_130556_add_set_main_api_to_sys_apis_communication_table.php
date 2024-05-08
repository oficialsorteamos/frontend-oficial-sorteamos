<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSetMainApiToSysApisCommunicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Coloca a Z-API como a principal
        DB::statement("UPDATE sys_apis_communication SET api_main = NULL WHERE id = 5");
        DB::statement("UPDATE sys_apis_communication SET api_main = 1 WHERE id = 8");
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
