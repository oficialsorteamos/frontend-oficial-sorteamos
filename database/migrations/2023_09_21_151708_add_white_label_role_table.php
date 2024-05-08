<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhiteLabelRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Cria o Perfil
        DB::table('sys_roles')->insert(
            array(
                [
                    'id' => 4,
                    'rol_name' => 'White Label',
                    'rol_description' => 'Perfil com as permissões referentes à gestão de um parceiro White Label',
                ],
            )
        );

        //
        DB::table('sys_resources')->insert(
            array(
                [
                    'id' => 13,
                    'res_name' => 'administration',
                    'res_description' => 'Módulo responsável pela gestão de empresas, parceiros, etc.',
                ],
            )
        );

        // Insert default values
        DB::table('sys_roles_resources')->insert(
            array(
                [
                    'id' => 25,
                    'role_id' => 3, //Administrador
                    'resource_id' => 13, //Administração (Gestão)
                ],
                [
                    'id' => 26,
                    'role_id' => 4, //White Label
                    'resource_id' => 13, //Administração (Gestão)
                ],
                
            )
        );

        // Insert default values
        DB::table('sys_permissions')->insert(
            array(
                [
                    'id' => 18,
                    'resource_id' => 13,
                    'per_name' => 'menu_administration_notification',
                    'per_description' => 'Acesso ao menu de notificações',
                ],
                [
                    'id' => 19,
                    'resource_id' => 13,
                    'per_name' => 'menu_administration_company',
                    'per_description' => 'Permite enviar notificações em tempo real para os operadores',
                ],
                [
                    'id' => 20,
                    'resource_id' => 13,
                    'per_name' => 'menu_administration_partner',
                    'per_description' => 'Permite gerir campanhas',
                ],
            )
        );

        DB::table('sys_roles_resources_permissions')->insert(
            array(
                [
                    'id' => 34,
                    'role_resource_id' => 25, //Administrator/Administration
                    'permission_id' => 18,
                ],
                [
                    'id' => 35,
                    'role_resource_id' => 25, //Administrator/Administration
                    'permission_id' => 19,
                ],
                [
                    'id' => 36,
                    'role_resource_id' => 25, //Administrator/Administration
                    'permission_id' => 20,
                ],
                [
                    'id' => 37,
                    'role_resource_id' => 26, //Administrator/Administration
                    'permission_id' => 18,
                ],
                [
                    'id' => 38,
                    'role_resource_id' => 26, //Administrator/Administration
                    'permission_id' => 19,
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
