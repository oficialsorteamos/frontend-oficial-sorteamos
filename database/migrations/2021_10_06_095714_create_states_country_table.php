<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_states_country', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('country_id')->comment('Id da do país onde a região se encontra');
            $table->unsignedBigInteger('country_region_id')->nullable()->comment('Id da região onde o Estado se encontra');
            $table->string('sta_name')->comment('Nome do Estado (Espírito Santo, Bahia, etc.)');
            $table->string('sta_uf')->nullable()->comment('UF do Estado (ES, BA, etc.)');
            $table->string('sta_status')->default('A')->comment('Status do Estado');
            $table->timestamps();

            $table->foreign('country_id')
                ->references('id')
                ->on('sys_countries')
                ->onDelete('cascade');

            $table->foreign('country_region_id')
                ->references('id')
                ->on('sys_country_regions')
                ->onDelete('cascade');
        });

        // Insert default values
        DB::table('sys_states_country')->insert(
            array(
                [
                    'id' => 1,
                    'country_id' => 1,
                    'country_region_id' => 1,
                    'sta_name' => 'Acre',
                    'sta_uf' => 'AC',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 2,
                    'country_id' => 1,
                    'country_region_id' => 1,
                    'sta_name' => 'Amapá',
                    'sta_uf' => 'AP',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 3,
                    'country_id' => 1,
                    'country_region_id' => 1,
                    'sta_name' => 'Pará',
                    'sta_uf' => 'PA',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 4,
                    'country_id' => 1,
                    'country_region_id' => 1,
                    'sta_name' => 'Rondônia',
                    'sta_uf' => 'RO',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 5,
                    'country_id' => 1,
                    'country_region_id' => 1,
                    'sta_name' => 'Roraima',
                    'sta_uf' => 'RR',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 6,
                    'country_id' => 1,
                    'country_region_id' => 1,
                    'sta_name' => 'Amazonas',
                    'sta_uf' => 'AM',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 7,
                    'country_id' => 1,
                    'country_region_id' => 1,
                    'sta_name' => 'Tocantins',
                    'sta_uf' => 'TO',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 8,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Alagoas',
                    'sta_uf' => 'AL',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 9,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Bahia',
                    'sta_uf' => 'BA',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 10,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Ceará',
                    'sta_uf' => 'CE',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 11,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Maranhão',
                    'sta_uf' => 'MA',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 12,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Paraíba',
                    'sta_uf' => 'PB',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 13,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Pernambuco',
                    'sta_uf' => 'PE',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 14,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Piauí',
                    'sta_uf' => 'PI',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 15,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Rio Grande do Norte',
                    'sta_uf' => 'RN',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 16,
                    'country_id' => 1,
                    'country_region_id' => 2,
                    'sta_name' => 'Sergipe',
                    'sta_uf' => 'SE',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 17,
                    'country_id' => 1,
                    'country_region_id' => 3,
                    'sta_name' => 'Goiás',
                    'sta_uf' => 'GO',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 18,
                    'country_id' => 1,
                    'country_region_id' => 3,
                    'sta_name' => 'Mato Grosso',
                    'sta_uf' => 'MT',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 19,
                    'country_id' => 1,
                    'country_region_id' => 3,
                    'sta_name' => 'Mato Grosso do Sul',
                    'sta_uf' => 'MS',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 20,
                    'country_id' => 1,
                    'country_region_id' => 3,
                    'sta_name' => 'Distrito Federal',
                    'sta_uf' => 'DF',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 21,
                    'country_id' => 1,
                    'country_region_id' => 4,
                    'sta_name' => 'Espírito Santo',
                    'sta_uf' => 'ES',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 22,
                    'country_id' => 1,
                    'country_region_id' => 4,
                    'sta_name' => 'Minas Gerais',
                    'sta_uf' => 'MG',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 23,
                    'country_id' => 1,
                    'country_region_id' => 4,
                    'sta_name' => 'São Paulo',
                    'sta_uf' => 'SP',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 24,
                    'country_id' => 1,
                    'country_region_id' => 4,
                    'sta_name' => 'Rio de Janeiro',
                    'sta_uf' => 'RJ',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 25,
                    'country_id' => 1,
                    'country_region_id' => 5,
                    'sta_name' => 'Paraná',
                    'sta_uf' => 'PR',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 26,
                    'country_id' => 1,
                    'country_region_id' => 5,
                    'sta_name' => 'Rio Grande do Sul',
                    'sta_uf' => 'RS',
                    'sta_status' => 'A',
                ],
                [
                    'id' => 27,
                    'country_id' => 1,
                    'country_region_id' => 5,
                    'sta_name' => 'Santa Catarina',
                    'sta_uf' => 'SC',
                    'sta_status' => 'A',
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
        Schema::dropIfExists('sys_states_country');
    }
}
