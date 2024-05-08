<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_parameter_id')->comment('Id de parâmetro financeiro');
            $table->string('par_value')->nullable()->comment('Valor do parâmetro');
            $table->boolean('par_proportional_charge')->nullable()->comment('Representa se a cobrança será proporcional ou não');
            $table->string('par_status')->default('A')->comment('Status do parâmetro');
            $table->timestamps();

            $table->foreign('type_parameter_id')
                ->references('id')
                ->on('fin_type_parameters')
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
        Schema::dropIfExists('fin_parameters');
    }
}
