<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_invoices_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->comment('Id da fatura');
            $table->unsignedBigInteger('type_fee_id')->comment('Tipo da taxa cobrada na fatura');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Id do usuário cuja a adição do mesmo gerou a cobrança na fatura');
            $table->unsignedBigInteger('channel_id')->nullable()->comment('Id do canal cuja a adição do mesmo gerou a cobrança');
            $table->decimal('inv_unit_value_fee', 10, 2)->comment('Valor da taxa cobrada por cada unidade');
            $table->decimal('inv_total_value_fee', 10, 2)->comment('Valor total da taxa que pode o valor cheio ou proporcional dependendo da data de adição do recurso');
            $table->string('inv_status')->default('A')->comment('Status do valor a ser cobrado na fatura');
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('fin_invoices')
                ->onDelete('cascade');
            
            $table->foreign('type_fee_id')
                ->references('id')
                ->on('fin_type_fees')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
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
        Schema::dropIfExists('fin_invoices_fees');
    }
}
