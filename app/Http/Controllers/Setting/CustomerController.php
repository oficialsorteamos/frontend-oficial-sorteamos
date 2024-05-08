<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Api\System\ApiSystemController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Financial\FeeController;
use App\Http\Controllers\Financial\InvoiceController;
use App\Http\Controllers\Financial\ParameterController;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Setting\Customer;
use App\Models\Setting\CustomerNotification;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Customer::first();

        return response()->json([
            'company' => $company,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {  
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        Log::debug('$request update customerController');
        Log::debug($request);
        $hasWhiteLabelUrl = false;
        $whiteLabelController = new WhiteLabelController();

        $company = Customer::first();
        
        //Pega o número do documento da empresa (CNPJ ou CPF), antes do mesmo ser atualizado
        $documentId = $company->com_cnpj? preg_replace( '/[^0-9]/', '', $company->com_cnpj) : preg_replace( '/[^0-9]/', '', $company->com_cpf);

        $company->com_white_label = isset($request->companyData['com_white_label'])? $request->companyData['com_white_label'] : 0;
        $company->com_name = $request->companyData['com_name'];
        $company->com_responsible_name = $request->companyData['com_responsible_name'];
        $company->com_email = $request->companyData['com_email'];
        $company->com_phone = preg_replace( '/[^0-9]/', '', $request->companyData['com_phone']);
        $company->com_finance_email = $request->companyData['com_finance_email'];
        $company->com_finance_phone = preg_replace( '/[^0-9]/', '', $request->companyData['com_finance_phone']);
        $company->com_mobile_phone = preg_replace( '/[^0-9]/', '', $request->companyData['com_phone']);
        //Se estiver salvando os dados gerais da empresa
        if(isset($request->companyData['legalPersonChecked'])) {
            $company->com_cpf = $request->companyData['legalPersonChecked'] == '0'? $request->companyData['com_cpf'] : null;
            $company->com_cnpj = $request->companyData['legalPersonChecked'] == '1'? $request->companyData['com_cnpj'] : null;
        }
        else {
            $company->com_postal_code = $request->companyData['com_postal_code'];
            $company->com_address = $request->companyData['com_address'];
            $company->com_address_number = $request->companyData['com_address_number'];
            $company->com_complement = $request->companyData['com_complement'];
            $company->com_province = $request->companyData['com_province'];
            $company->com_city = $request->companyData['com_city'];
            $company->com_state = $request->companyData['com_state'];
            $company->com_country = $request->companyData['com_country'];
        }
        
        //Se a os dados foram salvos e não vieram da CENTRAL DE GERENCIAMENTO (Módulo de Gestão)
        if($company->save() && !isset($request->companyData['managementCenterRequest'])) {
            $apiSystemController = new ApiSystemController();

            $hasWhiteLabel = $whiteLabelController->getWhiteLabel();
            //Se os dados do White Label foram preenchidos
            if($hasWhiteLabel->whi_document_number && $hasWhiteLabel->whi_url) {
                $partnerResponse = $apiSystemController->getPartnerByDocumentId(env('URL_MANAGEMENT_SERVER'), $hasWhiteLabel->whi_document_number);
                $hasWhiteLabelUrl = true;
            }
            
            //Verifica se a empresa já foi adicionada ao AMBIENTE DA DEVSKY
            $response = $apiSystemController->getCompanyByDocumentId(env('URL_MANAGEMENT_SERVER'), $documentId);

            $companyData['partner']['id'] = isset($partnerResponse['partner']['id'])? $partnerResponse['partner']['id'] : null;
            $companyData['com_name'] = $request->companyData['com_name'];
            $companyData['com_responsible_name'] = $request->companyData['com_responsible_name'];
            $companyData['com_email'] = $request->companyData['com_email'];;
            $companyData['com_url'] = env('URL_SERVER');
            if(isset($request->companyData['legalPersonChecked'])) {
                $companyData['com_cpf'] = $request->companyData['legalPersonChecked'] == '0'? $request->companyData['com_cpf'] : null;
                $companyData['com_cnpj'] = $request->companyData['legalPersonChecked'] == '1'? $request->companyData['com_cnpj'] : null;
            }
            $companyData['phoneNumber'] = '55'.preg_replace( '/[^0-9]/', '', $request->companyData['com_phone']);
            $companyData['com_responsible_email'] = $request->companyData['com_email'];
            $companyData['com_finance_email'] = $request->companyData['com_finance_email'];
            $companyData['com_mobile_phone'] = preg_replace( '/[^0-9]/', '', $request->companyData['com_phone']);
            $companyData['financialPhoneNumber'] = '55'.preg_replace( '/[^0-9]/', '', $request->companyData['com_finance_phone']);
            $companyData['com_postal_code'] = $request->companyData['com_postal_code'];
            $companyData['com_address'] = $request->companyData['com_address'];
            $companyData['com_address_number'] = $request->companyData['com_address_number'];
            $companyData['com_complement'] = $request->companyData['com_complement'];
            $companyData['com_province'] = $request->companyData['com_province'];
            $companyData['com_city'] = $request->companyData['com_city'];
            $companyData['com_state'] = $request->companyData['com_state'];
            $companyData['com_country'] = $request->companyData['com_country'];
            //Se a empresa já foi cadastrada no AMBIENTE DA DEVSKY
            if($response['company']) {
                $companyData['id'] = $response['company']['id'];
                $response = $apiSystemController->updateCompany(env('URL_MANAGEMENT_SERVER'), $companyData);
            }
            else {
                $response = $apiSystemController->addCompany(env('URL_MANAGEMENT_SERVER'), $companyData);
            }

            //Se tem White Label Associado a empresa
            if($hasWhiteLabelUrl) {
                //Verifica se a empresa já foi adicionada ao do parceiro White Label
                $response = $apiSystemController->getCompanyByDocumentId($hasWhiteLabel->whi_url, $documentId);
                //Ambiente do parceiro não precisa aparecer a tag do referido parceiro
                $companyData['partner']['id'] = null;
                
                if($response['company']) {
                    $companyData['id'] = $response['company']['id'];
                    $response = $apiSystemController->updateCompany($hasWhiteLabel->whi_url, $companyData);
                }
                else {
                    $response = $apiSystemController->addCompany($hasWhiteLabel->whi_url, $companyData);
                }
            }
        } //Se os dados vieram do AMBIENTE DA DEVSKY
        else if (isset($request->companyData['managementCenterRequest'])) {
            //Se Existe um parceiro White Label associado à empresa
            if(isset($request->companyData['whi_document_number']) && isset($request->companyData['whi_url'])) {
                $whiteLabelRequest = new Request([
                    "whi_name" => $request->companyData['whi_name'],
                    "whi_document_number" => $request->companyData['whi_document_number'],
                    "whi_url" => $request->companyData['whi_url'],
                ]);
                $whiteLabelController->update($whiteLabelRequest);
            }
        }
        
        return response()->json([
            'company' => $company,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
    }

    public function getCustomer()
    {
        $customer = Customer::all();

        return $customer;
    }

    //Traz os contatos (e-mail, telefone) de um cliente por tipo (E-mail, SMS, etc) e assunto (Financeiro, Vendas)
    public function getContactsNotificationByTypeAndSubject($typeNotificationId, $subjectId)
    {
        $costumerContacts = CustomerNotification::where('type_notification_id', $typeNotificationId)
                                                ->where('notification_subject_id', $subjectId)
                                                ->where('cus_status', 'A')
                                                ->get();
        return $costumerContacts;
    }

    //Traz os detalhes da empresa que serão enviados para o módulo de gestão
    public function getCompanyDetails()
    {
        $channelController = new ChannelController();
        $chatController = new ChatController();
        $serviceController = new ServiceController();
        $invoiceController = new InvoiceController();
        $parameterController = new ParameterController();
        $userController = new UserController();
        $planController = new PlanController();
        $feeController = new FeeController();

        ############################# COMPANY DETAILS ##################################
        //Traz o total de canais oficiais
        $totalChannelsOfficials = $channelController->getCountAllChannelByOfficial(1);
        //Traz o total de canais não oficiais
        $totalChannelsUnofficials = $channelController->getCountAllChannelByOfficial(0);
        //Traz o total de usuários ativos
        $totalUsers = $userController->getCountUsersByStatus('A');
        //Traz o total de canais conectados
        $totalConnectedChannels = $channelController->getTotalChannelsByStatus('A');
        //Traz o total de mensagens Recevidas em um período específico
        $totalMessagesReceived = $chatController->getTotalMessagesReceived(1);
        //Traz o total de mensagens Recevidas em um período específico
        $totalMessagesSent = $chatController->getTotalMessagesSent(1);
        //Traz o total de atendimentos
        $totalServices = $serviceController->getCountServicesByStatus(null);
        //Traz o total de contas vencidas
        $totalOverdueInvoices = $invoiceController->getCountOverdueBills();
        $totalOverdueInvoices = json_encode($totalOverdueInvoices);
        $totalOverdueInvoices = json_decode($totalOverdueInvoices, true);
        //Traz o dia do vencimento
        $dueDateInvoice = $parameterController->getParameterByType(2);
        //Traz o valor total devido pela empresa somando todas as faturas vencidas
        $totalOverdueAmount = $invoiceController->getTotalOverdueAmount();

        $companyStatus = self::getCustomer();

        $companyDetails['totalChannelsOfficials'] = $totalChannelsOfficials;
        $companyDetails['totalChannelsUnofficials'] = $totalChannelsUnofficials;
        $companyDetails['totalUsers'] = $totalUsers;
        $companyDetails['totalConnectedChannels'] = $totalConnectedChannels;
        $companyDetails['totalMessagesReceived'] = $totalMessagesReceived;
        $companyDetails['totalMessagesSent'] = $totalMessagesSent;
        $companyDetails['totalServices'] = $totalServices;
        $companyDetails['totalOverdueInvoices'] = $totalOverdueInvoices['original']['overdue'];
        $companyDetails['dueDateInvoice'] = $dueDateInvoice->par_value;
        $companyDetails['totalOverdueAmount'] = $totalOverdueAmount;
        $companyDetails['companyStatus'] = $companyStatus[0]['status_id'];

        ########################### PLAN ##############################
        $plan = $planController->getPlan();

        ########################### CHARGES ##########################
        $charges = $parameterController->fetchParametersCharge();
        $chargesData = json_encode($charges);
        $chargesData = json_decode($chargesData, true);
        //Pega os dados do novo contato
        $charges = $chargesData['original']['parametersCharge'];

        ########################## FEES #############################
        $fees = $feeController->getFees();

        ######################### INVOICES ##########################
        $invoicesCompany = $invoiceController->fetchAllInvoices();

        return response()->json([
            'companyDetails' => $companyDetails,
            'plan' => $plan,
            'charges' => $charges,
            'fees' => $fees,
            'invoicesCompany' => $invoicesCompany,
        ], 200);
    }

    //Atualiza o status de uma empresa
    public function updateCompanyStatus(Request $request)
    {
        $company = Customer::first();
        $company->status_id = $request['statusId'];
        $company->save();
    }

    //Atualiza as logos e demais elementos customizáveis no sistema
    public function updateCustomization(Request $request)
    {
        Log::debug('updateCustomization request');
        Log::debug($request);
        if($request->file()) {
            if(request()->logoFile) {
                request()->logoFile->move(public_path('images/logo'), 'logo.png');
            }

            if(request()->faviconFile) {
                request()->faviconFile->move(public_path('images/logo'), 'favicon.png');
            }
        }
        
    }
}
