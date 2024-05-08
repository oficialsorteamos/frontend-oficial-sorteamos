<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesLocationsParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_locations_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome do local onde o parâmetro irá ser inserido');
            $table->string('tem_status')->default('A')->comment('Situação do parâmetro de um template');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_templates_messages_locations_parameters')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Header',
                    'tem_status' => 'A',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'Body',
                    'tem_status' => 'A',
                ],
                [
                    'id' => 3,
                    'tem_name' => 'Footer',
                    'tem_status' => 'A',
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
        Schema::dropIfExists('cha_templates_messages_locations_parameters');
    }
}
