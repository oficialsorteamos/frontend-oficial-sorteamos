<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddApiMainToSysApisCommunicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sys_apis_communication', function (Blueprint $table) {
            $table->boolean('api_main')->nullable()->comment('Define a API principal')->after('api_official');
        });

        //Coloca a Z-API como a principal
        DB::statement("UPDATE sys_apis_communication SET api_main = 1 WHERE id = 5");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sys_apis_communication', function (Blueprint $table) {
            $table->dropColumn('api_main');
        });
    }
}
