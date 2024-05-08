<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFairDistributionsSetupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_fair_distributions_setup', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fai_name')->comment('Nome dado para a configuração de distribuição igualitária');
            $table->string('fai_description')->comment('Descrição da configuração de distribuição igualitária');
            $table->boolean('fai_main')->nullable()->comment('Configuração de distribuição igualitária principal');
            $table->string('fai_status')->default('A')->comment('Status da configuração de distribuição igualitária');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cha_fair_distributions_setup');
    }
}
