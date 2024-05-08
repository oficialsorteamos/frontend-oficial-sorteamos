<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmPartnersPaymentsOrdersStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_partners_payments_orders_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('par_description')->comment('Descrição do status de uma ordem de pagamento');
            $table->string('par_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('adm_partners_payments_orders_status')->insert(
            array(
                [
                    'id' => 1,
                    'par_description' => 'Aguardando Pagamento',
                    'par_status' => 'A',
                ],
                [
                    'id' => 2,
                    'par_description' => 'Pago',
                    'par_status' => 'A',
                ],
                [
                    'id' => 3,
                    'par_description' => 'Cancelado',
                    'par_status' => 'A',
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
        Schema::dropIfExists('adm_partners_payments_orders_status');
    }
}
