<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_type_id')->comment('Tipo de Campanha (Whatsapp, SMS, E-mail, etc.)');
            $table->string('cam_name')->comment('Nome da campanha.');
            $table->string('cam_description')->comment('Descrição da campanha.');
            $table->unsignedBigInteger('status_id')->default(4)->comment('Id do status da campanha');
            $table->timestamps();

            $table->foreign('campaign_type_id')
                ->references('id')
                ->on('cam_campaign_type')
                ->onDelete('cascade');

            $table->foreign('status_id')
                ->references('id')
                ->on('cam_campaign_status')
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
        Schema::dropIfExists('cam_campaigns');
    }
}
