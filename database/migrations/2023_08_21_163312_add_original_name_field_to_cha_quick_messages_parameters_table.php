<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalNameFieldToChaQuickMessagesParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_quick_messages_parameters', function (Blueprint $table) {
            $table->string('qui_media_original_name', 200)->nullable()->comment('Nome original do arquivo no momento do upload')->after('qui_media_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cha_quick_messages_parameters', function (Blueprint $table) {
            $table->dropColumn('qui_media_original_name');
        });
    }
}
