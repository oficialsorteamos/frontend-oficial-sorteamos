<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFreeKeyToChaBlocActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_bloc_actions', function (Blueprint $table) {
            $table->string('blo_free_key')->nullable()->comment('Identifica se ação pode receber qualquer chave para prosseguir')->after('blo_key');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cha_bloc_actions', function (Blueprint $table) {
            $table->dropColumn('blo_free_key');
        });
    }
}
