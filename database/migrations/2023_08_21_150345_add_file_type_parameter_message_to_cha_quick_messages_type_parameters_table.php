<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileTypeParameterMessageToChaQuickMessagesTypeParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        // Insert default values
        DB::table('cha_quick_messages_type_parameters')->insert(
            array(
                [
                    'id' => 4,
                    'qui_name' => 'Arquivo',
                    'qui_status' => 'I',
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
