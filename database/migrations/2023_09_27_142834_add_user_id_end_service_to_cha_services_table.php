<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdEndServiceToChaServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_services', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id_end_service')->nullable()->comment('Id do usuário do sistema que fechou o atendimento')->after('ser_dt_end_service');

            $table->foreign('user_id_end_service')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
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
            $table->dropColumn('user_id_end_service');
        });
    }
}
