<?php

namespace App\Http\Controllers\Api\Sms;

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
use App\Models\Chat\Service;
use App\Models\Management\Channel\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class UnipixController extends Controller
{
    const BASE_URL = 'https://api-sms-cliente.unipix.com.br/';
    //Envia o SMS
    public function sendMessage($destination, $message, $mailingId)
    {
        $endPoint = self::BASE_URL.'v2/api/campanha/simples';

        $customerController = new CustomerController();
        $customerData = $customerController->getCustomer();

        //32 - short; 34 - fast
        $bodyData = [
            "centroCustoId"=> 1348,
            "envios"=> [
              [
                  "mensagemNumero" => $message,
                  "numero" => $destination,
                  "smsClienteId" => $mailingId
              ]
            ],
            "mensagemCampanha" => "",
            "nome" => $customerData[0]->com_name,
            "produtoId" => 32,
            "telefones" => "",
            "urlCallbackEntrega" => env('WEBHOOK_URL_UNIPIX'),
            "urlCallbackResposta" => env('WEBHOOK_URL_UNIPIX')
        ];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => '*/*',
        ])
        ->withBasicAuth(env('UNIPIX_USER'), env('UNIPIX_PASSWORD'))
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $bodyData);
        
        Log::debug('Resposta Unipix SMS API');
        Log::debug($response);

        $responseData = [];
        
        //Se a mensagem foi ENVIADA
        if(isset($response['smsEnvios'][0]['smsId'])) {
            $responseData['status'] = 'success';
            $responseData['message']['id'] = $response['smsEnvios'][0]['smsId'];
        } //Se houve FALHA no envio
        else if($response['status'] == 400) {
            $responseData['status'] = 'failure';
        }

        return $responseData;
    }


    
    public function webhookUnipix(Request $request)
    {
        Log::debug('webhookUnipix request');
        Log::debug($request);

        $mailingController = new MailingController();

        //Se for um retorno do status de ENTREGA DE UM SMS
        if($request['status']) {
            //Se o SMS foi ENTREGUE
            if($request['status'] ='entregue no aparelho') {
                //Atualiza o status para ENTREGUE
                $mailingController->updateMailingStatus($request['smsClienteId'], 6);
            }
        }
        //Se for uma resposta do contato via SMS
        else if(isset($request['resposta'])) {
            $chatController = new ChatController();
            $contactController = new ContactController();
            $campaignController = new CampaignController();
            $costController = new CostController();
            $parameterController = new ParameterController();


            $chargeCampaign = $parameterController->getParameterByType(11);
            
            //Se a cobrança para retorno via SMS estiver habilitada
            if($chargeCampaign->par_value == '1') {
                //Registra a cobrança por retorno de mensagem via SMS
                $costData = new Request([
                    'typeCostId'   => 3, //Custo de retorno de mensagem pelo contato via SMS
                    'mailingId' => $request['smsClienteId'],
                ]);

                $costController->store($costData);
            }
            

            $contactPhoneNumber = $request['numero'];
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

            $chatId = isset($chat[0]->id)? $chat[0]->id : $chat['id'];

            $apiName = 'unipix';
            
            $payloadMessage = [];
            $payloadMessage['id'] = $request['smsId'].rand(100, 99999);
            $payloadMessage['type'] = 'text';
            $payloadMessage['payload']['text'] = $request['resposta'];

            //Mensagem de campanha enviada via SMS para o contato
            $messageSms = $chatController->getMessageById($request['smsId']);

            Log::debug('$messageSms');
            Log::debug($messageSms);
            //Traz de forma aleatória um canal associado a uma campanha
            $channelCampaign = $campaignController->randomChannelCampaign($messageSms['campaign_id']);
            //Se existe um canal associado a campanha
            if($channelCampaign) {
                $channelData['id'] = $channelCampaign[0]->channel_id;
            }

            $typeOriginMessageId = 2; //A origem é o SMS

            $chatController->storeMessage($chatId, 2, $contact['id'], $payloadMessage, null, $apiName, 'false', $channelData, null, null, null, null, $typeOriginMessageId);
        }
    }
}
