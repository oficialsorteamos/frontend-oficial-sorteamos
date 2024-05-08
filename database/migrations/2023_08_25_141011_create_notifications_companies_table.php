<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_notifications_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('notification_id')->comment('Id da notificação enviada');
            $table->unsignedBigInteger('company_id')->comment('Id da empresa que recebeu a notificação');
            $table->string('not_status')->default('A');
            $table->timestamps();

            $table->foreign('notification_id')
                ->references('id')
                ->on('adm_notifications')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')
                ->on('adm_companies')
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
        Schema::dropIfExists('adm_notifications_companies');
    }
}
