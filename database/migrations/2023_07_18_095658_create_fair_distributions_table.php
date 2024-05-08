<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFairDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_fair_distributions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que fará parte da distribuição igualitária');
            $table->integer('fai_total_forwarding')->default(0)->comment('Total de encaminhamentos realizados para esse usuário');
            $table->timestamp('fai_dt_last_forwarding')->nullable()->comment('Data do último encaminhamento de atendimento para esse usuário');
            $table->string('fai_status')->default('A')->comment('Status da distribuição igualitária');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('cha_fair_distributions');
    }
}
