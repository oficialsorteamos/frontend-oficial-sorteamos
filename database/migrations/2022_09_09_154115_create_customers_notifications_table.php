<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('set_customers_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id')->comment('Id do cliente que receberá as notificações');
            $table->unsignedBigInteger('type_notification_id')->comment('Id do tipo de notificação');
            $table->unsignedBigInteger('notification_subject_id')->nullable()->comment('Id do assunto da notificação');
            $table->string('cus_contact_value', 300)->comment('Contato do cliente. Pode ser um e-mail ou nº de telefone');
            $table->string('cus_status')->default('A')->comment('Status do custo');
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('set_customers')
                ->onDelete('cascade');

            $table->foreign('type_notification_id')
                ->references('id')
                ->on('set_type_notifications')
                ->onDelete('cascade');
            
            $table->foreign('notification_subject_id')
                ->references('id')
                ->on('set_notifications_subjects')
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
        Schema::dropIfExists('set_customers_notifications');
    }
}
