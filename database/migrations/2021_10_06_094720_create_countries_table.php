<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cou_name')->comment('Nome do país');
            $table->string('cou_status')->default('A')->comment('Status da região.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('sys_countries')->insert(
            array(
                [
                    'id' => 1,
                    'cou_name' => 'Brasil',
                    'cou_status' => 'A',
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
        Schema::dropIfExists('sys_countries');
    }
}
