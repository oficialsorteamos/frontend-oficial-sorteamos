<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExternalDeviceDataToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert default values
        DB::table('users')->insert(
            array(
                [
                    'id' => 3,
                    'name' => 'Usuário Externo',
                    'username' => 'usuario.externo',
                    'email' => 'usuario.externo@usuario.externo.com',
                    'password' => Hash::make('admin'),
                    'gender_id' => 3,
                    'phone' => '+5527999999998',
                    'status' => 'A',
                    'situation_user_id' => 1,
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
