<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_genders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('gen_description');
            $table->string('gen_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('sys_genders')->insert(
            array(
                [
                    'id' => 1,
                    'gen_description' => 'Masculino',
                ],
                [
                    'id' => 2,
                    'gen_description' => 'Feminino',
                ],
                [
                    'id' => 3,
                    'gen_description' => 'Indefinido',
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
        Schema::dropIfExists('sys_genders');
    }
}
