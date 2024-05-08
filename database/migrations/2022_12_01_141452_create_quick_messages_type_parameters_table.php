<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickMessagesTypeParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_quick_messages_type_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('qui_name', 500)->comment('Nome do tipo de parâmetro');
            $table->string('qui_status')->default('A')->comment('Status do tipo de parâmetro');
            $table->timestamps();

        });

        // Insert default values
        DB::table('cha_quick_messages_type_parameters')->insert(
            array(
                [
                    'id' => 1,
                    'qui_name' => 'Botão',
                    'qui_status' => 'I',
                ],
                [
                    'id' => 2,
                    'qui_name' => 'Imagem',
                    'qui_status' => 'A',
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
        Schema::dropIfExists('cha_quick_messages_type_parameters');
    }
}
