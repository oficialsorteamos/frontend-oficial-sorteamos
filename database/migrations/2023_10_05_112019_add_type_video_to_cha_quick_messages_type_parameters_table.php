<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeVideoToChaQuickMessagesTypeParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Cria o Perfil
        DB::table('cha_quick_messages_type_parameters')->insert(
            array(
                [
                    'id' => 5,
                    'qui_name' => 'Vídeo',
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
        
    }
}
