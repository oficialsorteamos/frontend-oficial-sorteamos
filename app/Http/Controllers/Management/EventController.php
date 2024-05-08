<?php

namespace App\Http\Controllers\Management;

use App\Events\Api\SendQrCode;
use App\Events\Api\StatusConnection;
use App\Events\Chat\StatusMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Events\Chat\SendMessage;
use App\Events\Chat\UpdateChat;
use App\Events\Chat\UpdateService;
use App\Events\Chat\UpdateStatusMessage;
use App\Events\Management\User\SendAlertNotification;
use App\Events\Service\UpdateServiceProgress;
use Illuminate\Support\Facades\Event;


class EventController extends Controller
{   //Transmite a mensagem enviada pelo contato para ser inserida no chat
    public function sendMessageChat($newMessageData, $userIdSendEvent)
    {
        Event::dispatch(new sendMessage($newMessageData, $userIdSendEvent));   
    }

    //Atualiza os chats de acordo com o status do mesmo (Chats Ativos, Chats Pendentes)
    public function updateChats($userIdSendEvent)
    {
        Event::dispatch(new UpdateChat($userIdSendEvent));   
    }
    //Traz o QrCode
    public function sendQrCode($qrCode, $userIdSendEvent)
    {
        Event::dispatch(new SendQrCode($qrCode, $userIdSendEvent));   
    }

    //Atualiza o status da conexão de acordo com a API
    public function statusConnection($status, $session, $userIdSendEvent)
    {
        Event::dispatch(new StatusConnection($status, $session, $userIdSendEvent));   
    }
    
    //Atualiza na tela de atendimento os atendimentos em progresso (Autoatendimento, Pendentes e Em Atendimento)
    public function updateServiceProgress($userIdSendEvent)
    {
        Event::dispatch(new UpdateServiceProgress($userIdSendEvent));   
    }

    //Atualiza a situação (pendente, ativo, etc.) do atendimento para todos os operadores
    //Usado para realizar as restrições de quais operadores podem iteragir com os contatos que estão em atendimento
    public function updateSituationService($userIdSendEvent, $situationService)
    {
        Event::dispatch(new UpdateService($userIdSendEvent, $situationService));   
    }

    //Envia um alerta do gestor para os usuários
    public function sendAlertNotification($newMessageData, $userId, $senderName)
    {
        Event::dispatch(new SendAlertNotification($newMessageData, $userId, $senderName));   
    }

    //Atualiza o status da conexão de acordo com a API
    public function statusMessage($statusMessage, $error, $userIdSendEvent)
    {
        Event::dispatch(new StatusMessage($statusMessage, $error, $userIdSendEvent));   
    }

    //Atualiza o status da conexão de acordo com a API
    public function updateStatusMessage($messageId, $statusId, $userIdSendEvent)
    {
        Event::dispatch(new UpdateStatusMessage($messageId, $statusId, $userIdSendEvent));   
    }
}
