<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendMessageToIntDialersFowardingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('int_dialers_fowarding_settings', function (Blueprint $table) {
            $table->boolean('send_message')->default(1)->comment('Indica se a mensagem automática deverá ser enviada ou não')->after('department_id');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('int_dialers_fowarding_settings', function (Blueprint $table) {
            $table->dropColumn('send_message');
        });
    }
}
