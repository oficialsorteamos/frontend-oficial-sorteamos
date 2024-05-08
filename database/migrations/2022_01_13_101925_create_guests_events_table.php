<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestsEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cal_guests_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id')->comment('Id do evento na agenda que o convidado participará');
            $table->unsignedBigInteger('contact_id')->comment('Id do contato que participará do evento');
            $table->timestamps();

            $table->foreign('event_id')
                    ->references('id')
                    ->on('cal_calendar')
                    ->onDelete('cascade');
            
            $table->foreign('contact_id')
                    ->references('id')
                    ->on('con_contacts')
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
        Schema::dropIfExists('cal_guests_events');
    }
}
