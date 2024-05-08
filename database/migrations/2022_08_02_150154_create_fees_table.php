<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_fee_id')->comment('Id do tipo de taxa');
            $table->decimal('fee_value', 10, 2)->comment('Valor da taxa');
            $table->string('fee_status')->default('A')->comment('Status do tipo de taxa');
            $table->timestamps();

            $table->foreign('type_fee_id')
                ->references('id')
                ->on('fin_type_fees')
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
        Schema::dropIfExists('fin_fees');
    }
}
