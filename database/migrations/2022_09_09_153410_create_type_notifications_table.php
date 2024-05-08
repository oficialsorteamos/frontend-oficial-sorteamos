<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('set_type_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_name')->comment('Tipo de notificação');
            $table->string('typ_status')->default('A')->comment('Status do tipo de notificação');
            $table->timestamps();
        });

        // Insert default values
        DB::table('set_type_notifications')->insert(
            array(
                [
                    'id' => 1,
                    'typ_name' => 'E-mail',
                ],
                [
                    'id' => 2,
                    'typ_name' => 'SMS',
                ],
                [
                    'id' => 3,
                    'typ_name' => 'WhatsApp',
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
        Schema::dropIfExists('set_type_notifications');
    }
}
