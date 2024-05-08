<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDddStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_ddd_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('state_id')->comment('Id do Estado associado ao DDD');
            $table->string('ddd_number')->comment('Número do DDD');
            $table->string('ddd_status')->default('A')->comment('Status do DDD');
            $table->timestamps();

            $table->foreign('state_id')
                ->references('id')
                ->on('sys_states_country')
                ->onDelete('cascade');
        });

        DB::table('sys_ddd_states')->insert(
            array(
                [
                    //'id' => 1,
                    'state_id' => 23,
                    'ddd_number' => '11',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 23,
                    'ddd_number' => '12',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 23,
                    'ddd_number' => '13',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 23,
                    'ddd_number' => '14',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 23,
                    'ddd_number' => '15',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 23,
                    'ddd_number' => '16',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 23,
                    'ddd_number' => '17',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 23,
                    'ddd_number' => '18',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 23,
                    'ddd_number' => '19',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 24,
                    'ddd_number' => '21',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 24,
                    'ddd_number' => '22',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 24,
                    'ddd_number' => '24',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 21,
                    'ddd_number' => '27',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 21,
                    'ddd_number' => '28',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 22,
                    'ddd_number' => '31',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 22,
                    'ddd_number' => '32',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 22,
                    'ddd_number' => '33',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 22,
                    'ddd_number' => '34',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 22,
                    'ddd_number' => '35',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 22,
                    'ddd_number' => '37',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 22,
                    'ddd_number' => '38',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 25,
                    'ddd_number' => '41',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 25,
                    'ddd_number' => '42',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 25,
                    'ddd_number' => '43',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 25,
                    'ddd_number' => '44',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 25,
                    'ddd_number' => '45',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 25,
                    'ddd_number' => '46',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 27,
                    'ddd_number' => '47',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 27,
                    'ddd_number' => '48',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 27,
                    'ddd_number' => '49',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 26,
                    'ddd_number' => '51',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 26,
                    'ddd_number' => '53',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 26,
                    'ddd_number' => '54',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 26,
                    'ddd_number' => '55',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 20,
                    'ddd_number' => '61',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 17,
                    'ddd_number' => '62',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 17,
                    'ddd_number' => '64',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 7,
                    'ddd_number' => '63',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 18,
                    'ddd_number' => '65',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 18,
                    'ddd_number' => '66',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 19,
                    'ddd_number' => '67',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 1,
                    'ddd_number' => '68',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 4,
                    'ddd_number' => '69',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 9,
                    'ddd_number' => '71',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 9,
                    'ddd_number' => '73',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 9,
                    'ddd_number' => '74',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 9,
                    'ddd_number' => '75',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 9,
                    'ddd_number' => '77',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 16,
                    'ddd_number' => '79',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 13,
                    'ddd_number' => '81',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 8,
                    'ddd_number' => '82',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 12,
                    'ddd_number' => '83',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 15,
                    'ddd_number' => '84',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 10,
                    'ddd_number' => '85',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 14,
                    'ddd_number' => '86',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 13,
                    'ddd_number' => '87',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 10,
                    'ddd_number' => '88',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 14,
                    'ddd_number' => '89',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 3,
                    'ddd_number' => '91',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 6,
                    'ddd_number' => '92',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 3,
                    'ddd_number' => '93',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 3,
                    'ddd_number' => '94',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 5,
                    'ddd_number' => '95',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 2,
                    'ddd_number' => '96',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 6,
                    'ddd_number' => '97',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 11,
                    'ddd_number' => '98',
                    'ddd_status' => 'A',
                ],
                [
                    'state_id' => 11,
                    'ddd_number' => '99',
                    'ddd_status' => 'A',
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
        Schema::dropIfExists('sys_ddd_states');
    }
}
