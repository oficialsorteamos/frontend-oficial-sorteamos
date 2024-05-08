<?php

namespace App\Http\Controllers\Api\Whatsapp;

use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\Campaign\MailingController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Financial\CostController;
use App\Http\Controllers\Financial\ParameterController;
use App\Http\Controllers\Integration\DialerController;
use App\Http\Controllers\Setting\CustomerController;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Chat\Service;
use App\Models\Management\Channel\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class ZapligueController extends Controller
{
    const BASE_URL = 'https://api.hamako.com.br/v1/';

    //Adiciona uma campanha envio de MESAGEM DE VOZ VIA WHATSAPP
    public function addCampaignApi($campaign)
    {  
        $utilsController = new UtilsController();
        $campaignController = new CampaignController();
        $mailingController = new MailingController();
        $customerController = new CustomerController();
        $customerData = $customerController->getCustomer();

        $endPoint = self::BASE_URL.'campanha?authorization='.env('ZAPLIGUE_TOKEN');

        //Se NÃO É o servidor local
        if(env('URL_SERVER') != 'https://127.0.0.1') {
            $baseUrl = env('URL_SERVER');
        }
        else {
            $baseUrl = env('NGROK_URL');
        }
        //Pega o áudio cadastrado para a campanha
        $audioMessage = $campaignController->getFirstCampaignMessage($campaign->id);

        $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/quickMessages/quickMessage'.$audioMessage['quick_message_id'].'/header/'.$audioMessage['quickMessage']['parameters'][0]['qui_media_name'];
        $data = Http::get($urlMedia);
        //Áudio que vai tocar para o contato inicialmente
        $initialAudioBase64 = $utilsController->convertToBase64($urlMedia, $data->body(), 2);

        //Áudio em resposta a interação POSITIVA
        $filePath = storage_path("app/public/campaign/audios/interactions/positive.mp3");
        $type = pathinfo($filePath, PATHINFO_EXTENSION);
        $data = file_get_contents($filePath);
        $positiveInteractionBase64 = 'data:audio/wav/' . $type . ';base64,' . base64_encode($data);

        //Áudio em resposta a interação POSITIVA
        $filePath = storage_path("app/public/campaign/audios/interactions/negative.mp3");
        $type = pathinfo($filePath, PATHINFO_EXTENSION);
        $data = file_get_contents($filePath);
        $negativeInteractionBase64 = 'data:audio/wav/' . $type . ';base64,' . base64_encode($data);

        //Áudio que é reproduzido quando a API NÃO entende o que o contato falou
        $filePath = storage_path("app/public/campaign/audios/interactions/not-understand.mp3");
        $type = pathinfo($filePath, PATHINFO_EXTENSION);
        $data = file_get_contents($filePath);
        $notUndertandInteractionBase64 = 'data:audio/wav/' . $type . ';base64,' . base64_encode($data);

        //Traz o mailing da campanha
        $mailing = $mailingController->getMailing($campaign->id);
        //Formata o mailing
        $mailingFormatted = self::formatMailing($mailing);

        $bodyData = [
            "label" => $campaign->cam_name.' - '.$customerData[0]->com_name,
            "empresa_midiaId" => "3b1fa7ad-4239-42c0-8abe-d1e061d6631e",
            "conteudoTexto" => "wav",
            "conteudoDestinoCSV" => $mailingFormatted,
            "json" => [
                "interacao" => [
                    "nome" => "Veiculação de Áudio",
                    "respostas" => "",
                    "fileBase64" => $initialAudioBase64,
                    "interacoes" => [
                        [
                            "nome" => "Finalizacao",
                            "respostas" => $audioMessage['quickMessage']['parameters'][0]['qui_positives_responses'],
                            "fileBase64" => $positiveInteractionBase64,
                            "interacoes" => [],
                            "webhook" => $baseUrl.'/api/zapligue/webhook-call-whatsapp/?campanhaId='.$campaign->id.'&acao=sim'
                        ],
                        [
                            "nome" => "Finalizacao2",
                            "respostas" => $audioMessage['quickMessage']['parameters'][0]['qui_negatives_responses'],
                            "fileBase64" => $negativeInteractionBase64,
                            "interacoes" => [],
                            "webhook" => $baseUrl.'/api/zapligue/webhook-call-whatsapp/?campanhaId='.$campaign->id.'&acao=nao'
                        ]
                    ],
                    "webhook" => "https://app.zapligue.com.br/api/webhook"
                ],
                "isInterativo" => true,
                "audioNaoEntendiFileBase64" => $notUndertandInteractionBase64
            ]
        ];

        
        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
        ])
        ->timeout(30)
        //->asForm()
        ->post($endPoint, $bodyData);
        
        Log::debug('Resposta ZapLigue addCampaign');
        Log::debug($response);

        $responseData = [];
        //Caso a campanha tenha sido criada
        if(isset($response['campanhaId'])) {
            $responseData['success'] = true;
            $responseData['campaignIdApi'] = $response['campanhaId'];
            $responseData['statusId'] = self::getCampaignStatusId($response['status']);
        }
        else {
            $responseData['success'] = false;
        }

        return $responseData;
    }

    //Formata o mailing para seguir o padrão da ZapLigue
    public function formatMailing($mailing)
    {   
        $mailingFormatted = '';
        //Para cada contato do mailing
        foreach($mailing AS $key => $contact) {
            //Se for o primeiro contato
            if($key == 0) {
                $mailingFormatted .= $contact['contact']->con_phone;
            }
            else {
                $mailingFormatted .= ','.$contact['contact']->con_phone;
            }
        }

        return $mailingFormatted;
    }

    //Traz o id do status correspondente
    public function getCampaignStatusId($statusId)
    {   //Se o status for Validando Campanha
        if($statusId == 2) {
            //Retorna o status VALIDANDO
            return 7;
        } //Se o status for Campanha Ativa
        else if($statusId == 6 || $statusId == 7) {
            //Retorna o status EM ANDAMENTO
            return 2;
        } // Se o status for Campanha Inválida
        else if($statusId == 9) {
            //Retorna o status  CAMPANHA INVÁLIDA
            return 9;
        } //Se o status for na fila para envio
        else if($statusId == 4 || $statusId == 5) {
            //Retorna o status PREPARANDO ENVIO
            return 8;
        }// Se o status for Campanha Concluída
        else if($statusId == 10) {
            //Retorna o status CAMPANHA FINALIZADA
            return 3;
        }
        else {
            return null;
        }
    }

    //Traz o id do status correspondente
    public function getMailingStatusId($statusId)
    {   //Se o status for Pendente de Envio ou Destinatário Validado || Em Processamento
        if($statusId == 1 || $statusId == 3 || $statusId == 4) {
            //Retorna o status AGUARDANDO ENVIO
            return 1;
        } //Se o status for Mensagem Enviada
        else if($statusId == 5) {
            //Retorna o status EM ANDAMENTO
            return 2;
        } // Se o status for Destinatário Inválido
        else if($statusId == 2) {
            //Retorna o status  CAMPANHA INVÁLIDA
            return 3;
        }
        else {
            return 1;
        }
    }

    //Traz uma campanha específica
    public function getCampaignApi($campaign)
    {
        $endPoint = self::BASE_URL.'campanha/'.$campaign->campaign_id_api.'/?authorization='.env('ZAPLIGUE_TOKEN');
        
        $response = Http::withHeaders([
            'Accept' => '*/*',
        ])
        ->timeout(30)
        //->asForm()
        ->get($endPoint);
        
        Log::debug('getcampaign zapligue');
        Log::debug($response);

        $responseData = [];
        //Caso a campanha tenha sido criada
        if(isset($response['campanhaId'])) {
            $responseData['success'] = true;
            $responseData['statusId'] = self::getCampaignStatusId($response['status']);
        }
        else {
            $responseData['success'] = false;
        }

        return $responseData;
    }

    //Traz os contatos de uma campanha específica
    public function getMailingCampaignApi($campaign)
    {
        $endPoint = self::BASE_URL.'campanha/'.$campaign->campaign_id_api.'/destinatario/?authorization='.env('ZAPLIGUE_TOKEN');
        
        $response = Http::withHeaders([
            'Accept' => '*/*',
        ])
        ->timeout(30)
        //->asForm()
        ->get($endPoint);
        
        Log::debug('getMailingCampaignApi zapligue');
        Log::debug($response);

        $responseData = [];
        //Caso tenha algum contato na campanha
        if(isset($response[0])) {
            $responseData['success'] = true;
            $responseData['data'] = $response;
        }
        else {
            $responseData['success'] = false;
        }

        return $response;
    }
    
    public function webhookCallWhatsappZapligue(Request $request)
    {
        Log::debug('webhookCallWhatsappZapligue request');
        Log::debug($request);
        
        //Se for uma resposta do contato via SMS
        if(isset($request['acao']) && $request['acao'] == 'sim') {
            $chatController = new ChatController();
            $contactController = new ContactController();
            $campaignController = new CampaignController();

            $contactPhoneNumber = $request['destinatario'];
            //Verifica se o contato já existe no sistema
            $contact = $contactController->getContactByPhoneNumber($contactPhoneNumber);

            //Se o contato NÃO existe
            if($contact) {
                //Pega o chat associado ao contato
                $chat = $chatController->getChatsContact($contact->id);
            }
            else {
                //Dados do contato
                $contactData = new Request([
                    'name'   => $contactPhoneNumber,
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
            

            $chatId = isset($chat[0]->id)? $chat[0]->id : $chat['id'];

            //Pega o áudio cadastrado para a campanha
            $audioMessage = $campaignController->getFirstCampaignMessage($request['campanhaId']);

            $messageData = [];
            $messageData['chatId'] = $chatId;
            $messageData['senderId'] = 1; //Robô
            $messageData['typeUserId'] = 3; //Robô
            $messageData['senderId'] = 1; //Robô
            $messageData['campaignId'] = $request['campanhaId']; //Robô
            $messageData['quickMessageId'] = $audioMessage['quick_message_id'];
            $messageData['typeMessageChatId'] = 2; //Áudio
            $messageData['statusMessageChatId'] = 3; //Entregue
            $messageData['mesPrivate'] = 0; //Não Privada

            $chatController->addChatMessage($messageData);


            $apiName = 'unipix';
            
            $payloadMessage = [];
            //$payloadMessage['id'] = null;
            $payloadMessage['type'] = 'text';
            $payloadMessage['payload']['text'] = $request['acao'];

            
            //Traz de forma aleatória um canal associado a uma campanha
            $channelCampaign = $campaignController->randomChannelCampaign($request['campanhaId']);
            //Se existe um canal associado a campanha
            if($channelCampaign) {
                $channelData['id'] = $channelCampaign[0]->channel_id;
            }

            $typeOriginMessageId = 3; //A origem é o LIGAÇÃO VIA WHATSAPP

            $chatController->storeMessage($chatId, 2, $contact['id'], $payloadMessage, null, $apiName, 'false', $channelData, null, null, null, null, $typeOriginMessageId);
        }

        
    }
}
