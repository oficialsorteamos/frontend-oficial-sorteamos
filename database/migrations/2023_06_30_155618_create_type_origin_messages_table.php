<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeOriginMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_type_origin_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição da origem da mensagem');
            $table->string('typ_status')->default('A')->comment('Status da origem da mensagem');
            $table->timestamps();
        });

        // Insert default values
        DB::table('cha_type_origin_messages')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'WhatsApp',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'SMS',
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
        Schema::dropIfExists('cha_type_origin_messages');
    }
}
