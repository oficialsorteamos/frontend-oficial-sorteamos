<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApisCommunicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_apis_communication', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_name')->comment('Nome da API');
            $table->string('api_description')->comment('Descrição da API');
            $table->boolean('api_official')->comment('Seta se a API  é oficial ou não');
            $table->string('api_status')->default('A')->comment('Status do tipo de API');
            $table->timestamps();

        });

        // Insert default values
        DB::table('sys_apis_communication')->insert(
            array(
                [
                    'api_name' => 'Gupshup',
                    'api_description' => 'API oficial para comunicação',
                    'api_official' => true,
                    'api_status' => 'A',
                ],
                [
                    'api_name' => 'WPPConnect',
                    'api_description' => 'API não oficial para comunicação',
                    'api_official' => false,
                    'api_status' => 'A',
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
        Schema::dropIfExists('sys_apis_communication');
    }
}
