<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhonesChannelsReceivedSendMessageToChaMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_messages', function (Blueprint $table) {
            $table->string('mes_phone_channel_received_message')->nullable()->comment('Número do canal para onde o contato mandou a mensagem')->after('type_message_chat_id');
            $table->string('mes_phone_channel_sent_message')->nullable()->comment('Número do canal usado pelo usuário do sistema para enviar a mensagem')->after('mes_phone_channel_received_message');
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
            $table->dropColumn('mes_phone_channel_received_message');
            $table->dropColumn('mes_phone_channel_sent_message');
        });
    }
}
