<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmInstancesStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_instances_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ins_description')->comment('Descrição do status de uma instância');
            $table->string('ins_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('adm_instances_status')->insert(
            array(
                [
                    'id' => 1,
                    'ins_description' => 'Ativa',
                    'ins_status' => 'A',
                ],
                [
                    'id' => 2,
                    'ins_description' => 'Bloqueada',
                    'ins_status' => 'A',
                ],
                [
                    'id' => 3,
                    'ins_description' => 'Removida',
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
        Schema::dropIfExists('adm_instances_status');
    }
}
