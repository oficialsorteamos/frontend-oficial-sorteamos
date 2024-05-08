<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adm_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id')->nullable()->comment('Id do Parceiro que trouxe o cliente');
            $table->unsignedBigInteger('gender_id')->nullable()->comment('Gênero do dono da empresa (quando é pessoa física)');
            $table->string('com_name')->comment('Razão Social da empresa ou nome do responsável');
            $table->string('com_cnpj')->nullable()->comment('CNPJ da empresa');
            $table->string('com_cpf')->nullable()->comment('CPF do responsável');
            $table->string('com_responsible_name')->comment('Nome do responsável da empresa');
            $table->string('com_url')->nullable()->comment('URL  do ambiente da empresa');
            $table->timestamp('com_birthday')->nullable()->comment('Data de aniversário do dono da empresa (quando é pessoa física)');
            $table->string('com_responsible_phone')->comment('Telefone do responsável pela empresa');
            $table->string('com_responsible_email')->comment('E-mail do responsável pela empresa');
            $table->string('com_finance_phone')->nullable()->comment('Telefone do financeiro da empresa');
            $table->string('com_finance_email')->nullable()->comment('E-mail do financeiro da empresa');
            $table->string('com_postal_code')->comment('CEP do endereço da empresa');
            $table->string('com_address', 1000)->comment('Logradouro da empresa');
            $table->string('com_address_number')->nullable()->comment('Número do endereço da empresa');
            $table->string('com_complement')->nullable()->comment('Complemento do endereço da empresa');
            $table->string('com_province')->comment('Bairro do endereço da empresa');
            $table->string('com_city')->comment('Cidade da empresa');
            $table->string('com_state')->comment('Estado da empresa');
            $table->string('com_country')->comment('País da empresa');
            $table->unsignedBigInteger('status_id')->default(1)->comment('Status da empresa');
            $table->timestamps();

            $table->foreign('partner_id')
                ->references('id')
                ->on('adm_partners')
                ->onDelete('cascade');

            $table->foreign('gender_id')
                ->references('id')
                ->on('sys_genders')
                ->onDelete('cascade');

            $table->foreign('status_id')
                ->references('id')
                ->on('adm_type_company_status')
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
