<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateIdToIntDialersFowardingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('int_dialers_fowarding_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id')->nullable()->comment('Id do template que será enviado')->after('dia_message');

            $table->foreign('template_id')
                ->references('id')
                ->on('cha_templates_messages')
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
        Schema::table('int_dialers_fowarding_settings', function (Blueprint $table) {
            $table->dropColumn('template_id');
        });
    }
}
