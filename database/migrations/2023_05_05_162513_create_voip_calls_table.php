<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoipCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voi_calls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('Usuário que realizou ou atendeu a ligação');
            $table->unsignedBigInteger('contact_id')->comment('Contato que recebeu ou ligou para a empresa');
            $table->unsignedBigInteger('service_id')->nullable()->comment('Id do atendimento associado ao contato no momento da ligação');
            $table->unsignedBigInteger('extension_id')->comment('Ramal usado para realizar ou receber a ligação');
            $table->string('cal_phone_contact')->comment('Número do contato no momento da ligação');
            $table->string('cal_record_name')->comment('Nome do arquivo de áudio da gravação da ligação');
            $table->string('cal_call_time')->comment('Tempo da ligação em segundos');
            $table->boolean('cal_call_made')->comment('Flag que representa se a ligação foi realizada ou recebida. 1 presenta que foi realizada, null, recebida');
            $table->timestamp('cal_call_date')->nullable()->comment('Data e hora de realização da ligação');
            $table->string('cal_status')->default('A')->comment('Status da ligação');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voi_calls');
    }
}
