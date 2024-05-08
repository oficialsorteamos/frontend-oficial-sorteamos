<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaysWeekOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cam_days_week_operations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('day_name')->comment('Nome da configuração associada a uma campanha');
            $table->string('day_description')->comment('Descrição da configuração associada a uma campanha');
            $table->string('day_status')->default('A')->comment('Status da associação canal/campanha');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cam_days_week_operations')->insert(
            array(
                [
                    'id' => 1,
                    'day_name' => 'Intervalo de horário de funcionamento (Segunda à Sexta)',
                    'day_description' => 'Intervalo de horário em que a campanha estará operante (Segunda à Sexta)',
                ],
                [
                    'id' => 2,
                    'day_name' => 'Intervalo de horário de funcionamento (Sábado)',
                    'day_description' => 'Intervalo de horário em que a campanha estará operante (Sábado)',
                ],
                [
                    'id' => 3,
                    'day_name' => 'Intervalo de horário de funcionamento (Domingo)',
                    'day_description' => 'Intervalo de horário em que a campanha estará operante (Domingo)',
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
        Schema::dropIfExists('cam_days_week_operations');
    }
}
