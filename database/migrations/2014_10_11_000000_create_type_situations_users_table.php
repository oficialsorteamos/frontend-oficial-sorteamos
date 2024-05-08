<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeSituationsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_type_situations_users', function (Blueprint $table) {
            $table->id();
            $table->string('typ_description')->comment('Descrição do tipo de situação. (Offline, Online, etc.)');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('man_type_situations_users')->insert(
            array(
                [
                    'typ_description' => 'Online',
                ],
                [
                    'typ_description' => 'Offline',
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
        Schema::dropIfExists('man_type_situations_users');
    }
}
