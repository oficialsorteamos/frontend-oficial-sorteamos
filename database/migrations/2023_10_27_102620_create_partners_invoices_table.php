<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_partners_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('partner_id')->comment('Id do parceiro associado a fatura');
            $table->string('api_payment_invoice_id')->comment('Id da fatura na API de pagamentos');
            $table->string('par_url_invoice', 500)->nullable()->comment('Link que aponta para a fatura');
            $table->string('par_month_year')->comment('Valor do parâmetro');
            $table->timestamp('par_opening')->nullable()->comment('Data de abertura da fatura');
            $table->timestamp('par_closing')->nullable()->comment('Data de fechamento da fatura');
            $table->timestamp('par_due')->nullable()->comment('Data de vencimento da fatura');
            $table->unsignedBigInteger('status_id')->comment('Id do status de pagamento da fatura');
            $table->string('par_status')->default('A')->comment('Status da fatura (Ativa ou Inativa)');
            $table->timestamps();

            $table->foreign('partner_id')
                ->references('id')
                ->on('adm_partners')
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
        Schema::dropIfExists('adm_partners_invoices');
    }
}
