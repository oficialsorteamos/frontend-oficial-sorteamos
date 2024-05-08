<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('con_contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('con_name')->nullable()->comment('Nome do contato');
            $table->unsignedBigInteger('gender_id')->default(3)->comment('Gênero do contato. Indefinido é o gênero padrão');
            $table->unsignedBigInteger('status_id')->default(1)->comment('Classificação do contato de acordo com um funil de vendas ou algo correlato.');
            $table->unsignedBigInteger('color_avatar_id')->nullable()->comment('Cor de background do avatar caso o usuário não tenha foto.');
            $table->string('con_email')->nullable()->comment('E-mail do contato');
            $table->timestamp('con_birthday')->nullable()->comment('Data de aniversário do contato.');
            $table->string('con_avatar')->nullable()->comment('Url da imagem do contato');
            $table->string('con_phone')->comment('Telefone do contato');
            $table->string('con_cpf')->nullable()->comment('CPF do contato');
            $table->string('con_cnpj')->nullable()->comment('CNPJ do contato');
            $table->timestamps();

            $table->foreign('gender_id')
                    ->references('id')
                    ->on('sys_genders')
                    ->onDelete('cascade');
            
            $table->foreign('tag_id')
                    ->references('id')
                    ->on('man_tags')
                    ->onDelete('cascade');
            
            $table->foreign('status_id')
                    ->references('id')
                    ->on('con_status')
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
        Schema::dropIfExists('con_contacts');
    }
}
