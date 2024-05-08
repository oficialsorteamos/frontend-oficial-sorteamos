<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('set_notifications_subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('not_name')->comment('Nome da notificação');
            $table->string('not_status')->default('A')->comment('Status do custo');
            $table->timestamps();
        });

        // Insert default values
        DB::table('set_notifications_subjects')->insert(
            array(
                [
                    'id' => 1,
                    'not_name' => 'Financeiro',
                ],
                [
                    'id' => 2,
                    'not_name' => 'Comercial',
                ],
                [
                    'id' => 3,
                    'not_name' => 'Vendas',
                ],
                [
                    'id' => 4,
                    'not_name' => 'Suporte',
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
        Schema::dropIfExists('set_notifications_subjects');
    }
}
