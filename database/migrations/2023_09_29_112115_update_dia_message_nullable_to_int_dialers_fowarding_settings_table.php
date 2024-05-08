<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDiaMessageNullableToIntDialersFowardingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('int_dialers_fowarding_settings', function (Blueprint $table) {
            $table->text('dia_message')->nullable()->change();
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
            $table->text('dia_message')->nullable(false)->change();
        });
    }
}
