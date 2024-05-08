<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewStatusCampaignToCamCampaignStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert default values
        DB::table('cam_campaign_status')->insert(
            array(
                [
                    'id' => 7,
                    'cam_description' => 'Validando',
                    'cam_status' => 'A',
                ],
                [
                    'id' => 8,
                    'cam_description' => 'Preparando Envio',
                    'cam_status' => 'A',
                ],
                [
                    'id' => 9,
                    'cam_description' => 'Campanha Inválida',
                    'cam_status' => 'A',
                ],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
