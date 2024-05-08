<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToAdmPartnersInvoicesFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adm_partners_invoices_fees', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->comment('Id da empresa que se refere a taxa')->after('invoice_id');

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
        Schema::table('adm_partners_invoices_fees', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
