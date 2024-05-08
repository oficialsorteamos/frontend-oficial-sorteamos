<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_partners_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('partner_id')->comment('Id do parceiro a qual a taxa se refere');
            $table->unsignedBigInteger('type_fee_id')->comment('Tipo de taxa');
            $table->decimal('par_value', 10, 2)->comment('Valor da taxa');
            $table->string('par_status')->default('A')->comment('Status da taxa');
            $table->timestamps();

            $table->foreign('partner_id')
                ->references('id')
                ->on('adm_partners')
                ->onDelete('cascade');

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
        Schema::dropIfExists('adm_partners_fees');
    }
}
