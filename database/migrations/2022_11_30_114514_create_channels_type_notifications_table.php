<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsTypeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_channels_type_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cha_name')->comment('Tipo de notificação');
            $table->string('cha_color')->comment('Cor associada à notificação');
            $table->string('cha_status')->default('A')->comment('Status do tipo de notificação');
            $table->timestamps();
        });

        // Insert default values
        DB::table('man_channels_type_notifications')->insert(
            array(
                [
                    'id' => 1,
                    'cha_name' => 'Confirmação',
                    'cha_color' => 'success',
                ],
                [
                    'id' => 2,
                    'cha_name' => 'Aviso',
                    'cha_color' => 'warning',
                ],
                [
                    'id' => 3,
                    'cha_name' => 'Erro',
                    'cha_color' => 'danger',
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
        Schema::dropIfExists('man_channels_type_notifications');
    }
}
