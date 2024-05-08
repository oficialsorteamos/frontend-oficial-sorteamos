<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_payment_methods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pay_name')->comment('Nome do tipo de plano');
            $table->string('pay_status')->default('A')->comment('Status do tipo de plano');
            $table->timestamps();

        });

        // Insert default values
        DB::table('fin_payment_methods')->insert(
            array(
                [
                    'id' => 1,
                    'pay_name' => 'Cartão de Crédito',
                ],
                [
                    'id' => 2,
                    'pay_name' => 'Cartão de Débito',
                ],
                [
                    'id' => 3,
                    'pay_name' => 'PIX',
                ],
                [
                    'id' => 4,
                    'pay_name' => 'Boleto',
                ],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fin_payment_methods');
    }
}
