<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhiteLabelToSetCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('set_customers', function (Blueprint $table) {
            $table->boolean('com_white_label')->default(0)->comment('Sinaliza se a empresa é uma parceira White Label')->after('whatsapp_business_account_id');
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
            $table->dropColumn('com_white_label');
        });
    }
}
