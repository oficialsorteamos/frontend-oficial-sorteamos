<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationsVoipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('int_voip', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('voi_name')->comment('Nome da empresa que fornece o serviço voip');
            $table->string('voi_description')->comment('Descrição da integração');
            $table->string('voi_status')->default('I')->comment('Status da integração');
            $table->timestamps();
        });

        // Insert default values
        DB::table('int_voip')->insert(
            array(
                [
                    'id' => 1,
                    'voi_name' => 'Mais Voip',
                    'voi_description' => 'Empresa que fornece soluções de telefonia',
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
        Schema::dropIfExists('int_voip');
    }
}
