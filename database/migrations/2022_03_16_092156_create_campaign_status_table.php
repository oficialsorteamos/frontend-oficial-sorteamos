<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_campaign_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cam_description')->comment('Descrição da campanha.');
            $table->string('cam_status')->default('A')->comment('Status.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cam_campaign_status')->insert(
            array(
                [
                    'id' => 1,
                    'cam_description' => 'Pausada',
                ],
                [
                    'id' => 2,
                    'cam_description' => 'Em Andamento',
                ],
                [
                    'id' => 3,
                    'cam_description' => 'Finalizada',
                ],
                [
                    'id' => 4,
                    'cam_description' => 'Não Iniciada',
                ],
                [
                    'id' => 5,
                    'cam_description' => 'Removida',
                ],
                [
                    'id' => 6,
                    'cam_description' => 'Pausada - Saldo Insuficiente',
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
        Schema::dropIfExists('cam_campaign_status');
    }
}
