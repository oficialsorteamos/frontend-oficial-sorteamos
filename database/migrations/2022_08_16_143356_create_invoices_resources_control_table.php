<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesResourcesControlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_invoices_resources_control', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_invoice_resource_id')->comment('Id do tipo de recurso que a cota se refere');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Id do usuário que gerou o aumento da cota do mês para a fatura');
            $table->unsignedBigInteger('channel_id')->nullable()->comment('Id do canal que gerou o aumento da cota do mês para a fatura');
            $table->timestamp('inv_opening')->nullable()->comment('Data de abertura da fatura');
            $table->timestamps();

            $table->foreign('type_invoice_resource_id')
                ->references('id')
                ->on('fin_type_invoices_resources')
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
        Schema::dropIfExists('fin_invoices_resources_control');
    }
}
