<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('set_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('com_name')->comment('Nome ou razão social do cliente');
            $table->string('com_email')->comment('E-mail do cliente');
            $table->string('com_phone')->comment('Telefone do cliente');
            $table->string('com_mobile_phone')->comment('Telefone celular do cliente');
            $table->string('com_cpf')->comment('CPF do cliente');
            $table->string('com_cnpj')->comment('CNPJ do cliente');
            $table->string('com_postal_code')->comment('CEP do cliente');
            $table->string('com_address', 1000)->comment('Logradouro do cliente');
            $table->string('com_address_number')->comment('Número do endereço cliente');
            $table->string('com_complement')->comment('Número do endereço cliente');
            $table->string('com_province')->comment('Bairro do endereço do cliente');
            $table->string('com_city')->comment('Cidade do cliente');
            $table->string('com_state')->comment('Estado do cliente');
            $table->string('com_country')->comment('País do cliente');
            $table->string('whatsapp_business_account_id', 500)->nullable()->comment('Id da conta do cliente no WhatsApp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('set_customers');
    }
}
