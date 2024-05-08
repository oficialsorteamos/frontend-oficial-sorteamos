<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmInstancesConnectionStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_instances_connection_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ins_description')->comment('Descrição do status de conexão de uma instância');
            $table->string('ins_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('adm_instances_connection_status')->insert(
            array(
                [
                    'id' => 1,
                    'ins_description' => 'Conectada',
                    'ins_status' => 'A',
                ],
                [
                    'id' => 2,
                    'ins_description' => 'Desconectada',
                    'ins_status' => 'A',
                ],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adm_instances_connection_status');
    }
}
