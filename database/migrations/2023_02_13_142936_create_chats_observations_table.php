<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsObservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_chats_observations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('chat_id')->comment('Id do chat associado a observação');
            $table->unsignedBigInteger('service_id')->nullable()->comment('Id do atendimento no momento da obervação');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que realizou a observação');
            $table->unsignedBigInteger('department_id')->comment('Id do departamento que o usuário fazia parte no momento da observação');
            $table->string('cha_observation', 500)->nullable()->comment('Observação em relação ao chat');
            $table->string('cha_status')->default('A')->comment('Status da observação');
            $table->timestamps();

            $table->foreign('chat_id')
                ->references('id')
                ->on('cha_chats')
                ->onDelete('cascade');

            $table->foreign('service_id')
                ->references('id')
                ->on('cha_services')
                ->onDelete('cascade');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('cha_chats_observations');
    }
}
