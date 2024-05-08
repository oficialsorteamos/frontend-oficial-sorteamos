<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsOperatingHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_operating_hours', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id')->comment('Id da campanha que o canal irá operar');
            $table->unsignedBigInteger('day_week_id')->comment('Id do dia da semana que o canal irá operar');
            $table->string('ope_hr_start')->nullable()->comment('Hora do dia que uma campanha inicia');
            $table->string('ope_hr_end')->nullable()->comment('Hora do dia que uma campanha finaliza');
            $table->string('ope_status')->default('A')->comment('Status da associação canal/campanha');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
                ->onDelete('cascade');
            
            $table->foreign('day_week_id')
                ->references('id')
                ->on('cam_days_week_operations')
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
        Schema::dropIfExists('cam_operating_hours');
    }
}
