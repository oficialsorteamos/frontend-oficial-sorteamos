<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsHoldersInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_cards_holders_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('card_id')->comment('Id do cartão cuja as informações do titular está associada');
            $table->string('car_name')->comment('Nome do titular do cartão');
            $table->string('car_email')->comment('E-mail do titular do cartão');
            $table->string('car_cpf')->nullable()->comment('CPF do titular do cartão');
            $table->string('car_cnpj')->nullable()->comment('CNPJ do titular do cartão');
            $table->string('car_postal_code')->comment('CEP do titular do cartão');
            $table->string('car_address_number')->nullable()->comment('Número do endereço do titular do cartão');
            $table->string('car_phone')->comment('Número do telefone do titular do cartão');
            $table->timestamps();

            $table->foreign('card_id')
                ->references('id')
                ->on('fin_cards')
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
        Schema::dropIfExists('fin_cards_holders_info');
    }
}
