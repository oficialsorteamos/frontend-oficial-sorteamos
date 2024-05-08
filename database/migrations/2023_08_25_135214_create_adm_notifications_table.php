<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_notification_id')->comment('Id do tipo de notificação enviada');
            $table->string('not_title')->comment('Título da mensagem de notificação');
            $table->text('not_message', 2000)->comment('Conteúdo da mensagem');
            $table->string('not_status')->default('A');
            $table->timestamps();

            $table->foreign('type_notification_id')
                ->references('id')
                ->on('adm_type_notifications')
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
        Schema::dropIfExists('adm_notifications');
    }
}
