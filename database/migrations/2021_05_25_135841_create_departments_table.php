<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dep_name')->comment('Nome do departamento');
            $table->string('dep_description')->comment('Descrição do departamento');
            $table->string('dep_status')->default('A');
            $table->timestamps();
        });


        // Insert default values
        DB::table('man_departments')->insert(
            array(
                [
                    'id' => 1,
                    'dep_name' => 'Geral',
                    'dep_description' => 'Departamento geral',
                    'dep_status' => 'A',
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
        Schema::dropIfExists('man_departments');
    }
}
