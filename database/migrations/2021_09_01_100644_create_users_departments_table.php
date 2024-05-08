<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_users_departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('Id do usuário que será alocado em um departamento');
            $table->unsignedBigInteger('department_id')->comment('Departamento onde o usuário será alocado');
            $table->string('use_status')->default('A');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('department_id')
                ->references('id')
                ->on('man_departments')
                ->onDelete('cascade');
        });

        // Insert default values
        DB::table('man_users_departments')->insert(
            array(
                [
                    'id' => 1,
                    'user_id' => 2,
                    'department_id' => 1,
                    'use_status' => 'A',
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
        Schema::dropIfExists('man_users_departments');
    }
}
