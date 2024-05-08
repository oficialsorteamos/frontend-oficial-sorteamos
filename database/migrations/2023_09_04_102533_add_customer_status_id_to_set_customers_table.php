<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerStatusIdToSetCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('set_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->default(1)->comment('Status de uma empresa')->after('whatsapp_business_account_id');

            $table->foreign('status_id')
                ->references('id')
                ->on('adm_type_company_status')
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
        Schema::table('set_customers', function (Blueprint $table) {
            $table->dropColumn('status_id');
        });
    }
}
