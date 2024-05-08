<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFairDistributionIdToChaBlocActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cha_bloc_actions', function (Blueprint $table) {
            $table->unsignedBigInteger('fair_distribution_id')->nullable()->comment('Id da configuração de distribuição igualitária')->after('department_id');

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
        Schema::table('cha_bloc_actions', function (Blueprint $table) {
            $table->dropColumn('fair_distribution_id');
        });
    }
}
