<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_partners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_partner_id')->comment('Tipo de parceiro (revendedor, white label, etc.)');
            $table->unsignedBigInteger('gender_id')->nullable()->comment('Gênero do dono da empresa (quando é pessoa física)');
            $table->string('par_corporate_name')->comment('Razão Social da empresa ou nome do responsável');
            $table->timestamp('par_partnership_started')->comment('Data de início da parceria');
            $table->string('par_cnpj')->nullable()->comment('CNPJ da empresa');
            $table->string('par_cpf')->nullable()->comment('CPF do responsável');
            $table->string('par_responsible_name')->comment('Nome do responsável da empresa');
            $table->timestamp('par_birthday')->nullable()->comment('Data de aniversário do dono da empresa (quando é pessoa física)');
            $table->string('par_responsible_phone')->comment('Telefone do responsável pela empresa');
            $table->string('par_responsible_email')->comment('E-mail do responsável pela empresa');
            $table->string('par_finance_phone')->comment('Telefone do financeiro da empresa');
            $table->string('par_finance_email')->comment('E-mail do financeiro da empresa');
            $table->string('par_postal_code')->comment('CEP do endereço do parceiro');
            $table->string('par_address', 1000)->comment('Logradouro do parceiro');
            $table->string('par_address_number')->nullable()->comment('Número do endereço parceiro');
            $table->string('par_complement')->nullable()->comment('Número do endereço parceiro');
            $table->string('par_province')->comment('Bairro do endereço do parceiro');
            $table->string('par_city')->comment('Cidade do parceiro');
            $table->string('par_state')->comment('Estado do parceiro');
            $table->string('par_country')->comment('País do parceiro');
            $table->string('par_status')->default('A')->comment('Status do Parceiro');
            $table->timestamps();

            $table->foreign('type_partner_id')
                ->references('id')
                ->on('adm_type_partners')
                ->onDelete('cascade');

            $table->foreign('gender_id')
                ->references('id')
                ->on('sys_genders')
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
        Schema::dropIfExists('adm_partners');
    }
}
