<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersInvoicesFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_partners_invoices_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->comment('Id da fatura');
            $table->unsignedBigInteger('type_fee_id')->comment('Tipo da taxa cobrada na fatura');
            $table->integer('par_total_resource')->nullable()->comment('Total de um determinado recurso (mensalidade, usuário, canal, etc.)');
            $table->decimal('par_unit_value_fee', 10, 2)->comment('Valor da taxa cobrada por cada unidade');
            $table->decimal('par_total_value_fee', 10, 2)->comment('Valor total da taxa que pode o valor cheio ou proporcional dependendo da data de adição do recurso');
            $table->string('par_status')->comment('Status do valor a ser cobrado na fatura');
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('id')
                ->on('adm_companies_invoices')
                ->onDelete('cascade');
            
            $table->foreign('type_fee_id')
                ->references('id')
                ->on('fin_type_fees')
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
        Schema::dropIfExists('adm_partners_invoices_fees');
    }
}
