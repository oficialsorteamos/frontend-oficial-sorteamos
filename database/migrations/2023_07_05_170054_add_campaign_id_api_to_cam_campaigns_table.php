<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampaignIdApiToCamCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cam_campaigns', function (Blueprint $table) {
            $table->string('campaign_id_api')->nullable()->comment('Id da campanha criada na API')->after('campaign_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cam_campaigns', function (Blueprint $table) {
            $table->dropColumn('campaign_id_api');
        });
    }
}
