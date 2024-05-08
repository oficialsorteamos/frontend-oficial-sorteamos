<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmTypeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_type_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de notificação');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('adm_type_notifications')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Push',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'WhatsApp',
                    'typ_status' => 'I',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'SMS',
                    'typ_status' => 'I',
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
        Schema::dropIfExists('adm_type_notifications');
    }
}
