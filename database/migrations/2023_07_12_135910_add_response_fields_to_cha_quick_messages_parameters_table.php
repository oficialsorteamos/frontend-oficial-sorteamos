<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponseFieldsToChaQuickMessagesParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_quick_messages_parameters', function (Blueprint $table) {
            $table->string('qui_positives_responses', 400)->nullable()->comment('Respostas positivas aceitas durante ligação via WhatsApp')->after('qui_media_name');
            $table->string('qui_negatives_responses', 400)->nullable()->comment('Respostas negativas aceitas durante ligação via WhatsApp')->after('qui_positives_responses');
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
            $table->dropColumn('qui_positives_responses');
            $table->dropColumn('qui_negatives_responses');
        });
    }
}
