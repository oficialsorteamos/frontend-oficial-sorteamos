<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_channels_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_notification_id')->comment('Id do canal notificado');
            $table->unsignedBigInteger('channel_id')->comment('Id do canal notificado');
            $table->string('cha_content', 500)->comment('Conteúdo da notificação do canal');
            $table->string('cha_status')->default('A')->comment('Status da notificação');
            $table->timestamps();

            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
                ->onDelete('cascade');
            
            $table->foreign('type_notification_id')
                ->references('id')
                ->on('man_channels_type_notifications')
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
        Schema::dropIfExists('man_channels_notifications');
    }
}
