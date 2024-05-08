<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_status_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sta_name')->comment('Nome do status do template');
            $table->string('sta_status')->default('A')->comment('Situação de um status de um template');
            $table->timestamps();
        });


        // Insert default values
        DB::table('fin_status_payments')->insert(
            array(
                [
                    'id' => 1,
                    'sta_name' => 'Aguardando Pagamento',
                ],
                [
                    'id' => 2,
                    'sta_name' => 'Pago',
                ],
                [
                    'id' => 3,
                    'sta_name' => 'Estornado',
                ],
                [
                    'id' => 4,
                    'sta_name' => 'Vencido',
                ],
                [
                    'id' => 5,
                    'sta_name' => 'Pagamento em Análise',
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
        Schema::dropIfExists('fin_status_payments');
    }
}
