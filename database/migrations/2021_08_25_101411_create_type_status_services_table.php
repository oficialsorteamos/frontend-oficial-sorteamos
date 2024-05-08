<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeStatusServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_type_status_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do status do atendimento (aberto, pedente, fechado).');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_type_status_services')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Aberto',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Pendente',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Fechado',
                ],
                [
                    'id' => 4,
                    'typ_description' => 'Avaliação',
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
        Schema::dropIfExists('cha_type_status_services');
    }
}
