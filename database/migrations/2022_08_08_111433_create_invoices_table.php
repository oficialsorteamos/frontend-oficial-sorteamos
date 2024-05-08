<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_payment_invoice_id')->comment('Id da fatura na API de pagamentos');
            $table->string('inv_url_invoice', 500)->nullable()->comment('Link que aponta para a fatura');
            $table->string('inv_month_year')->comment('Valor do parâmetro');
            $table->timestamp('inv_opening')->nullable()->comment('Data de abertura da fatura');
            $table->timestamp('inv_closing')->nullable()->comment('Data de fechamento da fatura');
            $table->timestamp('inv_due')->nullable()->comment('Data de vencimento da fatura');
            $table->unsignedBigInteger('status_id')->comment('Id de parâmetro financeiro');
            $table->timestamps();

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
        Schema::dropIfExists('fin_invoices');
    }
}
