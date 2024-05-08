<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersPaymentsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_partners_payments_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->comment('Id da fatura paga em que ordem de pagamento foi gerada');
            $table->decimal('par_value_order', 10, 2)->comment('Valor da ordem de pagamento');
            $table->string('par_link_payment_receipt')->nullable()->comment('Link que aponta para o comprovante da ordem de pagamento');
            $table->unsignedBigInteger('status_id')->default(1)->comment('Status da ordem de pagamento');
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('adm_companies_invoices')
                ->onDelete('cascade');

            $table->foreign('status_id')
                ->references('id')
                ->on('adm_partners_payments_orders_status')
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
        Schema::dropIfExists('adm_partners_payments_orders');
    }
}
