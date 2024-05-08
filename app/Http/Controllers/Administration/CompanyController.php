<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Api\System\ApiSystemController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Setting\CustomerController;
use App\Jobs\UpdateCompany;
use App\Models\Administration\Company\Company;
use App\Models\Administration\Company\CompanyCharge;
use App\Models\Administration\Company\CompanyContract;
use App\Models\Administration\Company\CompanyDetails;
use App\Models\Administration\Company\CompanyFee;
use App\Models\Administration\Company\CompanyInvoice;
use App\Models\Administration\Company\CompanyInvoiceFee;
use App\Models\Administration\Company\CompanyPlan;
use App\Models\Administration\Company\CompanyStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    { 
        //Log::debug('request companies');  
        //Log::debug($request);
        $customerController = new CustomerController();

        //Se o usuário não digitou nada no campo de pesquisa
        $skip = (($request['page']-1) * $request['perPage']);

        $companies = Company::with('partner.typePartner', 'status', 'details', 'contracts', 'lastContract', 'plan', 'fees', 'charges');
                            //->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_avatar');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($request['q'] != '') {
            $companies = $companies->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('adm_companies.com_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('adm_companies.com_cnpj', 'like', '%'.trim($request['q']).'%')
                        ->orWhere('adm_companies.com_cpf', 'like', '%'.trim($request['q']).'%');
            });
        }
        if($request['companyStatus'] != null) {            
            $companies = $companies->where('adm_companies.status_id', $request['companyStatus']);
        }
        if($request['typePartner'] != null) {            
            $companies = $companies->whereHas('partner.typePartner', function($q) use($request)
            {
                $q->where('adm_type_partners.id', $request['typePartner']);	
            });
        }
        if($request['partner'] != null) {
            //Se deseja filtrar por algum parceiro associado
            if($request['partner'] != '0') {
                $companies = $companies->whereHas('partner', function($q) use($request)
                {
                    $q->where('adm_partners.id', $request['partner']);	
                });
            } //Se deseja trazer apenas as empresas que NÃO tem parceiro associado
            else {
                $companies = $companies->whereNull('adm_companies.partner_id');
            }
        }
        if($request['overdueInvoice'] != '') {
            //Se filtrou por empresas com faturas vencidas
            if($request['overdueInvoice'] == 'S') {
                $companies = $companies->whereHas('details', function($q) use($request)
                {
                    $q->where('adm_companies_details.com_total_overdue_invoices','>' , 0);	
                });
            }//Se filtrou por empresas que NÃO tem faturas vencidas
            else {
                $companies = $companies->whereHas('details', function($q) use($request)
                {
                    $q->where('adm_companies_details.com_total_overdue_invoices', 0);	
                });
            }
            
        }
        if($request['daysDueContract'] != '') {
            //Se filtrou por empresas com contrato vencidas
            if($request['daysDueContract'] == 'V') {
                $datimeNow = Carbon::now()->startOfDay();
                $companies = $companies->whereHas('lastContract', function($q) use($datimeNow)
                {
                    //$q->orderBy('adm_companies_contracts.com_dt_end','DESC');	
                    $q->where('adm_companies_contracts.com_dt_end','<', $datimeNow);	
                });
            }//Se filtrou por empresas que estão com contratos a X dias de vencer
            else {
                $datimeEnd = Carbon::now()->addDays($request['daysDueContract'])->startOfDay();
                $datimeStart = Carbon::now()->startOfDay();
                $companies = $companies->whereHas('lastContract', function($q) use($datimeStart, $datimeEnd)
                {
                    $q->whereBetween('adm_companies_contracts.com_dt_end', [$datimeStart, $datimeEnd]);
                });
            }
        }
        
        $companies = $companies->orderBy('adm_companies.created_at', 'DESC');
        $total = $companies->count();
        $companies = $companies->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();

        //Log::debug('companies test');
        //Log::debug($companies);
        $customer = $customerController->getCustomer();

        return response()->json([
            'companies'=> $companies,
            'total'=> $total,
            'isWhiteLabel'=> $customer[0]->com_white_label,
        ], 201);
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
        //Log::debug('Dados parceiro');
        //Log::debug($request);

        //Se foi adicionada alguma URL
        if($request['companyData']['com_url']){
            $lastChar = mb_substr($request['companyData']['com_url'], -1);
            //Se a URL possui uma barra no final
            if($lastChar == '/') {
                //Remove a barra
                $request['com_url'] = rtrim($request['companyData']['com_url'], "/");
            }
            else {
                $request['com_url'] = $request['companyData']['com_url'];
            }
        }
        
        $newCompany = new Company();
        $newCompany->partner_id = isset($request['companyData']['partner']['id'])? $request['companyData']['partner']['id'] : null;
        $newCompany->gender_id = isset($request['companyData']['gender']['id'])? $request['companyData']['gender']['id'] : NULL;
        $newCompany->com_name = $request['companyData']['com_name'];
        $newCompany->com_url = isset($request['com_url'])? $request['com_url'] : NULL;
        $newCompany->com_cnpj = preg_replace('/[^0-9]/', '', $request['companyData']['com_cnpj']);
        $newCompany->com_cpf = preg_replace('/[^0-9]/', '', $request['companyData']['com_cpf']);
        $newCompany->com_responsible_name = $request['companyData']['com_cnpj']? $request['companyData']['com_responsible_name'] : $request['companyData']['com_name'];
        $newCompany->com_birthday = isset($request['companyData']['com_birthday'])? Carbon::createFromFormat('d/m/Y', $request['companyData']['com_corporate_name'])->format('Y-m-d') : NULL;
        $newCompany->com_responsible_phone = preg_replace('/[^0-9]/', '', $request['companyData']['phoneNumber']);
        $newCompany->com_responsible_email = $request['companyData']['com_responsible_email'];
        $newCompany->com_finance_phone = preg_replace('/[^0-9]/', '', $request['companyData']['financialPhoneNumber']);
        $newCompany->com_finance_email = $request['companyData']['com_finance_email'];
        $newCompany->com_postal_code = $request['companyData']['com_postal_code'];
        $newCompany->com_address = $request['companyData']['com_address'];
        $newCompany->com_address_number = $request['companyData']['com_address_number'];
        $newCompany->com_complement = $request['companyData']['com_complement'];
        $newCompany->com_province = $request['companyData']['com_province'];
        $newCompany->com_city = $request['companyData']['com_city'];
        $newCompany->com_state = $request['companyData']['com_state'];
        $newCompany->com_country = $request['companyData']['com_country'];

        if($newCompany->save()) {
            //Salva os detalhes da empresa
            $companyDetails = new CompanyDetails();
            $companyDetails->company_id = $newCompany->id;
            $companyDetails->com_total_overdue_amount = 0.0;
            $companyDetails->save();

            //Salva os dados referente ao plano
            $companyPlan = new CompanyPlan();
            $companyPlan->company_id = $newCompany->id;
            $companyPlan->save();
        }

        return response()->json([
            'company' => $newCompany,
        ], 200);
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
        Log::debug('request update');
        Log::debug($request);

        //Se foi adicionada alguma URL
        if($request['companyData']['com_url']){
            $lastChar = mb_substr($request['companyData']['com_url'], -1);
            //Se a URL possui uma barra no final
            if($lastChar == '/') {
                //Remove a barra
                $request['com_url'] = rtrim($request['companyData']['com_url'], "/");
            }
            else {
                $request['com_url'] = $request['companyData']['com_url'];
            }
        }

        $company = Company::find($request['companyData']['id']);
        $company->partner_id = isset($request['companyData']['partner']['id'])? $request['companyData']['partner']['id'] : null;
        $company->gender_id = isset($request['companyData']['gender']['id'])? $request['companyData']['gender']['id'] : NULL;
        $company->com_name = $request['companyData']['com_name'];
        $company->com_url = isset($request['com_url'])? $request['com_url'] : null;
        $company->com_cnpj = preg_replace('/[^0-9]/', '', $request['companyData']['com_cnpj']);
        $company->com_cpf = preg_replace('/[^0-9]/', '', $request['companyData']['com_cpf']);
        $company->com_responsible_name = $request['companyData']['com_cnpj']? $request['companyData']['com_responsible_name'] : $request['companyData']['com_name'];
        $company->com_birthday = isset($request['companyData']['com_birthday'])? Carbon::createFromFormat('d/m/Y', $request['companyData']['com_birthday'])->format('Y-m-d') : NULL;
        $company->com_responsible_phone = preg_replace('/[^0-9]/', '', $request['companyData']['phoneNumber']);
        $company->com_responsible_email = $request['companyData']['com_responsible_email'];
        $company->com_finance_phone = preg_replace('/[^0-9]/', '', $request['companyData']['financialPhoneNumber']);
        $company->com_finance_email = $request['companyData']['com_finance_email'];
        $company->com_postal_code = $request['companyData']['com_postal_code'];
        $company->com_address = $request['companyData']['com_address'];
        $company->com_address_number = $request['companyData']['com_address_number'];
        $company->com_complement = $request['companyData']['com_complement'];
        $company->com_province = $request['companyData']['com_province'];
        $company->com_city = $request['companyData']['com_city'];
        $company->com_state = $request['companyData']['com_state'];
        $company->com_country = $request['companyData']['com_country'];

        if($company->save()){
            $apiSystemController = new ApiSystemController();
            $partnerController = new PartnerController();

            //Se a URL do ambiente da empresa foi informada
            if($company->com_url) {
                //Indentica a requisição como sendo enviada do módulo de gestão
                $companyData['managementCenterRequest'] = true;
                
                $companyData['com_name'] = $request['companyData']['com_name'];
                $companyData['com_cnpj'] = preg_replace('/[^0-9]/', '', $request['companyData']['com_cnpj']);
                $companyData['com_cpf'] = preg_replace('/[^0-9]/', '', $request['companyData']['com_cpf']);
                $companyData['com_responsible_name'] = $request['companyData']['com_cnpj']? $request['companyData']['com_responsible_name'] : $request['companyData']['com_name'];
                $companyData['com_phone'] = substr($request['companyData']['phoneNumber'], 3, strlen($request['companyData']['phoneNumber']));
                $companyData['com_email'] = $request['companyData']['com_responsible_email'];
                $companyData['com_finance_phone'] = substr($request['companyData']['financialPhoneNumber'], 3, strlen($request['companyData']['financialPhoneNumber']));
                $companyData['com_finance_email'] = $request['companyData']['com_finance_email'];
                $companyData['com_postal_code'] = $request['companyData']['com_postal_code'];
                $companyData['com_address'] = $request['companyData']['com_address'];
                $companyData['com_address_number'] = $request['companyData']['com_address_number'];
                $companyData['com_complement'] = $request['companyData']['com_complement'];
                $companyData['com_province'] = $request['companyData']['com_province'];
                $companyData['com_city'] = $request['companyData']['com_city'];
                $companyData['com_state'] = $request['companyData']['com_state'];
                $companyData['com_country'] = $request['companyData']['com_country'];

                //Se a empresa está associada a alugm parceiro
                if($company->partner_id) {
                    $partner = $partnerController->getPartner($company->partner_id);
                    //Se o parceiro é um White Label
                    if($partner->type_partner_id == 2) {
                        $companyData['whi_name'] = $partner->par_corporate_name;
                        $companyData['whi_document_number'] = $partner->par_cnpj? $partner->par_cnpj : $partner->par_cpf;
                        $companyData['whi_url'] = $partner->par_url;
                    }
                }
                
                $apiSystemController->updateCustomer($company->com_url, $companyData);
            }
        }
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

    public function getCompaniesByStatus($statusId)
    {
        $companies = Company::where('status_id', $statusId)
                            ->get();

        return response()->json([
            'companies' => $companies,
        ], 200);
    }

    public function getCompanyById($companyId)
    {
        $company = Company::find($companyId);

        return $company;
    }

    //Atualiza o status de uma empresa. Diferencia se a ação de atualização veio do botão de atualização de detalhes da empresa
    public function updateCompanyStatus($companyId, $statusId, $buttonUpdate=false)
    {
        $company = Company::find($companyId);
        $company->status_id = $statusId;
        $company->save();

        //Se foi uma atualização de status feita pela tela de GESTÃO
        if(!$buttonUpdate) {
            $endPoint = $company['com_url'].'/api/setting/company/update-company-status';

            $companyData = [
                'api_token' => env('CHATX_API_TOKEN'),
                'statusId' => $statusId,
            ];
    
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Content-Type' => 'application/json; charset=utf-8'
            ])
            ->timeout(10)
            //->asForm()
            ->post($endPoint, $companyData);
        }

        return $statusId;
    }

    //Atualiza os detalhes da empresa
    public function updateCompanyDetails(Request $request)
    {
        Log::debug('$request updateCompanyDetails');
        Log::debug($request);

        ########################################## COMPANY DETAILS ##############################################

        $company = self::getCompanyById($request['companyId']);
        $response = self::getCompanyDetails($company);

        $companyDetails = CompanyDetails::where('company_id', $request['companyId'])->first();
        $companyDetails->com_total_official_channels = $response['companyDetails']['totalChannelsOfficials'];
        $companyDetails->com_total_unofficial_channels = $response['companyDetails']['totalChannelsUnofficials'];
        $companyDetails->com_total_users = $response['companyDetails']['totalUsers'];
        $companyDetails->com_total_connected_channels = $response['companyDetails']['totalConnectedChannels'];
        $companyDetails->com_total_messages_sent = $response['companyDetails']['totalMessagesSent'];
        $companyDetails->com_total_messages_received = $response['companyDetails']['totalMessagesReceived'];
        $companyDetails->com_total_services = $response['companyDetails']['totalServices'];
        $companyDetails->com_total_overdue_invoices = $response['companyDetails']['totalOverdueInvoices'];
        $companyDetails->com_total_overdue_amount = $response['companyDetails']['totalOverdueAmount'];
        $companyDetails->com_due_date_invoice = $response['companyDetails']['dueDateInvoice'];
        $companyDetails->save();

        //Atualiza o status da empresa
        self::updateCompanyStatus($request['companyId'], $response['companyDetails']['companyStatus'], true);

        ############################################# PLAN #################################################

        $planRequest = new Request([
            "id" => $request['companyId'],
            'plan' => [
                "com_total_users" => $response['plan']['pla_total_user'],
                "com_total_official_channels" => $response['plan']['pla_total_official_channel'],
                "com_total_unofficial_channels" => $response['plan']['pla_total_unofficial_channel'],
                "updateCompanyServer" => false,
            ]
        ]);

        self::updateCompanyPlan($planRequest);


        ############################################ CHARGES ################################################
        //Para cada cobrança
        foreach($response['charges'] AS $charge) {
            $hasCharge = self::getChargeByType($request['companyId'], $charge['type_parameter_id']);
            //Se a cobrança já foi cadastrada em algum momento
            if($hasCharge) {
                //Atualiza a cobrança existente
                $hasCharge->com_value = $charge['par_value'];
                //Se for um tipo de cobrança que possui cobrança proporcional, caso essa cobrança esteja desabilitada, também desabilita a cobrança proporcional
                if($charge['type_parameter_id'] == 6 || $charge['type_parameter_id'] == 7 || $charge['type_parameter_id'] == 8 || $charge['type_parameter_id'] == 9) {
                    $hasCharge->com_proportional_charge = $charge['par_value'] == '1'? $charge['par_proportional_charge'] : '0';
                }
                $hasCharge->save();
            }
            else {
                $companyChargeData = new Request([
                    'companyId'   => $request['companyId'],
                    'typeParameterId' => $charge['type_parameter_id'],
                    'value' => $charge['par_value'],
                    'proportionalCharge' => $charge['par_proportional_charge'],
                ]);
                self::storeCompanyCharge($companyChargeData);
            }
        }

        ################################################ FEES ################################################
        //Para cada taxa
        foreach($response['fees'] AS  $fee) {
            
            $hasFee = null;
            $hasFee = self::getFeeByType($request['companyId'], $fee['type_fee_id']);
            //Se a taxa já foi cadastrada em algum momento
            if($hasFee) {
                //Atualiza a taxa existente
                $hasFee->com_value = $fee['fee_value'];
                $hasFee->save();
            }
            else {
                $companyFeeData = new Request([
                    'companyId'   => $request['companyId'],
                    'typeFeeId' => $fee['type_fee_id'],
                    'value' => $fee['fee_value'],
                ]);
                self::storeCompanyFee($companyFeeData);
            }
        }

        ############################################### INVOICES ##############################################

        if(isset($response['invoicesCompany'])) {
            //Inativa todas as faturas associadas a empresa
            CompanyInvoice::where('company_id', $request['companyId'])
                            ->update([
                                'com_status' => 'I'
                            ]);

            foreach($response['invoicesCompany'] AS $invoice) {
                $hasInvoice = null;
    
                $invoiceData = new Request([
                    "companyId" => $request['companyId'],
                    "invoiceId" => $invoice['id'],
                    "apiPaymentInvoiceId" => $invoice['api_payment_invoice_id'],
                    "comUrlInvoice" => $invoice['inv_url_invoice'],
                    "comMonthYear" => $invoice['inv_month_year'],
                    "comOpening" => $invoice['inv_opening'],
                    "comClosing" => $invoice['inv_closing'],
                    "comDue" => $invoice['inv_due'],
                    "statusId" => $invoice['status_id'],
                ]);

                $hasInvoice = self::getCompanyInvoice($invoice['id']);
                //Se a fatura já existe
                if($hasInvoice) {
                    //atualiza a fatura 
                    $invoiceStored = self::updateCompanyInvoice($invoiceData);
                }
                else {
                    //Salva a fatura 
                    $invoiceStored = self::storeCompanyInvoice($invoiceData);
                }
                
                
                //Deleta todas taxas associadas a uma fatura
                CompanyInvoiceFee::where('invoice_id', $invoiceStored['id'])
                                ->update([
                                    "com_status" => 'I'
                                ]);

                //Para cada tipo de taxa na fatura
                foreach($invoice['invoice_fees'] AS $invoiceFee) {
                    $invoiceFeeData = new Request([
                        "invoiceId" => $invoiceStored['id'],
                        "invoiceFeeId" => $invoiceFee['id'],
                        "typeFeeId" => $invoiceFee['type_fee_id'],
                        "userId" => $invoiceFee['user_id'],
                        "channelId" => $invoiceFee['channel_id'],
                        "comUnitValueFee" => $invoiceFee['inv_unit_value_fee'],
                        "comTotalValueFee" => $invoiceFee['inv_total_value_fee'],
                        "comStatus" => $invoiceFee['inv_status'],
                    ]);

                    $hasInvoiceFee = null;
                    $hasInvoiceFee = self::getCompanyInvoiceFee($invoiceFee['id']);

                    //Se a taxa já existir
                    if($hasInvoiceFee) {
                        //Atualiza a taxa
                        self::updateCompanyInvoiceFee($invoiceFeeData);
                    }
                    else {
                        //Armazena a taxa
                        self::storeCompanyInvoiceFee($invoiceFeeData);
                    }
                }
            }
        }
    }

    //Traz uma taxa associada a uma fatura
    public function getCompanyInvoiceFee($invoiceFeeId)
    {
        $invoiceFee = CompanyInvoiceFee::where('invoice_fee_id', $invoiceFeeId)
                                        ->first();
        return $invoiceFee;
    }

    //Armazena uma taxa associada a uma fatura
    public function storeCompanyInvoiceFee(Request $request)
    {
        $invoiceFee = new CompanyInvoiceFee();
        $invoiceFee->invoice_id = $request['invoiceId'];
        $invoiceFee->invoice_fee_id = $request['invoiceFeeId'];
        $invoiceFee->type_fee_id = $request['typeFeeId'];
        $invoiceFee->user_id = $request['userId'];
        $invoiceFee->channel_id = $request['channelId'];
        $invoiceFee->com_unit_value_fee = $request['comUnitValueFee'];
        $invoiceFee->com_total_value_fee = $request['comTotalValueFee'];
        $invoiceFee->com_status = $request['comStatus'];
        $invoiceFee->save();
    }

    //Atualiza uma taxa associada a uma fatura
    public function updateCompanyInvoiceFee(Request $request)
    {
        $invoiceFee = CompanyInvoiceFee::where('invoice_fee_id', $request['invoiceFeeId'])
                                        ->first();
                                        
        $invoiceFee->invoice_id = $request['invoiceId'];
        $invoiceFee->invoice_fee_id = $request['invoiceFeeId'];
        $invoiceFee->type_fee_id = $request['typeFeeId'];
        $invoiceFee->user_id = $request['userId'];
        $invoiceFee->channel_id = $request['channelId'];
        $invoiceFee->com_unit_value_fee = $request['comUnitValueFee'];
        $invoiceFee->com_total_value_fee = $request['comTotalValueFee'];
        $invoiceFee->com_status = $request['comStatus'];
        $invoiceFee->save();
    }

    //Traz uma fatura pelo seu ID
    public function getCompanyInvoice($invoiceId)
    {
        $invoice = CompanyInvoice::where('invoice_id', $invoiceId)
                                ->first();
        return $invoice;
    }

    //Armazena uma fatura
    public function storeCompanyInvoice(Request $request)
    {
        $invoice = new CompanyInvoice();
        $invoice->company_id = $request['companyId'];
        $invoice->invoice_id = $request['invoiceId'];
        $invoice->api_payment_invoice_id = $request['apiPaymentInvoiceId'];
        $invoice->com_url_invoice = $request['comUrlInvoice'];
        $invoice->com_month_year = $request['comMonthYear'];
        $invoice->com_opening = $request['comOpening'];
        $invoice->com_closing = $request['comClosing'];
        $invoice->com_due = $request['comDue'];
        $invoice->status_id = $request['statusId'];
        $invoice->save();

        return $invoice;
    }

    //Atualiza uma fatura
    public function updateCompanyInvoice(Request $request)
    {
        $invoice = CompanyInvoice::where('invoice_id', $request['invoiceId'])
                                ->first();
        $invoice->company_id = $request['companyId'];
        $invoice->api_payment_invoice_id = $request['apiPaymentInvoiceId'];
        $invoice->com_url_invoice = $request['comUrlInvoice'];
        $invoice->com_month_year = $request['comMonthYear'];
        $invoice->com_opening = $request['comOpening'];
        $invoice->com_closing = $request['comClosing'];
        $invoice->com_due = $request['comDue'];
        $invoice->status_id = $request['statusId'];
        $invoice->com_status = 'A';
        $invoice->save();

        return $invoice;
    }

    //Traz os detalhes da empresa via API
    public function getCompanyDetails($company)
    {
        $endPoint = $company['com_url'].'/api/setting/company/get-company-details';
        $companyData = [
            'api_token' => env('CHATX_API_TOKEN'),
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $companyData);

        Log::debug('response getCompanyDetails');
        Log::debug($response);

        return $response;
    }

    //Adiciona um contrato
    public function addContract(Request $request)
    {
        $contractData = json_decode($request['contractData'], true);
        $filepath = null;

        if($request->file()) {
            $file = $request->file;   
            $filename = $file->getClientOriginalName();
            
            //Salva o arquivo
            Storage::disk('public')->putFileAs('contracts/company'.$contractData['companyId'].'/', $request->file, $filename);
            //Pega o caminho do arquivo
            $filepath = storage_path('app/public/contracts/company'.$contractData['companyId'].'/'. $filename);
        }

        $contract = new CompanyContract();
        $contract->company_id = $contractData['companyId'];
        $contract->com_dt_start = Carbon::createFromFormat('d/m/Y', $contractData['com_dt_start'])->format('Y-m-d');
        $contract->com_dt_end = Carbon::createFromFormat('d/m/Y', $contractData['com_dt_end'])->format('Y-m-d');
        $contract->com_link = $filepath;
        $contract->save();
    }

    //Traz os contratos associados a uma empresa
    public function fetchContracts(Request $request)
    {
        $contracts = CompanyContract::where('company_id', $request['companyId'])
                                    ->get();
        Log::debug('contracts');
        Log::debug($contracts);

        return response()->json([
            'contracts' => $contracts,
        ], 200);
    }

    //Remove um contrato
    public function removeContract($contractId)
    {
        CompanyContract::find($contractId)->delete();
    }

    public function downloadContract(Request $request)
    {
        $linkDivided = explode('/', $request['com_link']);
        
        //Pega no nome do arquivo
        $filename = $linkDivided[4];
        //$filePath = storage_path().'/app/public/'.$filename;

        //$file = Storage::get($filename);
        $file = file_get_contents(storage_path('app/public/contracts/company'.$request['company_id'].'/'.$filename));
        if ($file) {
            $fileLink = 'data:text/csv;charset=utf-8;base64,' . base64_encode($file);
            @chmod(Storage::disk('local')->path($filename), 0755);
            @unlink(Storage::disk('local')->path($filename));
        }

        return response()->json([
            'linkData' => $fileLink,
            'filename' => $filename,
        ], 200);
    }

    public function updateContract(Request $request)
    {
        Log::debug('updateContract request');
        Log::debug($request);
    }

    //Traz todos os Status que uma empresa pode ter
    public function fetchCompanyStatus($statusId)
    {
        $companyStatus = CompanyStatus::where('com_status', $statusId)
                                        ->get();

        return response()->json([
            'companyStatus' => $companyStatus,
        ], 200);
    }

    //Atualiza os dados do plano de uma empresa
    public function updateCompanyPlan(Request $request)
    {
        Log::debug('plan updateCompanyPlan');
        Log::debug($request);
        $updateCompanyServer = $request['plan']['updateCompanyServer']? $request['plan']['updateCompanyServer'] : false;

        $companyPlan = CompanyPlan::where('company_id', $request['id'])->first();
        $companyPlan->com_total_users = $request['plan']['com_total_users'];
        $companyPlan->com_total_official_channels = $request['plan']['com_total_official_channels'];
        $companyPlan->com_total_unofficial_channels = $request['plan']['com_total_unofficial_channels'];
        $companyPlan->save();
        
        //Se a requisição veio popup de plano da tela de gestão da DEVSKY
        if($updateCompanyServer) {
            $endPoint = $request['com_url'].'/api/setting/plan/update-company-plan';

            $companyData = [
                'api_token' => env('CHATX_API_TOKEN'),
                'plan' => $request['plan'],
            ];

            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Content-Type' => 'application/json; charset=utf-8'
            ])
            ->timeout(10)
            //->asForm()
            ->post($endPoint, $companyData);
        }
        

        return response()->json([
            'companyPlan' => $request['plan'],
        ], 200);
    }

    //Traz uma taxa pelo seu tipo
    public function getFeeByType($companyId, $typeFeeId)
    {
        $companyFee = CompanyFee::where('company_id', $companyId)
                                ->where('type_fee_id', $typeFeeId)
                                ->first();
        return $companyFee;
    }

    //Salva uma nova taxa para uma empresa
    public function storeCompanyFee(Request $request)
    {
        $companyFee = new CompanyFee();
        $companyFee->company_id = $request['companyId'];
        $companyFee->type_fee_id = $request['typeFeeId'];
        $companyFee->com_value = $request['value'];
        $companyFee->save();
    }

    //Atualiza as taxas de uma empresa
    public function updateCompanyFees(Request $request)
    {
        //Para cada taxa preenchida pelo usuário
        foreach($request['fees'] AS $typeFeeId => $feeValue) {
            //Começa o tipo de taxa pelo ID 1
            if($typeFeeId > 0) {
                $hasFee = null;
                $hasFee = self::getFeeByType($request['id'], $typeFeeId);
                //Se a taxa já foi cadastrada em algum momento
                if($hasFee) {
                    //Atualiza a taxa existente
                    $hasFee->com_value = $feeValue;
                    $hasFee->save();
                }
                else {
                    $companyFeeData = new Request([
                        'companyId'   => $request['id'],
                        'typeFeeId' => $typeFeeId,
                        'value' => $feeValue,
                    ]);
                    self::storeCompanyFee($companyFeeData);
                }
            }
        }

        $endPoint = $request['com_url'].'/api/financial/fee/update-company-fees';

        $companyData = [
            'api_token' => env('CHATX_API_TOKEN'),
            'fees' => $request['fees'],
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $companyData);
    }

    //Traz uma charge pelo seu tipo
    public function getChargeByType($companyId, $typeChargeId)
    {
        $companyCharge = CompanyCharge::where('company_id', $companyId)
                                    ->where('type_parameter_id', $typeChargeId)
                                    ->first();
        return $companyCharge;
    }

    //Salva uma nova cobrança para uma empresa
    public function storeCompanyCharge(Request $request)
    {
        $companyCharge = new CompanyCharge();
        $companyCharge->company_id = $request['companyId'];
        $companyCharge->type_parameter_id = $request['typeParameterId'];
        $companyCharge->com_value = $request['value'];
        $companyCharge->com_proportional_charge = $request['proportionalCharge'];
        $companyCharge->save();
    }

    //Atualiza os parâmetros de cobrança de uma empresa
    public function updateCompanyCharges(Request $request)
    {
        Log::debug('updateCompanyCharges request');
        Log::debug($request);

        //Para cada taxa preenchida pelo usuário
        foreach($request['charges'] AS $key => $chargeValue) {
            //Começa o tipo de cobrança pelo ID 1
            if($key > 0) {
                $proportionalCharge = null;
                $typeParameterId = null;
                // Se o parâmetro for a MENSALIDADE
                if($key == 1) {
                    $typeParameterId = 6;
                    //Pega o parâmetro de COBRANÇA PROPORCIONAL da MENSALIDADE
                    $proportionalCharge = $request['charges'][$key+1];
                } 
                //Se o parâmetro for a cobrança por USUÁRIO
                else if ($key == 3) {
                    $typeParameterId = 7;
                    //Pega o parâmetro de COBRANÇA PROPORCIONAL por USUÁRIO
                    $proportionalCharge = $request['charges'][$key+1];
                }//Se a cobraça for por CANAL OFICIAL
                else if ($key == 5) {
                    $typeParameterId = 8;
                    //Pega o parâmetro for de COBRANÇA PROPORCIONAL por CANAL OFICIAL
                    $proportionalCharge = $request['charges'][$key+1];
                }//Se a cobraça for por CANAL NÃO OFICIAL
                else if ($key == 7) {
                    $typeParameterId = 9;
                    //Pega o parâmetro for de COBRANÇA PROPORCIONAL por CANAL NÃO OFICIAL
                    $proportionalCharge = $request['charges'][$key+1];
                }//Se a cobrança for por ENVIO DE MENSAGEM VIA WHATSAPP EM UMA CAMPANHA
                else if ($key == 9) {
                    $typeParameterId = 5;
                } //Se a cobrança for por ENVIO DE SMS
                else if ($key == 10) {
                    $typeParameterId = 10;
                } //Se a cobrança for por RETORNO DE SMS
                else if ($key == 11) {
                    $typeParameterId = 11;
                } //Se a cobrança for por LIGAÇÃO VIA WHATSAPP
                else if ($key == 12) {
                    $typeParameterId = 12;
                }

                //Se houver algum parâmetro correspodente
                if($typeParameterId) {
                    $hasCharge = null;
                    $hasCharge = self::getChargeByType($request['id'], $typeParameterId);
                    //Se a cobrança já foi cadastrada em algum momento
                    if($hasCharge) {
                        //Atualiza a cobrança existente
                        $hasCharge->com_value = $chargeValue;
                        //Se for um tipo de cobrança que possui cobrança proporcional, caso essa cobrança esteja desabilitada, também desabilita a cobrança proporcional
                        if($typeParameterId == 6 || $typeParameterId == 7 || $typeParameterId == 8 || $typeParameterId == 9) {
                            $hasCharge->com_proportional_charge = $chargeValue == '1'? $proportionalCharge : '0';
                        }
                        $hasCharge->save();
                    }
                    else {
                        $companyChargeData = new Request([
                            'companyId'   => $request['id'],
                            'typeParameterId' => $typeParameterId,
                            'value' => $chargeValue,
                            'proportionalCharge' => $proportionalCharge,
                        ]);
                        self::storeCompanyCharge($companyChargeData);
                    }
                }
            }
        }
        
        $endPoint = $request['com_url'].'/api/financial/parameter/update-company-charges';

        $companyData = [
            'api_token' => env('CHATX_API_TOKEN'),
            'charges' => $request['charges'],
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $companyData);
    }

    //Traz a empresa de acordo com o CPF ou CNPJ
    public function getCompanyByDocumentId(Request $request)
    {
        Log::debug('$documentId getCompanyByDocumentId');
        Log::debug($request);

        $company = Company::where('com_cnpj', $request['documentId'])
                            ->orWhere('com_cpf', $request['documentId'])
                            ->first();
        
        return response()->json([
            'company' => $company,
        ], 200);
    }

    //Rotina para atualizar os dados das empresas
    public function updateAllCompaniesData()
    {
        $customerController = new CustomerController();
        $company =  $customerController->getCustomer();

        //Se for ambiente da Devsky ou o ambiente de alguma empresa WHITE LABEL
        if(env('URL_SERVER') == 'https://devsky.apps.chatx.com.br' || env('URL_SERVER') == 'https://127.0.0.1' || $company[0]->com_white_label == 1) {
        //if(env('URL_SERVER') == 'https://devsky.apps.chatx.com.br' || $company[0]->com_white_label == 1) {
            //Traz todas as empresas NÃO está INATIVA
            $companies = Company::where('status_id', '!=', 2)->get();
            //Para cada empresa
            foreach($companies AS $company) {
                $requestCompany = new Request([
                    "companyId" => $company['id']
                ]);
                
                //Cria o job na fila
                UpdateCompany::dispatch($requestCompany)->onQueue('update_company');
            }
        }
    }

    //Traz o total de empresas associadas a um parceiro
    public function getTotalCompaniesByPartner($partnerId)
    {
        $totalCompanies = Company::where('partner_id', $partnerId)
                                ->where('status_id','!=', 2) //Onde a empresa não está inativa
                                ->count();

        return $totalCompanies;
    }

    //Traz o total geral de usuários somando todas as empresas associadas oa parceiro
    public function getTotalUsersByPartner($partnerId)
    {
        $totalUsers = 0;

        $companies = Company::with('details')
                            ->where('partner_id', $partnerId)
                            ->where('status_id','!=', 2) //Onde a empresa não está inativa
                            ->get();

        //Para cada empresa associada ao parceiro
        foreach($companies AS $company) {
            $totalUsers += $company['details']->com_total_users;
        }

        return $totalUsers;
    }

    //Traz o total geral de canais oficiais e não oficiais somando todas as empresas associadas oa parceiro
    public function getTotalChannelsByPartner($partnerId)
    {
        $totaChannels = 0;

        $companies = Company::with('details')
                            ->where('partner_id', $partnerId)
                            ->where('status_id','!=', 2) //Onde a empresa não está inativa
                            ->get();

        //Para cada empresa associada ao parceiro
        foreach($companies AS $company) {
            $totaChannels += $company['details']->com_total_official_channels;
            $totaChannels += $company['details']->com_total_unofficial_channels;
        }

        return $totaChannels;
    }

    //Traz as empresas associadas a um parceiro
    public function getCompaniesByPartner($partnerId)
    {
        $companies = Company::with('plan')
                            ->where('partner_id', $partnerId)
                            ->where('status_id','!=', 2) //Onde a empresa não está inativa
                            ->get();

        return $companies;
    }

    //Traz as faturas por um ou mais status
    public function fetchCompanyInvoicesByStatus($companyId, $statusIds)
    {
        $invoices = CompanyInvoice::with('invoiceFees')
                                    ->where('company_id', $companyId)
                                    ->whereIn('status_id', $statusIds)
                                    ->get();
        return $invoices;
    }
}
