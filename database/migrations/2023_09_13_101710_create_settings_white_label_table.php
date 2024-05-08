<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsWhiteLabelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('set_white_label', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('whi_name')->nullable()->comment('Nome da empresa White Label');
            $table->string('whi_url')->nullable()->comment('URL base que aponta para o servidor do White Label');
            $table->string('whi_status')->default('A')->comment('Status do White Label');
            $table->timestamps();
        });

        // Insert default values
        DB::table('set_white_label')->insert(
            array(
                [
                    'id' => 1,
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
        Schema::dropIfExists('set_white_label');
    }
}
