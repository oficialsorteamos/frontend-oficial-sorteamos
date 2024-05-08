<?php

namespace App\Http\Controllers\Api\Dialers;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Integration\DialerController;
use App\Http\Controllers\Management\CallController;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\UserController;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\Service;
use App\Models\Contact\Contact;
use App\Models\Management\Call\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use FFMpeg;



class AzCallController extends Controller
{
    
    public function webhookAzCall(Request $request)
    {
        Log::debug('AZCALL request');
        Log::debug($request);

        /*$contactController = new ContactController();
        $chatController = new ChatController();
        $serviceController = new ServiceController();
        $dialerController = new DialerController();

        //Se foi definido o canal no mailing
        if($request['channel'] && $request['channel'] != '[canal]') {
            $is0800 = substr($request['channel'], 0, 3);
            //Se o canal for um 0800
            if($is0800 == '800') {
                //Na cloud api, o 0800 não vem com o número 0 na frente
                $channelPhoneNumber = '0'.$request['channel'];
            }
            else {
                $channelPhoneNumber = $request['channel'];
            }
            //Log::debug('número do canal');
            //Log::debug($channelPhoneNumber);
            $fowardingSetting = $dialerController->getFowardingSettingByChannel($channelPhoneNumber);
        } //Se NÃO foi definido o canal que deverá enviar a mensagem via WhatsApp no mailing
        else {
            $fowardingSetting = $dialerController->getRandomFowardingSetting();
        }

        //Se existe uma configuração de encaminhamento
        if($fowardingSetting != '[]') {
            Log::debug('$fowardingSetting');
            Log::debug($fowardingSetting);
            $contactPhoneNumber = '55'.$request['phone'];
            //Verifica se o contato já existe no sistema
            $contact = $contactController->getContactByPhoneNumber($contactPhoneNumber);
            //Se o contato NÃO existe
            if(!$contact) {
                //Dados do contato
                $contactData = new Request([
                    'name'   => $request['name'],
                    'phoneNumber' => $contactPhoneNumber,
                ]);
                //Salva o contato no banco de dados
                $contact = $contactController->store($contactData);
                $contactData = json_encode($contact);
                $contactData = json_decode($contactData, true);
                //Pega os dados do novo contato
                $contact = $contactData['original']['contact'];
                $chat = $contactData['original']['chat'];
            }
            else {
                //Pega o chat associado ao contato
                $chat = $chatController->getChatsContact($contact->id);
            }
            //Log::debug('$chat contact');
            //Log::debug($chat);

            $chatId = isset($chat[0]->id)? $chat[0]->id : $chat['id'];

            //Traz o atendimento que está aberto referente a um contato, se houver
            $service = $serviceController->getServiceByContactAndStatus($chatId, 1);

            //Se o contato NÃO possui atendimento em ABERTO
            if(!$service) {
                $service = new Service();
                $service->chat_id = $chatId;
                $service->channel_id = $fowardingSetting[0]->channel_id;
                $service->type_status_service_id = 1;
                $service->dialer_fowarding_setting_id = $fowardingSetting[0]->id;
                $service->ser_protocol_number = $chatController->generateProtocolNumber(); 
                //Caso o atendimento seja armazenado no banco de dados con sucesso
                if($service->save()) {
                    //Se não existe chatbot associada à configuração de encaminhamento
                    if(!$fowardingSetting[0]->chatbot_id) {
                        $requestTransferService = new Request([
                            'transferData' => [
                                'chatId'   => $chatId,
                                'department' => [
                                    'id' => $fowardingSetting[0]->department_id
                                ],
                            ]
                        ]);

                        //Transfere o atendimento
                        $serviceController->transferService($requestTransferService, true);
                    }

                    //Se envio de mensagem automática estiver ATIVADO
                    if($fowardingSetting[0]->send_message == 1) {
                        //Log::debug('entrou aqui no envio');
                        //Dados para envio da mensagem
                        $messageData = new Request([
                            'contactId'   => $contact['id'],
                            'typeUserId' => 3, //Robô
                            'privateMessage' => 'false',
                            'senderId' => 1, //Robô
                            'actionId' => null,
                            'message' => $fowardingSetting[0]->dia_message,
                            'template_id' => $fowardingSetting[0]->template_id,
                        ]);
                        //Envia a mensagem para o cliente
                        $chatController->sendMessage($messageData);
                    }
                }
            }
        }*/
    }
}
