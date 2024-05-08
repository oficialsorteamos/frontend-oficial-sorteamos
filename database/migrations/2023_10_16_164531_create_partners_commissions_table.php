<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_partners_commissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('partner_id')->comment('Id do parceiro em que a comissão está associada');
            $table->decimal('par_percentage_level_1', 10, 2)->comment('Porcentagem paga de commissão em relação a fatura para revendedores do nível 1');
            $table->integer('par_initial_quantity_level_1')->comment('Quantidade inicial de empresas associadas a um revendedor para enquadrá-lo no nível 1');
            $table->integer('par_final_quantity_level_1')->comment('Quantidade final de empresas associadas a um revendedor para enquadrá-lo no nível 1');
            $table->decimal('par_percentage_level_2', 10, 2)->comment('Porcentagem paga de commissão em relação a fatura para revendedores do nível 2');
            $table->integer('par_initial_quantity_level_2')->comment('Quantidade inicial de empresas associadas a um revendedor para enquadrá-lo no nível 2');
            $table->integer('par_final_quantity_level_2')->comment('Quantidade final de empresas associadas a um revendedor para enquadrá-lo no nível 2');
            $table->decimal('par_percentage_level_3', 10, 2)->comment('Porcentagem paga de commissão em relação a fatura para revendedores do nível 3');
            $table->integer('par_initial_quantity_level_3')->comment('Quantidade inicial de empresas associadas a um revendedor para enquadrá-lo no nível 3');
            $table->integer('par_final_quantity_level_3')->comment('Quantidade final de empresas associadas a um revendedor para enquadrá-lo no nível 3');
            $table->string('par_status')->default('A')->comment('Status do nível de enquadramento de um revendedor');
            $table->timestamps();

            $table->foreign('partner_id')
                ->references('id')
                ->on('adm_partners')
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
        Schema::dropIfExists('adm_partners_commissions');
    }
}
