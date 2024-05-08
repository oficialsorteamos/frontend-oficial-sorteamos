<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTypeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_notifications_type_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('notification_id')->comment('Id da notificação enviada');
            $table->unsignedBigInteger('role_id')->comment('Id do tipo de usuário que recebeu a notificação');
            $table->string('not_status')->default('A');
            $table->timestamps();

            $table->foreign('notification_id')
                ->references('id')
                ->on('adm_notifications')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('sys_roles')
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
        Schema::dropIfExists('adm_notifications_type_users');
    }
}
