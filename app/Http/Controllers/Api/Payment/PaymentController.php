<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\Payment\AsaasController;

class PaymentController extends Controller
{
    private $asaasController;

    public function __construct()
    {
        $this->asaasController = new AsaasController();
    }

    //Cria um cliente para que as cobranças possam ser geradas
    public function generateInvoice($invoiceData)
    {   
        $response = $this->asaasController->createCharge($invoiceData);

        return $response;
    }

    //Cria um cliente para que as cobranças possam ser geradas
    public function generateInvoicePartner($invoiceData)
    {   
        $response = $this->asaasController->createChargePartner($invoiceData);

        return $response;
    }

    //Cria um Qrcode estático para pagamento
    public function getPixQrcode($chargeIdApi)
    {   
        $response = $this->asaasController->getPixQrcode($chargeIdApi);

        return $response;
    }

    public function insertCredit($creditData)
    {
        $response = $this->asaasController->insertCredit($creditData);

        return $response;
    }

    public function getStatusPaymentId($statusPayment)
    {
        $response = $this->asaasController->getStatusPaymentId($statusPayment);

        return $response;
    }

    public function generateTokenCard($cardId, $cardCcv)
    {
        $response = $this->asaasController->generateTokenCard($cardId, $cardCcv);

        return $response;
    }
}
