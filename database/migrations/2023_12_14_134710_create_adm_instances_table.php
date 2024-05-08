<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_instances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('api_communication_id')->comment('Id da API em que a instância pertence');
            $table->string('ins_name')->comment('Nome da instância');
            $table->string('ins_webhook', 300)->nullable()->comment('Configuração de distribuição igualitária principal');
            $table->string('ins_token')->comment('Token de acesso a instância');
            $table->unsignedBigInteger('ins_status_instance_id')->comment('Status da configuração de distribuição igualitária');
            $table->unsignedBigInteger('ins_status_connection_id')->comment('Status da configuração de distribuição igualitária');
            $table->timestamp('ins_dt_created')->nullable()->comment('Data em que a instância foi criada');
            $table->timestamps();

            $table->foreign('ins_status_instance_id')
                ->references('id')
                ->on('adm_instances_status')
                ->onDelete('cascade');

            $table->foreign('ins_status_connection_id')
                ->references('id')
                ->on('adm_instances_connection_status')
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
        Schema::dropIfExists('adm_instances');
    }
}
