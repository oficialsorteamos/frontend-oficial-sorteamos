<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_companies_charges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->comment('Id da empresa a qual a cobrança se refere');
            $table->unsignedBigInteger('type_parameter_id')->comment('Id de parâmetro financeiro');
            $table->string('com_value')->nullable()->comment('Valor do parâmetro de cobrança');
            $table->boolean('com_proportional_charge')->nullable()->comment('Representa se a cobrança será proporcional ou não');
            $table->string('com_status')->default('A')->comment('Status da cobrança');
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('adm_companies')
                ->onDelete('cascade');

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
        Schema::dropIfExists('adm_companies_charges');
    }
}
