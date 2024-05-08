<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeMessagesChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_type_messages_chat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de mensagem em um chat. (Texto, áudio, imagem, etc.)');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('sys_type_messages_chat')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Texto',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Audio',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Imagem',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 4,
                    'typ_description' => 'Video',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 5,
                    'typ_description' => 'Arquivo',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 6,
                    'typ_description' => 'Contato',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 7,
                    'typ_description' => 'Sticker',
                    'typ_status' => 'A',
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
        Schema::dropIfExists('sys_type_messages_chat');
    }
}
