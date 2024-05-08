<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesResourcesPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_roles_resources_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_resource_id')->comment('Id da associação entre o perfil e um recurso cuja permissão será associada');
            $table->unsignedBigInteger('permission_id')->comment('Id da permissão associada ao perfil/resource');
            $table->timestamps();

            $table->foreign('role_resource_id')
                    ->references('id')
                    ->on('sys_roles_resources')
                    ->onDelete('cascade');

            $table->foreign('permission_id')
                    ->references('id')
                    ->on('sys_permissions')
                    ->onDelete('cascade');
        });

        DB::table('sys_roles_resources_permissions')->insert(
            array(
                [
                    'id' => 1,
                    'role_resource_id' => 1,
                    'permission_id' => 1,
                ],
                [
                    'id' => 2,
                    'role_resource_id' => 2,
                    'permission_id' => 2,
                ],
                [
                    'id' => 3,
                    'role_resource_id' => 3,
                    'permission_id' => 3,
                ],
                [
                    'id' => 4,
                    'role_resource_id' => 4,
                    'permission_id' => 4,
                ],
                [
                    'id' => 5,
                    'role_resource_id' => 5, //gestor-calendário
                    'permission_id' => 5,
                ],
                [
                    'id' => 6,
                    'role_resource_id' => 6, //operador-calendário
                    'permission_id' => 5,
                ],
                [
                    'id' => 7,
                    'role_resource_id' => 7,
                    'permission_id' => 6,
                ],
                [
                    'id' => 8,
                    'role_resource_id' => 8,
                    'permission_id' => 7,
                ],
                [
                    'id' => 9,
                    'role_resource_id' => 9,
                    'permission_id' => 8,
                ],
                [
                    'id' => 10,
                    'role_resource_id' => 10,
                    'permission_id' => 8,
                ],
                [
                    'id' => 11,
                    'role_resource_id' => 9,
                    'permission_id' => 9,
                ],
                [
                    'id' => 12,
                    'role_resource_id' => 11,
                    'permission_id' => 10,
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
        Schema::dropIfExists('sys_roles_resources_permissions');
    }
}
