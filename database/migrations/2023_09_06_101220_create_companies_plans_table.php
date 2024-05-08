<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_companies_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->comment('Id da empresa a qual o plano pertence');
            $table->integer('com_total_users')->default(0)->comment('Total de usuários do plano');
            $table->integer('com_total_official_channels')->default(0)->comment('Total de canais oficiais do plano');
            $table->integer ('com_total_unofficial_channels')->default(0)->comment('Total de canais não oficiais do plano');
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('adm_companies')
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
        Schema::dropIfExists('adm_companies_plans');
    }
}
