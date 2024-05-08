<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCallWhatsappToTypeOriginMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        // Insert default values
        DB::table('cha_type_origin_messages')->insert(
            array(
                [
                    'id' => 3,
                    'typ_description' => 'Ligação via WhatsApp',
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

    }
}
