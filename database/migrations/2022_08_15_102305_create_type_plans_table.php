<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('set_type_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_name')->comment('Nome do tipo de plano');
            $table->string('typ_description')->comment('Descrição do tipo de plano');
            $table->string('typ_status')->default('A')->comment('Status do tipo de plano');
            $table->timestamps();

        });

        // Insert default values
        DB::table('set_type_plans')->insert(
            array(
                [
                    'id' => 1,
                    'typ_name' => 'Plano Básico',
                    'typ_description' => 'Plano inicial de contratação da plataforma',
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
        Schema::dropIfExists('set_type_plans');
    }
}
