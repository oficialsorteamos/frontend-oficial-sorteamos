<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_channels_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('channel_id')->comment('Id do canal que foi assinado');
            $table->unsignedBigInteger('card_id')->comment('Id do cartão associado a assinatura');
            $table->string('cha_observation')->nullable()->comment('Alguma observação sobre a assinatura. Pode ser um erro ao passar o cartão');
            $table->string('cha_status')->default('A')->comment('Status do parâmetro');
            $table->timestamps();

            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
                ->onDelete('cascade');
            
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
        Schema::dropIfExists('man_channels_subscriptions');
    }
}
