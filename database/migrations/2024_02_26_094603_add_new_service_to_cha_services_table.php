<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddnewServiceToChaServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_services', function (Blueprint $table) {
            $table->boolean('ser_new_service')->nullable()->comment('Identifica se é um novo atendimento')->after('user_id_end_service');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cha_services', function (Blueprint $table) {
            $table->dropColumn('ser_new_service');
        });
    }
}
