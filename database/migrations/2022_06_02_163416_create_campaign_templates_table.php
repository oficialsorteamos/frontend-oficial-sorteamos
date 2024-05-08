<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id')->comment('Id do campaign cujo template faz parte');
            $table->unsignedBigInteger('template_id')->comment('Id do template que está assciado a campanha');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que está gravando o template');
            $table->string('tem_status')->default('A')->comment('Status do template na campanha.');
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('cam_campaigns')
                ->onDelete('cascade');

            $table->foreign('template_id')
                ->references('id')
                ->on('cha_templates_messages')
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
        Schema::dropIfExists('cam_templates');
    }
}
