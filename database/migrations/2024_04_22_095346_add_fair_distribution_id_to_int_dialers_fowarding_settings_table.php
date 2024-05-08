<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFairDistributionIdToIntDialersFowardingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('int_dialers_fowarding_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('fair_distribution_id')->nullable()->comment('Id da configuração de encaminhamento')->after('department_id');

            $table->foreign('fair_distribution_id')
                ->references('id')
                ->on('cha_fair_distributions_setup')
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
            $table->dropColumn('fair_distribution_id');
        });
    }
}
