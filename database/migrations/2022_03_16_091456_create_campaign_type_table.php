<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_campaign_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cam_description')->comment('Descrição do tipo de campanha.');
            $table->string('cam_status')->default('A')->comment('Status.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cam_campaign_type')->insert(
            array(
                [
                    'id' => 1,
                    'cam_description' => 'Whatsapp',
                ],
                [
                    'id' => 2,
                    'cam_description' => 'SMS',
                ],
                [
                    'id' => 3,
                    'cam_description' => 'E-mail',
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
        Schema::dropIfExists('cam_campaign_type');
    }
}
