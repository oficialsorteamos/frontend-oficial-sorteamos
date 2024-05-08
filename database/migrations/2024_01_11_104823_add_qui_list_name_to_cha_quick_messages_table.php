<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuiListNameToChaQuickMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_quick_messages', function (Blueprint $table) {
            $table->string('qui_list_name')->nullable()->comment('Nome da lista de botões')->after('qui_content');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cha_quick_messages', function (Blueprint $table) {
            $table->dropColumn('qui_list_name');
        });
    }
}
