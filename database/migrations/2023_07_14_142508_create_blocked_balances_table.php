<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_blocked_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('campaign_id')->comment('Id da campanha cujo saldo foi bloqueado');
            $table->decimal('blo_value', 10, 2)->comment('Valor bloqueado');
            $table->string('blo_status')->default('A')->comment('Status do bloqueio do saldo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fin_blocked_balances');
    }
}
