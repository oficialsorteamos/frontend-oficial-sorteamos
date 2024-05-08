<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('resource_id')->comment('Id do recurso cuja a permissão faz parte.');
            $table->string('per_name')->comment('Nome da permissão.');
            $table->string('per_description')->comment('Descrição da permissão.');
            $table->string('per_status')->default('A')->comment('Status da permissão.');
            $table->timestamps();

            $table->foreign('resource_id')
                    ->references('id')
                    ->on('sys_resources')
                    ->onDelete('cascade');
        });

        // Insert default values
        DB::table('sys_permissions')->insert(
            array(
                [
                    'id' => 1,
                    'resource_id' => 1,
                    'per_name' => 'menu_chat',
                    'per_description' => 'Acesso ao menu do chat',
                ],
                [
                    'id' => 2,
                    'resource_id' => 2,
                    'per_name' => 'menu_dashboard',
                    'per_description' => 'Acesso ao menu do dashboard',
                ],
                [
                    'id' => 3,
                    'resource_id' => 3,
                    'per_name' => 'menu_chatbot',
                    'per_description' => 'Acesso ao menu do chatbot',
                ],
                [
                    'id' => 4,
                    'resource_id' => 4,
                    'per_name' => 'menu_service',
                    'per_description' => 'Acesso ao menu de atendimentos',
                ],
                [
                    'id' => 5,
                    'resource_id' => 5,
                    'per_name' => 'menu_calendar',
                    'per_description' => 'Acesso ao menu do calendário',
                ],
                [
                    'id' => 6,
                    'resource_id' => 6,
                    'per_name' => 'menu_contact',
                    'per_description' => 'Acesso ao menu de contatos',
                ],
                [
                    'id' => 7,
                    'resource_id' => 7,
                    'per_name' => 'menu_management',
                    'per_description' => 'Acesso ao menu de gerenciamento de channels, tags, departments. etc.',
                ],
                [
                    'id' => 8,
                    'resource_id' => 8,
                    'per_name' => 'menu_notification',
                    'per_description' => 'Acesso ao menu de notificações',
                ],
                [
                    'id' => 9,
                    'resource_id' => 8,
                    'per_name' => 'alert_notification',
                    'per_description' => 'Permite enviar notificações em tempo real para os operadores',
                ],
                [
                    'id' => 10,
                    'resource_id' => 9,
                    'per_name' => 'menu_campaign',
                    'per_description' => 'Permite gerir campanhas',
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
        Schema::dropIfExists('sys_permissions');
    }
}
