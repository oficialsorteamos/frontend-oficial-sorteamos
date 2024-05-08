<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_channels_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('channel_id')->comment('Id do canal assinado');
            $table->string('api_payment_charge_id')->nullable()->comment('Id da cobrança na API');
            $table->unsignedBigInteger('payment_method_id')->comment('Id do tipo de pagamento');
            $table->unsignedBigInteger('card_id')->nullable()->comment('Id do cartão utilizado no pagamento');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que assinou o canal');
            $table->decimal('cha_value', 10, 2)->comment('Valor do canal');
            $table->string('cha_status')->default('A')->comment('Status do pagamento');
            $table->timestamps();

            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
                ->onDelete('cascade');
            
            $table->foreign('payment_method_id')
                ->references('id')
                ->on('fin_payment_methods')
                ->onDelete('cascade');

            $table->foreign('card_id')
                ->references('id')
                ->on('fin_cards')
                ->onDelete('cascade');
            
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
        Schema::dropIfExists('man_channels_payments');
    }
}
