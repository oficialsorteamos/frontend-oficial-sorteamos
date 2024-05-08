<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountryRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_country_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('country_id')->nullable()->comment('Id da do país onde a região se encontra');
            $table->string('cou_name')->comment('Nome da região do país (norte, nordeste, etc.)');
            $table->string('cou_status')->default('A')->comment('Status da região.');
            $table->timestamps();

            $table->foreign('country_id')
                ->references('id')
                ->on('sys_countries')
                ->onDelete('cascade');
        });

        // Insert default values
        DB::table('sys_country_regions')->insert(
            array(
                [
                    'id' => 1,
                    'country_id' => 1,
                    'cou_name' => 'Norte',
                    'cou_status' => 'A',
                ],
                [
                    'id' => 2,
                    'country_id' => 1,
                    'cou_name' => 'Nordeste',
                    'cou_status' => 'A',
                ],
                [
                    'id' => 3,
                    'country_id' => 1,
                    'cou_name' => 'Centro-Oeste',
                    'cou_status' => 'A',
                ],
                [
                    'id' => 4,
                    'country_id' => 1,
                    'cou_name' => 'Sudeste',
                    'cou_status' => 'A',
                ],
                [
                    'id' => 5,
                    'country_id' => 1,
                    'cou_name' => 'Sul',
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
        Schema::dropIfExists('sys_country_regions');
    }
}
