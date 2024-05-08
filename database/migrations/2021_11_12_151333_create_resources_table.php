<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_resources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('res_name')->comment('Nome do recurso presente no sistema (nome de um módulo, funcionalidade de algum módulo).');
            $table->string('res_description')->comment('Descrição do perfil.');
            $table->string('res_status')->default('A')->comment('Status do perfil.');
            $table->timestamps();
        });

        DB::table('sys_resources')->insert(
            array(
                [
                    'id' => 1,
                    'res_name' => 'chat',
                    'res_description' => 'Módulo responsável pela gestão do chat',
                ],
                [
                    'id' => 2,
                    'res_name' => 'dashboard',
                    'res_description' => 'Módulo responsável pela vizualização de gráficos, indicadores e afins',
                ],
                [
                    'id' => 3,
                    'res_name' => 'chatbot',
                    'res_description' => 'Módulo responsável pela gestão do chatbot',
                ],
                [
                    'id' => 4,
                    'res_name' => 'service',
                    'res_description' => 'Módulo responsável pela gestão dos atendimentos',
                ],
                [
                    'id' => 5,
                    'res_name' => 'calendar',
                    'res_description' => 'Módulo responsável pela gestão do calendário',
                ],
                [
                    'id' => 6,
                    'res_name' => 'contact',
                    'res_description' => 'Módulo responsável pela gestão dos contatos',
                ],
                [
                    'id' => 7,
                    'res_name' => 'management',
                    'res_description' => 'Módulo responsável pela gestão de departamentos, tags, channels, etc.',
                ],
                [
                    'id' => 8,
                    'res_name' => 'notification',
                    'res_description' => 'Módulo responsável pela apresentação das notificações',
                ],
                [
                    'id' => 9,
                    'res_name' => 'campaign',
                    'res_description' => 'Módulo responsável pela gestão de campanhas',
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
        Schema::dropIfExists('sys_resources');
    }
}
