<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceEvaluationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_service_evaluation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_id')->comment('Id do atendimento ao contato');
            $table->double('ser_rating', 6, 2)->nullable()->comment('A nota do atendimento dada pelo contato');
            $table->timestamps();

            $table->foreign('service_id')
                    ->references('id')
                    ->on('cha_services')
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
        Schema::dropIfExists('cha_service_evaluation');
    }
}
