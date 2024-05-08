<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsVoipSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('int_voip_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('voip_id')->comment('Id da empresa de voip cuja configuração faz parte');
            $table->string('voi_user')->comment('Usuário usado para logar no tronco');
            $table->string('voi_secret')->comment('Segredo usado para logar no tronco');
            $table->string('voi_status')->default('A')->comment('Status da configuração');
            $table->timestamps();

            $table->foreign('voip_id')
                ->references('id')
                ->on('int_voip')
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
        Schema::dropIfExists('int_voip_settings');
    }
}
