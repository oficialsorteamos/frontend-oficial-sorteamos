<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlocActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_bloc_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('main_bloc_id')->comment('Id do bloco cuja ação terá efeito');
            $table->unsignedBigInteger('action_id')->comment('Id da ação a ser tomada mediante a interação do contato');
            $table->unsignedBigInteger('destination_bloc_id')->nullable()->comment('Id do próximo bloco que será apresentado ao contato.');
            $table->unsignedBigInteger('department_id')->nullable()->comment('Id do departamento para onde o contato será transferido.');
            $table->string('blo_key')->comment('Valor que ao ser digitado pelo cliente, desencadeia em uma ação.');;
            $table->timestamps();

            $table->foreign('main_bloc_id')
                ->references('id')
                ->on('cha_chatbot_blocs')
                ->onDelete('cascade');
            
            $table->foreign('action_id')
                ->references('id')
                ->on('cha_type_actions')
                ->onDelete('cascade');
            
            $table->foreign('destination_bloc_id')
                ->references('id')
                ->on('cha_chatbot_blocs')
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
        Schema::dropIfExists('cha_bloc_actions');
    }
}
