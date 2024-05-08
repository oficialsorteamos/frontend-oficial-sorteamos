<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_costs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_cost_id')->comment('Id do tipo de custo');
            $table->unsignedBigInteger('mailing_id')->nullable()->comment('Id do contato no mailing com os dados da mensagem que gerou o custo');
            $table->decimal('cos_value', 10, 2)->comment('Valor do custo');
            $table->string('cos_status')->default('A')->comment('Status do custo');
            $table->timestamps();

            $table->foreign('type_cost_id')
                ->references('id')
                ->on('fin_type_costs')
                ->onDelete('cascade');
            
            $table->foreign('mailing_id')
                ->references('id')
                ->on('cam_mailings')
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
        Schema::dropIfExists('fin_costs');
    }
}
