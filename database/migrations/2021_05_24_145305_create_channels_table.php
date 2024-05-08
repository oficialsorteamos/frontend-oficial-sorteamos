<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('type_channel_id')->default(1)->comment('Tipo de canal (Whatsapp, Telegram, etc)');
            $table->unsignedBigInteger('api_communication_id')->default(2)->comment('API associada ao canal');
            $table->string('user_id_connection')->nullable()->comment('Usuário que irá conectar o canal ao Whatsapp');
            $table->boolean('cha_api_official')->nullable()->comment('Indica se o canal utiliza a API oficial ou não');
            $table->string('cha_name')->comment('Nome do canal');
            $table->string('cha_description', 500)->nullable()->comment('Breve descrição do canal');
            $table->string('cha_phone_ddi')->comment('Código internacional do número, (55, 91, etc.)');
            $table->string('cha_phone_number')->comment('Número de telefone associado ao canal');
            $table->string('cha_company_name')->nullable()->comment('Nome da empresa proprietária do canal');
            $table->string('cha_company_email')->nullable()->comment('E-mail da empresa proprietária do canal');
            $table->string('cha_company_site')->nullable()->comment('Site da empresa proprietária do canal');
            $table->string('cha_company_address', 500)->nullable()->comment('Endereço da empresa proprietária do canal');
            $table->string('cha_status')->default('A')->comment('Status do canal');
            $table->string('cha_session_name')->unique()->nullable()->comment('Nome da Sessão criada com a API.');
            $table->string('cha_session_token', 500)->nullable()->comment('Token gerado para conexão com a API.');
            $table->string('cha_app_id_api', 500)->nullable()->comment('Id do app na Cloud API onde o canal foi criado.');
            $table->string('cha_channel_id_api', 500)->nullable()->comment('Id do canal na Cloud API do WhatsApp.');
            $table->string('cha_app_name_api', 300)->nullable()->comment('Nome do app associado ao número na Gupshup');
            $table->string('cha_api_key', 500)->nullable()->comment('Chave que possibilita a troca de mensagens em uma API Oficial');
            $table->string('whatsapp_business_account_id', 500)->nullable()->comment('Id da conta do cliente (WABA) no WhatsApp');
            $table->timestamp('cha_due')->nullable()->comment('Data de vencimento da assinatura ou do trial do canal');
            $table->boolean('cha_subscription')->nullable()->comment('Indica se o canal foi assinado ou não');
            $table->boolean('cha_automatic_subscription_renewal')->default(0)->nullable()->comment('Indica se a renovação da assinatura do canal será feita de forma automática');
            $table->boolean('cha_trial')->nullable()->comment('Indica se o canal é trial ou não');
            $table->timestamps();

            $table->foreign('type_channel_id')
                ->references('id')
                ->on('man_type_channels')
                ->onDelete('cascade');
            
            $table->foreign('api_communication_id')
                ->references('id')
                ->on('sys_apis_communication')
                ->onDelete('cascade');
            
            $table->foreign('user_id_connection')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('man_channels');
    }
}
