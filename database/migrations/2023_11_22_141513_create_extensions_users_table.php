<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtensionsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voi_extensions_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('extension_id')->comment('Id do ramal em que um usuário está associado');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário associado ao ramal');
            $table->string('ext_status')->default('A')->comment('Status da relação entre ramal e usuário');
            $table->timestamps();

            $table->foreign('extension_id')
                ->references('id')
                ->on('voi_extensions')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voi_extensions_users');
    }
}
