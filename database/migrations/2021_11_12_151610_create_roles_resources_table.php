<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_roles_resources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id')->comment('Id do perfil cujo recurso será associado');
            $table->unsignedBigInteger('resource_id')->comment('Id do recurso associado ao perfil');
            $table->timestamps();

            $table->foreign('role_id')
                    ->references('id')
                    ->on('sys_roles')
                    ->onDelete('cascade');

            $table->foreign('resource_id')
                    ->references('id')
                    ->on('sys_resources')
                    ->onDelete('cascade');
        });

        // Insert default values
        DB::table('sys_roles_resources')->insert(
            array(
                [
                    'id' => 1,
                    'role_id' => 2, //Operador
                    'resource_id' => 1, //Chat
                ],
                [
                    'id' => 2,
                    'role_id' => 1, //Gestor
                    'resource_id' => 2, //Dashboard
                ],
                [
                    'id' => 3,
                    'role_id' => 1, //Gestor
                    'resource_id' => 3, //Chatbot
                ],
                [
                    'id' => 4,
                    'role_id' => 1, //Gestor
                    'resource_id' => 4, //Atendimento (service)
                ],
                [
                    'id' => 5,
                    'role_id' => 1, //Gestor
                    'resource_id' => 5, //Calendário
                ],
                [
                    'id' => 6,
                    'role_id' => 2, //Operador
                    'resource_id' => 5, //Calendário
                ],
                [
                    'id' => 7,
                    'role_id' => 1, //Gestor
                    'resource_id' => 6, //Contato
                ],
                [
                    'id' => 8,
                    'role_id' => 1, //Gestor
                    'resource_id' => 7, //Gerenciamento (management)
                ],
                [
                    'id' => 9,
                    'role_id' => 1, //Gestor
                    'resource_id' => 8, //Notificações
                ],
                [
                    'id' => 10,
                    'role_id' => 2, //Operador
                    'resource_id' => 8, //Notificações
                ],
                [
                    'id' => 11,
                    'role_id' => 1, //Gestor
                    'resource_id' => 9, //Campanhas
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
        Schema::dropIfExists('sys_roles_resources');
    }
}
