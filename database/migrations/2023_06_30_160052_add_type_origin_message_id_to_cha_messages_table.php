<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeOriginMessageIdToChaMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('type_origin_message_id')->nullable()->comment('Id da origem da mensagem (whatsapp, SMS, etc.)')->after('quick_message_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cha_messages', function (Blueprint $table) {
            $table->dropColumn('type_origin_message_id');
        });
    }
}
