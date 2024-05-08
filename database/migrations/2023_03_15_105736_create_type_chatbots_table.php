<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeChatbotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_type_chatbots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de chatbot (atendimento, campanha, etc).');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_type_chatbots')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Atendimento',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Campanha',
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
        Schema::dropIfExists('cha_type_chatbots');
    }
}
