<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_companies_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->comment('Id da empresa a qual os detalhes pertencem');
            $table->integer('com_total_official_channels')->default(0)->comment('Total de canais oficiais');
            $table->integer('com_total_unofficial_channels')->default(0)->comment('Total de canais não oficiais');
            $table->integer('com_total_users')->default(0)->comment('Total de usuários de sistema cadastrados');
            $table->integer('com_total_connected_channels')->default(0)->comment('Total de canais conectados');
            $table->integer('com_total_messages_sent')->default(0)->comment('Total de mensagens enviadas');
            $table->integer('com_total_messages_received')->default(0)->comment('Total de mensagens recebidas');
            $table->integer('com_total_services')->default(0)->comment('Total de atendimentos');
            $table->boolean('com_total_overdue_invoices')->default(0)->comment('Total de contas vencidas');
            $table->decimal('com_total_overdue_amount', 10, 2)->comment('Valor total vencido somando todas as faturas vencidas');
            $table->boolean('com_due_date_invoice')->nullable()->comment('Dia de vencimento da fatura');
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('adm_companies')
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
        Schema::dropIfExists('adm_companies_details');
    }
}
