<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Setting\CustomerController;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Chat\Action;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatMessage;
use App\Models\Contact\Contact;
use App\Models\Management\Channel\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use Auth;
use Exception;

class ApiZapController extends Controller
{
    const BASE_URL = 'https://api.apizap.me/v1/';

    public function sendMessage($channel, $destination, $message)
    {   
        try {
            $utilsController = new UtilsController();
            Log::debug('sendMessage api zap');
            Log::debug($message);
            $endPoint = self::BASE_URL.'message';

            //Se o tipo de mensagem for um texto
            if($message->type_message_chat_id == 1) {
                $endPoint = $endPoint.'/text';
                $messageData = ['numero' => $destination, 'texto' => $message->mes_message, 'tokenKey' => $channel['cha_session_token']];
            }
            //Se for uma imagem
            else if($message->type_message_chat_id == 3) {
                $endPoint = $endPoint.'/media';
                //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/images/".$message->mes_content_name);
                $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/images/".$message->mes_content_name;
                //Pega o arquivo
                //$data = file_get_contents($filePath);
                //$data = Http::get($filePath);
                //Converte para base64
                //$dataBase64 = $utilsController->convertToBase64($filePath, $data->body(), $message->type_message_chat_id);
                $messageData = ['tokenKey' => $channel['cha_session_token'], 'numero' => $destination, 'type' => 'image', 'caption' => '', 'url' => $filePath];
            }
            //Se for um arquivo 
            else if($message->type_message_chat_id == 5) {
                //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/files/".$message->mes_content_name);
                $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/files/".$message->mes_content_name;
                //$type = pathinfo($filePath, PATHINFO_EXTENSION);
                $endPoint = $endPoint.'/media';
                //$data = file_get_contents($filePath);
                //$data = Http::get($filePath);
                //$dataBase64 = $utilsController->convertToBase64($filePath, $data->body(), $message->type_message_chat_id);
                $fileName = explode('.', $message->mes_media_original_name);
                //$messageData = ['phone' => $destination, 'document' => $dataBase64, 'fileName' => $fileName[0], 'messageId' => $message->answered_message_id];
                $messageData = ['tokenKey' => $channel['cha_session_token'], 'numero' => $destination, 'type' => 'document', 'caption' => $fileName[0], 'url' => $filePath];
            }
            //Se for um audio 
            else if($message->type_message_chat_id == 2) {
                $endPoint = $endPoint.'/media';
                //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name);
                $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name;
                //$data = file_get_contents($filePath);
                //$data = Http::get($filePath);
                //$dataBase64 = $utilsController->convertToBase64($filePath, $data->body(), $message->type_message_chat_id);
                //$messageData = ['phone' => $destination, 'audio' => $dataBase64, 'messageId' => $message->answered_message_id];
                $messageData = ['tokenKey' => $channel['cha_session_token'], 'numero' => $destination, 'type' => 'audio', 'caption' => '', 'url' => $filePath];
            }
            //Se for um vídeo
            else if($message->type_message_chat_id == 4) {
                $endPoint = $endPoint.'/media';
                //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name);
                $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name;
                //$data = file_get_contents($filePath);
                //$data = Http::get($filePath);
                //$dataBase64 = $utilsController->convertToBase64($filePath, $data->body(), $message->type_message_chat_id);
                //$messageData = ['phone' => $destination, 'video' => $dataBase64, 'messageId' => $message->answered_message_id];
                $messageData = ['tokenKey' => $channel['cha_session_token'], 'numero' => $destination, 'type' => 'video', 'caption' => '', 'url' => $filePath];
            }
            
            //Envia os dados
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Content-Type' => 'application/json; charset=utf-8'
            ])
            ->timeout(60)
            //->asForm()
            ->post($endPoint, $messageData);
            
            Log::debug('Resposta sendMessage API ZAP');
            Log::debug($response);

            
            $responseData = [];
            //Se a mensagem foi ENVIADA
            if($response['result'] == 'success') {
                $responseData['status'] = 'success';
                $responseData['message']['id'] = $response['idMessage'];
            }

            return $responseData;
        }
        catch(Exception $e) {

            Log::debug('exception lançada');
            Log::debug($e);

            $response['status'] = 'error'; 

            return $response;
        } 
    }

    public function sendQuickMessageWithParameters($channel, $destination, $message)
    {
        $endPoint = self::BASE_URL.'message';
        $buttons = [];
        $typeButtons = 1;
        $hasImage = 0;
        $hasAudio = 0;
        $hasFile = 0;
        $hasMovie = 0;
        $hasButton = 0;

        $utilsController = new UtilsController();

        if(is_object($message)) {
            $messageAux  = (array) $message;
            if(isset($messageAux['quickMessageData'])) {
                $message = $messageAux;
            }
        }

        Log::debug('message send');
        Log::debug($message);

        foreach($message['quickMessageData']['parameters'] AS $key => $parameter) {
            //Log::debug('parameter');
            //Log::debug($parameter);
            //Se for um botão
            if($parameter['type_parameter_id'] == 1) {
                $hasButton = 1;  
                //Se for um botão de resposta rápida
                if($parameter['type_button_id'] == 1) {
                    $typeButtons = 1;
                    $buttonArray = array('id' => $key+1, 'label' => $parameter['qui_content']);
                }
                //Se for botões de Ação
                else if($parameter['type_button_id'] == 2) {
                    $typeButtons = 2;
                    //Se o botão for um LINK
                    if($parameter['qui_url']) {
                        $buttonArray = array('id' => $key+1, 'type' => 'URL', 'url' => $parameter['qui_url'], 'label' => $parameter['qui_content']);
                    } //Se for um telefone
                    else {
                        $buttonArray = array('id' => $key+1, 'type' => 'CALL', 'phone' => $parameter['qui_phone_number'], 'label' => $parameter['qui_content']);
                    }
                }
                //Insere o botão na array de botões
                array_push($buttons, $buttonArray);
            } //Se for uma imagem
            else if($parameter['type_parameter_id'] == 2) {
                $hasImage = 1;
                $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/quickMessages/quickMessage'.$parameter['quick_message_id'].'/header/'.$parameter['qui_media_name'];
            } //Se for um áudio
            else if($parameter['type_parameter_id'] == 3) {
                $hasAudio = 1;
                $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/quickMessages/quickMessage'.$parameter['quick_message_id'].'/header/'.$parameter['qui_media_name'];
            } //Se for um arquivo
            else if($parameter['type_parameter_id'] == 4) {
                $hasFile = 1;
                $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/quickMessages/quickMessage'.$parameter['quick_message_id'].'/header/'.$parameter['qui_media_name'];
                $fileNameData = explode('.', $parameter['qui_media_original_name']) ;
                $fileName = $fileNameData[0];
            } //Se for um vídeo
            else if($parameter['type_parameter_id'] == 5) {
                $hasMovie = 1;
                $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/quickMessages/quickMessage'.$parameter['quick_message_id'].'/header/'.$parameter['qui_media_name'];
                $fileNameData = explode('.', $parameter['qui_media_original_name']) ;
                $fileName = $fileNameData[0];
            }
            
        }
        //Se tem algum botão adicionado
        if($hasButton) {
            //Se for botão ou tem imagem 
            if($typeButtons == 1 || $hasImage) {
                $endPoint = $endPoint.'/send-button-list';
                
                if($hasImage) {
                    $messageData = ['phone' => $destination, 'message' => $message['mes_message'], 'buttonList' => array('image' => $urlMedia, 'buttons' =>$buttons)];
                }
                else {
                    $messageData = ['phone' => $destination, 'message' => $message['mes_message'], 'buttonList' => array('buttons' =>$buttons)];
                }
            }
            else {
                $endPoint = $endPoint.'/send-button-actions';
                $messageData = ['phone' => $destination, 'message' => $message['mes_message'], 'buttonActions' => $buttons];
            }
        } //Se não tem botão
        else {
            //Se tem imagem
            if($hasImage) {
                $endPoint = $endPoint.'/media';
                //$messageData = ['phone' => $destination, 'image' => $urlMedia, 'caption' => $message['mes_message']];
                $messageData = ['tokenKey' => $channel['cha_session_token'], 'numero' => $destination, 'type' => 'image', 'caption' => $message['mes_message'], 'url' => $urlMedia];
            } //Se for uma mensagem de áudio
            else if($hasAudio) {
                $endPoint = $endPoint.'/media';

                //$data = Http::get($urlMedia);
                //$dataBase64 = $utilsController->convertToBase64($urlMedia, $data->body(), 2);
                //$messageData = ['phone' => $destination, 'audio' => $dataBase64];
                $messageData = ['tokenKey' => $channel['cha_session_token'], 'numero' => $destination, 'type' => 'audio', 'caption' => $message['mes_message'], 'url' => $urlMedia];
            } //Se for uma mensagem de arquivo
            else if($hasFile) {
                $type = pathinfo($urlMedia, PATHINFO_EXTENSION);
                $endPoint = $endPoint.'/media';

                //$data = Http::get($urlMedia);
                //$dataBase64 = $utilsController->convertToBase64($urlMedia, $data->body(), 5);
                
                //$messageData = ['phone' => $destination, 'document' => $dataBase64, 'fileName' => $fileName];
                $messageData = ['tokenKey' => $channel['cha_session_token'], 'numero' => $destination, 'type' => 'document', 'caption' => $message['mes_message'], 'url' => $urlMedia];
            } //Se for uma mensagem de vídeo
            else if($hasMovie) {
                $endPoint = $endPoint.'/media';
                
                //$data = file_get_contents($filePath);
                //$data = Http::get($urlMedia);
                //$dataBase64 = $utilsController->convertToBase64($urlMedia, $data->body(), 4);
                //$messageData = ['phone' => $destination, 'video' => $dataBase64, 'messageId' => $message->answered_message_id, 'caption' => $message['mes_message']];
                $messageData = ['tokenKey' => $channel['cha_session_token'], 'numero' => $destination, 'type' => 'video', 'caption' => $message['mes_message'], 'url' => $urlMedia];
            }
            //Se não tem botão nem imagem (SE FOR APENAS TEXTO)
            else {
                $endPoint = $endPoint.'/text';
                //$messageData = ['phone' => $destination, 'message' => $message['mes_message']];
                $messageData = ['numero' => $destination, 'texto' => $message['mes_message'], 'tokenKey' => $channel['cha_session_token']];
            }
        }

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(60)
        //->asForm()
        ->post($endPoint, $messageData);
        
        Log::debug('Resposta sendMessage Z-API');
        Log::debug($response);

        
        $responseData = [];
        
        //Se a mensagem foi ENVIADA
        if($response['result'] == 'success') {
            $responseData['status'] = 'success';
            $responseData['message']['id'] = $response['idMessage'];
        }

        return $responseData;
    }

    //Cria uma nova instância
    public function createInstance($channel)
    {
        $endPoint = self::BASE_URL.'manager/create';

        //Nome da instância
        $instanceName = self::createInstanceName($channel);

        $bodyData = ['instanceName' => $instanceName, 'webhookUrl' => env('WEBHOOK_URL_APIZAP').'/'.$channel['id']];

        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '. env('APIZAP_TOKEN')
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('response createInstance APIZAP');
        Log::debug($response);
        return $response;
    }

    //Assina uma instância
    public function subscriptionInstance($channel)
    {   
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/token/'.$channel['cha_session_token'].'/integrator/on-demand/subscription';

        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '. env('ZAPI_TOKEN')
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint);

        Log::debug('Resposta subscriptionInstance Z-API');
        Log::debug($response);

        return $response;
    }

    //Cancela (exclui) uma instância
    public function cancelInstance($channel)
    {   
        $endPoint = self::BASE_URL.'manager/delete?tokenKey='.$channel['cha_session_token'];
        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '. env('APIZAP_TOKEN')
        ])
        ->timeout(30)
        //->asForm()
        ->delete($endPoint);

        Log::debug('Resposta cancelInstance(delete) API ZAP');
        Log::debug($response);

        return $response;
    }

    public function listInstances()
    {
        $endPoint = self::BASE_URL.'manager/list';

        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '. env('APIZAP_TOKEN')
        ])
        ->timeout(60)
        //->asForm()
        ->get($endPoint);

        Log::debug('response listInstances APIZAP');
        Log::debug($response);
        return $response;
    }

    //Atualiza o webhook de entrega de mensagem
    public function setWebhookDelivery($channel)
    {   
        $endPoint = self::BASE_URL.'instance/updateWebhook';

        //Fecha qualquer sessão que possa estar aberta para o canal na API
        //self::closeSession($channel);

        $bodyData = ['tokenKey' => $channel['cha_session_token'], 'webhookUrl' => env('WEBHOOK_URL_APIZAP').'/'.$channel['id']];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('Resposta setWebhookDelivery API ZAP');
        Log::debug($response);
    }

    //Seta os Webhooks na API ZAP
    public function setWebhook($channel)
    {
        self::setWebhookDelivery($channel);
    }

    //Limpa a fila de mensagens
    public function clearQueue($channel)
    {   
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/token/'.$channel['cha_session_token'].'/queue';
        
        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(30)
        ->delete($endPoint);

        Log::debug('Resposta clearQueue Z-API');
        Log::debug($response);
    }

    //Cria um nome para uma instância
    public function createInstanceName($channel)
    {
        $customerController = new CustomerController();
        //Traz os dados da empresa
        $customerData = $customerController->getCustomer();
        $customerCnpjCpf = $customerData[0]->com_cnpj? $customerData[0]->com_cnpj : $customerData[0]->com_cpf;
        //Nome da instância
        $instanceName = $customerData[0]->com_name. ' - '.$customerCnpjCpf. ' - ' .$channel['cha_phone_ddi'].$channel['cha_phone_number'];

        return $instanceName;
    }

    //Atualiza o nome de uma intância
    public function updateInstanceName($channel)
    {   
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/token/'.$channel['cha_session_token'].'/update-name';

        //Nome da instância
        $instanceName = self::createInstanceName($channel);

        $bodyData = ['value' => $instanceName];

        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '. env('ZAPI_TOKEN')
        ])
        ->timeout(30)
        //->asForm()
        ->put($endPoint, $bodyData);

        Log::debug('Resposta updateInstanceName Z-API');
        Log::debug($response);
    }

    //Inicia a sessão, retornando o qrCode
    public function startSession($channel, $user)
    {   
        $endPoint = self::BASE_URL.'instance/qrcode_base64?tokenKey='.$channel['cha_session_token'].'&online=true';

        $eventController = new EventController();

        //Fecha qualquer sessão que possa estar aberta para o canal na API
        self::closeSession($channel);

        $channel = Channel::find($channel['id']);
        $channel->user_id_connection = $user['id'];
        $channel->save();

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(30)
        //->asForm()
        ->get($endPoint);

        Log::debug('Resposta startSession API ZAP');
        Log::debug($response);

        if(isset($response['qrcode_base64'])) {
            $eventController->sendQrCode($response['qrcode_base64'], $channel['user_id_connection']);
        }

        return $response;
    }

    //Fecha a sessão, retornando o qrCode
    public function closeSession($channel)
    {
        $endPoint = self::BASE_URL.'instance/logout?tokenKey='.$channel['cha_session_token'];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(30)
        //->asForm()
        ->delete($endPoint);

        Log::debug('Resposta closeSession API ZAP');
        Log::debug($response);

        return $response;
    }

    //Verifica se sessão está conectada
    public function checkConnectionSession($channel)
    {
        try {
            $channel = Channel::find($channel['id']);
        
            $endPoint = self::BASE_URL.'instance/info?tokenKey='.$channel['cha_session_token'];

            $response = null;

            //Envia os dados
            $response = Http::withHeaders([
                'Accept' => '*/*',
            ])
            ->timeout(30) //Número máximo em segundos aguardando uma resposta da API
            //->asForm()
            ->get($endPoint);

            Log::debug('Status da sessão API ZAP');
            Log::debug($response);

            $responseData = [];
            //Se o canal está conectado
            if(isset($response['status']) && $response['status'] == 'connected') {
                $responseData['status'] = 'CONNECTED';
            }
            else if(isset($response['status']) && $response['status'] == 'disconnected') {
                $responseData['status'] = 'DISCONNECTED';
            }
            else {
                $responseData['status'] = 'NO INSTANCE';
            }

            return $responseData;

        }//Se o canal não respondeu
        catch(Exception $e) {

            Log::debug('exception lançada');
            Log::debug($e);

            return null;
        }
    }

    public function phoneExists($channel, $phoneNumberVerification)
    {
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/token/'.$channel['cha_session_token'].'/phone-exists/'.$phoneNumberVerification;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
        ])
        ->timeout(30) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->get($endPoint);

        //Log::debug('Response phoneExist Z-API');
        //Log::debug($response);

        return $response;
    }

    public function webhookApiZap(Request $callbackApi, $channelId)
    {
        Log::debug('Dados webhook da API ZAP');
        Log::debug($callbackApi);

        /*
        $callbackApi = array (
            'isGroup' => false,
            'instanceId' => '3B3DE56B4F41508BAC70D2E249D15609',
            'messageId' => '3A440792177149090D19',
            'momment' => 1675196157000,
            'status' => 'RECEIVED',
            'fromMe' => false,
            'phone' => '5527999955348',
            'chatName' => NULL,
            'senderName' => 'Ivahy Barcellos Baptista',
            'senderPhoto' => NULL,
            'photo' => NULL,
            'broadcast' => false,
            'participantPhone' => NULL,
            'type' => 'ReceivedCallback',
            'waitingMessage' => true,
        );
        */

        Log::debug('Id do Canal webhook da API ZAP');
        Log::debug($channelId);

        $apiName = 'apizap';
        $statusMessage = null;

        $eventController = new EventController();
        $channelController = new ChannelController();
        $userController = new UserController();
        $contactController = new ContactController();
        $chatController = new ChatController();

        //Se for uma mensagem recebida de um CONTATO e não é uma mensagem de um grupo
        if($callbackApi['type'] == 'messages' && $callbackApi['data']['wook'] == 'RECEIVE_MESSAGE' && (isset($callbackApi['data']['isGroup']) && $callbackApi['data']['isGroup'] == false) ) {
                
            $payloadMessage = [];
            //Pega o id da mensagem
            $payloadMessage['id'] = $callbackApi['data']['id'];
            //Pega se a mensagem foi enviada pelo usuário do sistema via celular (true) ou enviada pelo contato (false)
            $payloadMessage['fromSystemUser'] = $callbackApi['data']['fromMe'];
            
            //Se a mensagem foi enviada pelo contato
            if(!$callbackApi['data']['fromMe']) {
                //Pega o canal que recebeu a mensagem enviada pelo CONTATO
                $payloadMessage['mesPhoneChannelReceivedMessage'] = $callbackApi['connectedPhone'];
            }
            else {
                //Pega o canal usado pelo usuário do sistema para enviar a mensagem
                $payloadMessage['mesPhoneChannelSentMessage'] = $callbackApi['connectedPhone'];
            }
            
            //Se for uma mensagem de resposta a uma mensagem anterior, pega o id da mensagem respondida
            $payloadMessage['answeredMessageId'] = isset($callbackApi['referenceMessageId'])? $callbackApi['referenceMessageId'] : null;
            //Caso seja mensagem de texto ou o conteúdo da mensagem ainda não chegou (Aguardando a mensagem)
            //if(isset($callbackApi['data']['extended']) || $callbackApi['data']['waitingMessage']) {
            if($callbackApi['data']['type'] == 'text' || $callbackApi['data']['type'] == 'extended') {
                $payloadMessage['type'] = 'text';
                //Se a mensagem veio com conteúdo
                /*if($callbackApi['data']['waitingMessage'] == false) {
                    $payloadMessage['payload']['text'] = $callbackApi['data']['content'];
                } //Se a mensagem veio SEM conteúdo
                else {
                    $payloadMessage['payload']['text'] = 'Aguardando o conteúdo da mensagem';
                    $payloadMessage['payload']['waitingMessage'] = true;
                }*/

                $payloadMessage['payload']['text'] = $callbackApi['data']['content'];
                
            }
            else if($callbackApi['data']['type'] == 'audio') {
                $payloadMessage['type'] = 'audio';
                $payloadMessage['payload']['contentType'] = $callbackApi['data']['mimetype'];
                $payloadMessage['payload']['base64'] = $callbackApi['data']['base64'];
            }
            // Se o contato enviou uma imagem
            else if($callbackApi['data']['type'] == 'image') {
                $payloadMessage['type'] = 'image';
                $payloadMessage['payload']['contentType'] = $callbackApi['data']['mimetype'];
                $payloadMessage['payload']['base64'] = $callbackApi['data']['base64'];
                $payloadMessage['payload']['caption'] = $callbackApi['data']['caption'];
            }
            else if($callbackApi['data']['type'] == 'document') {
                $payloadMessage['type'] = 'file';

                $typeMedia = explode('/', $callbackApi['data']['mimetype']);

                $payloadMessage['payload']['name'] = 'Arquivo.'.$typeMedia[1];
                $payloadMessage['payload']['contentType'] = $callbackApi['data']['mimetype'];
                $payloadMessage['payload']['base64'] = $callbackApi['data']['base64'];
                $payloadMessage['payload']['caption'] = $callbackApi['data']['caption'];
            }
            else if($callbackApi['data']['type'] == 'video') {
                $payloadMessage['type'] = 'video';
                //$payloadMessage['payload']['caption'] = $callbackApi['messages'][0]['document']['caption'];
                $payloadMessage['payload']['contentType'] = $callbackApi['data']['mimetype'];
                $payloadMessage['payload']['base64'] = $callbackApi['data']['base64'];
                $payloadMessage['payload']['caption'] = $callbackApi['data']['caption'];
            }
            //Se o contato enviou um contato
            else if($callbackApi['data']['type'] == 'vcard') {
                $payloadMessage['type'] = 'vcard';
                $payloadMessage['payload']['contactName'] = $callbackApi['data']['displayName'];

                $dataDivided = explode('waid=', $callbackApi['data']['vcard']);
                $dataDivided = explode(':', $dataDivided[1]);
                //Telefone do contato compartilhado
                $payloadMessage['payload']['contactPhoneNumber'] = $dataDivided[0];
            }
            else if(isset($callbackApi['sticker'])) {
                $payloadMessage['type'] = 'sticker';
                $payloadMessage['payload']['contentType'] = $callbackApi['sticker']['mimeType'];
                $payloadMessage['payload']['url'] = $callbackApi['sticker']['stickerUrl'];
            }
            else if($callbackApi['data']['type'] == 'location') {
                $payloadMessage['type'] = 'location';
                $payloadMessage['payload']['latitude'] = $callbackApi['data']['loc'];
                $payloadMessage['payload']['longitude'] = $callbackApi['data']['lat'];
            }
            else if(isset($callbackApi['buttonsResponseMessage'])) {
                $payloadMessage['type'] = 'text';
                $payloadMessage['payload']['text'] = $callbackApi['buttonsResponseMessage']['message'];
            }
            else if(isset($callbackApi['notification'])) {
                //Se for o status de uma mensagem DELETADA
                if($callbackApi['notification'] == 'REVOKE') {
                    $statusMessageNotification['status'] = 'DELETED';
                    $statusMessageNotification['id'] = $callbackApi['messageId'];
                }
            }
            $payloadMessage['sender']['name'] = null;
            //Se a mensagem foi enviada pelo app ou whatsapp web, pega o dado do atributo chatName, se não, pega do atributo senderName
            $payloadMessage['sender']['name'] = $callbackApi['data']['fromMe']? $callbackApi['data']['to'] : $callbackApi['data']['name'];

            $phoneContact = $callbackApi['data']['fromMe']? $callbackApi['data']['to'] : $callbackApi['data']['from'];
            //Se o telefone do contato já tem o 9 na frente do número
            if(strlen($phoneContact) == 13) {
                $payloadMessage['sender']['phone'] = $phoneContact;
            } //Se o número veio SEM o 9 na frente do mesmo
            else {
                $ddi = substr($phoneContact, 0, 2);
                //Se for o DDI do Brasil
                if($ddi == '55') {
                    $payloadMessage['sender']['phone'] = substr_replace($phoneContact, '9', 4, 0);
                } //Se for um número estrangeiro
                else {
                    $payloadMessage['sender']['phone'] = $phoneContact;
                }
            }

            //Dados do contato
            $contactData = new Request([
                'name'   => $payloadMessage['sender']['name'],
                'phoneNumber' => $payloadMessage['sender']['phone'],
                'avatarUrl' => isset($callbackApi['photo'])? $callbackApi['data']['photo'] : null,
            ]);
            
            //Busca o contato, se houver
            $contact = Contact::with('blocked')->where('con_phone', $payloadMessage['sender']['phone'])->first();

            //Se o contato NÃO existe
            if(!$contact) {
                //Salva o contato no banco de dados
                $contact = $contactController->store($contactData);
                $contactData = json_encode($contact);
                $contactData = json_decode($contactData, true);
                //Pega os dados do novo contato
                $contact = $contactData['original']['contact'];
                $contact['blocked'] = null;
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
                    $channelData['id'] = $channelId;
                    //Só grava a mensagem se o contato enviou algum tipo de mensagem suportada pela plataforma
                    if(isset($payloadMessage['type'])) {
                        //Se a mensagem foi enviada pelo CONTATO
                        if($payloadMessage['fromSystemUser'] == false) {
                            $chatController->storeMessage($chat['id'], 2, $contact['id'], $payloadMessage, null, $apiName, 'false', $channelData, null, null);
                            //Incrementa o número de mensagens não visualizadas em 1
                            $chatController->incrementUnseenMessage($chat['id']);
                        } //Se foi o USUÁRIO DO SISTEMA enviou uma mensagem pelo celular ou WhatsApp Web
                        else {
                            $chatController->storeMessage($chat['id'], 1, 3, $payloadMessage, null, $apiName, 'false', $channelData, null, null);
                        }
                    }
                }
            }
        }
        //Se for um evento de status de envio de uma mensagem
        if($callbackApi['type'] == 'status' || ($callbackApi['type'] == 'messages' && $callbackApi['data']['wook'] == 'SEND_MESSAGE') ) {
            //Se for um status de uma mensagem (mensagem entregue, enviada, etc.)
            //if($callbackApi['statuses'][0]['type'] == 'message') {
                //Se a mensagem foi ENVIADA
                if(isset($callbackApi['data']['status']) && $callbackApi['data']['status'] == 'SENT') {
                    $statusMessageChatId = 2;
                    $apiMessageId = $callbackApi['data']['id'];
                } //Se a mensagem foi ENTREGUE
                else if(isset($callbackApi['data']['status']) && $callbackApi['data']['status'] == 'RECEIVED') {
                    $statusMessageChatId = 3;
                    $apiMessageId = $callbackApi['data']['id'];
                }
                else if(isset($callbackApi['data']['status']) && $callbackApi['data']['status'] == 'READ') {
                    $statusMessageChatId = 5;
                    $apiMessageId = $callbackApi['data']['id'];
                }//Se houve algum erro na entrega da mensagem
                /*else if(isset($callbackApi['error'])) {
                    if($callbackApi['error'] == 'Phone number does not exist') {
                        $statusMessage = "O número de telefone não existe";
                    }
                    else {
                        $statusMessage = "Erro ao enviar a mensagem";
                    }
                    $statusMessageChatId = 4;
                    $apiMessageId = $callbackApi['messageId'];
                }*/
                else if(isset($statusMessageNotification) && $statusMessageNotification['status'] == 'DELETED') {
                    $statusMessageChatId = 6;
                    $apiMessageId = $statusMessageNotification['id'];
                }
            //}
            $messageChat = null;
            //Se existe algum id da API da mensagem 
            if(isset($apiMessageId)) {
                //Pega a mensagem que deverá ter o status atualizado
                $messageChat = ChatMessage::where('api_message_id', $apiMessageId)->first();
            }
            
            //Se existe algum status de mensagem
            if($statusMessage && $messageChat) {
                $eventController->statusMessage($statusMessage, true, $messageChat->sender_id);
            }
            else {

            }

            if($messageChat)
            {   //Se a mensagem ainda NÃO possui status de entregue
                if($messageChat->status_message_chat_id !=3) {
                    //Atualiza os status da mensagem (Enviado, entregue, etc.)
                    $messageChat->status_message_chat_id = $statusMessageChatId;
                    $messageChat->save();

                    $lastAction =  Action::where('chat_id', $messageChat->chat_id)
                                                ->orderBy('created_at', 'desc')
                                                ->first();

                    //Se NÃO foi o contato que mandou a mensagem
                    if($messageChat->type_user_id != 2) {
                        if(isset($lastAction->user_id)) {
                            //Se tem algum operador que capturou o atendimento
                            if($lastAction->user_id) {
                                //Atualiza o status da mensagem na tela do OPERADOR
                                $eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $lastAction->user_id);
                            }
                        }
                    }//Se o foi o contato que mandou a mensagem e seja um status de mensagem deletada
                    else if($statusMessageChatId == 6) {
                        //Caso algum operador já tenha capturado o atendimento 
                        if($lastAction->user_id) {
                            //Atualiza o status da mensagem na tela do OPERADOR
                            $eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $lastAction->user_id);
                        }
                    }

                    //Traz todos os gestores
                    $managerUsers = $userController->getUsersByRoles([1, 3]);
                    //Para cada gestor, envia o status da mensagem
                    foreach($managerUsers as $manager) {
                        $eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $manager->id);
                    }
                }
            }
        }
        if($callbackApi['type'] == 'DeliveryCallback') {
            if(isset($callbackApi['error'])) {
                if($callbackApi['error'] == 'Phone number does not exist') {
                    $statusMessage = "O número de telefone não existe";
                }
                else {
                    $statusMessage = "Erro ao enviar a mensagem";
                }
                $statusMessageChatId = 4;
                $apiMessageId = $callbackApi['messageId'];
            }

            $messageChat = null;
            //Se existe algum id da API da mensagem 
            if(isset($apiMessageId)) {
                //Pega a mensagem que deverá ter o status atualizado
                $messageChat = ChatMessage::where('api_message_id', $apiMessageId)->first();
            }
            
            //Se existe algum status de mensagem
            if($statusMessage && $messageChat) {
                $eventController->statusMessage($statusMessage, true, $messageChat->sender_id);
            }
            
            if($messageChat)
            {   //Se a mensagem já não possui status de entregue
                if($messageChat->status_message_chat_id !=3) {
                    //Atualiza os status da mensagem (Enviado, entregue, etc.)
                    $messageChat->status_message_chat_id = $statusMessageChatId;
                    $messageChat->save();

                    //Se o foi NÃO foi o contato que mandou a mensagem
                    if($messageChat->type_user_id != 2) {
                        //Atualiza o status da mensagem na tela do OPERADOR
                        $eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $messageChat->sender_id);
                    }//Se o foi o contato que mandou a mensagem e seja um status de mensagem deletada
                    else if($statusMessageChatId == 6) {
                        //Pega a última ação para poder pegar o operador associado ao atendimento
                        $lastAction =  Action::where('chat_id', $messageChat->chat_id)
                                            ->orderBy('created_at', 'desc')
                                            ->first();

                        //Caso algum operador já tenha capturado o atendimento 
                        if($lastAction->user_id) {
                            //Atualiza o status da mensagem na tela do OPERADOR
                            $eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $lastAction->user_id);
                        }
                    }

                    //Traz todos os gestores
                    $managerUsers = $userController->getUsersByRoles([1, 3]);
                    //Para cada gestor, envia o status da mensagem
                    foreach($managerUsers as $manager) {
                        $eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $manager->id);
                    }
                }
            }
        }
        //Se é um retorno de conexão de um canal
        else if($callbackApi['type'] == 'connection') {
            //Se o canal foi conectado ao WhatsApp
            if(isset($callbackApi['data']['connection']) && $callbackApi['data']['connection'] == 'open') {
                $channel = $channelController->getChannel($channelId);
                //Envia uma notificação ao usuário comunicando que o canal foi conectado
                $eventController->statusConnection('inChat', null, $channel['user_id_connection']);
            }//Se é um retorno de desconexão de um canal e a desconexão não tenha se dado por Precondition Required
            else if(isset($callbackApi['data']['connection']) && $callbackApi['data']['connection'] == 'close' && 
            $callbackApi['data']['lastDisconnect']['error']['output']['statusCode'] != 428 && 
            $callbackApi['data']['lastDisconnect']['error']['output']['statusCode'] != 515 &&
            $callbackApi['data']['lastDisconnect']['error']['output']['statusCode'] != 503 &&
            $callbackApi['data']['lastDisconnect']['error']['output']['statusCode'] != 408) {
                Log::debug('código close connection');
                Log::debug($callbackApi['data']['lastDisconnect']['error']['output']['statusCode']);
                $channel = $channelController->getChannel($channelId);
                $channel->cha_status = 'I';
                $channel->save();

                //Envia uma notificação ao usuário comunicando que o canal foi conectado
                $eventController->statusConnection('Disconnected', null, $channel['user_id_connection']);
            }
        }

        return response()->json([
            
        ], 200);
    }
}
