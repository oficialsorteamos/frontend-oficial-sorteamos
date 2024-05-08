<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplatesMessagesCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cha_templates_messages_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tem_name')->comment('Nome da categoria do template');
            $table->string('tem_description', 1000)->comment('Descrição da categoria do template');
            $table->string('tem_tag')->comment('Tag que será enviada para criação da mensagem template');
            $table->string('tem_status')->default('A')->comment('Situação da categoria de um template');
            $table->timestamps();
        });


        // Insert default values
        DB::table('cha_templates_messages_categories')->insert(
            array(
                [
                    'id' => 1,
                    'tem_name' => 'Atualização de Conta',
                    'tem_description' => 'Usado para notificar o contato sobre alguma atualização em sua conta. (Ex: senha alterada, expiração de assinatura, etc.)',
                    'tem_tag' => 'ACCOUNT_UPDATE',
                ],
                [
                    'id' => 2,
                    'tem_name' => 'Atualização de Pagamento',
                    'tem_description' => 'Usado para notificar o contato sobre alguma atualização de pagamento. (Ex: pagamento confirmado, envio de recibo, etc.)',
                    'tem_tag' => 'PAYMENT_UPDATE',
                ],
                [
                    'id' => 3,
                    'tem_name' => 'Atualização de Finanças Pessoais',
                    'tem_description' => 'Usado para confirmar alguma atividade financeira de um contato. (Ex: lembrete de pagamento de contas, notificação de recibo de pagamento, etc.)',
                    'tem_tag' => 'PERSONAL_FINANCE_UPDATE',
                ],
                [
                    'id' => 4,
                    'tem_name' => 'Atualização de Envio',
                    'tem_description' => 'Usado para notificar um contato sobre o status de envio de algum produto comprado. (Ex: produto enviado, produto entregue, etc.)',
                    'tem_tag' => 'SHIPPING_UPDATE',
                ],
                [
                    'id' => 5,
                    'tem_name' => 'Atualização de Reserva',
                    'tem_description' => 'Usado para notificar um contato sobre uma atualização de uma reserva existente. (Ex: reserva de hotel cancelada, alteração de local, etc.)',
                    'tem_tag' => 'RESERVATION_UPDATE',
                ],
                [
                    'id' => 6,
                    'tem_name' => 'Atualização de Compromisso',
                    'tem_description' => 'Usado para notificar um contato sobre a alteração de um compromisso existente. (Ex: mudanças em horário de atendimento, agendamento cancelado, etc.)',
                    'tem_tag' => 'APPOINTMENT_UPDATE',
                ],
                [
                    'id' => 7,
                    'tem_name' => 'Resolução de Problema',
                    'tem_description' => 'Usado para notificar um contato sobre a atualização de um problema. (Ex: o problema foi resolvido, o problema requer informações adicionais, etc.)',
                    'tem_tag' => 'ISSUE_RESOLUTION',
                ],
                [
                    'id' => 8,
                    'tem_name' => 'Resposta Automática',
                    'tem_description' => 'Usado para notificar um contato sobre algum evento ou situação de forma automática. (Ex: nenhum operador online no momento, a empresa funciona de 08h às 18h, etc.)',
                    'tem_tag' => 'AUTO_REPLY',
                ],
                [
                    'id' => 9,
                    'tem_name' => 'Atualização de Alerta',
                    'tem_description' => 'Usado para notificar um contato sobre atualizações ou notícias importantes para os clientes. (Ex: você recebeu uma nova atualização de sistema, você foi notificado pela empresa, etc.)',
                    'tem_tag' => 'ALERT_UPDATE',
                ],
		[
                    'id' => 10,
                    'tem_name' => 'Atualização de Ingresso',
                    'tem_description' => 'Usado para notificar um contato sobre atualizações ou lembretes para um evento para qual a pessoa já possui um ingresso. (Ex: mudanças de horário de um show, mudanças no local do evento, etc.)',
                    'tem_tag' => 'TICKET_UPDATE',
                ],
		[
                    'id' => 11,
                    'tem_name' => 'Atualização de Transporte',
                    'tem_description' => 'Usado para notificar um contato sobre a atualização de uma reserva de transporte. (Ex: alteração de status de um vôo, a viagem foi iniciada, etc.)',
                    'tem_tag' => 'TRANSPORTATION_UPDATE',
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
        Schema::dropIfExists('cha_templates_messages_categories');
    }
}
