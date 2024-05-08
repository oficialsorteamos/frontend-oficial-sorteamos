<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\EventController;
use App\Models\Management\Channel\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use Auth;
use Exception;

class NodeApiController extends Controller
{
    const BASE_URL = 'http://localhost:3333';

    public function sendMessage($channel, $destination, $message)
    {   
        try {
            //Log::debug('dados para envio');
            //Log::debug($message);

            $endPoint = self::BASE_URL.'/message/text?key='.$channel->cha_session_name;

            //Se o tipo de mensagem for um texto
            if($message->type_message_chat_id == 1) {
                
                $messageData = ['id' => $destination, 'message' => $message->mes_message];      
            }
            //Se o tipo de mensagem for um áudio, imagem, vídeo ou arquivo
            else if($message->type_message_chat_id == 2 || $message->type_message_chat_id == 3 || $message->type_message_chat_id == 4 
                    || $message->type_message_chat_id == 5) {
                $endPoint = self::BASE_URL.$channel->cha_session_name.'/send-file-base64';
                //Se for uma imagem
                if($message->type_message_chat_id == 3) {
                    $filePath = storage_path("app/public/chats/chat".$message->chat_id."/images/".$message->mes_content_name);
                    //Pega a extensão da imagem
                    $type = pathinfo($filePath, PATHINFO_EXTENSION);
                    //Pega o arquivo
                    $data = file_get_contents($filePath);
                    //Converte em base64
                    $dataBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
                //Se for um arquivo
                else if($message->type_message_chat_id == 4) {
                    $filePath = storage_path("app/public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name);
                    $type = pathinfo($filePath, PATHINFO_EXTENSION);
                    $data = file_get_contents($filePath);
                    $dataBase64 = 'data:video/' . $type . ';base64,' . base64_encode($data);
                }
                //Se for um arquivo 
                else if($message->type_message_chat_id == 5) {
                    $filePath = storage_path("app/public/chats/chat".$message->chat_id."/files/".$message->mes_content_name);
                    $type = pathinfo($filePath, PATHINFO_EXTENSION);
                    $data = file_get_contents($filePath);
                    $dataBase64 = 'data:application/' . $type . ';base64,' . base64_encode($data);
                }
                //Se for um audio 
                else if($message->type_message_chat_id == 2) {
                    $filePath = storage_path("app/public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name);
                    $type = pathinfo($filePath, PATHINFO_EXTENSION);
                    $data = file_get_contents($filePath);
                    $dataBase64 = 'data:audio/wav/' . $type . ';base64,' . base64_encode($data);
                }
                
                //Log::debug('Arquivo em base64');
                //Log::debug($dataBase64);
                $messageData = ['phone' => $destination, 'base64' => $dataBase64];
                    
            }

            //Envia os dados
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Content-Type' => 'application/json; charset=utf-8'
            ])
            ->timeout(5)
            //->asForm()
            ->post($endPoint, $messageData);
            
            Log::debug('Resposta Node Api');
            Log::debug($response);

            
            if(isset($response['status'])) {
                if($response['status'] == 'Disconnected') {
                    $eventController = new EventController();
                    if(isset(Auth::user()->id)) {
                        //Envia uma notificação ao usuário comunicando que o canal está desconectado
                        $eventController->statusConnection($response['status'], null, Auth::user()->id);
                    }
                }
            }
            
            return $response;
            
        }
        catch(Exception $e) {

            Log::debug('exception lançada');
            Log::debug($e);

            $response['status'] = 'error'; 

            return $response;
        }
        
        
    }

    //Gera o token de acesso a API (Bearer Token)
    public function generateToken($channel)
    {   
        //$userId = Auth::user()->id;
        //Nome da sessão
        $sessionName = $channel['cha_phone_ddi'].$channel['cha_phone_number'];

        $endPoint = self::BASE_URL.$sessionName.'/THISISMYSECURETOKEN/generate-token';
        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(5)
        //->asForm()
        ->post($endPoint);

        //Se a sessão foi gerada com sucesso
        if($response['status'] == 'success') {
            $channel = Channel::find($channel['id']);
            $channel->cha_session_name = $sessionName; 
            $channel->cha_session_token = $response['token'];
            $channel->save();
        }

        return $channel;
    }

    //Inicia uma instância, retornando o qrCode
    public function startSession($channel, $user)
    {   
        //Fecha qualquer sessão que possa estar aberta para o canal na API
        //self::closeSession($channel);

        $endPoint = self::BASE_URL.'/instance/init';

        /*Log::debug('Start na conexão');
        $channel = Channel::find($channel['id']);
        $channel->cha_session_token = null;
        $channel->user_id_connection = $user['id'];
        $channel->save();*/

        $keyInstance = $channel['cha_phone_ddi'].$channel['cha_phone_number'];

        $bodyData = ['key' => $keyInstance, 'webhook' => 'true' ,'webhookUrl' => env('WEBHOOK_URL_ZAPI').'/'.$channel['id']];
        
        //$channel = self::generateToken($channel);

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
                        ->timeout(15)
                        //->asForm()
                        ->get($endPoint, $bodyData);

        Log::debug('Resposta criação de instância da Node Api');
        Log::debug($response);

        if($response['error'] == false) {
            sleep(3);
            $qrcodeResponse = self::getQrCode($keyInstance);

            $eventController = new EventController();
            $channelController = new ChannelController();
            $channelLogin = $channelController->getChannelBySessionName($keyInstance);
            //Envia o qrcode para ser exibido ao usuário
            $eventController->sendQrCode($qrcodeResponse['qrcode'], $channelLogin['user_id_connection']);
        }
 
        return $response;
    }

    //Pega o QrCode associado a instância
    public function getQrCode($keyInstance)
    {
        $endPoint = self::BASE_URL.'/instance/qrbase64?key='.$keyInstance;

        //$bodyData = ['key' => $keyInstance];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
                        ->timeout(15)
                        //->asForm()
                        ->get($endPoint);

        Log::debug('Qrcode da Node Api');
        Log::debug($response);

        return $response;
    }

    //Fecha a sessão, retornando o qrCode
    public function closeSession($channel)
    {
        $keyInstance = $channel['cha_phone_ddi'].$channel['cha_phone_number'];

        $endPoint = self::BASE_URL.'/instance/logout?key='.$keyInstance;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
        ])
        ->timeout(15)
        //->asForm()
        ->get($endPoint);

        Log::debug('Resposta sessão fechada da Node Api');
        Log::debug($response);

        return $response;
    }

    //Fecha a sessão, retornando o qrCode
    public function removeInstance($channel)
    {
        $keyInstance = $channel['cha_phone_ddi'].$channel['cha_phone_number'];

        $endPoint = self::BASE_URL.'/instance/delete?key='.$keyInstance;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
        ])
        ->timeout(15)
        //->asForm()
        ->get($endPoint);

        Log::debug('Resposta instância deletada da Node Api');
        Log::debug($response);

        return $response;
    }

    //Verifica se sessão está conectada
    public function checkConnectionSession($channel)
    {
        try {
            $channel = Channel::find($channel['id']);
        
            $endPoint = self::BASE_URL.$channel['cha_session_name'].'/check-connection-session';

            $response = null;

            //Envia os dados
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Authorization' => 'Bearer '.$channel['cha_session_token']
            ])
            ->timeout(5) //Número máximo em segundos aguardando uma resposta da API
            //->asForm()
            ->get($endPoint);

            Log::debug('Status da sessão');
            Log::debug($response);

            return $response;

        }//Se o canal não respondeu
        catch(Exception $e) {

            Log::debug('exception lançada');
            Log::debug($e);

            return null;
        }
    }

    public function webhookNodeApi(Request $callbackApi, $channelId)
    {
        Log::debug('Dados webhook da Node API');
        Log::debug($callbackApi);

        Log::debug('Id do Canal webhook da Node API');
        Log::debug($channelId);

        //Caso seja um evento de mensagem
        if($callbackApi->type == 'message') {
            //Caso os dados recebidos sejam da API NÃO OFICIAL
            if($callbackApi->event == 'onmessage') {
                $apiName = 'wppconnect';
                $payloadMessage = [];
                //Pega o id da mensagem
                $payloadMessage['id'] = $callbackApi->id;
                //Caso seja mensagem de texto
                if($callbackApi->type == 'chat') {
                    $payloadMessage['type'] = 'text';
                    $payloadMessage['payload']['text'] = $callbackApi->content;
                } //Se o usuário enviou um contato
                else if($callbackApi->type == 'vcard') {
                    $payloadMessage['type'] = 'vcard';
                    $payloadMessage['payload']['contactName'] = $callbackApi->vcardFormattedName;
                    
                    //Extrai o telefone do contato compartilhado
                    $contentVcard = explode('waid=',$callbackApi->content);
                    $contentVcard = explode(':',$contentVcard[1]);
                    $contactPhoneNumber = $contentVcard[0]; 
                    
                    $payloadMessage['payload']['contactPhoneNumber'] = $contactPhoneNumber;
                }
                else {
                    $payloadMessage['payload']['contentType'] = $callbackApi->mimetype;
                    $payloadMessage['payload']['base64'] = $callbackApi->body;
                    
                    if($callbackApi->type == 'document') {
                        $payloadMessage['type'] = 'file';
                        $payloadMessage['payload']['caption'] = $callbackApi->caption;
                    }
                    else if($callbackApi->type == 'ptt') {
                        $payloadMessage['type'] = 'audio';
                    }
                    else if($callbackApi->type == 'location') {
                        $payloadMessage['type'] = 'location';
                        $payloadMessage['payload']['latitude'] = $callbackApi->lat;
                        $payloadMessage['payload']['longitude'] = $callbackApi->lng;
                    }
                    else {
                        $payloadMessage['type'] = $callbackApi->type;
                    }
                }

                //Pega o número de telefone de quem enviou a mensagem
                if(isset($callbackApi->author)) {
                    $dividePhone = explode('@', $callbackApi->author);
                }
                else {
                    $dividePhone = explode('@', $callbackApi->from);
                }
                $payloadMessage['sender']['phone'] = $dividePhone[0];

                //Dados do contato
                $contactData = new Request([
                    'name'   => isset($callbackApi->sender['name'])? $callbackApi->sender['name'] : $callbackApi->sender['pushname'],
                    'phoneNumber' => $payloadMessage['sender']['phone'],
                    'avatarUrl' => isset($callbackApi->sender['profilePicThumbObj']['eurl'])? $callbackApi->sender['profilePicThumbObj']['eurl'] : null,
                ]);
            }
            //Se for uma mensagem de um contato via API OFICIAL
            if($callbackApi->type == 'message') {
                $apiName = 'gupshup';
                $payloadMessage = $callbackApi->payload;
                $contactData = new Request([
                    'name'   => $payloadMessage['sender']['name'],
                    'phoneNumber' => $payloadMessage['sender']['phone'],
                ]);
            }

            //Busca o contato, se houver
            $contact = Contact::with('blocked')->where('con_phone', $payloadMessage['sender']['phone'])->first();

            //Se o contato NÃO existe
            if(!$contact) {
                //Salva o contato no banco de dados
                $contact = $this->contactController->store($contactData);
                $contactData = json_encode($contact);
                $contactData = json_decode($contactData, true);
                //Pega os dados do novo contato
                $contact = $contactData['original']['contact'];
                $chat = $contactData['original']['chat'];
            }
            else {
                //Verifica se já existe chat para este contato
                $chat = Chat::where('contact_id', $contact->id)->first();
            }
            
            //Se o contato NÃO estiver bloqueado
            if($contact['blocked'] == null ) {
                //Verifica se a mensagem já não foi gravada antes
                $checkMessage = ChatMessage::where('api_message_id', $payloadMessage['id'])->first();
                //Se a mensagem ainda não foi gravada
                if(!$checkMessage) {
                    $channelData['sessionName'] = $callbackApi->session;
                    //Só grava a mensagem se o contato enviou algum tipo de mensagem suportada pela plataforma
                    if(isset($payloadMessage['type'])) {
                        self::storeMessage($chat['id'], 2, $contact['id'], $payloadMessage, null, $apiName, 'false', $channelData, null, null);
                        //Incrementa o número de mensagens não visualizadas em 1
                        self::incrementUnseenMessage($chat['id']);
                    }
                }
            }
        }
        //Se for um evento da mensagem (mensagem entregue, enviada, etc.)
        else if($callbackApi->type == 'message-event' || $callbackApi->event == 'onack') {
            //Se for um evento da API OFICIAL
            if($callbackApi->type == 'message-event') {
                //Se a mensagem foi enfileirada
                if($callbackApi->payload['type'] == 'enqueued') {
                    $statusMessageChatId = 1;
                    $apiMessageId = $callbackApi->payload['id'];
                }
                else {
                    $apiMessageId = $callbackApi->payload['gsId'];
                    //Se a mensagem foi enviada
                    if($callbackApi->payload['type'] == 'sent') {
                        $statusMessageChatId = 2;
                    } //Se a mensagem foi entregue
                    else if($callbackApi->payload['type'] == 'delivered') {
                        $statusMessageChatId = 3;
                    }
                }
            }
            //Se for um evento da API NÃO OFICIAL
            else {
                //Pega o id da mensagem
                $apiMessageId = $callbackApi->id['_serialized'];
                //Se a mensagem foi ENVIADA
                if($callbackApi->ack == 1) {
                    $statusMessageChatId = 2;
                }
                //Se a mensagem foi ENTREGUE
                else if($callbackApi->ack == 2) {
                    $statusMessageChatId = 3;
                }
                //Se a mensagem foi ENTREGUE (As vezes, mensagens entregues vem com o id 3(pode ser que represente o status de mensagem LIDA))
                else if($callbackApi->ack == 3) {
                    $statusMessageChatId = 3;
                }
            }

            //Pega a mensagem que deverá ter o status atualizado
            $messageChat = ChatMessage::where('api_message_id', $apiMessageId)->first();
            //Se a mensagem a ser atualizada existe
            if($messageChat)
            {   
                //Se a mensagem já não possui status de entregue
                if($messageChat->status_message_chat_id !=3) {
                    //Atualiza os status da mensagem (Enviado, entregue, etc.)
                    $messageChat->status_message_chat_id = $statusMessageChatId;
                    $messageChat->save();

                    //Atualiza o status da mensagem na tela do OPERADOR que enviou a mensagem
                    $this->eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $messageChat->sender_id);

                    //Traz todos os gestores
                    $managerUsers = $this->userController->getUsersByRoles([1, 3]);
                    //Para cada gestor, envia o status da mensagem
                    foreach($managerUsers as $manager) {
                        $this->eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $manager->id);
                    }
                }
            }
        }
        //Caso seja o qrCode para autenticação de sessão no Whatsapp
        else if(isset($callbackApi->event) && $callbackApi->event == 'qrcode') {
            //Caso a resposta da API tenha vindo com o nome da sessão (o nome contém o id do usuário)
            if($callbackApi->session) {
                $channelController = new ChannelController();
                $channelLogin = $channelController->getChannelBySessionName($callbackApi->session);
                //Envia o qrcode para ser exibido ao usuário
                $this->eventController->sendQrCode('data:image/jpeg;base64,'.$callbackApi->qrcode, $channelLogin['user_id_connection']);
            }
            else {
                //Traz todos os gestores
                $managerUsers = $this->userController->getUsersByRoles([1, 3]);
                //Para cada gestor, envia o qrCode
                foreach($managerUsers as $userChannel) {
                    //Envia o qrcode para ser exibido ao usuário
                    $this->eventController->sendQrCode('data:image/jpeg;base64,'.$callbackApi->qrcode, $userChannel->id);
                }
            }
             
        }
        else if(isset($callbackApi->event) && $callbackApi->event == 'status-find') {
            //Caso a resposta da API tenha vindo com o nome da sessão (o nome contém o id do usuário)
            if($callbackApi->session) {
                $channelController = new ChannelController();
                $channelLogin = $channelController->getChannelBySessionName($callbackApi->session);
                //Envia o status da conexão com o whatsapp para ser exibido no sistema
                $this->eventController->statusConnection($callbackApi->status, $callbackApi->session, $channelLogin['user_id_connection']);
            }
            else {
                //Traz todos os gestores
                $managerUsers = $this->userController->getUsersByRoles([1, 3]);
                //Para cada gestor, envia o qrCode
                foreach($managerUsers as $userChannel) {
                    //Envia o qrcode para ser exibido ao usuário
                    //$this->eventController->sendQrCode($callbackApi->qrcode, $userChannel->id);
                    //Envia o status da conexão com o whatsapp para ser exibido no sistema
                    $this->eventController->statusConnection($callbackApi->status, $callbackApi->session, $userChannel->id);
                }
            }
        }
    }
}
