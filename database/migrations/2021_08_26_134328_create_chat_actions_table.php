<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_id')->comment('Id do atendimento que a ação se refere.');
            $table->unsignedBigInteger('chat_id')->comment('Chat em que a ação está associada.');
            $table->unsignedBigInteger('type_action_id')->comment('Tipo de ação realizada. (Transferência, rastreamento, etc.)');
            $table->unsignedBigInteger('department_id')->nullable()->comment('Departamento para onde o cliente foi transferido');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Usuário que iniciou o atendimento');
            $table->unsignedBigInteger('department_id_sender')->nullable()->comment('Departamento que transferiu o cliente');
            $table->unsignedBigInteger('user_id_sender')->nullable()->comment('Usuário que está transferindo o atendimento');
            $table->timestamps();

            $table->foreign('service_id')
                ->references('id')
                ->on('cha_services')
                ->onDelete('cascade');

            $table->foreign('chat_id')
                ->references('id')
                ->on('cha_chats')
                ->onDelete('cascade');

            $table->foreign('type_action_id')
                ->references('id')
                ->on('cha_type_actions')
                ->onDelete('cascade');
            
            $table->foreign('department_id')
                ->references('id')
                ->on('man_departments')
                ->onDelete('cascade');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('department_id_sender')
                ->references('id')
                ->on('man_departments')
                ->onDelete('cascade');
            
            $table->foreign('user_id_sender')
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
        Schema::dropIfExists('cha_actions');
    }
}
