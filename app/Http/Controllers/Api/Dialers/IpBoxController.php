<?php

namespace App\Http\Controllers\Api\Dialers;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Integration\DialerController;
use App\Http\Controllers\Management\CallController;
use App\Http\Controllers\Management\ChannelController;
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



class IpBoxController extends Controller
{
    
    public function webhookIpBox(Request $request)
    {
        Log::debug('IPBOX request');
        Log::debug($request);
        $contactController = new ContactController();
        $chatController = new ChatController();
        $serviceController = new ServiceController();
        $dialerController = new DialerController();
        $channelController = new ChannelController();
        $chatbotController = new ChatbotController();

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

                        //Se existir alguma configuração de transferência igualitária
                        if($fowardingSetting[0]->fair_distribution_id) {
                            $chat = $chatController->getChatById($chatId);
                            //Verifica se a distrinuição igualitária está configurada, se estiver, TRANSFERE de acordo  com a distribuição
                            $farTransferResponse = $chatbotController->fairTransfer($service->id, $service->chat_id, null, $chat->contact_id, $fowardingSetting[0]->fair_distribution_id);
                        }
                        else {
                            //Transfere o atendimento
                            $serviceController->transferService($requestTransferService, true);
                        }
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
        }
    }

    //Faz uma ligação para um determinado número
    public function callPhone($phoneData)
    {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC92ZXJpeC5jb20uYnIiLCJhdWQiOiJodHRwOlwvXC9pcGJveC5jb20uYnIiLCJpYXQiOjE2OTk1NTkyNDcsIm5iZiI6MTY5OTU1OTI0OSwiZGF0YSI6eyJ1c3VhcmlvX2lkIjoiMSIsInRva2VuX2lkIjoiQ1FNcXY3dm9waG9QTHpERmtobXUifX0.lTqmwZElSBsrHRHr3Em8FrxJmUteSKZE7IWeR9g8ADg';
        Log::debug('$phoneData');
        Log::debug($phoneData);

        $endPoint = 'https://devsky1.ipboxcloud.com.br:8358/ipbox/api/discarParaRamal';
        $bodyData = ['numero' => $phoneData['phone'], 'ramal' => $phoneData['extension'], 'aguardar_resposta' => 'Y'];
        
        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => $token
        ])
        ->timeout(10)
        ->asForm()
        ->post($endPoint, $bodyData);

        //Log::debug('Call $response');
        //Log::debug($response);

        return $response;
    }

    //Traz uma gravação de ligação
    public function getRecord($recordId)
    {
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC92ZXJpeC5jb20uYnIiLCJhdWQiOiJodHRwOlwvXC9pcGJveC5jb20uYnIiLCJpYXQiOjE2OTk1NTkyNDcsIm5iZiI6MTY5OTU1OTI0OSwiZGF0YSI6eyJ1c3VhcmlvX2lkIjoiMSIsInRva2VuX2lkIjoiQ1FNcXY3dm9waG9QTHpERmtobXUifX0.lTqmwZElSBsrHRHr3Em8FrxJmUteSKZE7IWeR9g8ADg';

        $endPoint = 'https://devsky1.ipboxcloud.com.br:8358/ipbox/api/getGravacao';
        $bodyData = ['uid' => $recordId];
        
        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => $token
        ])
        ->timeout(10)
        ->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('Record call response');
        Log::debug($response);

        return $response;

    }

    public function getRecordsRoutine()
    {
        $chatController = new ChatController();
        $eventController = new EventController();
        $userController = new UserController();
        $callController = new CallController();

        $calls = Call::whereNull('cal_record_name')
                    ->whereNull('cal_has_record')
                    ->whereNotNull('cal_call_api_id')
                    ->get();

        //Log::debug('$calls');
        //Log::debug($calls);
        //Para cada ligação sem gravação associada
        foreach($calls as $call) {
            //$response = self::getActiveCallByExtension('2500');
            $response = self::getRecord($call['cal_call_api_id']);

            //Se houver gravação associada
            if(isset($response['data']['gravacao'])) {
                $chats = $chatController->getChatsContact($call['contact_id']);
                //$response = Http::get($payloadMessage['payload']['url']);

                $fileNameArray = explode('-', $call['cal_call_api_id']);
                
                FFMpeg::openUrl($response['data']['gravacao'])
                    ->export()
                    //->toDisk('converted_songs')
                    ->inFormat(new \FFMpeg\Format\Audio\Mp3)
                    ->toDisk('spaces')
                    ->withVisibility('public')
                    ->save('public/chats/chat'.$chats[0]->id.'/calls/'.$fileNameArray[2].'.mp3');

                //Pega o tamanho do arquivo
                $recordSize = Storage::disk('spaces')->size('public/chats/chat'.$chats[0]->id.'/calls/'.$fileNameArray[2].'.mp3');

                $callProcessingData = [];
                $callProcessingData['id'] = $call['id'];
                $callProcessingData['cal_record_name'] = $fileNameArray[2];
                $callProcessingData['cal_call_date'] = $call['created_at'];

                //Se o áudio for maior que 2kb (ou seja, se de fato existir o áudio)
                if($recordSize > 2000) {
                    $callProcessingData['cal_has_record'] = 1; //Marca que tem ligação
                    $callProcessingData['cal_status'] = 'A';

                    $message = new ChatMessage();

                    $message->chat_id = $chats[0]->id;
                    $message->service_id = isset($service)? $service->id : null;
                    $message->type_user_id = 1; //Operador
                    $message->sender_id = $call['user_id'];
                    $message->mes_content_name = $fileNameArray[2].'.mp3';
                    $message->status_message_chat_id = 1; //Coloca a mensagem inicialmente com status enfileirada
                    $message->type_message_chat_id = 9; //Ligação
                    $message->mes_private = 0; //Não privada
                    $message->created_at = $call['created_at'];
                    $message->updated_at = $call['created_at'];

                    if($message->save()) {
                        $newMessageData = array(
                            'id' => $message->id, 
                            'chat_id' => $chats[0]->id, 
                            'type_message_chat_id' => 9,
                            'mes_content_type' => null,
                            'mes_content_name' => $fileNameArray[2].'.mp3',
                            'mes_url' =>  null,
                            'created_at' => $call['created_at'],
                            'senderId' => $call['user_id'],
                            'contactId' => $call['contact_id'],
                            'type_user_id' => 1, //Operador
                            'mes_private' => 0, //Não privada
                            'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                        );

                        $eventController->sendMessageChat($newMessageData, $call['user_id']);

                        //Traz todos os usuários que são GESTORES
                        $usersManagers = $userController->getUsersByRoles([1, 3]);

                        //Se existir algum GESTOR
                        if($usersManagers) {
                            //Envia a mensagem para o GESTOR
                            foreach ($usersManagers as $userManager) {
                                $eventController->sendMessageChat($newMessageData, $userManager->id);
                            }
                        }
                    }
                }
                else {
                    $callProcessingData['cal_has_record'] = 0; //Marca que NÃO tem ligação
                    $callProcessingData['cal_status'] = 'I';
                }

                $callProcessingData;
                $callController->updateCallProccessingStatus($callProcessingData);

            }
        }
    }
}
