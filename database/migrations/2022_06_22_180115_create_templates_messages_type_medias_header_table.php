<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesTypeMediasHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_type_medias_header', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome do tipo de mídia do cabeçalho');
            $table->string('tem_status')->default('A')->comment('Status do tipo de mídia do cabeçalho');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cha_templates_messages_type_medias_header')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Texto',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'Imagem',
                ],
                [
                    'id' => 3,
                    'tem_name' => 'Documento',
                ],
                [
                    'id' => 4,
                    'tem_name' => 'Vídeo',
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
        Schema::dropIfExists('cha_templates_messages_type_medias_header');
    }
}
