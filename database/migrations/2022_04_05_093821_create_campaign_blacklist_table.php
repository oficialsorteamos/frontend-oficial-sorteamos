<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignBlacklistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_blacklist', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id')->nullable()->comment('Id da campanha a qual o contato foi bloqueado. Dado para fim de histórico');
            $table->unsignedBigInteger('contact_id')->comment('Id do contato bloqueado para campanhas');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que colocou o contato na blacklist');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
                ->onDelete('cascade');
            
            $table->foreign('contact_id')
                ->references('id')
                ->on('con_contacts')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('cam_blacklist');
    }
}
