<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_credits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_payment_credit_id')->comment('Id da cobrança gerada para pagamento');
            $table->unsignedBigInteger('card_id')->nullable()->comment('Id do cartão utilizado para pagamento');
            $table->unsignedBigInteger('payment_method_id')->comment('Id do método de pagamento utilizado');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Id do usuário que inseriu o crédito na plataforma');
            $table->string('url_external_checkout', 500)->nullable()->comment('Link para pagamento através da url do gateway de pagamento');
            $table->timestamp('cre_due')->nullable()->comment('Data de vencimento da cobrança');
            $table->decimal('cre_value', 10, 2)->comment('Valor do crédito');
            $table->unsignedBigInteger('status_id')->comment('Id de status de pagamento');
            $table->timestamps();

            $table->foreign('card_id')
                ->references('id')
                ->on('fin_cards')
                ->onDelete('cascade');
            
            $table->foreign('payment_method_id')
                ->references('id')
                ->on('fin_payment_methods')
                ->onDelete('cascade');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('status_id')
                ->references('id')
                ->on('fin_status_payments')
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
        Schema::dropIfExists('fin_credits');
    }
}
