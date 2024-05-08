<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefaultColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_default_colors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('def_name')->comment('Cores padrões do sistema.');
            $table->string('def_status')->default('A');
            $table->timestamps();
        });


        // Insert default values
        DB::table('sys_default_colors')->insert(
            array(
                [
                    'id' => 1,
                    'def_name' => 'primary',
                    'def_status' => 'A',
                ],
                [
                    'id' => 2,
                    'def_name' => 'secondary',
                    'def_status' => 'A',
                ],
                [
                    'id' => 3,
                    'def_name' => 'success',
                    'def_status' => 'A',
                ],
                [
                    'id' => 4,
                    'def_name' => 'danger',
                    'def_status' => 'A',
                ],
                [
                    'id' => 5,
                    'def_name' => 'warning',
                    'def_status' => 'A',
                ],
                [
                    'id' => 6,
                    'def_name' => 'info',
                    'def_status' => 'A',
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
        Schema::dropIfExists('sys_default_colors');
    }
}
