<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_type_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de usuário (Operador, Contato, Gestor, etc.)');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });


        // Insert default values
        DB::table('sys_type_users')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Operador',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Contato',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Robô',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 4,
                    'typ_description' => 'Gestor',
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
        Schema::dropIfExists('sys_type_users');
    }
}
