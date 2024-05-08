<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('con_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sta_description')->comment('Descrição do status do contato. (Opt-In, Opt-Out, etc.)');
            $table->string('sta_status')->default('A')->comment('Situação dO status.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('con_status')->insert(
            array(
                [
                    'id' => 1,
                    'sta_description' => 'Opt-in',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 2,
                    'sta_description' => 'Opt-out',
                    'sta_status' => 'A',
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
        Schema::dropIfExists('con_status');
    }
}
