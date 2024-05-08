<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('rol_name')->comment('Nome do perfil.');
            $table->string('rol_description')->comment('Descrição do perfil.');
            $table->string('rol_status')->default('A')->comment('Status do perfil.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('sys_roles')->insert(
            array(
                [
                    'id' => 1,
                    'rol_name' => 'Gestor',
                    'rol_description' => 'Perfil com as permissões referentes à gestão do sistema',
                ],
                [
                    'id' => 2,
                    'rol_name' => 'Operador',
                    'rol_description' => 'Perfil com as permissões referentes à operação do atendimento ao cliente',
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
        Schema::dropIfExists('sys_roles');
    }
}
