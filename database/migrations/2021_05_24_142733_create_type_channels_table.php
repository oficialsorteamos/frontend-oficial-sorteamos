<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_type_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_name')->comment('Nome do tipo de canal');
            $table->string('typ_status')->default('A')->comment('Status do tipo do canal');
            $table->timestamps();

        });

        // Insert default values
        DB::table('man_type_channels')->insert(
            array(
                [
                    'typ_name' => 'Whatsapp',
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
        Schema::dropIfExists('man_type_channels');
    }
}
