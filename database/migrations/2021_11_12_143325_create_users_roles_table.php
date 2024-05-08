<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_users_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que possui o perfil');
            $table->unsignedBigInteger('role_id')->comment('Id do perfil associado ao usuário');
            $table->timestamps();

            $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

            $table->foreign('role_id')
                    ->references('id')
                    ->on('sys_roles')
                    ->onDelete('cascade');
        });
        
        //Concede ao admin permissão de gestor
        DB::table('sys_users_roles')->insert(
            array(
                [
                    'id' => 1,
                    'user_id' => 2,
                    'role_id' => 1,
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
        Schema::dropIfExists('sys_users_roles');
    }
}
