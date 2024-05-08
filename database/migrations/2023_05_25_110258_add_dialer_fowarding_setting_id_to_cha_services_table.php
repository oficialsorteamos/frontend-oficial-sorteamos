<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDialerFowardingSettingIdToChaServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_services', function (Blueprint $table) {
            $table->unsignedBigInteger('dialer_fowarding_setting_id')->nullable()->comment('Id da configuração de encaminhamento de um discador')->after('type_status_service_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cha_services', function (Blueprint $table) {
            $table->dropColumn('dialer_fowarding_setting_id');
        });
    }
}
