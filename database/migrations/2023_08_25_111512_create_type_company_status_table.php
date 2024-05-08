<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeCompanyStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_type_company_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('com_description')->comment('Descrição do status que uma empresa pode ter');
            $table->string('com_status')->default('A');
            $table->timestamps();
        });

        // Insert default values
        DB::table('adm_type_company_status')->insert(
            array(
                [
                    'id' => 1,
                    'com_description' => 'Ativa',
                ],
                [
                    'id' => 2,
                    'com_description' => 'Inativa',
                ],
                [
                    'id' => 3,
                    'com_description' => 'Pausada',
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
        Schema::dropIfExists('adm_type_company_status');
    }
}
