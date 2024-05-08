<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\Sms\UnipixController;

class SmsController extends Controller
{
    private $wppConnectController;

    public function __construct()
    {
        $this->wppConnectController = new UnipixController();
    }

    //Envia o SMS
    public function sendMessageApi($destination, $message, $mailingId)
    {  
        $response = $this->wppConnectController->sendMessage($destination, $message, $mailingId);
            
        return $response;
    }
    
    /*
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
    }*/
}
