<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\TemplateController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\UserController;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatMessage;
use App\Models\Contact\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GupshupController extends Controller
{
    const BASE_URL = 'https://api.gupshup.io/sm/api/v1';
    const PARTNER_BASE_URL = 'https://partner.gupshup.io/partner';

    public function sendMessage($channel, $destination, $message)
    {   
        $endPoint = self::BASE_URL.'/msg';

        /*$baseUrlServer = env('URL_SERVER');

        if($baseUrlServer == 'https://127.0.0.1') {
            $filePath = ENV('NGROK_URL')."/storage/chats/chat".$message->chat_id;
        }
        else {
            $filePath = $baseUrlServer."/storage/chats/chat".$message->chat_id;
        }*/
        $filePath = ENV('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id;

        //Se o tipo de mensagem for um texto
        if($message->type_message_chat_id == 1)
        {
            $messagePayload = json_encode(array("isHSM" => "false", "type" => "text", "text" => $message->mes_message));    
        }
        //Se o tipo de mensagem for um áudio
        else if($message->type_message_chat_id == 2)
        {
            $filePath = $filePath."/audios/".$message->mes_content_name;
            //$filePath = "https://www.buildquickbots.com/whatsapp/media/sample/audio/sample01.mp3";
            $messagePayload = json_encode(array("isHSM" => "false", "type" => "audio", "url" => $filePath));
        }
        //Se o tipo de mensagem for uma imagem
        else if($message->type_message_chat_id == 3)
        {
            $filePath = $filePath."/images/".$message->mes_content_name;
            //$filePath = "https://www.buildquickbots.com/whatsapp/media/sample/jpg/sample02.jpg";
            $messagePayload = json_encode(array("isHSM" => "false", "type" => "image", "originalUrl" => $filePath, "previewUrl" => $filePath));
        }
        //Se o tipo de mensagem for um vídeo
        else if($message->type_message_chat_id == 4)
        {   
            $filePath = $filePath."/videos/".$message->mes_content_name;
            //$filePath = "https://www.buildquickbots.com/whatsapp/media/sample/video/sample01.mp4";
            $messagePayload = json_encode(array("isHSM" => "false", "type" => "video", "url" => $filePath));
        }
        //Se o tipo de mensagem for um arquivo
        else if($message->type_message_chat_id == 5)
        {
            $filePath = $filePath."/files/".$message->mes_content_name;
            //$filePath = "https://www.buildquickbots.com/whatsapp/media/sample/pdf/sample01.pdf";
            $messagePayload = json_encode(array("isHSM" => "false", "type" => "file", "url" => $filePath, "filename" => $message->mes_content_name));
        }
        //Log::debug('filePath '. $filePath);

        $bodyData = [
            'channel' => 'whatsapp',
            'source' => $channel['cha_phone_ddi'].$channel['cha_phone_number'],
            'destination' => $destination,
            'src.name' => $channel['cha_app_name_api'],
            'message.payload' => $messagePayload,
        ];

        $response = Http::withHeaders([
            //'Cache-Control' => 'no-cache',
            'apikey' => $channel['cha_api_key'],
        ])
        ->asForm()
        ->timeout(30)
        ->post($endPoint, $bodyData);
        
        Log::debug('Resposta Gupshup');
        Log::debug($response);

        $responseData = [];
        //Se a mensagem foi ENVIADA
        if($response['status'] == 'submitted') {
            $responseData['status'] = 'success';
            $responseData['message']['id'] = $response['messageId'];
        }

        return $responseData;  
    }

    public function uploadMedia($channel, $tokenApp, $medialUrl, $mediaType)
    {
        $endPoint = self::PARTNER_BASE_URL.'/app/'.$channel['cha_channel_id_api'].'/upload/media';

        //$filePath = storage_path('app/public/templates/template190/header/20221005084517.png');
        $filePath = 'C:/projetos/chat/storage/app/public/templates/template190/header/20221005084517.png';

        //$info = pathinfo(file_get_contents($filePath));
        //$name = $info['basename'];
        $curlfile = new \CURLFile($filePath);

        $filedata = ['file' => $curlfile, 'file_type' => $mediaType];

        /*$response = Http::attach(
                            'attachment', $filePath, $mediaType
                        )
                        ->withHeaders([
                            //'Content-Type' => 'multipart/form-data',
                            'Authorization' => $tokenApp,
                        ])
                        //->asForm()
                        ->timeout(30)
                        ->post($endPoint, $bodyData);

        Log::debug('response upload media');
        Log::debug($response);

        return $response;

        */
        $headers = [
            //'Content-Type: multipart/form-data',
            'Authorization: '.$tokenApp,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $endPoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('file'=> new \CURLFILE($medialUrl),'file_type' => 'image/png'),
        CURLOPT_HTTPHEADER => array(
            'Authorization: '.$tokenApp
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        
        Log::debug('response upload media');
        Log::debug($response);

        return $response;
    }

    //Cria um template
    public function createTemplate($templateData)
    {
        Log::debug('create template gupshup');
        Log::debug($templateData);
        //Seta o padrão do tipo de template como TEXT
        $templateType = 'TEXT';

        $endPoint = self::PARTNER_BASE_URL.'/app/'.$templateData['channel']['cha_channel_id_api'].'/templates';

        //Traz o token do partner
        $tokenApp = self::getTokenApp($templateData['channel']);

        if(isset($templateData['mediaUrl'])) {
            //Se a mídia for uma IMAGEM
            if($templateData['typeMediaId'] == 3) {
                $templateType = 'IMAGE';
            }
            else if($templateData['typeMediaId'] == 4) {
                $templateType = 'VIDEO';
            }
            else {
                $templateType = 'DOCUMENT';
            }

            $mediaId = self::uploadMedia($templateData['channel'], $tokenApp['token']['token'], $templateData['mediaUrl'], $templateData['mediaType']);
        }

        
        //Se o usuário adicionou algum botão no template
        if($templateData['typeButton'] != null) {
            $buttons = [];
            //Se o botão adicionado foi de RESPOSTA RÁPIDA
            if($templateData['typeButton']['id'] == 1) {
                //Para cada botão
                foreach($templateData['buttonLabel'] as $key => $button) {
                    $buttonArray = array('type' => 'QUICK_REPLY', 'text' => $button);
                    array_push($buttons, $buttonArray);
                }
            }
            //Se o botão adicionado foi de CHAMADA PARA AÇÃO
            else if($templateData['typeButton']['id'] == 2) {
                foreach($templateData['callActions'] as $key => $callAction) {
                    //Caso a ação seja uma URL
                    if($callAction['id'] == 1) {
                        $buttonArray = array('type' => 'URL', 'text' => $templateData['buttonLabel'][$key], 'url' => $templateData['buttonUrl']);
                    }
                    //Caso a ação seja um NÚMERO DE TELEFONE
                    else {
                        $buttonArray = array('type' => 'PHONE_NUMBER', 'text' => $templateData['buttonLabel'][$key], 'phone_number' => $templateData['phoneNumber']);
                    }
                    //Adiciona o botão no array de botões
                    array_push($buttons, $buttonArray);
                }
            }
        }

        //'buttons' => [["type" => "PHONE_NUMBER","text" => "Ligue para nós","phone_number" => "+919872329959"], ["type" => "URL", "text" => "Agende uma demonstração", "url " => "https://bookins.gupshup.io", "example" => ["https://bookins.gupshup.io/abc"]]],

        $bodyData = [
            'elementName' => $templateData['tem_name'],
            'languageCode' => $templateData['language']['tem_code'],
            'category' => $templateData['category']['tem_tag'],
            'vertical' => $templateData['tem_name'],
            'header' => $templateData['header'],
            'footer' => $templateData['footer'],
            'templateType' => $templateType,
            'buttons' => isset($buttons)? json_encode($buttons) : null,
            'content' => $templateData['body'],
            'example' => $templateData['body'],
            'exampleMedia' => isset($mediaId['handleId']['message'])? $mediaId['handleId']['message'] : null,
            'enableSample' => count($templateData['parameters']) > 0 || isset($mediaId['handleId']['message'])? 'true' : false,
        ];

        $response = Http::withHeaders([
            'Connection' => 'keep-alive',
            'Authorization' => $tokenApp['token']['token'],
        ])
        ->asForm()
        ->timeout(30)
        ->post($endPoint, $bodyData);

        $responseData = []; 
        //Se o template foi criado com sucesso
        if($response['status'] == 'success') {
            $responseData['status'] = 'success';
            $responseData['id'] = $response['template']['id'];
            $responseData['message'] = 'Modelo criado com sucesso!';
        } //Se houve algum erro ao criar o template
        else if($response['status'] == 'error') {
            $responseData['status'] = 'error';
            if($response['message'] == 'Template Not Supported On Gupshup Platform') {
                $responseData['message'] = "O modelo enviado não é suportado pela GupShup";
            }
        }

        return $responseData;
    }

    //Envio de mensagens template
    public function sendTemplateMessage($channel, $destination, $messageTemplateData)
    {
        $endPoint = self::BASE_URL.'/template/msg';
        $responseFormatted['apiName'] = 'gupshup';

        //Se a variável for um objeto, converte em array
        if(is_object($messageTemplateData)) {
            $messageTemplateDataAux  = (array) $messageTemplateData;
            if(isset($messageTemplateDataAux['templateData'])) {
                $messageTemplateData = $messageTemplateDataAux;
            }
        }

        Log::debug('messageTemplateData sendTemplate');
        Log::debug($messageTemplateData);

        $params = [];

        if(isset($messageTemplateData['componentsData']['body']['variables']['value'])) {
            //Se existe parâmetros (variáveis)
            if(count($messageTemplateData['componentsData']['body']['variables']['value']) > 0) {
                foreach($messageTemplateData['componentsData']['body']['variables']['value'] as $variable) {
                    array_push($params, $variable);
                }
            }
        }

        $headerMedia = [];

        //Se houver parâmetros associados ao template
        if(count($messageTemplateData['templateData']['parameters']) > 0) {
            foreach($messageTemplateData['templateData']['parameters'] as $key => $parameter) {
                //Se o parâmetro estiver localizado no BODY
                /*if($parameter['location_parameter_id'] == 2) {
                    //Se o tipo de parâmetro for uma variável
                    if($parameter['type_parameter_id'] == 1) {
                        array_push($bodyParameters, array('type' => 'text', 'text' => $messageTemplateData['componentsData']['body']['variables']['value'][$key] ));
                    }
                }*/ //Se o parâmetro estiver locailizado no HEADER
                if($parameter['location_parameter_id'] == 1) {
                    /*$urlMedia = env('URL_SERVER');

                    //Se o template estiver sendo criado localmente, aponta para mídia upada
                    if($urlMedia == 'https://127.0.0.1') {
                        $urlMedia = env('NGROK_URL').'/storage/templates/template'.$parameter['template_id'].'/header/'.$parameter['tem_media_name'];
                    }
                    else {
                        $urlMedia .= '/storage/templates/template'.$parameter['template_id'].'/header/'.$parameter['tem_media_name'];
                    }*/
                    $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/templates/template'.$parameter['template_id'].'/header/'.$parameter['tem_media_name'];

                    //Se o tipo de parâmetro for uma imagem
                    if($parameter['type_parameter_id'] == 3) {
                        $headerMedia = json_encode(array('type' => 'image', 'image' => array('link' => $urlMedia)));
                    } //Se o tipo de parâmetro for um vídeo
                    else if($parameter['type_parameter_id'] == 4) {
                        $headerMedia = json_encode(array('type' => 'video', 'video' => array('link' => $urlMedia)));
                    }
                    else if($parameter['type_parameter_id'] == 5) {
                        $headerMedia = json_encode(array('type' => 'document', 'document' => array('link' => $urlMedia)));
                    }
                }
            } 
        }

        //self::getTemplatesList($channel);
        //$templateData = json_encode(array("id" => "6692b7b0-3265-4022-8f3e-47ffe0781c7d", "params" => []));
        $templateData = json_encode(array("id" => $messageTemplateData['templateData']['template_id_api'], "params" => $params));

        //Traz o token do partner
        //$tokenApp = self::getTokenApp($channel);

        $bodyData = [
            'source' => $channel['cha_phone_ddi'].$channel['cha_phone_number'],
            'destination' => $destination,
            'template' => $templateData,
            'src.name' => $channel['cha_app_name_api'],
            'message' => $headerMedia,
        ];

        $response = Http::withHeaders([
            //'Content-Type' => 'application/x-www-form-urlencoded',
            'apikey' => $channel['cha_api_key'],
        ])
        ->asForm()
        ->timeout(30)
        ->post($endPoint, $bodyData);

        $reponseDataFormatted = []; 
        //Se o template foi criado com sucesso
        if($response['status'] == 'submitted') {
            $reponseDataFormatted['status'] = 'success';
            $reponseDataFormatted['message']['id'] = $response['messageId'];
        } //Se houve algum erro ao criar o template
        else if($response['status'] == 'error') {
            $reponseDataFormatted['error']['message'] = '';
            if($response['message'] == 'Template Not Supported On Gupshup Platform') {
                $reponseDataFormatted['error']['message'] = "O template submetido não é suportado pela GupShup";
            }
        }
        
        Log::debug('createTemplate');
        Log::debug($response);

        return $reponseDataFormatted;
        
        return $response;
    }

    public function getTemplatesList($channel)
    {
        $endPoint = self::BASE_URL.'/template/list/'.$channel['cha_app_name_api'];

        $response = Http::withHeaders([
            'apikey' => $channel['cha_api_key'],
        ])
        //->asForm()
        ->timeout(30)
        ->get($endPoint);

        Log::debug('List Templates APP');
        Log::debug($response);
    }

    //Pega o token associado a um APP. Necessário para manipular modelos
    public function getTokenApp($channel)
    {
        $endPoint = self::PARTNER_BASE_URL.'/app/'.$channel['cha_channel_id_api'].'/token';
        $tokenPartner = self::getTokenPartner();

        $response = Http::withHeaders([
            'token' => $tokenPartner['token'],
        ])
        //->asForm()
        ->timeout(30)
        ->get($endPoint);

        Log::debug('Token App');
        Log::debug($response);
        
        return $response;

    }

    //Pega o token geral associado ao partner. Esse token expira a cada 24h
    public function getTokenPartner()
    {
        $endPoint = self::PARTNER_BASE_URL.'/account/login';

        $bodyData = [
            'email' => env('GUPSHUP_EMAIL'),
            'password' => env('GUPSHUP_PASSWORD'),
        ];

        $response = Http::withHeaders([
            'Cache-Control' => 'no-cache',
        ])
        ->asForm()
        ->timeout(30)
        ->post($endPoint, $bodyData);
        
        Log::debug('token response');
        Log::debug($response);
        
        return $response;
    }

    //Define o webhook para o canal na API
    public function setWebhook($channel)
    {
        $endPoint = self::PARTNER_BASE_URL.'/app/'.$channel['cha_channel_id_api'].'/callbackUrl';

        $baseUrlServer = env('URL_SERVER');

        if($baseUrlServer == 'https://127.0.0.1') {
            $webhook = ENV('NGROK_URL')."/api/webhook-gupshup";
        }
        else {
            $webhook = $baseUrlServer."/api/webhook-gupshup";
        }

        //Traz o token do partner
        $tokenApp = self::getTokenApp($channel);

        $bodyData = [
            'callbackUrl' => $webhook
        ];

        $response = Http::withHeaders([
            //'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => $tokenApp['token']['token'],
        ])
        ->asForm()
        ->timeout(5)
        ->put($endPoint, $bodyData);
        
        Log::debug('set webhook response');
        Log::debug($response);
    }

    public function removeTemplate($channel, $templateName)
    {
        $endPoint = self::PARTNER_BASE_URL.'/app/'.$channel['cha_channel_id_api'].'/template/'.$templateName;

        //Traz o token do partner
        $tokenApp = self::getTokenApp($channel);

        //Envia os dados
        $response = Http::withHeaders([
            'Authorization' => $tokenApp['token']['token'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->delete($endPoint);
        
        Log::debug('resposta da função deleteTemplate Gupshup');
        Log::debug($response);

        $responseData = [];
        //Se o template foi excluído com sucesso
        if($response['status'] == 'success') {
            $responseData['status'] = 'success';
        } //Se houve algum erro ao excluir o template e esse é por o mesmo não existir no broker-
        else if($response['status'] == 'error') {
            $responseData['status'] = 'error';
            if($response['message'] == 'Template Does not exists.') {
                $responseData['message'] = 'Template Does not exists';
            }
            else {
                $responseData['message'] = 'Another Error';
            }
        }

        return $responseData;
    }

    //Recebe os eventos (status de envio de mensagens, mensagens de clientes, etc.)
    public function webhookGupshup(Request $callbackApi)
    {
        Log::debug('Mensagem recebida da Gupshup');
        Log::debug($callbackApi);
        $chatController = new ChatController();
        $eventController = new EventController();
        $userController = new UserController();

        //Nome da API de onde os dados estão vindo
        $apiName = 'gupshup';
        $statusMessage = null;
        
        //Log::debug($callbackApi->type);
        //Caso seja mensagem da API OFICIAL ou a mensagem seja da API NÃO OFICIAL e não seja mensagem de um grupo
        if($callbackApi->type == 'message' || ($callbackApi->event == 'onmessage' && $callbackApi->isGroupMsg == false)) {
            //Se for uma mensagem de um contato via API OFICIAL
            if($callbackApi->type == 'message') {
                $payloadMessage = $callbackApi->payload;
                //Se a mensagem for um contato compartilhado
                if($payloadMessage['type'] == 'contact') {
                    $payloadMessage['type'] = 'vcard';
                    $payloadMessage['payload']['contactName'] = $callbackApi['payload']['payload']['contacts'][0]['name']['formatted_name'];
                    $payloadMessage['payload']['contactPhoneNumber'] = preg_replace('/[^0-9]/', '', $callbackApi['payload']['payload']['contacts'][0]['phones'][0]['phone']);
                }
                else if(isset($callbackApi['payload']['payload']['type']) && $callbackApi['payload']['payload']['type'] == 'button') {
                    $payloadMessage['type'] = 'text';
                    $payloadMessage['payload']['text'] = $callbackApi['payload']['payload']['text'];
                }

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
                    $channelController = new ChannelController();
                    
                    //Traz o canal que irá receber a mensagem
                    $channel = $channelController->getChannelByAppName($callbackApi['app']);
                    $channelData['id'] = $channel->id;
                    //Só grava a mensagem se o contato enviou algum tipo de mensagem suportada pela plataforma
                    if(isset($payloadMessage['type'])) {
                        $chatController->storeMessage($chat['id'], 2, $contact['id'], $payloadMessage, null, $apiName, 'false', $channelData, null, null);
                        //Incrementa o número de mensagens não visualizadas em 1
                        $chatController->incrementUnseenMessage($chat['id']);
                    }
                }
            }
        }
        //Se for um evento da mensagem (mensagem entregue, enviada, etc.)
        else if($callbackApi->type == 'message-event') {
            $apiMessageId = isset($callbackApi->payload['gsId'])? $callbackApi->payload['gsId'] : $callbackApi->payload['id'];
            //Se a mensagem foi enfileirada
            if($callbackApi->payload['type'] == 'enqueued') {
                $statusMessageChatId = 1;
            }
            else if($callbackApi->payload['type'] == 'sent') {
                $statusMessageChatId = 2;
            } //Se a mensagem foi entregue
            else if($callbackApi->payload['type'] == 'delivered') {
                $statusMessageChatId = 3;
            }
            else if($callbackApi->payload['type'] == 'read') {
                $statusMessageChatId = 5;
            }
            else if($callbackApi->payload['type'] == 'failed') {
                $statusMessageChatId = 4;

                //Se o erro for por que já faz mais de 24 horas de inatividade na conversação
                if($callbackApi['payload']['payload']['code'] == 1007 || $callbackApi['payload']['payload']['code'] == 1005) {
                    $statusMessage = "A mensagem não foi entregue pois já faz mais de 24 horas desde a última vez que o contato lhe enviou uma mensagem. Caso queira se comunicar com o contato, experimente enviar uma mensagem modelo.";
                }
                else if($callbackApi['payload']['payload']['code'] == 1006) {
                    $statusMessage = "A mensagem não foi entregue pois o contato não optou por receber mensagens modelo";
                }
                else if($callbackApi['payload']['payload']['code'] == 1003) {
                    $statusMessage = "A mensagem não foi entregue pois você não possui saldo na GupShup";
                }
                else if($callbackApi['payload']['payload']['code'] == 2001) {
                    $statusMessage = "Template não disponível no momento. Caso o template tenha acabado de ter sido aprovado, aguarde alguns instantes e tente novamente";
                }
                else {
                    $statusMessage = "Erro ao enviar a mensagem";
                }
            }
            else if($callbackApi->payload['type'] == 'deleted') {
                $statusMessageChatId = 6;
            }
            
            //Se existe algum id da API da mensagem 
            if(isset($apiMessageId)) {
                //Pega a mensagem que deverá ter o status atualizado
                $messageChat = ChatMessage::where('api_message_id', $apiMessageId)->first();
            }

            //Se existe algum status de mensagem
            if($statusMessage && $messageChat) {
                $eventController->statusMessage($statusMessage, true, $messageChat->sender_id);
            }

            //Se a mensagem a ser atualizada existe
            if($messageChat)
            {   
                //Se a mensagem já não possui status de entregue
                if($messageChat->status_message_chat_id !=3) {
                    //Atualiza os status da mensagem (Enviado, entregue, etc.)
                    $messageChat->status_message_chat_id = $statusMessageChatId;
                    $messageChat->save();

                    //Atualiza o status da mensagem na tela do OPERADOR que enviou a mensagem
                    $eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $messageChat->sender_id);

                    //Traz todos os gestores
                    $managerUsers = $userController->getUsersByRoles([1, 3]);
                    //Para cada gestor, envia o status da mensagem
                    foreach($managerUsers as $manager) {
                        $eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $manager->id);
                    }
                }
            }
        } //Se for um evento de atualização de status de um template
        else if($callbackApi->type == 'template-event') {
            $templateController = new TemplateController();
            $templateStatusId = $templateController->getStatusIdTemplateCorrelation($callbackApi['payload']['status']);
            //Atualiza o status do template
            $templateController->updateStatusTemplateMessage(null, $callbackApi['payload']['id'], $templateStatusId);
        }
    }
}
