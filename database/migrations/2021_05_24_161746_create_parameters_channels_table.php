<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametersChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_parameters_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('channel_id')->comment('Id do canal');
            $table->unsignedBigInteger('type_parameter_channel_id')->comment('Id do parâmetro de canal');
            $table->string('par_value')->nullable()->comment('Valor do parâmetro');
            $table->unsignedBigInteger('department_id')->nullable()->comment('Departamento associado ao parâmetro');
            $table->string('par_status')->default('A')->comment('Status do parâmetro');
            $table->timestamps();

            $table->foreign('channel_id')
                    ->references('id')
                    ->on('man_channels')
                    ->onDelete('cascade');
            
            $table->foreign('type_parameter_channel_id')
                    ->references('id')
                    ->on('man_type_parameters_channels')
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
        Schema::dropIfExists('man_parameters_channels');
    }
}
