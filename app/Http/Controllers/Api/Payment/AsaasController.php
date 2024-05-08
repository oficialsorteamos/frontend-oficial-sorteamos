<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Administration\PartnerController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Financial\CardController;
use App\Http\Controllers\Financial\InvoiceController;
use App\Http\Controllers\Financial\ParameterController;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Setting\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

class AsaasController extends Controller
{
    //const BASE_URL = 'https://sandbox.asaas.com/';
    
    //Cria um cliente para que as cobranças possam ser geradas
    public function createCustomer()
    {   
        $endPoint = env('ASAAS_BASE_URL').'api/v3/customers';
        $customerController = new CustomerController();
        $customerData = $customerController->getCustomer();
        
        $bodyData = [
            "name" => $customerData[0]->com_name,
            "email" => $customerData[0]->com_email,
            "phone" => $customerData[0]->com_phone,
            "mobilePhone" => $customerData[0]->com_mobile_phone,
            "cpfCnpj" => $customerData[0]->com_cnpj? $customerData[0]->com_cnpj : $customerData[0]->com_cpf,
            "postalCode" => $customerData[0]->com_postal_code,
            "address" => $customerData[0]->com_address,
            "addressNumber" => $customerData[0]->com_address_number,
            "complement" => $customerData[0]->com_complement,
            "province" => $customerData[0]->com_province,
            "externalReference" => $customerData[0]->id,
            "notificationDisabled" => false,
            "additionalEmails" => "",
            "municipalInscription" => "",
            "stateInscription" => "",
            "observations" => ""
        ];

        //Envia os dados
        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('response ASAAS');
        Log::debug($response);

        return $response;   
    }

    //Cria um cliente para que as cobranças possam ser geradas
    public function createCustomerPartner($partner)
    {   
        $endPoint = env('ASAAS_BASE_URL').'api/v3/customers';
        
        $bodyData = [
            "name" => $partner['par_corporate_name'].' - Parceiro',
            "email" => $partner['par_finance_email'],
            "phone" => $partner['par_finance_phone'],
            "mobilePhone" => $partner['par_finance_phone'],
            "cpfCnpj" => $partner['par_cnpj']? $partner['par_cnpj'] : $partner['par_cpf'],
            "postalCode" => $partner['par_postal_code'],
            "address" => $partner['par_address'],
            "addressNumber" => $partner['par_address_number'],
            "complement" => $partner['par_complement'],
            "province" => $partner['par_province'],
            "externalReference" => $partner['id'],
            "notificationDisabled" => false,
            "additionalEmails" => "",
            "municipalInscription" => "",
            "stateInscription" => "",
            "observations" => ""
        ];

        //Envia os dados
        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('response ASAAS');
        Log::debug($response);

        return $response;   
    }

    //Cria uma cobrança (boleto,cartão, etc)
    public function createCharge($invoiceData)
    {
        $endPoint = env('ASAAS_BASE_URL').'api/v3/payments';
        $parameterController = new ParameterController();
        $customerCreated = null;

        $customerId =  $parameterController->getParameterByType(1);

        //Se o cliente ainda não é cadastrado na ASAAS 
        if(!$customerId) {
            //Cria um cliente na ASAAS
            $customerCreated = self::createCustomer();

            //Salva o id do cliente
            $requestCustomerCreate = new Request([
                'type_parameter_id'   => 1,
                'par_value' => $customerCreated['id'],
            ]);
            $parameterController->store($requestCustomerCreate);
        }
        
        $bodyData = [
            "customer" => isset($customerId->par_value)? $customerId->par_value : $customerCreated['id'],
            "billingType" => "BOLETO",
            "dueDate" => $invoiceData['invoice_due'],
            "value" => $invoiceData['invoice_total_value'],
            "description" => "Fatura mensal para uso do ChatX. Período: ".$invoiceData['invoice_opening'].' à '.$invoiceData['invoice_closing'],
            "externalReference" => env('URL_SERVER'),
            /*"discount" => [
                "value" => 10.00,
                "dueDateLimitDays" => 0
            ]
            ,
            "fine" => [
                "value" => 1.00
            ],
            "interest" => [
                "value" => 2.00,
            ],*/ 
            "postalService" => false
        ];

        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('response CREATE CHARGE');
        Log::debug($response);

        return $response;
    }

    //Cria uma cobrança para um PARCEIRO (boleto,cartão, etc)
    public function createChargePartner($invoiceData)
    {
        $endPoint = env('ASAAS_BASE_URL').'api/v3/payments';
        $partnerController = new PartnerController();
        $customerCreated = null;

        //Se o cliente ainda não é cadastrado na ASAAS 
        if(!$invoiceData['partner']['payment_api_customer_id']) {
            //Cria um cliente na ASAAS
            $customerCreated = self::createCustomerPartner($invoiceData['partner']);

            //Salva o id do cliente
            $partnerController->updatePaymentApiCustomerId($invoiceData['partner']['id'], $customerCreated['id']);
        }
        
        $bodyData = [
            "customer" => $invoiceData['partner']['payment_api_customer_id']? $invoiceData['partner']['payment_api_customer_id'] : $customerCreated['id'],
            "billingType" => "BOLETO",
            "dueDate" => $invoiceData['invoice_due'],
            "value" => $invoiceData['invoice_total_value'],
            "description" => "Fatura mensal para concessão do sistema ChatX via parceria. Período: ".$invoiceData['invoice_opening'].' à '.$invoiceData['invoice_closing'],
            "externalReference" => env('URL_SERVER'),
            /*"discount" => [
                "value" => 10.00,
                "dueDateLimitDays" => 0
            ]
            ,
            "fine" => [
                "value" => 1.00
            ],
            "interest" => [
                "value" => 2.00,
            ],*/ 
            "postalService" => false
        ];

        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('response CREATE CHARGE PARTNER');
        Log::debug($response);

        return $response;
    }

    public function getPixQrcode($chargeIdApi)
    {
        $endPoint = env('ASAAS_BASE_URL').'api/v3/payments/'.$chargeIdApi.'/pixQrCode';

        //self::createPixKey();

        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->get($endPoint);

        Log::debug('reponse pixQrcodeStatic');
        Log::debug($response);

        return $response;
    }

    //Cria uma chave PIX aleatória
    public function createPixKey()
    {
        $endPoint = env('ASAAS_BASE_URL').'api/v3/pix/addressKeys';

        $bodyData = [
            "type"=> "EVP"
        ];

        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('reponse createPixKey');
        Log::debug($response);

    }

    //Realiza a tokenização do cartão de crédito
    public function generateTokenCard($cardId, $cardCcv)
    {
        $endPoint = env('ASAAS_BASE_URL').'api/v3/creditCard/tokenize';

        $parameterController = new ParameterController();
        $cardController = new CardController();

        //Traz o id da empresa na ASAAS
        $customer =  $parameterController->getParameterByType(1);
        $card = $cardController->getCard($cardId);
        
        $bodyData = [
            "customer" => $customer->par_value,
            "creditCard" => [
              "holderName" => $card['car_holder_name'],
              "number" => $card['car_number'],
              "expiryMonth" => $card['car_due_month'],
              "expiryYear" => $card['car_due_year'],
              "ccv" => $cardCcv
            ],
            "creditCardHolderInfo" => [
              "name" => $card['holderInfo']['car_name'],
              "email" => $card['holderInfo']['car_email'],
              "cpfCnpj" => $card['holderInfo']['car_cnpj']? $card['holderInfo']['car_cnpj'] : $card['holderInfo']['car_cpf'],
              "postalCode" => $card['holderInfo']['car_postal_code'],
              "addressNumber" => $card['holderInfo']['car_address_number'],
              "addressComplement" => null,
              "phone" => $card['holderInfo']['car_phone'],
              "mobilePhone" => $card['holderInfo']['car_phone']
            ]
        ];

        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(50)
        //->asForm()
        ->post($endPoint, $bodyData);

        //Log::debug('reponse generateTokenCard');
        //Log::debug($response);

        return $response;
    }

    //Webhook para recebimento de atualizações de cobranças
    public function webhookCharges(Request $response)
    {
        $invoiceController = new InvoiceController();
        $channelController = new ChannelController();
        $partnerController = new PartnerController();

        Log::debug('response webhookCharges');
        Log::debug($response);
        //Se o status do pagamento é RECEBIDO ou CONFIRMADO
        if($response['payment']['status'] == 'RECEIVED' || $response['payment']['status'] == 'CONFIRMED') {
            //Atualiza a fatura para PAGO
            $invoiceController->updateStatusPaymentChargeApiId($response['payment']['id'], 2, $response['payment']['billingType']);
            $channelController->updateStatusChannelPaymentChargeApiId($response['payment']['id'], 2);
            //Se a atualização for do status da fatura de um parceiro WHITE LABEL
            $partnerController->updateStatusInvoicePartnerPaymentChargeApiId($response['payment']['id'], 2);

        } //Caso a fatura esteja aguardando pagamento
        else if($response['payment']['status'] == 'PENDING') {
            //Atualiza a fatura para AGUARDANDO PAGAMENTO
            $invoiceController->updateStatusPaymentChargeApiId($response['payment']['id'], 1, $response['payment']['billingType']);
        }
        else if($response['payment']['status'] == 'OVERDUE') {
            //Atualiza a fatura para VENCIDA
            $invoiceController->updateStatusPaymentChargeApiId($response['payment']['id'], 4, $response['payment']['billingType']);
        }
        else if($response['payment']['status'] == 'REFUNDED') {
            //Atualiza a fatura para ESTORNADO
            $invoiceController->updateStatusPaymentChargeApiId($response['payment']['id'], 3, $response['payment']['billingType']);
        }
        else if($response['payment']['status'] == 'AWAITING_RISK_ANALYSIS') {
            //Atualiza a fatura para ESTORNADO
            $invoiceController->updateStatusPaymentChargeApiId($response['payment']['id'], 5, $response['payment']['billingType']);
        }
    }

    //Insere créditos na plataforma
    public function insertCredit($creditData)
    {
        $parameterController = new ParameterController();
        $customerCreated = null;

        //Traz o id da empresa na ASAAS
        $customer =  $parameterController->getParameterByType(1);

        //Se o cliente ainda não é cadastrado na ASAAS 
        if(!$customer) {
            //Cria um cliente na ASAAS
            $customerCreated = self::createCustomer();

            //Salva o id do cliente
            $requestCustomerCreate = new Request([
                'type_parameter_id'   => 1,
                'par_value' => $customerCreated['id'],
            ]);
            $parameterController->store($requestCustomerCreate);
        }
        $customerId = isset($customer->par_value)? $customer->par_value : $customerCreated['id'];

        Log::debug('customerId');
        Log::debug($customerId);

        //Se o método de pagamento for Cartão de crédito ou débito
        if($creditData['generalData']['payment_method']['id'] == 1 || $creditData['generalData']['payment_method']['id'] == 2) {
            $response = self::createCardCharge($creditData, $customerId);
        } //Se for PIX
        else if($creditData['generalData']['payment_method']['id'] == 3) {
            $response = self::createPixCharge($creditData, $customerId);
        }
        
        return $response;
    }

    public function createPixCharge($chargeData, $customerId) {    
        $endPoint = env('ASAAS_BASE_URL').'api/v3/payments';
        
        
        $bodyData = [
            "customer" => $customerId,
            "billingType" => "PIX",
            "dueDate" => Carbon::now()->addMonth(),
            "value" => $chargeData['generalData']['credit_value'],
            "description" => "Assinatura de canal",
            "externalReference" => env('URL_SERVER'),
            /*"discount" => [
                "value" => 10.00,
                "dueDateLimitDays" => 0
            ]
            ,
            "fine" => [
                "value" => 1.00
            ],
            "interest" => [
                "value" => 2.00,
            ],*/ 
            "postalService" => false
        ];

        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(20)
        //->asForm()
        ->post($endPoint, $bodyData);

        Log::debug('response CREATE PIX CHARGE');
        Log::debug($response);

        return $response;
    }

    public function createCardCharge($cardData, $customerId)
    {
        $endPoint = env('ASAAS_BASE_URL').'api/v3/payments';

        Log::debug('createCardCharge');
        Log::debug($cardData);
        Log::debug($customerId);
        //Data de vencimento da cobrança
        $dueDate = Carbon::now()->addMonth();

        //Se o cartão já possui o token
        if($cardData['paymentMethodData']['car_token']) {
            $bodyData = [
                "customer" => $customerId,
                "billingType" => $cardData['generalData']['payment_method']['id'] == 1? "CREDIT_CARD": "DEBIT_CARD",
                "dueDate" => $dueDate,
                "value" => $cardData['generalData']['credit_value'],
                "description" => isset($cardData['generalData']['credit_description'])? $cardData['generalData']['credit_description'] : "Inserção de crédito na plataforma",
                "creditCardToken" => $cardData['paymentMethodData']['car_token']
            ];
        }
        else {
            $bodyData = [
                "customer" => $customerId,
                "billingType" => $cardData['generalData']['payment_method']['id'] == 1? "CREDIT_CARD": "DEBIT_CARD",
                "dueDate" => $dueDate,
                "value" => $cardData['generalData']['credit_value'],
                "description" => isset($cardData['generalData']['credit_description'])? $cardData['generalData']['credit_description'] : "Inserção de crédito na plataforma",
                "externalReference" => env('URL_SERVER'),
                "creditCard" => [
                    "holderName" => $cardData['paymentMethodData']['car_holder_name'],
                    "number" => $cardData['paymentMethodData']['car_number'],
                    "expiryMonth" => $cardData['paymentMethodData']['car_due_month'],
                    "expiryYear" => $cardData['paymentMethodData']['car_due_year'],
                    "ccv" => $cardData['generalData']['ccv']
                ],
                "creditCardHolderInfo" => [
                    "name" => $cardData['paymentMethodData']['holderInfo']['car_name'],
                    "email" => $cardData['paymentMethodData']['holderInfo']['car_email'],
                    "cpfCnpj" => $cardData['paymentMethodData']['holderInfo']['car_cpf']? $cardData['paymentMethodData']['holderInfo']['car_cpf'] : $cardData['paymentMethodData']['holderInfo']['car_cnpj'],
                    "postalCode" => $cardData['paymentMethodData']['holderInfo']['car_postal_code'],
                    "addressNumber" => $cardData['paymentMethodData']['holderInfo']['car_address_number'],
                    "addressComplement" => null,
                    "phone" => null,
                    "mobilePhone" => $cardData['paymentMethodData']['holderInfo']['car_phone']
                ],
            ];
        }
        
        Log::debug('$bodyData');
        Log::debug($bodyData);
        $response = Http::withHeaders([
            'access_token' => env('ASAAS_API_KEY'),
            'Content-Type' => 'application/json; charset=utf-8'
        ])
                        ->timeout(70)
                        //->asForm()
                        ->post($endPoint, $bodyData);

        return $response;
    }

    //Retorna id de um status de pagamento
    public function getStatusPaymentId($statusPayment)
    {
        //Se o status do pagamento é RECEBIDO ou CONFIRMADO
        if($statusPayment == 'RECEIVED' || $statusPayment == 'CONFIRMED') {
            //Atualiza a fatura para PAGO
            return 2;
        } //Caso a fatura esteja aguardando pagamento
        else if($statusPayment == 'PENDING') {
            //Atualiza a fatura para AGUARDANDO PAGAMENTO
            return 1;
        }
        else if($statusPayment == 'OVERDUE') {
            //Atualiza a fatura para VENCIDA
            return 4;
        }
        else if($statusPayment == 'REFUNDED') {
            //Atualiza a fatura para ESTORNADO
            return 3;
        }
        else if($statusPayment == 'AWAITING_RISK_ANALYSIS') {
            //Atualiza a fatura para ESTORNADO
            return 5;
        }
    }

    //Encaminha um retorno do webhook para a aplicação correta
    public function forwardDataWebhook(Request $request)
    {
        try {
            Log::debug('request a ser encaminhado');
            Log::debug($request['payment']['externalReference']);
            //Se for um link
            if(filter_var($request['payment']['externalReference'], FILTER_VALIDATE_URL)) {
                //Se a resposta não for para o própria aplicação
                if($request['payment']['externalReference'] != env('URL_SERVER')) {
                    $endpontForward = $request['payment']['externalReference'].'/api/webhook-asaas-charges';
                    //$endpontForward = 'https://main.dev.chatx.com.br/api/webhook-asaas-charges';
                    //Converte o request em array
                    $bodyData = $request->all();
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json; charset=utf-8'
                    ])
                    ->timeout(60)
                    //->asForm()
                    ->post($endpontForward, $bodyData);
                } //Se for uma resposta para a própria aplicação
                else {
                    //Chama a função interna que atualiza 
                    self::webhookCharges($request);
                }
            }
        } catch(Exception $e) {

            Log::debug('erro ao encaminhar dados de pagamento');
            Log::debug($e);

            return response()->json(
                ''
            , 200);
        }
        
    }
}
