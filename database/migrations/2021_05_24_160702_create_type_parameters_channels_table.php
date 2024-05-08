<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeParametersChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_type_parameters_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->comment('Categoria do tipo de parâmetro do canal');
            $table->string('typ_name')->comment('Nome do parâmetro');
            $table->string('typ_description')->nullable()->comment('Descrição do parâmetro');
            $table->string('typ_status')->default('A')->comment('Status do parâmetro');
            $table->timestamps();

            $table->foreign('category_id')
                ->references('id')
                ->on('man_categories_parameter_channel')
                ->onDelete('cascade');

        });

        // Insert default values
        DB::table('man_type_parameters_channels')->insert(
            array(
                [
                    'id' => 1,
                    'category_id' => 1,
                    'typ_name' => 'Tempo de Inatividade Durante o Atendimento',
                    'typ_description' => 'Tempo máximo de inatividade durante o atendimento. Caso o contato ou o operador não troquem mensagens durante esse tempo, o atendimento será encerrado automaticamente',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 2,
                    'category_id' => 1,
                    'typ_name' => 'Tempo de Inatividade Durante o Autoatendimento',
                    'typ_description' => 'Tempo máximo de inatividade durante o autoatendimento. Caso o contato não envie mensagens durante esse tempo, o atendimento será encerrado automaticamente',
                    'typ_status' => 'A',
                ],
                [
                    'id' => 3,
                    'category_id' => 2,
                    'typ_name' => 'Departamento Padrão de Transferência',
                    'typ_description' => 'Departamento para onde o contato será transferido caso o chatbot não tenha sido configurado',
                    'typ_status' => 'A',
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
        Schema::dropIfExists('man_type_parameters_channels');
    }
}
