<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Chat\Action;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatMessage;
use App\Models\Contact\Contact;
use App\Models\Management\Channel\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;

use Auth;
use Exception;

class Dialog360Controller extends Controller
{
    const BASE_URL = 'https://waba.360dialog.io/';

    public function sendMessage($channel, $destination, $message)
    {   
        try {
            $endPoint = self::BASE_URL.'v1/messages';

            $messageData = '';
            $mediaUploadedData = '';

            //$contactSituation = self::checkContact($channel, $destination);
            
            //Se o contato é válido e está disponível no Whatsapp
            //if($contactSituation['contacts'][0]['status'] == 'valid') {
                //Se o tipo de mensagem for um texto
                if($message->type_message_chat_id == 1) {
                    //$messageData = json_encode(array("isHSM" => "false", "type" => "text", "text" => $message->mes_message));
                    $bodyMessage = array('body' => $message->mes_message);
                    $messageData = ['recipient_type' => 'individual', 'to' => $destination, 'type' => 'text', 'text' => $bodyMessage];      
                }
                //Se o tipo de mensagem for um áudio, imagem, vídeo ou arquivo
                else if($message->type_message_chat_id == 2 || $message->type_message_chat_id == 3 || $message->type_message_chat_id == 4 
                        || $message->type_message_chat_id == 5) {
                    //Se for uma imagem
                    if($message->type_message_chat_id == 3) {
                        //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/images/".$message->mes_content_name);
                        $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/images/".$message->mes_content_name;
                        //Pega a extensão da imagem
                        $type = pathinfo($filePath, PATHINFO_EXTENSION);

                        $typeData = 'image/'.$type;
                        //Faz o upload da mídia na 360Dialog, que retorna o id da mídia upada
                        if($mediaUploadedData = self::uploadMedia($channel, $filePath, $typeData, $message->type_message_chat_id)) {
                            Log::debug('mídia que foi realizada o download');
                            Log::debug($mediaUploadedData);
                            $bodyMessage = array('id' => $mediaUploadedData['media'][0]['id'], 'caption' => $message->mes_content_name);
                            $messageData = ['recipient_type' => 'individual', 'to' => $destination, 'type' => 'image', 'image' => $bodyMessage];
                        }

                    }
                    //Se for um vídeo
                    else if($message->type_message_chat_id == 4) {
                        //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name);
                        $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name;
                        $type = pathinfo($filePath, PATHINFO_EXTENSION);
                        $typeData = 'video/'.$type;
                        //Faz o upload da mídia na 360Dialog, que retorna o id da mídia upada
                        if($mediaUploadedData = self::uploadMedia($channel, $filePath, $typeData, $message->type_message_chat_id)) {
                            Log::debug('mídia que foi realizada o download');
                            Log::debug($mediaUploadedData);
                            $bodyMessage = array('id' => $mediaUploadedData['media'][0]['id'], 'caption' => $message->mes_content_name);
                            $messageData = ['recipient_type' => 'individual', 'to' => $destination, 'type' => 'video', 'video' => $bodyMessage];
                        }
                    }
                    //Se for um arquivo 
                    else if($message->type_message_chat_id == 5) {
                        //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/files/".$message->mes_content_name);
                        $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/files/".$message->mes_content_name;
                        $type = pathinfo($filePath, PATHINFO_EXTENSION);
                        $typeData = 'application/'.$type;
                        
                        //Faz o upload da mídia na 360Dialog, que retorna o id da mídia upada
                        if($mediaUploadedData = self::uploadMedia($channel, $filePath, $typeData, $message->type_message_chat_id)) {
                            Log::debug('mídia que foi realizada o download');
                            Log::debug($mediaUploadedData);
                            $bodyMessage = array('id' => $mediaUploadedData['media'][0]['id'], 'caption' => $message->mes_content_name);
                            $messageData = ['recipient_type' => 'individual', 'to' => $destination, 'type' => 'document', 'document' => $bodyMessage];
                        }                    
                    }
                    //Se for um audio 
                    else if($message->type_message_chat_id == 2) {
                        //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name);
                        $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name;
                        $type = pathinfo($filePath, PATHINFO_EXTENSION);

                        $typeData = 'audio/aac';

                        //Faz o upload da mídia na 360Dialog, que retorna o id da mídia upada
                        if($mediaUploadedData = self::uploadMedia($channel, $filePath, $typeData, $message->type_message_chat_id)) {
                            $bodyMessage = array('id' => $mediaUploadedData['media'][0]['id']);
                            $messageData = ['recipient_type' => 'individual', 'to' => $destination, 'type' => 'audio', 'audio' => $bodyMessage];
                        }
                    }    
                }
                //Log::debug('dados de envio da mensagem');
                //Log::debug($messageData);

                //Envia os dados
                $response = Http::withHeaders([
                    'Accept' => '*/*',
                    'D360-API-KEY' => $channel['cha_api_key'],
                    'Content-Type' => 'application/json; charset=utf-8'
                ])
                ->timeout(20)
                //->asForm()
                ->post($endPoint, $messageData);
                
                Log::debug('Resposta 360Dialog '.$response);
                
                if(isset($response['meta']['success'])) {
                    //Se a mensagem não foi enviada com sucesso
                    if($response['meta']['success'] == false) {
                        //Tenta enviar novamente
                        $response = Http::withHeaders([
                            'Accept' => '*/*',
                            'D360-API-KEY' => $channel['cha_api_key'],
                            'Content-Type' => 'application/json; charset=utf-8'
                        ])
                        ->timeout(20)
                        //->asForm()
                        ->post($endPoint, $messageData);
                    }
                }

                //Se o tipo de mensagem for um áudio, imagem, vídeo ou arquivo
                if($message->type_message_chat_id == 2 || $message->type_message_chat_id == 3 || $message->type_message_chat_id == 4 
                        || $message->type_message_chat_id == 5) {
                    //Deleta a mídia da 360 Dialog após o envio
                    self::deleteMedia($channel, $mediaUploadedData['media'][0]['id']);
                }

                $responseData = [];
                //Se a mensagem foi ENVIADA
                if(isset($response['messages'][0]['id'])) {
                    $responseData['status'] = 'success';
                    $responseData['message']['id'] = $response['messages'][0]['id'];
                }

                return $responseData;
            //}
            
        }
        catch(Exception $e) {

            Log::debug('exception lançada');
            Log::debug($e);

            $response['status'] = 'error'; 

            return $response;
        }
        
        
    }

    public function sendTemplateMessage($channel, $destination, $messageTemplateData)
    {
        $endPoint = self::BASE_URL.'v1/messages';
        $responseFormatted['apiName'] = '360Dialog';
        $components = [];
        $bodyParameters = [];
        $headerParameters = [];
        
        //Se a variável for um objeto, converte em array
        if(is_object($messageTemplateData)) {
            $messageTemplateDataAux  = (array) $messageTemplateData;
            if(isset($messageTemplateDataAux['templateData'])) {
                $messageTemplateData = $messageTemplateDataAux;
            }
        }

        //Log::debug('sendTemplateMessage data');
        //Log::debug($messageTemplateData['templateData']['parameters']);

        //Se houver parâmetros associados ao template
        if(count($messageTemplateData['templateData']['parameters']) > 0) {
            foreach($messageTemplateData['templateData']['parameters'] as $key => $parameter) {
                //Se o parâmetro estiver localizado no BODY
                if($parameter['location_parameter_id'] == 2) {
                    //Se o tipo de parâmetro for uma variável
                    if($parameter['type_parameter_id'] == 1) {
                        array_push($bodyParameters, array('type' => 'text', 'text' => $messageTemplateData['componentsData']['body']['variables']['value'][$key] ));
                    }
                }
                else if($parameter['location_parameter_id'] == 1) {
                    /*$urlMedia = env('URL_SERVER');

                    //Se o template estiver sendo criado localmente
                    if($urlMedia == 'https://127.0.0.1') {
                        $urlMedia = env('NGROK_URL').'/storage/templates/template'.$parameter['template_id'].'/header/'.$parameter['tem_media_name'];
                    }
                    else {
                        $urlMedia .= '/storage/templates/template'.$parameter['template_id'].'/header/'.$parameter['tem_media_name'];
                    }*/
                    $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/templates/template'.$parameter['template_id'].'/header/'.$parameter['tem_media_name'];
                    //Se o tipo de parâmetro for uma imagem
                    if($parameter['type_parameter_id'] == 3) {
                        array_push($headerParameters, array('type' => 'image', 'image' => array('link' => $urlMedia) ));
                    } //Se o tipo de parâmetro for um vídeo
                    else if($parameter['type_parameter_id'] == 4) {
                        array_push($headerParameters, array('type' => 'video', 'video' => array('link' => $urlMedia) ));
                    }
                    else if($parameter['type_parameter_id'] == 5) {
                        array_push($headerParameters, array('type' => 'document', 'document' => array('link' => $urlMedia) ));
                    }
                }
            } 
        }

        //Se houver algum parâmetro no body
        if(count($bodyParameters) > 0) {
            //Armazena os parÂmetros de BODY dentro da array COMPONENTS
            $aux = array('type' => 'body', 'parameters' => $bodyParameters);
            array_push($components, $aux);
        }
        if(count($headerParameters) > 0) {
            //Armazena o parâmetro do HEADER dentro da array COMPONENTS
            $aux = array('type' => 'header', 'parameters' => $headerParameters);
            array_push($components, $aux);
        }

        //Caso a mensagem contenha algum componente (contenha variáveis, botões, etc.)
        if(count($components) > 0) {
            $bodyTemplateMessage = array('namespace' => $messageTemplateData['templateData']['tem_namespace'], 'language' => array('policy' => 'deterministic', 
                                    'code' => $messageTemplateData['templateData']['tem_code']), 'name' => $messageTemplateData['templateData']['tem_name'],
                                    'components' => $components);
        }
        else {
            $bodyTemplateMessage = array('namespace' => $messageTemplateData['templateData']['tem_namespace'], 'language' => array('policy' => 'deterministic', 
                                    'code' => $messageTemplateData['templateData']['tem_code']), 'name' => $messageTemplateData['templateData']['tem_name']);
        }
        
        $messageData = ['to' => $destination, 'type' => 'template', 'template' => $bodyTemplateMessage];

        //Log::debug('dados para envio pela função sendTemplateMessage');
        //Log::debug($messageData);

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'D360-API-KEY' => $channel['cha_api_key'],
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $messageData);
        
        Log::debug('Resposta envio da mensagem template');
        Log::debug($response);
         
        //Se a mensagem foi enviada
        if(isset($response['messages'])) {
            $responseFormatted['status'] = 'success';
            $responseFormatted['message']['id'] = $response['messages'][0]['id'];
        }
        else if(isset($response['errors'])) {
            //Se usuário não existe (telefone incorreto, sem whatsapp, etc.)
            if($response['errors'][0]['code'] == 1013) {
                $responseFormatted['status'] = 'userInvalid';
            } //Se o template está aprovado mas ainda não está disponível para envio
            else if($response['errors'][0]['code'] == 2001) {
                $responseFormatted['status'] = 'Template Unavailable';
            }
        }
        
        return $responseFormatted;
    }

    //Faz o upload para API da mídia que será enviada
    public function uploadMedia($channel, $mediaPath, $contentType, $typeMedia)
    {   
        $endPoint = self::BASE_URL.'/v1/media';
        //Se for um áudio, imagem ou vídeo
        if($typeMedia == 2 || $typeMedia == 3 || $typeMedia == 4) {
            $response = Http::withBody(file_get_contents($mediaPath), $contentType)
                            ->withHeaders([
                                'Accept' => '*/*',
                                'Content-Type' => $contentType,
                                'D360-API-KEY' => $channel['cha_api_key'],
                            ])
                            ->timeout(60)
                            ->post($endPoint);
        }
        //Se for um arquivo
        else if($typeMedia == 5) {
            //Envia os dados
            $response = Http::attach('file', file_get_contents($mediaPath))
                            ->withHeaders([
                                'Accept' => '*/*',
                                'Content-Type' => $contentType,
                                'D360-API-KEY' => $channel['cha_api_key'],
                            ])
                            ->timeout(60)
                            //->asForm()
                            ->post($endPoint);
        }

        return $response;
    }

    //Define o webhook para o canal na API
    public function setWebhook($channel)
    {   
        $endPoint = self::BASE_URL.'/v1/configs/webhook';
        $bodyData = ['url' => env('WEBHOOK_360_DIALOG').$channel['id']]; //Coloca o id do canal no final do webhook
        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $channel['cha_api_key'],
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('resposta setWebhook');
        Log::debug($response);
    }

    //Verifica a situação de um contato em relação ao Whatsapp
    public function checkContact($channel, $phoneNumber)
    {   
        $endPoint = self::BASE_URL.'v1/contacts';
        $bodyData = ['blocking' => 'wait', 'contacts'=> array('+'.$phoneNumber), 'force_check' => true];
        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $channel['cha_api_key'],
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('resposta da função check contacts');
        Log::debug($response);

        return $response;
    }

    //Baixa a mídia no servidor
    public function getMedia($channel, $mediaId)
    {
        $endPoint = self::BASE_URL.'v1/media/'.$mediaId;

        $response = null;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->get($endPoint);

        return $response;
    }

    public function deleteMedia($channel, $mediaId)
    {
        $endPoint = self::BASE_URL.'v1/media/'.$mediaId;

        $response = null;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->delete($endPoint);
    }

    //Verifica o status do canal na API
    public function checkHealthChannel($channel)
    {
        $endPoint = self::BASE_URL.'v1/health';

        $response = null;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->get($endPoint);

        return $response;
    }

    public function createTemplate($templateData)
    {
        $utilsController = new UtilsController();
        $endPoint = self::BASE_URL.'v1/configs/templates';
        $components = [];
        $variablesExampleBody = [];

        Log::debug('createTemplate $templateData');
        Log::debug($templateData);

        
        if(count($templateData['parameters']) > 0) {
            //Para cada parâmetro
            foreach($templateData['parameters'] as $key => $parameter) {
                //Se for um nome de um contato
                if($parameter['id'] == 1) {
                    //Adiciona um valor de exemplo
                    array_push($variablesExampleBody, 'João');
                } 
                //Se for um CPF
                else if($parameter['id'] == 2) {
                    //Adiciona um valor de exemplo
                    array_push($variablesExampleBody, '000.000.000-00');
                }
                //Se for um CNPJ
                else if($parameter['id'] == 3) {
                    //Adiciona um valor de exemplo
                    array_push($variablesExampleBody, '00.000.000/0000-00');
                }
                //Se for um nome de um Nome de usuário do sistema
                else if($parameter['id'] == 4) {
                    //Adiciona um valor de exemplo
                    array_push($variablesExampleBody, 'Miguel');
                }
                //Se for uma saudação
                else if($parameter['id'] == 5) {
                    //Adiciona um valor de exemplo
                    array_push($variablesExampleBody, 'Bom dia');
                }
                //Se for um protocolo
                else if($parameter['id'] == 6) {
                    //Adiciona um valor de exemplo
                    array_push($variablesExampleBody, '1232453445');
                }
                //Se for um dado adicional
                else if($parameter['id'] == 7) {
                    //Adiciona um valor de exemplo
                    array_push($variablesExampleBody, 'valor aleatório');
                }
            }
            $parametersBody = array('body_text' => array($variablesExampleBody));
        }
        
        //Adequa os parágrafos do texto de acordo com o formato suportado pelo Whatsapp
        $textBody = $utilsController->changeParagraphContentTemplate($templateData['body']);
        //Se tiver algum parâmetro (variáveis) no BODY
        if(isset($parametersBody)) {
            $bodyData = array('type' => 'BODY', 'text' => $textBody, 'example' => $parametersBody);
        }
        else {
            $bodyData = array('type' => 'BODY', 'text' => $textBody);
        }
        //Armazena os dados do body no array de componentes
        array_push($components, $bodyData);

        //Se existe algum TEXTO para o cabeçalho
        if($templateData['header']) {
            //Adequa os parágrafos do texto de acordo com o formato suportado pelo Whatsapp
            $textHeader = $utilsController->changeParagraphContentTemplate($templateData['header']);
            $headerData = array('type' => 'HEADER', 'format' => 'TEXT', 'text' => $textHeader);
            //Armazena os dados do body no array de componentes
            array_push($components, $headerData);
        } //Se alguma mídia foi upada para o cabeçalho
        else if(isset($templateData['mediaUrl'])) {
            //Se a mídia for uma IMAGEM
            if($templateData['typeMediaId'] == 3) {
                $typeMediaFormat = 'IMAGE';
            }
            else if($templateData['typeMediaId'] == 4) {
                $typeMediaFormat = 'VIDEO';
            }
            else {
                $typeMediaFormat = 'DOCUMENT';
            }

            $headerData = array('type' => 'HEADER', 'format' => $typeMediaFormat, 'example' => array('header_handle' => [$templateData['mediaUrl']]));
            //$headerData = array('type' => 'HEADER', 'format' => $typeMediaFormat);
            array_push($components, $headerData);
        }

        //Se o usuário digitou algum texto para o rodapé
        if($templateData['footer']) {
            //Adequa os parágrafos do texto de acordo com o formato suportado pelo Whatsapp
            $textFooter = $utilsController->changeParagraphContentTemplate($templateData['footer']);
            $footerData = array('type' => 'FOOTER', 'text' => $textFooter);
            //Armazena os dados do body no array de componentes
            array_push($components, $footerData);
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
            //Cria o array com os botões
            $buttonData = array('type' => 'BUTTONS', 'buttons' => $buttons);
            //Armazena os dados dos botões no array de componentes
            array_push($components, $buttonData);
        }


        $template = ['name' => $templateData['tem_name'], 'category'=> $templateData['category']['tem_tag'], 'components' => $components, 'language' => $templateData['language']['tem_code']];

        Log::debug('template montado para submissão');
        Log::debug($template);

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $templateData['channel']['cha_api_key'],
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $template);

        $responseData = [];
        if(isset($response['status'])) {
            //Caso o template tenha sido enviada com sucesso
            if($response['status'] == 'submitted') {
                $responseData['status'] = 'success';
                $responseData['namespace'] = $response['namespace'];
                $responseData['message'] = 'Modelo criado com sucesso!';
            }
            else {
                $responseData['status'] = 'error';
                $responseData['message'] = 'Erro ao criar o modelo. Aguarde alguns instantes e tente novamente'; 
            }
        }

        return $responseData;
    }

    public function listTemplates($channel)
    {
        $endPoint = self::BASE_URL.'v1/configs/templates';

        $bodyData = ['limit' => 20000, 'offset'=> 0, 'sort' => 'id'];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->get($endPoint, $bodyData);

        //Log::debug('listTemplates 360Dialog');
        //Log::debug($response);

        return $response;
    }

    public function removeTemplate($channel, $templateName)
    {
        $endPoint = self::BASE_URL.'v1/configs/templates/'.$templateName;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->delete($endPoint);
        
        Log::debug('resposta da função deleteTemplate 360Dialog');
        Log::debug($response);

        $responseData = [];
        //Se o template foi excluído com sucesso
        if($response['meta']['success'] == true) {
            $responseData['status'] = 'success';
        } //Se houve algum erro ao excluir o template e esse é por o mesmo não existir no broker-
        else if($response['meta']['success'] == false) {
            $responseData['status'] = 'error';
            if($response['meta']['developer_message'] == 'Object not found') {
                $responseData['message'] = 'Template Does not exists';
            }
            else {
                $responseData['message'] = 'Another Error';
            }
        }

        return $responseData;
    }

    public function getSettingsApplication($channel)
    {
        $endPoint = self::BASE_URL.'v1/settings/application';

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'D360-API-KEY' => $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->get($endPoint);
        
        Log::debug('resposta da função getSettingsApplication');
        Log::debug($response);

        //return $response;
    }

    //Webhook para recebimentos das informações referentes a API da 360 Dialog
    public function webhook360Dialog(Request $callback_api, $channelId)
    { 
        Log::debug('mensagem recebida da 360 dialog');
        Log::debug($callback_api);
        Log::debug('Id do canal 360 dialog');
        Log::debug($channelId);
        
        $contactController = new ContactController();
        $chatController = new ChatController();
        $eventController = new EventController();
        $userController = new UserController();

        $statusMessage = null;
        $apiName = '360Dialog';

        //Se for uma mensagem recebida de um CONTATO
        if(isset($callback_api['messages'])) {
            
            $payloadMessage = [];
            //Pega o id da mensagem
            $payloadMessage['id'] = $callback_api['messages'][0]['id'];
            //Caso seja mensagem de texto
            if($callback_api['messages'][0]['type'] == 'text') {
                $payloadMessage['type'] = 'text';
                $payloadMessage['payload']['text'] = $callback_api['messages'][0]['text']['body'];
            } //Se o usuário enviou um contato
            else if($callback_api->type == 'vcard') {
                $payloadMessage['type'] = 'vcard';
                $payloadMessage['payload']['contactName'] = $callback_api->vcardFormattedName;
                
                //Extrai o telefone do contato compartilhado
                $contentVcard = explode('waid=',$callback_api->content);
                $contentVcard = explode(':',$contentVcard[1]);
                $contactPhoneNumber = $contentVcard[0]; 
                
                $payloadMessage['payload']['contactPhoneNumber'] = $contactPhoneNumber;
            } //Caso seja uma resposta rápida de um botão
            else if($callback_api['messages'][0]['type'] == 'button') {
                $payloadMessage['type'] = 'text';
                $payloadMessage['payload']['text'] = $callback_api['messages'][0]['button']['text'];
            }
            else if($callback_api['messages'][0]['type'] == 'document' && !(isset($callback_api['messages'][0]['errors']))) {
                $payloadMessage['type'] = 'file';
                //$payloadMessage['payload']['caption'] = $callback_api['messages'][0]['document']['caption'];
                $payloadMessage['payload']['name'] = $callback_api['messages'][0]['document']['filename'];
                $payloadMessage['payload']['contentType'] = $callback_api['messages'][0]['document']['mime_type'];
                $payloadMessage['payload']['mediaId'] = $callback_api['messages'][0]['document']['id']; // Id da mídia para download
            }
            else if($callback_api['messages'][0]['type'] == 'image') {
                $payloadMessage['type'] = 'image';
                //$payloadMessage['payload']['caption'] = $callback_api['messages'][0]['document']['caption'];
                $payloadMessage['payload']['contentType'] = $callback_api['messages'][0]['image']['mime_type'];
                $payloadMessage['payload']['mediaId'] = $callback_api['messages'][0]['image']['id']; // Id da mídia para download
            }
            else if($callback_api['messages'][0]['type'] == 'video') {
                $payloadMessage['type'] = 'video';
                //$payloadMessage['payload']['caption'] = $callback_api['messages'][0]['document']['caption'];
                $payloadMessage['payload']['contentType'] = $callback_api['messages'][0]['video']['mime_type'];
                $payloadMessage['payload']['mediaId'] = $callback_api['messages'][0]['video']['id']; // Id da mídia para download
            }
            else if($callback_api['messages'][0]['type'] == 'voice') {
                $payloadMessage['type'] = 'audio';
                $payloadMessage['payload']['contentType'] = $callback_api['messages'][0]['voice']['mime_type'];
                $payloadMessage['payload']['mediaId'] = $callback_api['messages'][0]['voice']['id']; // Id da mídia para download
            }
            else if($callback_api['messages'][0]['type'] == 'audio') {
                $payloadMessage['type'] = 'audio';
                $payloadMessage['payload']['contentType'] = $callback_api['messages'][0]['audio']['mime_type'];
                $payloadMessage['payload']['mediaId'] = $callback_api['messages'][0]['audio']['id']; // Id da mídia para download
            }
            else if($callback_api['messages'][0]['type'] == 'contacts') {
                $payloadMessage['type'] = 'vcard';
                $payloadMessage['payload']['contactName'] = $callback_api['messages'][0]['contacts'][0]['name']['formatted_name'];
                $payloadMessage['payload']['contactPhoneNumber'] = $callback_api['messages'][0]['contacts'][0]['phones'][0]['wa_id'];
            }
            else if($callback_api['messages'][0]['type'] == 'sticker' && !(isset($callback_api['messages'][0]['errors']))) {
                $payloadMessage['type'] = 'sticker';
                $payloadMessage['payload']['contentType'] = $callback_api['messages'][0]['sticker']['mime_type'];
                $payloadMessage['payload']['mediaId'] = $callback_api['messages'][0]['sticker']['id']; // Id da mídia para download
            }
            else if($callback_api['messages'][0]['type'] == 'location') {
                $payloadMessage['type'] = 'location';
                $payloadMessage['payload']['latitude'] = $callback_api['messages'][0]['location']['latitude'];
                $payloadMessage['payload']['longitude'] = $callback_api['messages'][0]['location']['longitude'];
            }

            $payloadMessage['sender']['name'] = $callback_api['contacts'][0]['profile']['name'];

            //Se o telefone do contato já tem o 9 na frente do número
            if(strlen($callback_api['contacts'][0]['wa_id']) == 13) {
                $payloadMessage['sender']['phone'] = $callback_api['contacts'][0]['wa_id'];
            } //Se o número veio SEM o 9 na frente do mesmo
            else {
                $ddi = substr($callback_api['contacts'][0]['wa_id'], 0, 2);
                //Se for o DDI do Brasil
                if($ddi == '55') {
                    $payloadMessage['sender']['phone'] = substr_replace($callback_api['contacts'][0]['wa_id'], '9', 4, 0);
                }
                else {
                    $payloadMessage['sender']['phone'] = $callback_api['contacts'][0]['wa_id'];
                }
            }

            //Dados do contato
            $contactData = new Request([
                'name'   => $payloadMessage['sender']['name'],
                'phoneNumber' => $payloadMessage['sender']['phone'],
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
                        $chatController->storeMessage($chat['id'], 2, $contact['id'], $payloadMessage, null, $apiName, 'false', $channelData, null, null);
                        //Incrementa o número de mensagens não visualizadas em 1
                        $chatController->incrementUnseenMessage($chat['id']);
                    }
                }
            }
        }
        //Se for um evento de status 
        else if(isset($callback_api['statuses'])) {
            //Se for um status de uma mensagem (mensagem entregue, enviada, etc.)
            if($callback_api['statuses'][0]['type'] == 'message') {
                //Se a mensagem foi ENVIADA
                if($callback_api['statuses'][0]['status'] == 'sent') {
                    $statusMessageChatId = 2;
                    $apiMessageId = $callback_api['statuses'][0]['id'];
                } //Se a mensagem foi ENTREGUE
                else if($callback_api['statuses'][0]['status'] == 'delivered') {
                    $statusMessageChatId = 3;
                    $apiMessageId = $callback_api['statuses'][0]['id'];
                }
                else if($callback_api['statuses'][0]['status'] == 'read') {
                    $statusMessageChatId = 5;
                    $apiMessageId = $callback_api['statuses'][0]['id'];
                }
                else if($callback_api['statuses'][0]['status'] == 'failed') {
                    $statusMessageChatId = 4;
                    $apiMessageId = $callback_api['statuses'][0]['id'];

                    //Se o erro for por que já faz mais de 24 horas de inatividade na conversação
                    if($callback_api['statuses'][0]['errors'][0]['code'] == 470) {
                        $statusMessage = "A mensagem não foi entregue pois já faz mais de 24 horas desde a última vez que o contato lhe enviou uma mensagem. Caso queira se comunicar com o contato, envie uma mensagem modelo.";
                    }
                    else {
                        $statusMessage = "Erro ao enviar a mensagem";
                    }
                }
                else if($callback_api['statuses'][0]['status'] == 'deleted') {
                    $statusMessageChatId = 6;
                    $apiMessageId = $callback_api['statuses'][0]['id'];
                }
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
            else {

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
    }

}
