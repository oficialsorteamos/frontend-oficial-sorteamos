<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_type_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do taxa');
            $table->string('typ_status')->default('A')->comment('Status do tipo de taxa de conversação');
            $table->timestamps();

        });

        // Insert default values
        DB::table('fin_type_fees')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Taxa Fixa Mensal',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Taxa por Usuário',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Taxa por Canal Oficial',
                ],
                [
                    'id' => 4,
                    'typ_description' => 'Taxa por Canal Não Oficial',
                ],
                [
                    'id' => 5,
                    'typ_description' => 'Taxa por Envio de Mensagem de Campanha via WhatsApp',
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
        Schema::dropIfExists('fin_type_fees');
    }
}
