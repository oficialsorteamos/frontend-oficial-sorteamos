<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_addresses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('Id do contato ou usuário (operador, gestor, etc.) cujo endereço se refere');
            $table->unsignedBigInteger('type_user_id')->comment('Tipo de usuário que enviou a mensagem. (contato, operador)');
            $table->string('add_zip_code')->comment('CEP do endereço.');
            $table->string('add_street')->comment('Nome da rua.');
            $table->string('add_district')->comment('Nome da bairro.');
            $table->string('add_number')->nullable()->comment('Número da casa.');
            $table->string('add_address_complement')->nullable()->comment('Casa, apartamento.');
            $table->string('add_city')->comment('Nome da cidade.');
            $table->unsignedBigInteger('state_id')->comment('Id do estado.');
            $table->unsignedBigInteger('country_id')->comment('Id do País.');
            $table->timestamps();

            $table->foreign('type_user_id')
                ->references('id')
                ->on('sys_type_users')
                ->onDelete('cascade');
            
            $table->foreign('state_id')
                ->references('id')
                ->on('sys_states_country')
                ->onDelete('cascade');
            
            $table->foreign('country_id')
                ->references('id')
                ->on('sys_countries')
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
        Schema::dropIfExists('sys_addresses');
    }
}
