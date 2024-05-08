<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsDialersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('int_dialers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dia_name')->comment('Nome do discador');
            $table->string('dia_description')->comment('Descrição do discador');
            $table->string('dia_status')->default('A')->comment('Status do discador');
            $table->timestamps();
        });

        // Insert default values
        DB::table('int_dialers')->insert(
            array(
                [
                    'id' => 1,
                    'dia_name' => 'IPBoX',
                    'dia_description' => 'Empresa que fornece soluções de Call Center',
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
        Schema::dropIfExists('int_dialers');
    }
}
