<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoipExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voi_extensions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('voip_id')->comment('Id da empresa de voip cujo ramal faz parte');
            $table->string('name')->comment('Nome do ramal');
            $table->string('description')->nullable()->comment('Nome do ramal');
            $table->string('context')->nullable()->comment('Contexto a ser seguido na chamada');
            $table->string('secret')->nullable()->comment('Senha para conectar o ramal');
            $table->string('host')->comment('IP ou hostname do dispositivo associado ao ramal');
            $table->string('fromdomain')->nullable();
            $table->string('nat')->nullable();
            $table->string('type');
            $table->string('callerid')->nullable();
            $table->string('qualify')->nullable();
            $table->string('rtptimeout')->nullable();
            $table->string('username')->nullable();
            $table->string('md5secret')->nullable();
            $table->integer('lastms')->nullable();
            $table->integer('regseconds')->nullable();
            $table->string('useragent')->nullable();
            $table->string('ipaddr')->nullable();
            $table->integer('port')->nullable();
            $table->string('fullcontact')->nullable();
            $table->string('regserver')->nullable();
            $table->string('deny')->nullable();
            $table->text('disallow')->nullable();
            $table->string('allow')->nullable();
            $table->text('insecure')->nullable();
            $table->text('fromuser')->nullable();
            $table->string('permit')->nullable();
            $table->string('callbackextension')->nullable();
            $table->string('dtmfmode')->nullable();
            $table->string('ip')->nullable();
            $table->string('status')->nullable();
            $table->string('defaultuser')->nullable();
            $table->integer('call-limit')->nullable();
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
        Schema::dropIfExists('voi_extensions');
    }
}
