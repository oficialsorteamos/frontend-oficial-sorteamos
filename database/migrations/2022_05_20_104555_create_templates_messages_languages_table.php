<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome da categoria do template');
            $table->string('tem_code')->comment('Tag que será enviada para criação da mensagem template');
            $table->string('tem_status')->default('A')->comment('Situação da categoria de um template');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cha_templates_messages_languages')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Português (Brasil)',
                    'tem_code' => 'pt_BR',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'Inglês',
                    'tem_code' => 'en_US',
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
        Schema::dropIfExists('cha_templates_messages_languages');
    }
}
