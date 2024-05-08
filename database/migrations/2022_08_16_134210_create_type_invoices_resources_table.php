<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeInvoicesResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fin_type_invoices_resources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do taxa');
            $table->string('typ_status')->default('A')->comment('Status do tipo de taxa de conversação');
            $table->timestamps();

        });

        // Insert default values
        DB::table('fin_type_invoices_resources')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Usuário',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'Canal Oficial',
                ],
                [
                    'id' => 3,
                    'typ_description' => 'Canal Não Oficial',
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
        Schema::dropIfExists('fin_type_invoices_resources');
    }
}
