<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_type_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de parâmetro');
            $table->string('typ_status')->default('A')->comment('Status do tipo de parâmetro');
            $table->timestamps();

        });

        // Insert default values
        DB::table('fin_type_parameters')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Id do cliente na API de pagamentos',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Dia de vencimento da fatura',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Diferença de dias entre vencimento e data de fechamento da fatura',
                ],
                [
                    'id' => 4,
                    'typ_description' => 'Data de quando a empresa começou a usar o sistema',
                ],
                [
                    'id' => 5,
                    'typ_description' => 'Cobrança por Envio de Mensagem de Campanha',
                ],
                [
                    'id' => 6,
                    'typ_description' => 'Cobrança de Taxa Fixa Mensal (Mensalidade)',
                ],
                [
                    'id' => 7,
                    'typ_description' => 'Cobrança por Criação de Usuário',
                ],
                [
                    'id' => 8,
                    'typ_description' => 'Cobrança por Criação de Canal Oficial',
                ],
                [
                    'id' => 9,
                    'typ_description' => 'Cobrança por Criação de Canal Não Oficial',
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
        Schema::dropIfExists('fin_type_parameters');
    }
}
