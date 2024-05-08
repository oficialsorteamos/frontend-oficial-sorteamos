<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\TemplateController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Setting\CustomerController;
use App\Http\Controllers\Utils\UtilsController;
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

use function Psy\debug;

class CloudApiWhatsAppController extends Controller
{
    const BASE_URL = 'https://graph.facebook.com/v19.0/';

    public function sendMessage($channel, $destination, $message)
    {   
        try {
            $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/messages';

            $messageData = '';
            $mediaUploadedData = '';

            Log::debug('send Message cloud');
            Log::debug($message);

            //$contactSituation = self::checkContact($channel, $destination);
            $bodyReplyMessage = array('message_id' => isset($message->answered_message_id)? $message->answered_message_id : 'null');
            //Se o contato é válido e está disponível no Whatsapp
            //if($contactSituation['contacts'][0]['status'] == 'valid') {
                //Se o tipo de mensagem for um texto
                if($message->type_message_chat_id == 1) {
                    //$messageData = json_encode(array("isHSM" => "false", "type" => "text", "text" => $message->mes_message));
                    $bodyMessage = array('body' => $message->mes_message);
                    $messageData = ['messaging_product' => 'whatsapp', 'to' => $destination, 'type' => 'text', 'text' => $bodyMessage, 'context' => $bodyReplyMessage];
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
                        if($mediaUploadedData = self::uploadMedia($channel, $filePath, $typeData, $message->type_message_chat_id, $message->mes_content_name)) {
                            $bodyMessage = array('id' => $mediaUploadedData['id'], 'caption' => $message->mes_content_name);
                            //$bodyMessage = array('link' => $filePath, 'caption' => $message->mes_content_name);
                            $messageData = ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'image', 'image' => $bodyMessage, 'context' => $bodyReplyMessage];
                        }

                    }
                    //Se for um vídeo
                    else if($message->type_message_chat_id == 4) {
                        //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name);
                        $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name;
                        $type = pathinfo($filePath, PATHINFO_EXTENSION);
                        $typeData = 'video/'.$type;

                        //Faz o upload da mídia na 360Dialog, que retorna o id da mídia upada
                        if($mediaUploadedData = self::uploadMedia($channel, $filePath, $typeData, $message->type_message_chat_id, null)) {
                            Log::debug('mídia que foi realizada o download');
                            Log::debug($mediaUploadedData);
                            $bodyMessage = array('id' => $mediaUploadedData['id'], 'caption' => $message->mes_content_name);
                            $messageData = ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'video', 'video' => $bodyMessage, 'context' => $bodyReplyMessage];
                        }
                    }
                    //Se for um arquivo 
                    else if($message->type_message_chat_id == 5) {
                        //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/files/".$message->mes_content_name);
                        $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/files/".$message->mes_content_name;
                        $type = pathinfo($filePath, PATHINFO_EXTENSION);
                        //$typeData = 'application/'.$type;
                        //Se for uma planilha
                        if($type == 'sheet') {
                            $typeData = 'application/vnd.ms-excel';
                        }
                        else if($type == 'pdf') {
                            $typeData = 'application/pdf';
                        }
                        else if($type == 'document') {
                            $typeData = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                        }
                        else {
                            $typeData = 'application/'.$type;
                        }

                        //Log::debug('tipo de arquivo');
                        //Log::debug($typeData);
                        
                        //Faz o upload da mídia na 360Dialog, que retorna o id da mídia upada
                        //if($mediaUploadedData = self::uploadMedia($channel, $filePath, $typeData, $message->type_message_chat_id)) {
                            //Log::debug('mídia que foi realizada o download');
                            //Log::debug($mediaUploadedData);
                            /*$urlMedia = env('URL_SERVER');

                            //Se estiver sendo criado localmente
                            if($urlMedia == 'https://127.0.0.1') {
                                $bodyMessage = array('link' => ENV('NGROK_URL')."/storage/chats/chat".$message->chat_id."/files/".$message->mes_content_name, 'caption' => $message->mes_content_name);
                            }
                            else {
                                $bodyMessage = array('link' => $urlMedia."/storage/chats/chat".$message->chat_id."/files/".$message->mes_content_name, 'caption' => $message->mes_content_name);
                            }*/
                            $bodyMessage = array('link' => $filePath, 'caption' => $message->mes_content_name);                            
                            $messageData = ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'document', 'document' => $bodyMessage, 'context' => $bodyReplyMessage];
                        //}                    
                    }
                    //Se for um audio 
                    else if($message->type_message_chat_id == 2) {
                        //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name);
                        $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name;
                        $type = pathinfo($filePath, PATHINFO_EXTENSION);

                        $typeData = 'audio/'.$type;

                        //Log::debug('tipo de áudio');
                        //Log::debug($typeData);

                        //Faz o upload da mídia na Cloud API, que retorna o id da mídia upada
                        if($mediaUploadedData = self::uploadMedia($channel, $filePath, $typeData, $message->type_message_chat_id)) {
                            Log::debug('$mediaUploadedData');
                            Log::debug($mediaUploadedData);
                            /*$urlMedia = env('URL_SERVER');

                            //Se o template estiver sendo criado localmente
                            if($urlMedia == 'https://127.0.0.1') {
                                $bodyMessage = array('link' => ENV('NGROK_URL')."/storage/chats/chat".$message->chat_id."/audios/".$message->mes_content_name);
                            }
                            else {
                                $bodyMessage = array('link' => $urlMedia."/storage/chats/chat".$message->chat_id."/audios/".$message->mes_content_name);
                            }*/
                            $bodyMessage = array('id' => $mediaUploadedData['id']);
                            $messageData = ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'audio', 'audio' => $bodyMessage, 'context' => $bodyReplyMessage];
                        }
                    }    
                }
                Log::debug('dados de envio da mensagem');
                Log::debug($messageData);

                //Envia os dados
                $response = Http::withHeaders([
                    'Accept' => '*/*',
                    'Authorization' => 'Bearer '. $channel['cha_api_key'],
                    'Content-Type' => 'application/json; charset=utf-8'
                ])
                ->timeout(20)
                //->asForm()
                ->post($endPoint, $messageData);
                
                Log::debug('Resposta cloud API');
                Log::debug($response);
                
                if(isset($response['meta']['success'])) {
                    //Se a mensagem não foi enviada com sucesso
                    if($response['meta']['success'] == false) {
                        //Tenta enviar novamente
                        $response = Http::withHeaders([
                            'Accept' => '*/*',
                            'Authorization' => 'Bearer '. $channel['cha_api_key'],
                            'Content-Type' => 'application/json; charset=utf-8'
                        ])
                        ->timeout(20)
                        //->asForm()
                        ->post($endPoint, $messageData);
                    }
                }
                
                //Se o tipo de mensagem for um áudio, imagem, vídeo ou arquivo
                /*if($message->type_message_chat_id == 2 || $message->type_message_chat_id == 3 || $message->type_message_chat_id == 4 
                        || $message->type_message_chat_id == 5) {
                    //Deleta a mídia da 360 Dialog após o envio
                    self::deleteMedia($channel, $mediaUploadedData['media'][0]['id']);
                }*/

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

    public function sendQuickMessageWithParameters($channel, $destination, $message)
    {
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/messages';
        $buttons = [];
        $typeButtons = 1;
        $hasImage = 0;
        $hasAudio = 0;
        $hasFile = 0;
        $hasButton = 0;
        $hasMovie = 0;

        $utilsController = new UtilsController();

        if(is_object($message)) {
            $messageAux  = (array) $message;
            if(isset($messageAux['quickMessageData'])) {
                $message = $messageAux;
            }
        }

        Log::debug('quick message send cloud API');
        Log::debug($message);

        foreach($message['quickMessageData']['parameters'] AS $key => $parameter) {
            //Se for um botão
            if($parameter['type_parameter_id'] == 1) {
                $hasButton = 1;  
                //Se for um botão de resposta rápida
                if($parameter['type_button_id'] == 1) {
                    $typeButtons = 1;
                    $buttonArray = array('type' => "reply", 'reply' => ['id' => $key+1, 'title' => $parameter['qui_content'] ] );
                }
                //Se for botões de Ação
                else if($parameter['type_button_id'] == 2) {
                    $typeButtons = 1;
                    //Se o botão for um LINK
                    if($parameter['qui_url']) {
                        //$buttonArray = array('id' => $key+1, 'type' => 'URL', 'url' => $parameter['qui_url'], 'label' => $parameter['qui_content']);
                        $buttonArray = array('type' => "url", 'url' => ['id' => $key+1, 'title' => $parameter['qui_content'], 'url' => $parameter['qui_url']] );
                    } //Se for um telefone
                    else {
                        $buttonArray = array('type' => "phone_number", 'phone_number' => ['id' => $key+1, 'title' => $parameter['qui_content'], 'phone_number' => $parameter['qui_phone_number']] );
                        //$buttonArray = array('id' => $key+1, 'type' => 'CALL', 'phone' => $parameter['qui_phone_number'], 'label' => $parameter['qui_content']);
                    }
                }
                //Se for botões de Lista
                else if($parameter['type_button_id'] == 3) {
                    $typeButtons = 2;
                    $buttonArray = array('id' => $key+1, 'title' => $parameter['qui_content'], 'description' => $parameter['qui_description']);
                }
                //Insere o botão na array de botões
                array_push($buttons, $buttonArray);
            } //Se for uma imagem
            else if($parameter['type_parameter_id'] == 2) {
                $hasImage = 1;
                $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/quickMessages/quickMessage'.$parameter['quick_message_id'].'/header/'.$parameter['qui_media_name'];
            }
            else if($parameter['type_parameter_id'] == 3) {
                $hasAudio = 1;
                $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/quickMessages/quickMessage'.$parameter['quick_message_id'].'/header/'.$parameter['qui_media_name'];
            }
            else if($parameter['type_parameter_id'] == 4) {
                $hasFile = 1;
                $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/quickMessages/quickMessage'.$parameter['quick_message_id'].'/header/'.$parameter['qui_media_name'];
                $fileNameData = explode('.', $parameter['qui_media_original_name']) ;
                $fileName = $fileNameData[0];
            }//Se for um vídeo
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
                if($hasImage) {
                    $messageData = ['phone' => $destination, 'message' => $message['mes_message'], 'buttonList' => array('image' => $urlMedia, 'buttons' =>$buttons)];
                }
                else {
                    $messageData = ['messaging_product'=> 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'interactive', 'interactive' => ['type' => 'button', 'body' => [ 'text' => $message['mes_message'] ], 'action' => ['buttons' => $buttons]] ];
                    //$messageData = ['phone' => $destination, 'message' => $message['mes_message'], 'buttonList' => array('buttons' =>$buttons)];
                }
            } //Se for botão de LISTA
            else if($typeButtons == 2) {
                $messageData = ['messaging_product'=> 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'interactive', 'interactive' => ['type' => 'list', 'body' => [ 'text' => $message['mes_message'] ], 'action' => [ "button" => $message['quickMessageData']['qui_list_name'], 'sections' => [json_encode(["title" => "SECTION_1_TITLE", 'rows' => $buttons ])] ] ] ];
            }
            else {
                $endPoint = $endPoint.'/send-button-actions';
                $messageData = ['phone' => $destination, 'message' => $message['mes_message'], 'buttonActions' => $buttons];
            }
        } //Se não tem botão
        else {
            //Se tem imagem
            if($hasImage) {
                $type = pathinfo($urlMedia, PATHINFO_EXTENSION);

                if($type == 'jpg') {
                    $type = 'jpeg';
                }

                $typeData = 'image/'.$type;
                //Faz o upload da mídia na 360Dialog, que retorna o id da mídia upada
                if($mediaUploadedData = self::uploadMedia($channel, $urlMedia, $typeData, $message->type_message_chat_id, $message->mes_content_name)) {
                    $bodyMessage = array('id' => $mediaUploadedData['id'], 'caption' => $message['mes_message']);
                    //$bodyMessage = array('link' => $filePath, 'caption' => $message->mes_content_name);
                    $messageData = ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'image', 'image' => $bodyMessage];
                }
            } //Se for uma mensagem de áudio
            else if($hasAudio) {
                $type = pathinfo($urlMedia, PATHINFO_EXTENSION);

                $bodyMessage = array('link' => $urlMedia);
                $messageData = ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'audio', 'audio' => $bodyMessage];
            } //Se for uma mensagem de arquivo
            else if($hasFile) {
                $bodyMessage = array('link' => $urlMedia, 'caption' => $message['quickMessageData']['parameters'][0]['qui_media_original_name']);                            
                $messageData = ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'document', 'document' => $bodyMessage];
            } //Se for uma mensagem de vídeo
            else if($hasMovie) {
                $type = pathinfo($urlMedia, PATHINFO_EXTENSION);
                $typeData = 'video/'.$type;

                //Faz o upload da mídia na 360Dialog, que retorna o id da mídia upada
                if($mediaUploadedData = self::uploadMedia($channel, $urlMedia, $typeData, $message->type_message_chat_id, null)) {
                    $bodyMessage = array('id' => $mediaUploadedData['id'], 'caption' => $message['mes_message']);
                    $messageData = ['messaging_product' => 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'video', 'video' => $bodyMessage];
                }
            }
            //Se não tem botão nem imagem (SE FOR APENAS TEXTO)
            else {
                $bodyMessage = array('body' => $message['mes_message']);
                $messageData = ['messaging_product' => 'whatsapp', 'to' => $destination, 'type' => 'text', 'text' => $bodyMessage];
            }
        }

        Log::debug('data quick message formatted');
        Log::debug($messageData);

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(60)
        //->asForm()
        ->post($endPoint, $messageData);
        
        Log::debug('Resposta Quick Message cloud API');
        Log::debug($response);

        
        $responseData = [];
        
        //Se a mensagem foi ENVIADA
        if(isset($response['messages'])) {
            $responseData['status'] = 'success';
            $responseData['message']['id'] = $response['messages'][0]['id'];
        }

        return $responseData;
    }

    //Envia uma mensagem template (modelo)
    public function sendTemplateMessage($channel, $destination, $messageTemplateData)
    {
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/messages';
        $responseFormatted['apiName'] = 'cloudApiWhatsapp';
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
            $bodyTemplateMessage = array('language' => array('policy' => 'deterministic', 
                                    'code' => $messageTemplateData['templateData']['tem_code']), 'name' => $messageTemplateData['templateData']['tem_name'],
                                    'components' => $components);
        }
        else {
            $bodyTemplateMessage = array('language' => array('policy' => 'deterministic', 
                                    'code' => $messageTemplateData['templateData']['tem_code']), 'name' => $messageTemplateData['templateData']['tem_name']);
        }
        
        $messageData = ['messaging_product'=> 'whatsapp', 'recipient_type' => 'individual', 'to' => $destination, 'type' => 'template', 'template' => $bodyTemplateMessage];

        //Log::debug('dados para envio pela função sendTemplateMessage');
        //Log::debug($messageData);

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $messageData);
        
        Log::debug('Resposta envio da mensagem template via CLOUD API');
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
    public function uploadMedia($channel, $mediaPath, $contentType, $typeMedia, $fileName=null)
    {   
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/media';
        
        //$curlfile = new \CURLFile($filePath);
        $filedata = array("file" => new \CURLFile($mediaPath, $contentType, $fileName), "type" => $contentType, "messaging_product" => "whatsapp");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endPoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: Bearer ".$channel['cha_api_key'], "Content-Type: multipart/form-data"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $filedata);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        
        Log::debug('response upload media');
        Log::debug($response);
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

    //Obtém a URL mídia
    public function getMedia($channel, $mediaId)
    {
        $endPoint = self::BASE_URL.$mediaId;

        Log::debug('getMedia cloud api');
        Log::debug($channel);

        $response = null;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->get($endPoint);
        
        Log::debug('getMedia response Cloud');
        Log::debug($response);

        return $response;
    }

    //Utilizado para carregamento de mídias para TEMPLATES
    public function uploadMediaResumable($channel, $mediaData)
    {
        $endPoint = self::BASE_URL.$channel['cha_app_id_api'].'/uploads';

        $bodyData = ['file_length' => $mediaData['file_size'], 'file_type' => $mediaData['mime_type'], 'access_token' => $channel['cha_api_key']];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->post($endPoint, $bodyData);
        
        Log::debug('uploadMediaResumable response Cloud');
        Log::debug($response);

        return $response;
    }

    //Utilizado para carregamento de mídias para TEMPLATES
    public function uploadMediaLoading($channel, $mediaPath, $uploadSessionId, $contentType, $fileName)
    {
        $endPoint = self::BASE_URL.$uploadSessionId;
        
        $filedata = array("file" => new \CURLFile($mediaPath, $contentType, $fileName));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endPoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Authorization: OAuth ".$channel['cha_api_key'], "Content-Type: multipart/form-data", "file_offset: 0"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $filedata);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        
        Log::debug('response uploadMediaLoading');
        Log::debug($response);
        return $response;
    }

    public function downloadMedia($channel, $mediaUrl)
    {
        $response = null;

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->get($mediaUrl);

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
        //$customerController = new CustomerController();
        //Traz os dados do cliente
        //$customerData =  $customerController->getCustomer();

        $endPoint = self::BASE_URL.$templateData['channel']['whatsapp_business_account_id'].'/message_templates';

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

            $mediaData =  self::uploadMedia($templateData['channel'], $templateData['mediaUrl'], $templateData['mediaType'], $templateData['mediaName']);
            $mediaDetails = self::getMedia($templateData['channel'], $mediaData['id']);
            $mediaDataResumable = self::uploadMediaResumable($templateData['channel'], $mediaDetails);
            $mediaDataLoading = self::uploadMediaLoading($templateData['channel'], $templateData['mediaUrl'], $mediaDataResumable['id'], $templateData['mediaType'], $templateData['mediaName']); 

            $headerData = array('type' => 'HEADER', 'format' => $typeMediaFormat, 'example' => array('header_handle' => [$mediaDataLoading['h']]));
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
        //Log::debug('canal para criação do template');
        //Log::debug($templateData['channel']);

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer '. $templateData['channel']['cha_api_key'],
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $template);

        $responseData = [];
        if(isset($response['id'])) {
            $responseData['status'] = 'success';
            $responseData['id'] = $response['id'];
            $responseData['message'] = 'Modelo criado com sucesso!';
        }
        else if(isset($response['error'])) {
            $responseData['status'] = 'error';
            if($response['error']['code'] == 100) {
                if($response['error']['error_subcode'] == 2388023) {
                    $responseData['message'] = 'Não foi possível criar o modelo pois existe um modelo com o mesmo nome em processo de remoção. Tente criar o modelo com um nome diferente.';
                }
                else {
                    $responseData['message'] = 'Erro ao criar o template.';
                }
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

        return $response;
    }

    public function removeTemplate($channel, $templateName)
    {
        //$customerController = new CustomerController();
        //Traz os dados do cliente
        //$customerData =  $customerController->getCustomer();

        $endPoint = self::BASE_URL.$channel['whatsapp_business_account_id'].'/message_templates?name='.$templateName.'&access_token='.$channel['cha_api_key'];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->delete($endPoint);
        
        Log::debug('resposta da função deleteTemplate CloudApi');
        Log::debug($response);

        $responseData = [];
        //Se o template foi excluído com sucesso
        if(isset($response['success']) && $response['success'] == true) {
            $responseData['status'] = 'success';
        } //Se houve algum erro ao excluir o template e esse é por o mesmo não existir no broker-
        else if(isset($response['error'])) {
            $responseData['status'] = 'error';
            if($response['error']['error_user_title'] == 'Message Template Not Found') {
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

    //Busca as informações do número de telefone cadastrado no WhatsApp
    public function getInfoPhoneNumber($channel)
    {
        //$customerController = new CustomerController();
        $phoneInfo = null;

        //Traz os dados do cliente
        //$customerData =  $customerController->getCustomer();

        $endPoint = self::BASE_URL.$channel['whatsapp_business_account_id'].'/phone_numbers';

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
        ])
        ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
        //->asForm()
        ->get($endPoint);
        
        //Filtra a array de números pelo número de telefone específico
        $phoneInfo = array_filter($response['data'], function ($item) use($channel) {               
            return preg_replace('/[^0-9]/', '', $item['display_phone_number']) == $channel['cha_phone_ddi'].$channel['cha_phone_number'];
        });

        Log::debug('$phoneInfo');
        Log::debug($phoneInfo);

        return $phoneInfo;
    }

    public function getWabaInfo($channel)
    {
        $endPoint = self::BASE_URL.'/debug_token?input_token='.$channel['cha_api_key'];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
        ])
                    ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
                    //->asForm()
                    ->get($endPoint);
        
        Log::debug('waba info');
        Log::debug($response);
        
        return $response;
    }

    //Habilita a verificação em duas etapas, setando o PIN
    public function setTwoStepVerification($channel)
    {
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'];
        
        $bodyData = ['pin' => 303912];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
        ])
                    ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
                    //->asForm()
                    ->post($endPoint, $bodyData);
        
        Log::debug('setTwoStepVerification response');
        Log::debug($response);
        
        return $response;
    }

    //Registra um telefone no WhatsApp (em caso de um novo número ou da alteração do nome de exibição de um número)
    public function registerPhone($channel)
    {
        $endPoint = self::BASE_URL.$channel['cha_channel_id_api'].'/register';
        
        $bodyData = ['messaging_product'=> 'whatsapp', 'pin' => 303912];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '. $channel['cha_api_key'],
        ])
                    ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
                    //->asForm()
                    ->post($endPoint, $bodyData);
        
        Log::debug('registerPhone response');
        Log::debug($response);
        
        return $response;
    }

    //Faz uma chamada para migração de um número de telefone
    public function migratePhone($channel)
    {
        //$customerController = new CustomerController();

        //Traz os dados do cliente
        //$customerData =  $customerController->getCustomer();

        $endPoint = self::BASE_URL.$channel['whatsapp_business_account_id'].'/phone_numbers';
        
        $bodyData = ['cc'=> 55, 'phone_number' => 27988603935, 'migrate_phone_number' => true, 'access_token' => $channel['cha_api_key']];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
        ])
                    ->timeout(60) //Número máximo em segundos aguardando uma resposta da API
                    //->asForm()
                    ->post($endPoint, $bodyData);
        
        Log::debug('migratePhone response');
        Log::debug($response);
        
        return $response;
    }

    //Webhook para recebimentos das informações referentes a Cloud API
    public function apiCloudWebhook(Request $callbackApi)
    {
        Log::debug('chamou a apiCloud');
        $webhookData =  $callbackApi->all();
        Log::debug($webhookData);
        
        //Se NÃO for um desafio do Whatsapp
        if(!isset($webhookData['hub_challenge'])) {
            $eventController = new EventController();        
            $chatController = new ChatController();        
            $userController = new UserController();
            $contactController = new ContactController();
    
            $statusMessage = null;
            $apiName = 'cloudApiWhatsapp';

            $callbackApi = $callbackApi['entry'][0]['changes'][0]['value'];
    
            //Se for uma mensagem recebida de um CONTATO
            if(isset($callbackApi['messages'])) {
                
                $payloadMessage = [];
                //Pega o id da mensagem
                $payloadMessage['id'] = $callbackApi['messages'][0]['id'];
                //Se for uma mensagem de resposta a uma mensagem anterior, pega o id da mensagem respondida
                $payloadMessage['answeredMessageId'] = isset($callbackApi['messages'][0]['context']['id'])? $callbackApi['messages'][0]['context']['id'] : null;
                //Caso seja mensagem de texto
                if($callbackApi['messages'][0]['type'] == 'text') {
                    $payloadMessage['type'] = 'text';
                    $payloadMessage['payload']['text'] = $callbackApi['messages'][0]['text']['body'];
                } //Se o usuário enviou um contato
                else if($callbackApi['messages'][0]['type'] == 'vcard') {
                    $payloadMessage['type'] = 'vcard';
                    $payloadMessage['payload']['contactName'] = $callbackApi->vcardFormattedName;
                    
                    //Extrai o telefone do contato compartilhado
                    $contentVcard = explode('waid=',$callbackApi->content);
                    $contentVcard = explode(':',$contentVcard[1]);
                    $contactPhoneNumber = $contentVcard[0]; 
                    
                    $payloadMessage['payload']['contactPhoneNumber'] = $contactPhoneNumber;
                } //Caso seja uma resposta rápida de um botão
                else if($callbackApi['messages'][0]['type'] == 'button') {
                    $payloadMessage['type'] = 'text';
                    $payloadMessage['payload']['text'] = $callbackApi['messages'][0]['button']['text'];
                }
                else if($callbackApi['messages'][0]['type'] == 'document') {
                    $payloadMessage['type'] = 'file';
                    $payloadMessage['payload']['name'] = $callbackApi['messages'][0]['document']['filename'];
                    $payloadMessage['payload']['contentType'] = $callbackApi['messages'][0]['document']['mime_type'];
                    $payloadMessage['payload']['mediaId'] = $callbackApi['messages'][0]['document']['id']; // Id da mídia para download
                    $payloadMessage['payload']['caption'] = isset($callbackApi['messages'][0]['document']['caption'])? $callbackApi['messages'][0]['document']['caption'] : null;
                }
                else if($callbackApi['messages'][0]['type'] == 'image') {
                    $payloadMessage['type'] = 'image';
                    $payloadMessage['payload']['contentType'] = $callbackApi['messages'][0]['image']['mime_type'];
                    $payloadMessage['payload']['mediaId'] = $callbackApi['messages'][0]['image']['id']; // Id da mídia para download
                    $payloadMessage['payload']['caption'] = isset($callbackApi['messages'][0]['image']['caption'])? $callbackApi['messages'][0]['image']['caption'] : null;
                }
                else if($callbackApi['messages'][0]['type'] == 'video') {
                    $payloadMessage['type'] = 'video';
                    $payloadMessage['payload']['contentType'] = $callbackApi['messages'][0]['video']['mime_type'];
                    $payloadMessage['payload']['mediaId'] = $callbackApi['messages'][0]['video']['id']; // Id da mídia para download
                    $payloadMessage['payload']['caption'] = isset($callbackApi['messages'][0]['video']['caption'])? $callbackApi['messages'][0]['video']['caption'] : null;
                }
                else if($callbackApi['messages'][0]['type'] == 'voice') {
                    $payloadMessage['type'] = 'audio';
                    $payloadMessage['payload']['contentType'] = $callbackApi['messages'][0]['voice']['mime_type'];
                    $payloadMessage['payload']['mediaId'] = $callbackApi['messages'][0]['voice']['id']; // Id da mídia para download
                }
                else if($callbackApi['messages'][0]['type'] == 'audio') {
                    $payloadMessage['type'] = 'audio';
                    $payloadMessage['payload']['contentType'] = $callbackApi['messages'][0]['audio']['mime_type'];
                    $payloadMessage['payload']['mediaId'] = $callbackApi['messages'][0]['audio']['id']; // Id da mídia para download
                }
                else if($callbackApi['messages'][0]['type'] == 'interactive') {
                    $payloadMessage['type'] = 'text';

                    //Se for um botão de AÇÃO
                    if(isset($callbackApi['messages'][0]['interactive']['button_reply'])) {
                        $payloadMessage['payload']['text'] = $callbackApi['messages'][0]['interactive']['button_reply']['title']; //Pega a resposta associada ao botão
                    } //Se for um botão de LISTA
                    else if(isset($callbackApi['messages'][0]['interactive']['list_reply']))
                    {
                        //$descriptionButton = isset($callbackApi['messages'][0]['interactive']['list_reply']['description'])? $callbackApi['messages'][0]['interactive']['list_reply']['description'] : NULL;
                        $payloadMessage['payload']['text'] = $callbackApi['messages'][0]['interactive']['list_reply']['title'];
                    }
                }
                else if($callbackApi['messages'][0]['type'] == 'contacts') {
                    $payloadMessage['type'] = 'vcard';
                    $payloadMessage['payload']['contactName'] = $callbackApi['messages'][0]['contacts'][0]['name']['formatted_name'];
                    $payloadMessage['payload']['contactPhoneNumber'] = $callbackApi['messages'][0]['contacts'][0]['phones'][0]['wa_id'];
                }
                else if($callbackApi['messages'][0]['type'] == 'sticker') {
                    $payloadMessage['type'] = 'sticker';
                    $payloadMessage['payload']['contentType'] = $callbackApi['messages'][0]['sticker']['mime_type'];
                    $payloadMessage['payload']['mediaId'] = $callbackApi['messages'][0]['sticker']['id']; // Id da mídia para download
                }
                else if($callbackApi['messages'][0]['type'] == 'location') {
                    $payloadMessage['type'] = 'location';
                    $payloadMessage['payload']['latitude'] = $callbackApi['messages'][0]['location']['latitude'];
                    $payloadMessage['payload']['longitude'] = $callbackApi['messages'][0]['location']['longitude'];
                }
    
                $payloadMessage['sender']['name'] = $callbackApi['contacts'][0]['profile']['name'];
    
                //Se o telefone do contato já tem o 9 na frente do número
                if(strlen($callbackApi['contacts'][0]['wa_id']) == 13) {
                    $payloadMessage['sender']['phone'] = $callbackApi['contacts'][0]['wa_id'];
                } //Se o número veio SEM o 9 na frente do mesmo
                else {
                    $ddi = substr($callbackApi['contacts'][0]['wa_id'], 0, 2);
                    //Se for o DDI do Brasil
                    if($ddi == '55') {
                        $payloadMessage['sender']['phone'] = substr_replace($callbackApi['contacts'][0]['wa_id'], '9', 4, 0);
                    }
                    else {
                        $payloadMessage['sender']['phone'] = $callbackApi['contacts'][0]['wa_id'];
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
                        $channelController = new ChannelController();
                        $phoneNumberData = $callbackApi['metadata']['display_phone_number'];
                        $phoneNumber = mb_substr($phoneNumberData, 2);
                        $is0800 = substr($phoneNumber, 0, 3);
                        //Log::debug('phoneNumber meta');
                        //Log::debug($is0800);
                        //Se o canal for um 0800
                        if($is0800 == '800') {
                            //Na cloud api, o 0800 não vem com o número 0 na frente
                            $phoneNumber = '0'.$phoneNumber;
                            //Pega o canal que recebeu a mensagem enviada pelo CONTATO
                            $payloadMessage['mesPhoneChannelReceivedMessage'] = '55'.$phoneNumber;
                        } //Se NÃO for um 0800
                        else {
                            //Pega o canal que recebeu a mensagem enviada pelo CONTATO
                            $payloadMessage['mesPhoneChannelReceivedMessage'] = $callbackApi['metadata']['display_phone_number'];
                        }
                        //Número do canal de teste na API Oficial (sem o ddi (1) na frente)
                        //$phoneNumber = '5550626149';

                        //Traz o canal que irá receber a mensagem
                        $channel = $channelController->getChannelByPhoneNumber($phoneNumber);
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
            //Se for um evento de status 
            else if(isset($callbackApi['statuses'])) {
                //Se for um status de uma mensagem (mensagem entregue, enviada, etc.)
                //if($callbackApi['statuses'][0]['type'] == 'message') {
                    //Se a mensagem foi ENVIADA
                    if($callbackApi['statuses'][0]['status'] == 'sent') {
                        $statusMessageChatId = 2;
                        $apiMessageId = $callbackApi['statuses'][0]['id'];
                    } //Se a mensagem foi ENTREGUE
                    else if($callbackApi['statuses'][0]['status'] == 'delivered') {
                        $statusMessageChatId = 3;
                        $apiMessageId = $callbackApi['statuses'][0]['id'];
                    }
                    else if($callbackApi['statuses'][0]['status'] == 'read') {
                        $statusMessageChatId = 5;
                        $apiMessageId = $callbackApi['statuses'][0]['id'];
                    }
                    else if($callbackApi['statuses'][0]['status'] == 'failed') {
                        $statusMessageChatId = 4;
                        $apiMessageId = $callbackApi['statuses'][0]['id'];
    
                        //Se o erro for por que já faz mais de 24 horas de inatividade na conversação
                        if($callbackApi['statuses'][0]['errors'][0]['code'] == 131047) {
                            $statusMessage = "A mensagem não foi entregue pois já faz mais de 24 horas desde a última vez que o contato lhe enviou uma mensagem. Caso queira se comunicar com o contato, envie uma mensagem modelo.";
                        }
                        else {
                            $statusMessage = "Erro ao enviar a mensagem";
                        }
                    }
                    else if($callbackApi['statuses'][0]['status'] == 'deleted') {
                        $statusMessageChatId = 6;
                        $apiMessageId = $callbackApi['statuses'][0]['id'];
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
            } //Se for o status de um template
            else if(isset($callbackApi['message_template_id'])) {
                $templateController = new TemplateController();
                $templateStatusId = $templateController->getStatusIdTemplateCorrelation($callbackApi['event']);
                //Atualiza o status do template
                $templateController->updateStatusTemplateMessage(null, $callbackApi['message_template_id'], $templateStatusId);
            }

            return response()->json([
                
            ], 200);
        }//Se for um desafio enviado pelo Whatsapp
        else {
            return $webhookData['hub_challenge'];
        }
    }
}
