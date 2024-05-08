<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\EventController;
use App\Models\Management\Channel\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use Auth;
use Exception;

class WppConnectController extends Controller
{
    const BASE_URL = 'http://3.22.101.198:21465/api/';

    public function sendMessage($channel, $destination, $message)
    {   
        try {
            //Log::debug('dados para envio');
            //Log::debug($message);
            //Se o tipo de mensagem for um texto
            if($message->type_message_chat_id == 1) {
                $endPoint = self::BASE_URL.$channel->cha_session_name.'/send-message';
                $messageData = ['phone' => $destination, 'message' => $message->mes_message];      
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
                    //Log::debug('base64');
                    //Log::debug($filePath);
                    $dataBase64 = 'data:audio/' . $type . ';base64,' . base64_encode($data);
                }
                
                //Log::debug('Arquivo em base64');
                //Log::debug($dataBase64);
                $messageData = ['phone' => $destination, 'base64' => $dataBase64];
                    
            }

            //Envia os dados
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Authorization' => 'Bearer '.$channel['cha_session_token'],
                'Content-Type' => 'application/json; charset=utf-8'
            ])
            ->timeout(30)
            //->asForm()
            ->post($endPoint, $messageData);

            Log::debug("Resposta WppConnect");
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

            $responseData = [];
            if($response["status"] == "success") { 
                $responseData['status'] = 'success';
                $responseData['message']['id'] =  isset($response['response'][0]['id'])? $response['response'][0]['id'] : $response['response'][0]['to']['_serialized'];
            }
            else if($response['status'] == 'Disconnected') {
                $responseData['status'] = 'Disconnected';
            }
            else if($response['status'] == 'Connected') {
                $responseData['status'] = 'Connected';
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

    //Inicia a sessão, retornando o qrCode
    public function startSession($channel, $user)
    {   
        //Fecha qualquer sessão que possa estar aberta para o canal na API
        self::closeSession($channel);

        Log::debug('Start na conexão');
        $channel = Channel::find($channel['id']);
        $channel->cha_session_token = null;
        $channel->user_id_connection = $user['id'];
        $channel->save();

        $messageData = ['webhook' => env('WEBHOOK_URL_API_MESSAGE')];
        
        $channel = self::generateToken($channel);
        
        $endPoint = self::BASE_URL.$channel['cha_session_name'].'/start-session';

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '.$channel['cha_session_token'],
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(5)
        //->asForm()
        ->post($endPoint, $messageData);

        Log::debug('Resposta sessão');
        Log::debug($response);

        return $response;
    }

    //Fecha a sessão, retornando o qrCode
    public function closeSession($channel)
    {
        $endPoint = self::BASE_URL.$channel['cha_session_name'].'/logout-session';

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Authorization' => 'Bearer '.$channel['cha_session_token'],
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(5)
        //->asForm()
        ->post($endPoint);

        Log::debug('Resposta sessão fechada');
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

            Log::debug('Status da sessão Wppconnect');
            Log::debug($response);

            $responseData = [];
            if(isset($response['message'])) {
                //Se o canal está conectado
                if($response['message'] == 'Connected') {
                    $responseData['status'] = 'CONNECTED';
                }
                else if($response['message'] == 'Disconnected') {
                    $responseData['status'] = 'DISCONNECTED';
                }
            }

            return $responseData;

        }//Se o canal não respondeu
        catch(Exception $e) {

            Log::debug('exception lançada');
            Log::debug($e);

            return null;
        }
    }
}
