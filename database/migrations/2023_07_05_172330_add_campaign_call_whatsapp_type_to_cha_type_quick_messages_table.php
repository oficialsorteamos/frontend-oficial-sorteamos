<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampaignCallWhatsappTypeToChaTypeQuickMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert default values
        DB::table('cha_type_quick_messages')->insert(
            array(
                [
                    'id' => 5,
                    'typ_description' => 'Campanha de Ligação via WhatsApp',
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
        
    }
}
