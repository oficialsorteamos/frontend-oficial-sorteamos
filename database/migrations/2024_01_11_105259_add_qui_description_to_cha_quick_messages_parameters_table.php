<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuiDescriptionToChaQuickMessagesParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_quick_messages_parameters', function (Blueprint $table) {
            $table->string('qui_description')->nullable()->comment('Descrição de um botão em uma lista ou de outro parâmetro')->after('qui_content');
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
            $table->dropColumn('qui_description');
        });
    }
}
