<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id')->comment('Id da campanha que o canal irá operar');
            $table->unsignedBigInteger('operation_frequency_id')->nullable()->comment('Frequência de operação da campanha');
            $table->unsignedBigInteger('department_id')->nullable()->comment('Departamento para onde o contato será transferido, caso o contato responda a campanha');
            $table->string('set_status')->default('A')->comment('Status da associação canal/campanha');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
                ->onDelete('cascade');
            
            $table->foreign('operation_frequency_id')
                ->references('id')
                ->on('cam_operating_frequency')
                ->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')
                ->on('man_departments')
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
        Schema::dropIfExists('cam_settings');
    }
}
