<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceIdToAdmPartnersInvoicesFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adm_partners_invoices_fees', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->comment('Id da fatura que se refere a taxa')->after('id');

            $table->foreign('invoice_id')
                ->references('id')
                ->on('adm_partners_invoices')
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
        Schema::table('adm_partners_invoices_fees', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
        });
    }
}
