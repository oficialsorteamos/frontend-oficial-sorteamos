<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeFormatMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_type_format_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de formato de mensagem (texto, áudio, etc).');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_type_format_messages')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Texto',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Áudio',
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
        Schema::dropIfExists('cha_type_format_messages');
    }
}
