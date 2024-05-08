<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeGeneralMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_type_general_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de mensagem geral');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('man_type_general_messages')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Transferência de atendimento para um setor',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Transferência de atendimento para um operador',
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
        Schema::dropIfExists('man_type_general_messages');
    }
}
