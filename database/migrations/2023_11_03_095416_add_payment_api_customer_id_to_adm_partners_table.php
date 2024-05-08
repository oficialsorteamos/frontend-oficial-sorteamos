<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentApiCustomerIdToAdmPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adm_partners', function (Blueprint $table) {
            $table->string('payment_api_customer_id')->nullable()->comment('Id do parceiro na api de pagamentos')->after('type_partner_id');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adm_partners', function (Blueprint $table) {
            $table->dropColumn('payment_api_customer_id');
        });
    }
}
