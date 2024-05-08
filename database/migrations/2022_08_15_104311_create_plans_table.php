<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('set_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_plan_id')->comment('Id do tipo de plano');
            $table->integer('pla_total_user')->comment('Total de usuários que fazem parte do plano');
            $table->integer('pla_total_official_channel')->comment('Total de canais oficiais que fazem parte do plano');
            $table->integer('pla_total_unofficial_channel')->comment('Total de canais oficiais que fazem parte do plano');
            $table->decimal('pla_value', 10, 2)->comment('Valor do plano');
            $table->string('pla_status')->default('A')->comment('Status do plano');
            $table->timestamps();

            $table->foreign('type_plan_id')
                ->references('id')
                ->on('set_type_plans')
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
        Schema::dropIfExists('set_plans');
    }
}
