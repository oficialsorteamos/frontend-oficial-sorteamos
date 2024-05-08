<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_type_costs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do custo');
            $table->string('typ_status')->default('A')->comment('Status do tipo de custo');
            $table->timestamps();

        });

        // Insert default values
        DB::table('fin_type_costs')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Envio de Mensagem de Campanha por Whatsapp',
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
        Schema::dropIfExists('fin_type_costs');
    }
}
