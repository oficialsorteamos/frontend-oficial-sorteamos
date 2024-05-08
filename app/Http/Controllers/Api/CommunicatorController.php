<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\WppConnectController;
use App\Http\Controllers\Api\ZApiController;
use App\Http\Controllers\Api\Dialog360Controller;
use App\Http\Controllers\Api\CloudApiWhatsAppController;
use App\Http\Controllers\Api\Whatsapp\ZapligueController;
use App\Models\System\ApiCommunication;

class CommunicatorController extends Controller
{
    private $wppConnectController;
    private $dialog360Controller;
    private $zApiController;
    private $cloudApiWhatsAppController;
    private $gupshupController;

    public function __construct()
    {
        $this->wppConnectController = new WppConnectController();
        $this->zApiController = new ZApiController();
        $this->dialog360Controller = new Dialog360Controller();
        $this->cloudApiWhatsAppController = new CloudApiWhatsAppController();
        $this->gupshupController = new GupshupController();
    }

    public function sendMessageApi($channel, $destination, $message)
    {  
        //Log::debug('dados canal sendMessageApi');
        //Log::debug($channel);
        //Se o envio for realizado pela Wppconnect
        if($channel->cha_api_official == false) {
            if($channel->api_communication_id == 2) {
                $response = $this->wppConnectController->sendMessage($channel, $destination, $message);
            } //Se for enviado pela Z-API
            else if($channel->api_communication_id == 5) {
                //Se não for uma mensagem rápida com parámetros
                if(!$message->quickMessageWithParameters) {
                    $response = $this->zApiController->sendMessage($channel, $destination, $message);
                } //Se for uma mensagem rápida com parâmetros
                else {
                    $response = $this->zApiController->sendQuickMessageWithParameters($channel, $destination, $message);
                }
                
            } //Se for enviado pela API ZAP
            else if($channel->api_communication_id == 6) {
                $apiZapcontroller = new ApiZapController();
                //Se não for uma mensagem rápida com parámetros
                if(!$message->quickMessageWithParameters) {
                    $response = $apiZapcontroller->sendMessage($channel, $destination, $message);
                } //Se for uma mensagem rápida com parâmetros
                else {
                    $response = $apiZapcontroller->sendQuickMessageWithParameters($channel, $destination, $message);
                }
                
            } //Se for enviado pela API EHOST
            else if($channel->api_communication_id == 8) {
                $apiEHostController = new ApiEHostController();
                //Se não for uma mensagem rápida com parámetros
                if(!$message->quickMessageWithParameters) {
                    $response = $apiEHostController->sendMessage($channel, $destination, $message);
                } //Se for uma mensagem rápida com parâmetros
                else {
                    $response = $apiEHostController->sendQuickMessageWithParameters($channel, $destination, $message);
                }
                
            }//Se for enviado pela API WA
            else if($channel->api_communication_id == 7) {
                $apiWacontroller = new ApiWaController();
                //Se não for uma mensagem rápida com parámetros
                if(!$message->quickMessageWithParameters) {
                    $response = $apiWacontroller->sendMessage($channel, $destination, $message);
                } //Se for uma mensagem rápida com parâmetros
                else {
                    $response = $apiWacontroller->sendQuickMessageWithParameters($channel, $destination, $message);
                }
                
            }
            

            return $response;
        }
        //Se for pela API OFICIAL
        else {
            Log::debug('chamou a parte da API OFICIAL');
            Log::debug($message);
            //Se NÃO for uma mensagem template
            //if(!(isset($message->template_id) && !$message->template_id) || (isset($message['template_id']) && !$message['template_id'])) {
            if(!$message['template_id']) {
                if($channel->api_communication_id == 1) {
                    $response = $this->gupshupController->sendMessage($channel, $destination, $message); 
                }
                else if($channel->api_communication_id == 3) {
                    $response = $this->dialog360Controller->sendMessage($channel, $destination, $message);
                }
                else if($channel->api_communication_id == 4) {
                    if(!$message->quickMessageWithParameters) {
                        $response = $this->cloudApiWhatsAppController->sendMessage($channel, $destination, $message);
                    } //Se for uma mensagem rápida com parâmetros
                    else {
                        $response = $this->cloudApiWhatsAppController->sendQuickMessageWithParameters($channel, $destination, $message);
                    }
                }
            } //Se for uma mensagem template
            else {
                if($channel->api_communication_id == 1) {
                    $response = $this->gupshupController->sendTemplateMessage($channel, $destination, $message);
                }
                else if($channel->api_communication_id == 3) {
                    $response = $this->dialog360Controller->sendTemplateMessage($channel, $destination, $message);
                }
                else if($channel->api_communication_id == 4) {
                    $response = $this->cloudApiWhatsAppController->sendTemplateMessage($channel, $destination, $message);
                }
            }
            

            return $response;
        }
        
    }

    //inicia uma sessão com a API, gerando o qrCode
    public function startSession(Request $request)
    {
        //Log::debug('dados sessão');
        //Log::debug($request->params['channelData']['api_communication_id']);
        //Se a API associada ao canal for a WppConnect
        if($request->params['channelData']['api_communication_id'] == 2) {
            $response = $this->wppConnectController->startSession($request->params['channelData'], $request->params['userData']);
        }
        else if($request->params['channelData']['api_communication_id'] == 5) {
            $response = $this->zApiController->startSession($request->params['channelData'], $request->params['userData']);
        } //Se for a API ZAP
        else if($request->params['channelData']['api_communication_id'] == 6)
        {
            $apiZapcontroller = new ApiZapController();
            $response = $apiZapcontroller->startSession($request->params['channelData'], $request->params['userData']);
        } //Se for a API eHost
        else if($request->params['channelData']['api_communication_id'] == 8)
        {
            $apiEHostController = new ApiEHostController();
            $response = $apiEHostController->startSession($request->params['channelData'], $request->params['userData']);
        } //Se for a API-WA
        else if($request->params['channelData']['api_communication_id'] == 7)
        {
            $apiWacontroller = new ApiWaController();
            $response = $apiWacontroller->startSession($request->params['channelData'], $request->params['userData']);
        }

        return response()->json(
            $response
        , 200);
    }

    //Fecha a sessão com o Whatsapp
    public function closeSession(Request $request)
    {
        //Log::debug('dados canal');
        //Log::debug($request);
        //Se a API associada ao canal for a WppConnect
        if($request->channelData['api_communication_id'] == 2) {
            $response = $this->wppConnectController->closeSession($request->channelData);
        } //Se for a Z-API
        else if($request->channelData['api_communication_id'] == 5) {
            $response = $this->zApiController->closeSession($request->channelData);
        } //Se for a API ZAP
        else if($request->channelData['api_communication_id'] == 6) {
            $apiZapcontroller = new ApiZapController();
            $response = $apiZapcontroller->closeSession($request->channelData);
        } // Se for a eHost
        else if($request->channelData['api_communication_id'] == 8) {
            $apiEHostController = new ApiEHostController();
            $response = $apiEHostController->closeSession($request->channelData);
        } //Se for a API WA
        else if($request->channelData['api_communication_id'] == 7) {
            $apiWacontroller = new ApiWaController();
            $response = $apiWacontroller->closeSession($request->channelData);
        }

        return response()->json(
            $response
        , 200);
    }

    public function disconnectInstance($channel)
    {
        if($channel['api_communication_id'] == 8) {
            $apiEHostController = new ApiEHostController();
            $response = $apiEHostController->disconnectInstance($channel['ins_token']);
        }
    }

    //Checa o status do canal
    public function checkConnectionSession($channel)
    {
        //Se o envio for realizado pela API NÃO OFICIAL
        if($channel->cha_api_official == false) {
            if($channel->api_communication_id == 2) {
                $response = $this->wppConnectController->checkConnectionSession($channel);
            } //Se for a Z-API
            else if($channel->api_communication_id == 5) {
                $response = $this->zApiController->checkConnectionSession($channel);
            }//Se for a API ZAP
            else if($channel->api_communication_id == 6) {
                $apiZapcontroller = new ApiZapController();
                $response = $apiZapcontroller->checkConnectionSession($channel);
            } //Se for a API eHost
            else if($channel->api_communication_id == 8) {
                $apiEHostController = new ApiEHostController();
                $response = $apiEHostController->checkConnectionSession($channel);
            }//Se for a API-WA
            else if($channel->api_communication_id == 7) {
                $apiWacontroller = new ApiWaController();
                $response = $apiWacontroller->checkConnectionSession($channel);
            }
            return $response;
        }
        //Se for pela API OFICIAL
        else {

        }
    }

    //Limpa a fila de mensagens
    public function clearQueue($channel)
    {
        //Se o envio for realizado pela API NÃO OFICIAL
        if($channel->cha_api_official == false) {
            if($channel->api_communication_id == 2) {
                $response = $this->wppConnectController->clearQueue($channel);
            }
            else if($channel->api_communication_id == 5) {
                $response = $this->zApiController->clearQueue($channel);
            }
            return $response;
        }
        //Se for pela API OFICIAL
        else {

        }
    }

    //Atualiza o nome de uma instância
    public function updateInstanceName($channel)
    {
        //Se o envio for realizado pela API NÃO OFICIAL
        if($channel->cha_api_official == false) {
            if($channel->api_communication_id == 5) {
                $response = $this->zApiController->updateInstanceName($channel);
            }
            return $response;
        }
        //Se for pela API OFICIAL
        else {

        }
    }

    //Checa o status do canal
    public function setWebhook($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 1) {
                $response = $this->gupshupController->setWebhook($channel);
            }
            else if($channel->api_communication_id == 3) {
                $response = $this->dialog360Controller->setWebhook($channel);
            } 

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {
            //Se for a Z-API
            if($channel->api_communication_id == 5) {
                $response = $this->zApiController->setWebhook($channel);
            } //Se for a API ZAP
            else if($channel->api_communication_id == 6) {
                $apiZapcontroller = new ApiZapController();
                $response = $apiZapcontroller->setWebhook($channel);
            } 
            //Se for a API eHost
            else if($channel->api_communication_id == 8) {
                $apiEHostController = new ApiEHostController();
                $response = $apiEHostController->setWebhook($channel);
            }
            //Se for a API WA
            else if($channel->api_communication_id == 7) {
                $apiWacontroller = new ApiWaController();
                $response = $apiWacontroller->setWebhook($channel);
            }
        }
    }

    //Verifica se um número de telefone existe ou possui WhatsApp associado
    public function phoneExists($channel, $phoneNumberVerification)
    {
        //Se for a Z-API
        if($channel->api_communication_id == 5) {
            $response = $this->zApiController->phoneExists($channel, $phoneNumberVerification);
        }

        return $response;
    }

    //Cria uma instância
    public function createInstance($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            
        }
        //Se for pela API NÃO OFICIAL
        else {
            //Se for a Z-API
            if($channel->api_communication_id == 5) {
                $response = $this->zApiController->createInstance($channel);
            } //Se for APIZAP
            else if($channel->api_communication_id == 6) {
                $apiZapcontroller = new ApiZapController();
                $response = $apiZapcontroller->createInstance($channel);
            } //Se for a eHost
            else if($channel->api_communication_id == 8) {
                $apiEHostController = new ApiEHostController();
                $response = $apiEHostController->createInstance($channel);
            }

            return $response;
        }
    }

    //Assina uma instância
    public function subscriptionInstance($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            
        }
        //Se for pela API NÃO OFICIAL
        else {
            //Se for a Z-API
            if($channel->api_communication_id == 5) {
                $response = $this->zApiController->subscriptionInstance($channel);
            }

            return $response;
        }
    }

    //Assina uma instância
    public function cancelInstance($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel['cha_api_official'] == true) {
            
        }
        //Se for pela API NÃO OFICIAL
        else {
            //Se for a Z-API
            if($channel['api_communication_id'] == 5) {
                $response = $this->zApiController->cancelInstance($channel);
            } //Se for API ZAP
            else if($channel['api_communication_id'] == 6) {
                $apiZapcontroller = new ApiZapController();
                $response = $apiZapcontroller->cancelInstance($channel);
            } //Se for a API eHost
            else if($channel['api_communication_id'] == 8) {
                $apiEHostController = new ApiEHostController();
                $response = $apiEHostController->cancelInstance($channel);
            }

            return $response;
        }
    }

    //Baixa ou obtém a URL de uma mídia
    public function getMedia($channel, $mediaId)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 3) {
                $response = $this->dialog360Controller->getMedia($channel, $mediaId);
            }
            else if($channel->api_communication_id == 4) {
                $response = $this->cloudApiWhatsAppController->getMedia($channel, $mediaId);
            }
            

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    public function downloadMedia($channel, $mediaUrl)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 3) {
                $response = $this->dialog360Controller->getMedia($channel, $mediaUrl);
                //$response = $this->dialog360Controller->getMedia($channel, $mediaId);
            }
            else if($channel->api_communication_id == 4) {
                $response = $this->cloudApiWhatsAppController->downloadMedia($channel, $mediaUrl);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Baixa uma mídia
    public function deleteMedia($channel, $mediaId)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 3) {
                $response = $this->dialog360Controller->deleteMedia($channel, $mediaId);
            }
            

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Checa o status do canal
    public function checkHealthChannel($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 3) {
                $response = $this->dialog360Controller->checkHealthChannel($channel);
            }
            
            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Checa o status do canal
    public function createTemplate($templateData)
    {
        //Se o envio for realizado pela API OFICIAL
        if($templateData['channel']['cha_api_official'] == true) {
            if($templateData['channel']['api_communication_id'] == 1) {
                $response = $this->gupshupController->createTemplate($templateData);
            }
            else if($templateData['channel']['api_communication_id'] == 3) {
                $response = $this->dialog360Controller->createTemplate($templateData);
            }
            else if($templateData['channel']['api_communication_id'] == 4) {
                $response = $this->cloudApiWhatsAppController->createTemplate($templateData);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Checa o status do canal
    public function listTemplates($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 3) {
                $response = $this->dialog360Controller->listTemplates($channel);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Checa o status do canal
    public function removeTemplate($channel, $templateName)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 1) {
                $response = $this->gupshupController->removeTemplate($channel, $templateName);
            }
            else if($channel->api_communication_id == 3) {
                $response = $this->dialog360Controller->removeTemplate($channel, $templateName);
            }
            else if($channel->api_communication_id == 4) {
                $response = $this->cloudApiWhatsAppController->removeTemplate($channel, $templateName);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Traz informações do canal cadastrado no WhatsApp
    public function getInfoPhoneNumber($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 4) {
                $response = $this->cloudApiWhatsAppController->getInfoPhoneNumber($channel);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Traz informações da Waba cadastrado no WhatsApp
    public function getWabaInfo($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 4) {
                $response = $this->cloudApiWhatsAppController->getWabaInfo($channel);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Habilita a verificação em duas etapas, setando o PIN
    public function setTwoStepVerification($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 4) {
                $response = $this->cloudApiWhatsAppController->setTwoStepVerification($channel);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Registra um telefone no WhatsApp (em caso de um novo número ou da alteração do nome de exibição de um número)
    public function registerPhone($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 4) {
                $response = $this->cloudApiWhatsAppController->registerPhone($channel);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Faz uma chamada para migração de um número de telefone
    public function migratePhone($channel)
    {
        //Se o envio for realizado pela API OFICIAL
        if($channel->cha_api_official == true) {
            if($channel->api_communication_id == 4) {
                $response = $this->cloudApiWhatsAppController->migratePhone($channel);
            }

            return $response;
        }
        //Se for pela API NÃO OFICIAL
        else {

        }
    }

    //Adiciona uma campanha na API
    public function addCampaignApi($campaign)
    {   //Se for uma campanha de disparo de ligação via WhatsApp
        if($campaign->campaign_type_id == 4) {
            $zapLigueController = new ZapligueController();
            $response = $zapLigueController->addCampaignApi($campaign);
        }

        return $response;
    }

    //Adiciona uma campanha na API
    public function getCampaignApi($campaign)
    {   //Se for uma campanha de disparo de ligação via WhatsApp
        if($campaign->campaign_type_id == 4) {
            $zapLigueController = new ZapligueController();
            $response = $zapLigueController->getCampaignApi($campaign);
        }

        return $response;
    }

    //Traz o mailing da campanha na API
    public function getMailingCampaignApi($campaign)
    {   //Se for uma campanha de disparo de ligação via WhatsApp
        if($campaign->campaign_type_id == 4) {
            $zapLigueController = new ZapligueController();
            $response = $zapLigueController->getMailingCampaignApi($campaign);
        }

        return $response;
    }

    //Pega o status correspondente de um mailing
    public function getMailingStatusId($statusId)
    {   
        $zapLigueController = new ZapligueController();
        $response = $zapLigueController->getMailingStatusId($statusId);
        

        return $response;
    }

    //Traz uma API por ser oficial ou não
    public function getApisByOfficial($official)
    {
        $apis = ApiCommunication::where('api_official', $official)
                                ->where('api_status', 'A')
                                ->get();

        return $apis;
    }

    //Traz a API principal, filtrando se a mesma é oficial ou não
    public function getMainApiByOfficial($official)
    {
        $mainApi = ApiCommunication::where('api_official', $official)
                                    ->where('api_main', 1)
                                    ->where('api_status', 'A')
                                    ->first();
        return $mainApi;
    }
    
}
