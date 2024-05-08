<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cal_calendar', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('Colaborador que fará o evento com o contato');
            $table->unsignedBigInteger('tag_id')->comment('Tag que identifica o assunto principal da evento');
            $table->string('cal_title')->comment('Título do evento');
            $table->string('cal_description', 1000)->nullable()->comment('Descrição do evento');
            $table->string('cal_url')->nullable()->comment('Url da sala onde ocorrerá o evento');
            $table->timestamp('cal_event_start')->nullable()->comment('Data e hora de início do evento.');
            $table->timestamp('cal_event_end')->nullable()->comment('Data e hora do fim do evento.');
            $table->timestamps();

            $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            
            $table->foreign('tag_id')
                    ->references('id')
                    ->on('man_tags')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cal_calendar');
    }
}
