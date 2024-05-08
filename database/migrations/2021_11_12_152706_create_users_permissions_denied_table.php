<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPermissionsDeniedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_users_permissions_denied', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_role_id')->comment('Id do usuário associado a uma determinada permissão');
            $table->unsignedBigInteger('role_resour_perm_id')->comment('Id da associação entre o permissão, seu recurso e o perfil associado a esse recurso');
            $table->timestamps();

            $table->foreign('user_role_id')
                    ->references('id')
                    ->on('sys_users_roles')
                    ->onDelete('cascade');

            $table->foreign('role_resour_perm_id')
                    ->references('id')
                    ->on('sys_roles_resources_permissions')
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
        Schema::dropIfExists('sys_users_permissions_denied');
    }
}
