<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeBlocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_type_blocs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de bloco (padrão. primeiro bloco, último bloco, avaliação).');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_type_blocs')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Padrão',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Primeiro Bloco',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Último Bloco',
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
        Schema::dropIfExists('cha_type_blocs');
    }
}
