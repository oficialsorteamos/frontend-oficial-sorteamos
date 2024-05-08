<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_type_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_name')->comment('Nome do tipo de tag');
            $table->string('typ_status')->default('A')->comment('Status do tipo de tag.');
            $table->timestamps();
        });

        // Insert default values
        DB::table('man_type_tags')->insert(
            array(
                [
                    'typ_name' => 'Contato',
                ],
                [
                    'typ_name' => 'Agenda',
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
        Schema::dropIfExists('man_type_tags');
    }
}
