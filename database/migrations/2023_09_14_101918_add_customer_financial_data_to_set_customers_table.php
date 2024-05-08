<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerFinancialDataToSetCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('set_customers', function (Blueprint $table) {
            $table->string('com_finance_phone')->nullable()->comment('Telefone do financeiro da empresa')->after('com_mobile_phone');
            $table->string('com_finance_email')->nullable()->comment('E-mail do financeiro da empresa')->after('com_finance_phone');
            $table->string('com_responsible_name')->nullable()->comment('Nome do Responsável pela empresa')->after('com_finance_email');
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
            $table->dropColumn('com_finance_phone');
            $table->dropColumn('com_finance_email');
            $table->dropColumn('com_responsible_name');
        });
    }
}
