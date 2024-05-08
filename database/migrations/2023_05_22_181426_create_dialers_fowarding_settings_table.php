<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDialersFowardingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('int_dialers_fowarding_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dialer_id')->comment('Id do discador');
            $table->unsignedBigInteger('channel_id')->comment('Id do canal que será terá seu encaminhamento configurado');
            $table->unsignedBigInteger('chatbot_id')->nullable()->comment('Id do chatbot que será usado para autoatendimento');
            $table->unsignedBigInteger('department_id')->comment('Id do departamento para onde o atendimento será transferido caso não tenha chatbot associado');
            $table->text('dia_message')->comment('Mensagem que será enviada para o contato');
            $table->string('dia_status')->default('A')->comment('Status da configuração');
            $table->timestamps();

            $table->foreign('dialer_id')
                ->references('id')
                ->on('int_dialers')
                ->onDelete('cascade');
            
            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
                ->onDelete('cascade');

            $table->foreign('chatbot_id')
                ->references('id')
                ->on('cha_chatbots')
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
        Schema::dropIfExists('int_dialers_fowarding_settings');
    }
}
