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
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Auth;
use Exception;

class ApiEHostController extends Controller
{
    const BASE_URL = 'https://send4256.api.mkzap.chat/';

    public function sendMessage($channel, $destination, $message)
    {   
        try {
            $utilsController = new UtilsController();
            Log::debug('sendMessage api eHost');

            $endPoint = self::BASE_URL.'send';

            $destination = self::adjustOfPhoneNumber($destination);

            //Se o tipo de mensagem for um texto
            if($message->type_message_chat_id == 1) {
                $endPoint = $endPoint.'/text';
            
                $messageData = ['Phone' => $destination, 'Body' => $message->mes_message];
            }
            //Se for uma imagem
            else if($message->type_message_chat_id == 3) {
                $endPoint = $endPoint.'/image';
                //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/images/".$message->mes_content_name);
                $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/images/".$message->mes_content_name;
                //Pega o arquivo
                //$data = file_get_contents($filePath);
                $data = Http::get($filePath);
                //Converte para base64
                $dataBase64 = $utilsController->convertToBase64EHost($filePath, $data->body(), $message->type_message_chat_id);
                $messageData = ['Phone' => $destination, 'Image' => $dataBase64, 'Caption' => ''];
            }
            //Se for um arquivo 
            else if($message->type_message_chat_id == 5) {
                //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/files/".$message->mes_content_name);
                $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/files/".$message->mes_content_name;
                //$type = pathinfo($filePath, PATHINFO_EXTENSION);
                $endPoint = $endPoint.'/document';
                //$data = file_get_contents($filePath);
                $data = Http::get($filePath);
                $dataBase64 = $utilsController->convertToBase64EHost($filePath, $data->body(), $message->type_message_chat_id);
                $fileName = explode('.', $message->mes_media_original_name);
                $messageData = ['Phone' => $destination, 'Document' => $dataBase64, 'FileName' => $fileName[0]];
            }
            //Se for um audio 
            else if($message->type_message_chat_id == 2) {
                $endPoint = $endPoint.'/audio';
                //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name);
                $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/audios/".$message->mes_content_name;
                //$data = file_get_contents($filePath);
                $data = Http::get($filePath);
                //Traz a duração do áudio em segundos
                $audioDuration = self::getAudioDuration($filePath);

                $dataBase64 = $utilsController->convertToBase64EHost($filePath, $data->body(), $message->type_message_chat_id);
                
                /*Log::debug('$dataBase64 do áudio enviado');
                Log::debug($dataBase64);

                Log::debug('duração audio');
                Log::debug($audioDuration);*/

                $messageData = ['Phone' => $destination, 'Audio' => $dataBase64, 'Duration' => $audioDuration];
            }
            //Se for um vídeo
            else if($message->type_message_chat_id == 4) {
                $endPoint = $endPoint.'/video';
                //$filePath = storage_path("app/public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name);
                $filePath = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC')."public/chats/chat".$message->chat_id."/videos/".$message->mes_content_name;
                //$data = file_get_contents($filePath);
                $data = Http::get($filePath);
                $dataBase64 = $utilsController->convertToBase64EHost($filePath, $data->body(), $message->type_message_chat_id);
                $messageData = ['Phone' => $destination, 'Video' => $dataBase64, 'Caption' => ''];
            }

            //Se for uma resposta a uma mensagem enviada pelo contato
            if($message->answered_message_id != 'null' && $message->answered_message_id != null) {
                $messageData['ContextInfo'] = ['StanzaId' => $message->answered_message_id, 'Participant' => $destination.'@s.whatsapp.net'];
            }
            
            //Envia os dados
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Content-Type' => 'application/json; charset=utf-8',
                'Token' => $channel['cha_session_token']
            ])
            ->timeout(60)
            //->asForm()
            ->post($endPoint, $messageData);

            
            Log::debug('Resposta sendMessage API eHost');
            Log::debug($response);

            
            $responseData = [];
            //Se a mensagem foi ENVIADA
            if($response['code'] == '200' && $response['success'] == true) {
                $responseData['status'] = 'success';
                $responseData['message']['id'] = $response['data']['Id'];
            } //Se o canal está DESCONECTADO
            else if($response['code'] == '500' && $response['success'] == false && $response['error'] == 'No session') {
                $channel->cha_status = 'I';
                $channel->save();
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

    public function getAudioDuration($filePath)
    {
        $command = "ffprobe -i $filePath 2>&1 | grep Duration | cut -d ' ' -f 4 | sed s/,//";
        $duration = shell_exec($command);
        $timeDuration = 0;
        
        //$duration = '00:05:02.00';

        $durationInSeconds = explode(':', $duration);
        //Se o áudio tem mais de 1 minuto
        if(isset($durationInSeconds[1]) && $durationInSeconds[1] != '00') {
            $timeInSeconds = 60* $durationInSeconds[1];
            $timeDuration += $timeInSeconds;
        } //Pega os segundos do áudio
        if(isset($durationInSeconds[2])) {
            $timeInSeconds = 0;
            $timeInSeconds = explode('.', $durationInSeconds[2]);
            
            $timeDuration += (int) $timeInSeconds[0];

            return $timeDuration;
        }
        else {
            $timeDuration = 0;
            return $timeDuration;
        }
    }

    //Função que remove o número 9 de determinados DDD's
    public function adjustOfPhoneNumber($phoneNumber)
    {
        $ddi = substr($phoneNumber, 0, 2);
        //Se é um telefone do Brasil
        if($ddi == '55') {
            $ddd = substr($phoneNumber, 2, 2);
            //Se o DDD for maior ou igual a 31
            if($ddd >= '31') {
                //Se o número possui o nono dígito
                if(strlen($phoneNumber) == 13) {
                    $phoneNumberWithoutNine = substr($phoneNumber, 5, 12);

                    $newPhoneNumber = $ddi.$ddd.$phoneNumberWithoutNine;
                    return $newPhoneNumber;
                } //Se o número veio SEM o 9 na frente do mesmo
                else {
                    return $phoneNumber;
                }
            }
            else {
                return $phoneNumber;
            }
        } //Se for um número de fora do país
        else {
            return $phoneNumber;
        }
    }

    public function sendQuickMessageWithParameters($channel, $destination, $message)
    {
        $endPoint = self::BASE_URL.'send';
        $buttons = [];
        $typeButtons = 1;
        $hasImage = 0;
        $hasAudio = 0;
        $hasFile = 0;
        $hasMovie = 0;
        $hasButton = 0;

        $endpoint = 'https://send4256.api.mkzap.chat/send';

        $destination = self::adjustOfPhoneNumber($destination);

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
                    $buttonArray = array('id' => 'btn'.$key+1, 'text' => $parameter['qui_content']);
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
                $endPoint = $endPoint.'/buttons';
                
                if($hasImage) {
                    $messageData = ['Phone' => $destination, 'Content' => $message['mes_message'], 'Buttons' => array('image' => $urlMedia, 'buttons' =>$buttons)];
                }
                else {
                    $messageData = ['Phone' => $destination, 'Title' => 'Teste', 'Content' => $message['mes_message'], 'Footer' => 'Teste', 'Buttons' => $buttons];
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
                $endPoint = $endPoint.'/mediaurl';
                
                $messageData = ['Jid' => $destination, 'Type' => 'image', 'Caption' => $message['mes_message'], 'Url' => $urlMedia];
            } //Se for uma mensagem de áudio
            else if($hasAudio) {
                $endPoint = $endPoint.'/audio';

                //$data = file_get_contents($filePath);
                $data = Http::get($urlMedia);

                $dateTimeNow = Carbon::now();
                $contentName = 'nameFile'.preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.ogg';
                Log::debug('$contentName');
                Log::debug($contentName);
                $finalFilePath = storage_path('app/public/chats/chat'.$message['chat_id'].'/audios/'.$contentName);
                //Converte para ogg pois a API eHost não aceita .mp3
                $output = shell_exec('ffmpeg -i '.$urlMedia.' -c:a libopus -b:a 128k '.$finalFilePath);
                
                $media = file_get_contents($finalFilePath);

                //Traz a duração do áudio em segundos
                $audioDuration = self::getAudioDuration($urlMedia);

                $dataBase64 = $utilsController->convertToBase64EHost($finalFilePath, $media, 2);
                //Remove o arquivo recém convertido
                unlink($finalFilePath);
                $messageData = ['Phone' => $destination, 'Audio' => $dataBase64, 'Duration' => $audioDuration];
            } //Se for uma mensagem de arquivo
            else if($hasFile) {
                $endPoint = $endPoint.'/mediaurl';

                $messageData = ['Jid' => $destination, 'Type' => 'document', 'Caption' => $message['mes_message'], 'Url' => $urlMedia];
            } //Se for uma mensagem de vídeo
            else if($hasMovie) {
                $endPoint = $endPoint.'/mediaurl';
                
                $messageData = ['Jid' => $destination, 'Type' => 'video', 'Caption' => $message['mes_message'], 'Url' => $urlMedia];
            }
            //Se não tem botão nem imagem (SE FOR APENAS TEXTO)
            else {
                $endPoint = $endPoint.'/text';
                //$messageData = ['phone' => $destination, 'message' => $message['mes_message']];
                //$messageData = ['numero' => $destination, 'texto' => $message['mes_message'], 'tokenKey' => $channel['cha_session_token']];
                $messageData = ['Phone' => $destination, 'Body' => $message['mes_message']];
            }
        }

        Log::debug('$messageData');
        Log::debug($messageData);

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Token' => $channel['cha_session_token']
        ])
        ->timeout(60)
        //->asForm()
        ->post($endPoint, $messageData);
        
        Log::debug('Resposta sendMessage eHost');
        Log::debug($response);

        
        $responseData = [];
        
        //Se a mensagem foi ENVIADA
        //Se a mensagem foi ENVIADA
        if($response['code'] == '200' && $response['success'] == true) {
            $responseData['status'] = 'success';
            $responseData['message']['id'] = $response['data']['Id'];
        }
        else {
            $responseData['status'] = 'error';
        }

        return $responseData;
    }

    //Cria uma nova instância
    public function createInstance($channel)
    {
        $endPoint = self::BASE_URL.'session/init';

        //Nome da instância
        $instanceName = self::createInstanceName($channel);

        $dateTimeNow = Carbon::now();
        $dateTimeNow = $dateTimeNow->format('Y-m-d H:i:s.u');
        
        //Cria uma hash para o token
        $hashedRandom = Hash::make(Str::random(30));

        $token = preg_replace( '/[^0-9]/', '', $dateTimeNow).$hashedRandom;

        $bodyData = ['Name' => $instanceName, 'Token' => $token, 'Os' => 'Chrome'];

        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'admintoken' => env('APIEHOST_TOKEN')
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('response createInstance API eHost');
        Log::debug($response);

        $responseData = [];
        //Se a instância foi criada com sucesso
        if($response['code'] == '200' && $response['success'] == true) {
            $responseData['success'] = true;
            $responseData['tokenKey'] = $token;
        }

        return $responseData;
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
        $endPoint = self::BASE_URL.'session/delete';

        $bodyData = ['Id' => $channel['cha_session_token']];

        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'admintoken' => env('APIEHOST_TOKEN')
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('Resposta cancelInstance(delete) API eHost');
        Log::debug($response);

        return $response;
    }

    //Lista as instâncias
    public function listInstances()
    {
        $endPoint = self::BASE_URL.'session/all';

        //Envia os dados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'admintoken' => env('APIEHOST_TOKEN')
        ])
        ->timeout(30)
        //->asForm()
        ->get($endPoint);

        Log::debug('response listInstances API eHost');
        Log::debug($response);
        
        return $response;
    }

    public function disconnectInstance($token)
    {
        $endPoint = self::BASE_URL.'session/logout';

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Token' => $token
        ])
        ->timeout(60)
        //->asForm()
        ->post($endPoint);
        
        Log::debug('Resposta disconnectInstance eHost');
        Log::debug($response);
    }

    //Atualiza o webhook de entrega de mensagem
    public function setWebhookDelivery($channel)
    {   
        $endPoint = self::BASE_URL.'webhook';

        //Fecha qualquer sessão que possa estar aberta para o canal na API
        //self::closeSession($channel);

        $bodyData = [ "WebhookURL" => env('WEBHOOK_URL_APIEHOST').'/'.$channel['id'] ];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Token' => $channel['cha_session_token']
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('Resposta setWebhookDelivery API eHost');
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
        $endPoint = self::BASE_URL.'session/connect';

        $eventController = new EventController();

        //$statusConnection = self::checkConnectionSession($channel);
    
        //Fecha qualquer sessão que possa estar aberta para o canal na API
        self::closeSession($channel);
    
        $channel = Channel::find($channel['id']);
        $channel->user_id_connection = $user['id'];
        $channel->save();

        $bodyData = ['Immediate' => true];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Token' => $channel['cha_session_token']
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('Resposta startSession eHost');
        Log::debug($response);
        
        //Traz o QrCode
        $qrCode = self::getQrcode($channel);
        
        //Se o qrcode foi gerado
        if($qrCode['code'] == 200) {
            $eventController->sendQrCode($qrCode['data']['QRCode'], $channel['user_id_connection']);
        }

        return $response;
    }

    public function getQrcode($channel)
    {
        //Gera o QRCode
        $endPoint = self::BASE_URL.'session/qr';

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Token' => $channel['cha_session_token']
        ])
        ->timeout(30)
        //->asForm()
        ->get($endPoint);

        Log::debug('Resposta getQrcode eHost');
        Log::debug($response);

        return $response;
    }

    //Fecha a sessão, retornando o qrCode
    public function closeSession($channel)
    {
        

        self::logoutConnection($channel);

       
    }

    //Desloga da conexão com o WhatsApp
    public function logoutConnection($channel)
    {
        $endPoint = self::BASE_URL.'session/logout';

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Token' => $channel['cha_session_token']
        ])
        ->timeout(5)
        //->asForm()
        ->post($endPoint);

        Log::debug('Resposta logoutConnection API eHost');
        Log::debug($response);

        return $response;
    }



    //Verifica se sessão está conectada
    public function checkConnectionSession($channel)
    {
        try {
            $channel = Channel::find($channel['id']);
        
            $endPoint = self::BASE_URL.'session/status';

            $response = null;

            //Envia os dados
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Token' => $channel['cha_session_token'],
            ])
            ->timeout(30) //Número máximo em segundos aguardando uma resposta da API
            //->asForm()
            ->get($endPoint);

            Log::debug('Status da sessão API eHost');
            Log::debug($response);

            $responseData = [];
            //Se a requisição foi feita com sucesso
            if($response['code'] == 200 && $response['success'] == true) {
                //Se o canal está conectado
                if($response['data']['Connected'] == true) {
                    $responseData['status'] = 'CONNECTED';
                }
                else if($response['data']['Connected'] == false) {
                    $responseData['status'] = 'DISCONNECTED';
                }
                else {
                    $responseData['status'] = 'NO INSTANCE';
                }
            }
            else if($response['code'] == '500' && $response['success'] == false && $response['error'] == 'No session') {
                $responseData['status'] = 'DISCONNECTED';
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

    public function downloadMedia($mediaKey, $directPath, $url, $mimeType, $channelId, $fileEncSHA256, $fileSHA256, $fileLength)
    {
        $channelController = new ChannelController();
        $channel = $channelController->getChannel($channelId);

        Log::debug('$channel[cha_session_token]');
        Log::debug($channel['cha_session_token']);

        $endPoint = self::BASE_URL.'message/downloadimage';

        $bodyData = [
            "url" => $url,
            "directPath" => $directPath,
            "mediaKey" => $mediaKey,
            "Mimetype" => $mimeType,
            "FileEncSHA256" => $fileEncSHA256,
            "FileSHA256" => $fileSHA256,
            "FileLength" => $fileLength,
        ];

        Log::debug('$bodyData');
        Log::debug($bodyData);
        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8',
            'Token' => $channel['cha_session_token'],
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('Resposta downloadMedia API WA');
        Log::debug($response);

        return $response;
    }

    public function webhookApiEHost(Request $callbackApi, $channelId)
    {
        Log::debug('Dados webhook da API eHost');
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

        Log::debug('Id do Canal webhook da eHost');
        Log::debug($channelId);

        //Se for uma mídia, pega os dados
        $fileData = isset($callbackApi['file'])? $callbackApi['file'] : NULL;

        $callbackApi = json_decode($callbackApi['jsonData'], true);

        $apiName = 'api-ehost';
        $statusMessage = null;

        $eventController = new EventController();
        $channelController = new ChannelController();
        $userController = new UserController();
        $contactController = new ContactController();
        $chatController = new ChatController();

        //Se for uma mensagem recebida de um CONTATO e não é uma mensagem de um grupo
        if(($callbackApi['type'] == 'Message' || $callbackApi['type'] == 'messageFromMe') && (isset($callbackApi['event']['Info']['IsGroup']) && $callbackApi['event']['Info']['IsGroup'] == false) ) {
            $payloadMessage = [];
            //Pega o id da mensagem
            $payloadMessage['id'] = $callbackApi['event']['Info']['ID'];
            //Pega se a mensagem foi enviada pelo usuário do sistema via celular (true) ou enviada pelo contato (false)
            $payloadMessage['fromSystemUser'] = $callbackApi['event']['Info']['IsFromMe'];
            
            //Se a mensagem foi enviada pelo contato
            if($callbackApi['event']['Info']['IsFromMe'] == false) {
                $numberPhoneArray = explode('@', $callbackApi['event']['Info']['Chat']);
                //Pega o canal que recebeu a mensagem enviada pelo CONTATO
                $payloadMessage['mesPhoneChannelReceivedMessage'] = isset($numberPhoneArray[0])? $numberPhoneArray[0] : NULL;
            }
            else {
                $numberPhoneArray = explode('@', $callbackApi['event']['Info']['Sender']);
                //Pega o canal usado pelo usuário do sistema para enviar a mensagem
                $payloadMessage['mesPhoneChannelSentMessage'] = isset($numberPhoneArray[0])? $numberPhoneArray[0] : NULL;;
            }

            
            //Se for uma mensagem de resposta a uma mensagem anterior, pega o id da mensagem respondida
            if(isset($callbackApi['event']['Message']['extendedTextMessage']['contextInfo']['stanzaId'])) {
                $payloadMessage['answeredMessageId'] =  $callbackApi['event']['Message']['extendedTextMessage']['contextInfo']['stanzaId'];
            }
            else if(isset($callbackApi['event']['Message']['audioMessage']['contextInfo']['stanzaId'])) {
                $payloadMessage['answeredMessageId'] =  $callbackApi['event']['Message']['audioMessage']['contextInfo']['stanzaId'];
            }
            else if(isset($callbackApi['event']['Message']['imageMessage']['contextInfo']['stanzaId'])) {
                $payloadMessage['answeredMessageId'] =  $callbackApi['event']['Message']['imageMessage']['contextInfo']['stanzaId'];
            }
            else if(isset($callbackApi['event']['Message']['documentMessage']['contextInfo']['stanzaId'])) {
                $payloadMessage['answeredMessageId'] =  $callbackApi['event']['Message']['documentMessage']['contextInfo']['stanzaId'];
            }
            else if(isset($callbackApi['event']['Message']['videoMessage']['contextInfo']['stanzaId'])) {
                $payloadMessage['answeredMessageId'] =  $callbackApi['event']['Message']['videoMessage']['contextInfo']['stanzaId'];
            }
            
            //Caso seja mensagem de texto ou o conteúdo da mensagem ainda não chegou (Aguardando a mensagem)
            if(isset($callbackApi['event']['Message']['conversation']) || isset($callbackApi['event']['Message']['extendedTextMessage'])) {
                $payloadMessage['type'] = 'text';
                //Se a mensagem veio com conteúdo
                /*if($callbackApi['data']['waitingMessage'] == false) {
                    $payloadMessage['payload']['text'] = $callbackApi['data']['content'];
                } //Se a mensagem veio SEM conteúdo
                else {
                    $payloadMessage['payload']['text'] = 'Aguardando o conteúdo da mensagem';
                    $payloadMessage['payload']['waitingMessage'] = true;
                }*/

                $payloadMessage['payload']['text'] = isset($callbackApi['event']['Message']['conversation'])? $callbackApi['event']['Message']['conversation'] : $callbackApi['event']['Message']['extendedTextMessage']['text'];
                
            }
            else if(isset($callbackApi['event']['Message']['audioMessage'])) {
                $payloadMessage['type'] = 'audio';
                $payloadMessage['payload']['contentType'] = $callbackApi['event']['Message']['audioMessage']['mimetype'];
                $payloadMessage['payload']['file'] = $fileData;
            }
            // Se o contato enviou uma imagem
            else if(isset($callbackApi['event']['Message']['imageMessage'])) {
                $payloadMessage['type'] = 'image';
                $payloadMessage['payload']['contentType'] = $callbackApi['event']['Message']['imageMessage']['mimetype'];
                $payloadMessage['payload']['caption'] = isset($callbackApi['event']['Message']['imageMessage']['caption'])? $callbackApi['event']['Message']['imageMessage']['caption'] : NULL;
                $payloadMessage['payload']['file'] = $fileData;
            }
            else if(isset($callbackApi['event']['Message']['documentMessage'])) {
                $payloadMessage['type'] = 'file';
                
                $payloadMessage['payload']['name'] = $callbackApi['event']['Message']['documentMessage']['fileName'];
                $payloadMessage['payload']['contentType'] = $callbackApi['event']['Message']['documentMessage']['mimetype'];
                $payloadMessage['payload']['caption'] = isset($callbackApi['event']['Message']['documentMessage']['caption'])? $callbackApi['event']['Message']['documentMessage']['caption'] : NULL;
                $payloadMessage['payload']['file'] = $fileData;
            }
            else if(isset($callbackApi['event']['Message']['videoMessage'])) {
                $payloadMessage['type'] = 'video';
                $payloadMessage['payload']['contentType'] = $callbackApi['event']['Message']['videoMessage']['mimetype'];
                $payloadMessage['payload']['caption'] = isset($callbackApi['event']['Message']['videoMessage']['caption'])? $callbackApi['event']['Message']['videoMessage']['caption'] : NULL;
                $payloadMessage['payload']['file'] = $fileData;           
            }
            //Se o contato enviou um contato
            else if(isset($callbackApi['event']['Message']['contactMessage'])) {
                $payloadMessage['type'] = 'vcard';
                $payloadMessage['payload']['contactName'] = $callbackApi['event']['Message']['contactMessage']['displayName'];

                $dataDivided = explode('waid=', $callbackApi['event']['Message']['contactMessage']['vcard']);
                $dataDivided = explode(':', $dataDivided[1]);
                //Telefone do contato compartilhado
                $payloadMessage['payload']['contactPhoneNumber'] = $dataDivided[0];
            }
            else if(isset($callbackApi['event']['Message']['stickerMessage'])) {
                $payloadMessage['type'] = 'sticker';

                $payloadMessage['payload']['contentType'] = $callbackApi['event']['Message']['stickerMessage']['mimetype'];
                $payloadMessage['payload']['file'] = $fileData;
            }
            else if(isset($callbackApi['event']['Message']['locationMessage'])) {
                $payloadMessage['type'] = 'location';
                $payloadMessage['payload']['latitude'] = $callbackApi['event']['Message']['locationMessage']['degreesLatitude'];
                $payloadMessage['payload']['longitude'] = $callbackApi['event']['Message']['locationMessage']['degreesLongitude'];
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
            $senderPhoneDivided = explode('@', $callbackApi['event']['Info']['Chat']);
            //Se a mensagem foi enviada pelo app ou whatsapp web, pega o dado do atributo chatName, se não, pega do atributo senderName
            $payloadMessage['sender']['name'] = $callbackApi['event']['Info']['IsFromMe']? $senderPhoneDivided[0] : $callbackApi['event']['Info']['PushName'];
            

            $phoneDivided = explode('@', $callbackApi['event']['Info']['Chat']);

            $phoneContact = $phoneDivided[0];
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
                    $channelController = new ChannelController();
                    $channelData = $channelController->getChannel($channelId);
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
        if($callbackApi['type'] == 'ReadReceipt') {
            //Para cada mensagem
            foreach($callbackApi['event']['MessageIDs'] AS $messageId)
            {
                //Se for um status de uma mensagem (mensagem entregue, enviada, etc.)
                //Se a mensagem foi ENVIADA
                if($callbackApi['state'] == 'Sent') {
                    $statusMessageChatId = 2;
                    $apiMessageId = $messageId;
                } //Se a mensagem foi ENTREGUE
                else if($callbackApi['state'] == 'Delivered') {
                    $statusMessageChatId = 3;
                    $apiMessageId = $messageId;
                }
                else if($callbackApi['state'] == 'Read') {
                    $statusMessageChatId = 5;
                    $apiMessageId = $messageId;
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
        else if($callbackApi['type'] == 'SyncComplete') {
            $channel = $channelController->getChannel($channelId);
            //Envia uma notificação ao usuário comunicando que o canal foi conectado
            $eventController->statusConnection('inChat', null, $channel['user_id_connection']);

        } //Se é um retorno de desconexão de um canal e a desconexão não tenha se dado por Precondition Required
        else if(isset($callbackApi['data']['connection']) && $callbackApi['data']['connection'] == 'close') {
            $channel = $channelController->getChannel($channelId);
            $channel->cha_status = 'I';
            $channel->save();

            //Envia uma notificação ao usuário comunicando que o canal foi conectado
            $eventController->statusConnection('Disconnected', null, $channel['user_id_connection']);
        }

        return response()->json([
            
        ], 200);
    }
}
