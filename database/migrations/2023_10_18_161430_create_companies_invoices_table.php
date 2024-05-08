<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_companies_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->comment('Id da fatura na API de pagamentos');
            $table->string('invoice_id')->comment('Id da fatura no ambiente da empresa');
            $table->string('api_payment_invoice_id')->comment('Id da fatura na API de pagamentos');
            $table->string('com_url_invoice', 500)->nullable()->comment('Link que aponta para a fatura');
            $table->string('com_month_year')->comment('Valor do parâmetro');
            $table->timestamp('com_opening')->nullable()->comment('Data de abertura da fatura');
            $table->timestamp('com_closing')->nullable()->comment('Data de fechamento da fatura');
            $table->timestamp('com_due')->nullable()->comment('Data de vencimento da fatura');
            $table->unsignedBigInteger('status_id')->comment('Id do status de pagamento da fatura');
            $table->string('com_status')->default('A')->comment('Status da fatura (Ativa ou Inativa)');
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('adm_companies')
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
        Schema::dropIfExists('adm_companies_invoices');
    }
}
