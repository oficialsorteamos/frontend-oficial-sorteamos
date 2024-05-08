<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_type_partners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('typ_description')->comment('Descrição do tipo de parceria (Revendedor, White Label)');
            $table->string('typ_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('adm_type_partners')->insert(
            array(
                [
                    'id' => 1,
                    'typ_description' => 'Revendedor',
                ],
                [
                    'id' => 2,
                    'typ_description' => 'White Label',
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
        Schema::dropIfExists('adm_type_partners');
    }
}
