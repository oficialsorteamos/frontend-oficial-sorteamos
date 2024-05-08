<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_card_id')->comment('Id do tipo de cartão');
            $table->boolean('car_main')->nullable()->comment('Sinaliza se o cartão é o principal');
            $table->string('car_holder_name')->comment('Nome do titular do cartão');
            $table->string('car_number')->comment('Número do cartão');
            $table->string('car_due_month')->comment('Mês de vencimento do cartão');
            $table->string('car_due_year')->comment('Ano de vencimento do cartão');
            $table->string('car_token')->comment('Usado para transações sem necessidade dos dados do cartão');
            $table->string('car_status')->default('A')->comment('Status do plano');
            $table->timestamps();

            $table->foreign('type_card_id')
                ->references('id')
                ->on('fin_payment_methods')
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
        Schema::dropIfExists('fin_cards');
    }
}
