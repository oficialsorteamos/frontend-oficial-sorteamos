<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesParameterChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_categories_parameter_channel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cat_name')->comment('Nome da categoria do canal');
            $table->string('cat_status')->default('A')->comment('Status da da categoria do canal.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('man_categories_parameter_channel')->insert(
            array(
                [
                    'cat_name' => 'Temporizador',
                ],
                [
                    'cat_name' => 'Opção',
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
        Schema::dropIfExists('man_categories_parameter_channel');
    }
}
