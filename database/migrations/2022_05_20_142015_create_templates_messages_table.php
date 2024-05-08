<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome do template');
            $table->unsignedBigInteger('category_id')->comment('Categoria do template (Atualização de Conta, Atualização de alerta, etc.)');
            $table->unsignedBigInteger('language_id')->comment('Idioma do texto do template');
            $table->string('template_id_api', 500)->nullable()->comment('Id do template na API do WhatsApp');
            $table->text('tem_body', 2000)->comment('Texto do template');
            $table->string('tem_header')->nullable()->comment('Texto do template');
            $table->string('tem_footer')->nullable()->comment('Texto do template');
            $table->string('tem_namespace', 500)->nullable()->comment('Namespace ao qual o template está associado');
            $table->unsignedBigInteger('status_id')->default(1)->comment('Status do template (Aprovado, Pendente, etc.)');
            $table->unsignedBigInteger('user_id')->comment('Usuário que adicionou o template');
            $table->unsignedBigInteger('channel_id')->nullable()->comment('Id do canal associado ao template');
            $table->timestamps();


            $table->foreign('category_id')
                ->references('id')
                ->on('cha_templates_messages_categories')
                ->onDelete('cascade');
            
            $table->foreign('language_id')
                ->references('id')
                ->on('cha_templates_messages_languages')
                ->onDelete('cascade');
            
            $table->foreign('status_id')
                ->references('id')
                ->on('cha_templates_messages_type_status')
                ->onDelete('cascade');
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('channel_id')
                ->references('id')
                ->on('man_channels')
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
        Schema::dropIfExists('cha_templates_messages');
    }
}
