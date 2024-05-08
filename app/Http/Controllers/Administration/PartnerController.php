<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\System\ApiSystemController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Financial\FeeController;
use App\Models\Administration\Company\CompanyInvoiceFee;
use App\Models\Administration\Partner\Partner;
use App\Models\Administration\Partner\PartnerCommission;
use App\Models\Administration\Partner\PartnerFee;
use App\Models\Administration\Partner\PartnerInvoice;
use App\Models\Administration\Partner\PartnerInvoiceFee;
use App\Models\Administration\Partner\PartnerPaymentOrder;
use App\Models\Administration\Partner\PartnerPaymentOrderStatus;
use App\Models\Administration\Partner\TypePartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use App\Models\Setting\Plan;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Se o usuário não digitou nada no campo de pesquisa
        $skip = (($request['page']-1) * $request['perPage']);

        $partners = Partner::with('typePartner', 'commission', 'fees')
                            ->where('par_status', 'A');
                            //->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_avatar');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($request['q'] != '') {
            $partners = $partners->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('adm_partners.par_corporate_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('adm_partners.par_cnpj', 'like', '%'.trim($request['q']).'%')
                        ->orWhere('adm_partners.par_cpf', 'like', '%'.trim($request['q']).'%');
            });
        }
        if($request['typePartner'] != null) {            
            $partners = $partners->where('type_partner_id', $request['typePartner']);	
        }
        $partners = $partners->orderBy('adm_partners.created_at', 'DESC');
        $total = $partners->count();
        $partners = $partners->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();

        
        //Se existir alguma campanha
        if($partners) {
            //Para cada campanha
            foreach($partners as $partner) {
                self::getDetailsPartner($partner);
            }
        }

        return response()->json([
            'partners'=> $partners,
            'total'=> $total,
        ], 201);
    }

    public function getDetailsPartner($partner)
    {
        $companyController = new CompanyController();
        $totalCompaniesPartner = $companyController->getTotalCompaniesByPartner($partner->id);
        $partner->setAttribute('totalCompaniesPartner', $totalCompaniesPartner);

        $totalUsersCompaniesPartner = $companyController->getTotalUsersByPartner($partner->id);
        $partner->setAttribute('totalUsersCompaniesPartner', $totalUsersCompaniesPartner);

        $totalChannelsCompaniesPartner = $companyController->getTotalChannelsByPartner($partner->id);
        $partner->setAttribute('totalChannelsCompaniesPartner', $totalChannelsCompaniesPartner);
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
        if($request['par_url']){
            $lastChar = mb_substr($request['par_url'], -1);
            //Se a URL possui uma barra no final
            if($lastChar == '/') {
                //Remove a barra
                $request['par_url'] = rtrim($request['par_url'], "/");
            }
        }
        
        $newPartner = new Partner();
        $newPartner->type_partner_id = $request['type_partner']['id'];
        $newPartner->gender_id = isset($request['gender']['id'])? $request['gender']['id'] : NULL;
        $newPartner->par_corporate_name = $request['par_corporate_name'];
        $newPartner->par_url = $request['par_url'];
        $newPartner->par_partnership_started = Carbon::createFromFormat('d/m/Y', $request['par_partnership_started'])->format('Y-m-d');
        $newPartner->par_cnpj = preg_replace('/[^0-9]/', '', $request['par_cnpj']);
        $newPartner->par_cpf = preg_replace('/[^0-9]/', '', $request['par_cpf']);
        $newPartner->par_responsible_name = $request['par_cnpj']? $request['par_responsible_name'] : $request['par_corporate_name'];
        $newPartner->par_birthday = $request['par_birthday']? Carbon::createFromFormat('d/m/Y', $request['par_corporate_name'])->format('Y-m-d') : NULL;
        $newPartner->par_responsible_phone = preg_replace('/[^0-9]/', '', $request['phoneNumber']);
        $newPartner->par_responsible_email = $request['par_responsible_email'];
        $newPartner->par_finance_phone = preg_replace('/[^0-9]/', '', $request['financialPhoneNumber']);
        $newPartner->par_finance_email = $request['par_finance_email'];
        $newPartner->par_postal_code = $request['par_postal_code'];
        $newPartner->par_address = $request['par_address'];
        $newPartner->par_address_number = $request['par_address_number'];
        $newPartner->par_complement = $request['par_complement'];
        $newPartner->par_province = $request['par_province'];
        $newPartner->par_city = $request['par_city'];
        $newPartner->par_state = $request['par_state']['sta_name'];
        $newPartner->par_country = $request['par_country']['cou_name'];

        if($newPartner->save()) {
            //Se for um REVENDEDOR
            if($newPartner->type_partner_id == 1) {
                $commissionRequest = new Request([
                    'partner_id' => $newPartner->id,
                    'par_percentage_level_1' => 10.0,
                    'par_initial_quantity_level_1' => 1,
                    'par_final_quantity_level_1'=> 10,
                    'par_percentage_level_2' => 15.0,
                    'par_initial_quantity_level_2' => 11,
                    'par_final_quantity_level_2'=> 25,
                    'par_percentage_level_3' => 20.0,
                    'par_initial_quantity_level_3' => 26,
                    'par_final_quantity_level_3' => 1000,
                ]);
                //Salva a comissão
                self::storeCommission($commissionRequest);
            } //Se for um parceiro WHITE LABEL
            else if($newPartner->type_partner_id == 2) {

                $feeController = new FeeController();
                $typeFees = $feeController->getTypeFees();

                //Cria um registro para cada tipo de TAXA
                foreach($typeFees as $typeFee) {
                    $partnerFeeData = new Request([
                        'partnerId'   => $newPartner->id,
                        'typeFeeId' => $typeFee->id,
                        'value' => '0.0',
                    ]);
                    self::storePartnerFee($partnerFeeData);
                }
            }
        }

        return response()->json([
            'partner' => $newPartner,
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
        Log::debug('request update partner');
        Log::debug($request);
        
        //Se foi adicionada alguma URL
        if($request['partnerData']['par_url']){
            $lastChar = mb_substr($request['partnerData']['par_url'], -1);
            //Se a URL possui uma barra no final
            if($lastChar == '/') {
                //Remove a barra
                $request['par_url'] = rtrim($request['partnerData']['par_url'], "/");
            }
            else {
                $request['par_url'] = $request['partnerData']['par_url'];
            }
        }

        $partner = Partner::find($request['partnerData']['id']);
        $partner->type_partner_id = $request['partnerData']['type_partner_id'];
        $partner->gender_id = isset($request['partnerData']['gender']['id'])? $request['partnerData']['gender']['id'] : NULL;
        $partner->par_corporate_name = $request['partnerData']['par_corporate_name'];
        $partner->par_url = isset($request['par_url'])? $request['par_url'] : null;
        $partner->par_partnership_started = Carbon::createFromFormat('d/m/Y', $request['partnerData']['par_partnership_started'])->format('Y-m-d');
        $partner->par_cnpj = preg_replace('/[^0-9]/', '', $request['partnerData']['par_cnpj']);
        $partner->par_cpf = preg_replace('/[^0-9]/', '', $request['partnerData']['par_cpf']);
        $partner->par_responsible_name = $request['partnerData']['par_cnpj']? $request['partnerData']['par_responsible_name'] : $request['partnerData']['par_corporate_name'];
        $partner->par_birthday = isset($request['partnerData']['par_birthday'])? Carbon::createFromFormat('d/m/Y', $request['partnerData']['par_birthday'])->format('Y-m-d') : NULL;
        $partner->par_responsible_phone = preg_replace('/[^0-9]/', '', $request['partnerData']['par_responsible_phone']);
        $partner->par_responsible_email = $request['partnerData']['par_responsible_email'];
        $partner->par_finance_phone = preg_replace('/[^0-9]/', '', $request['partnerData']['par_finance_phone']);
        $partner->par_finance_email = $request['partnerData']['par_finance_email'];
        $partner->par_postal_code = $request['partnerData']['par_postal_code'];
        $partner->par_address = $request['partnerData']['par_address'];
        $partner->par_address_number = $request['partnerData']['par_address_number'];
        $partner->par_complement = $request['partnerData']['par_complement'];
        $partner->par_province = $request['partnerData']['par_province'];
        $partner->par_city = $request['partnerData']['par_city'];
        $partner->par_state = $request['partnerData']['par_state'];
        $partner->par_country = $request['partnerData']['par_country'];

        $partner->save();
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

    //Cria uma comissão
    public function storeCommission(Request $request)
    {
        $commission = new PartnerCommission();
        
        $commission->partner_id = $request['partner_id'];
        $commission->par_percentage_level_1 = $request['par_percentage_level_1'];
        $commission->par_initial_quantity_level_1 = $request['par_initial_quantity_level_1'];
        $commission->par_final_quantity_level_1 = $request['par_final_quantity_level_1'];
        $commission->par_percentage_level_2 = $request['par_percentage_level_2'];
        $commission->par_initial_quantity_level_2 = $request['par_initial_quantity_level_2'];
        $commission->par_final_quantity_level_2 = $request['par_final_quantity_level_2'];
        $commission->par_percentage_level_3 = $request['par_percentage_level_3'];
        $commission->par_initial_quantity_level_3 = $request['par_initial_quantity_level_3'];
        $commission->par_final_quantity_level_3 = $request['par_final_quantity_level_3'];
        $commission->save();
    }

    //Atualiza a comissão
    public function updateCommission(Request $request)
    {
        Log::debug('updateCommission request');
        Log::debug($request);

        $commission = PartnerCommission::find($request['commission']['id']);
        
        $commission->par_percentage_level_1 = $request['commission']['par_percentage_level_1'];
        $commission->par_initial_quantity_level_1 = $request['commission']['par_initial_quantity_level_1'];
        $commission->par_final_quantity_level_1 = $request['commission']['par_final_quantity_level_1'];
        $commission->par_percentage_level_2 = $request['commission']['par_percentage_level_2'];
        $commission->par_initial_quantity_level_2 = $request['commission']['par_initial_quantity_level_2'];
        $commission->par_final_quantity_level_2 = $request['commission']['par_final_quantity_level_2'];
        $commission->par_percentage_level_3 = $request['commission']['par_percentage_level_3'];
        $commission->par_initial_quantity_level_3 = $request['commission']['par_initial_quantity_level_3'];
        $commission->par_final_quantity_level_3 = $request['commission']['par_final_quantity_level_3'];
        $commission->save();

        return response()->json([
            'commission' => $request['commission'],
        ], 200);
    }

    //Traz os tipos de parceiros
    public function fetchTypePartners()
    {
        $typePartners = TypePartner::where('typ_status', 'A')
                                    ->get();

        return response()->json([
            'typePartners' => $typePartners,
        ], 200);
    }

    public function getPartnersByStatus($statusId)
    {
        $partners = Partner::where('par_status', $statusId)
                            ->get();

        return response()->json([
            'partners' => $partners,
        ], 200);
    }

    public function getPartners()
    {
        $partners = Partner::get();

        return response()->json([
            'partners' => $partners,
        ], 200);
    }

    //Traz a empresa de acordo com o CPF ou CNPJ
    public function getPartnerByDocumentId(Request $request)
    {
        Log::debug('$request getPartnerByDocumentId');
        Log::debug($request);

        $partner = Partner::where('par_cnpj', $request['documentId'])
                            ->orWhere('par_cpf', $request['documentId'])
                            ->first();
        
        return response()->json([
            'partner' => $partner,
        ], 200);
    }

    //Traz o parceiro pelo seu ID
    public function getPartner($partnerId)
    {
        $partner = Partner::find($partnerId);

        return $partner;
    }

    //Atualiza as taxas de uma empresa
    public function updatePartnerFees(Request $request)
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
                    $hasFee->par_value = $feeValue;
                    $hasFee->save();
                }
                else {
                    $partnerFeeData = new Request([
                        'partnerId'   => $request['id'],
                        'typeFeeId' => $typeFeeId,
                        'value' => $feeValue,
                    ]);
                    self::storePartnerFee($partnerFeeData);
                }
            }
        }
    }

    //Salva uma nova taxa para um parceiro
    public function storePartnerFee(Request $request)
    {
        $companyFee = new PartnerFee();
        $companyFee->partner_id = $request['partnerId'];
        $companyFee->type_fee_id = $request['typeFeeId'];
        $companyFee->par_value = $request['value'];
        $companyFee->save();
    }

    //Traz uma taxa pelo seu tipo
    public function getFeeByType($partnerId, $typeFeeId)
    {
        $partnerFee = PartnerFee::where('partner_id', $partnerId)
                                ->where('type_fee_id', $typeFeeId)
                                ->first();
        return $partnerFee;
    }

    //Gera as ordens de pagamento para os parceiros REVENDEDORES
    public function generatePaymentOrders()
    {
        $companyController = new CompanyController();
        
        //Traz os parceiros REVENDEDORES
        $resellersPartners = self::getPartnersByType(1);

        //Para cada REVENDEDOR
        foreach($resellersPartners AS $partner) {
            //Traz as empresas associadas ao REVENDEDOR
            $companiesPartner = $companyController->getCompaniesByPartner($partner->id);
            //Para cada empresa associada ao REVENDEDOR
            foreach($companiesPartner AS $companyPartner) {
                //Traz as faturas pagas
                $statusId = [2];
                $invoices = $companyController->fetchCompanyInvoicesByStatus($companyPartner['id'], $statusId);
                $commssionPercentage = self::getCommissionPartner($partner->id);

                //Para cada fatura PAGA pela empresa associada ao REVENDEDOR
                foreach($invoices AS $invoice) {
                    $hasPaymentOrder = null;
                    $hasPaymentOrder = self::getPaymentOrderByInvoiceId($invoice->id);
                    //Se ainda NÃO existe uma ordem de pagamento em relação a uma fatura
                    if(!$hasPaymentOrder) {
                        //Traz o valor calculado da comissão do revendedor
                        $totalCommissionPartner = self::calculateCommissionPartner($commssionPercentage, $invoice);

                        $paymentOrderData = new Request([
                            "invoice_id" => $invoice->id,
                            "par_value_order" => $totalCommissionPartner,
                        ]);

                        //Salva a ordem de pagamento
                        self::storePaymentOrder($paymentOrderData);
                    }
                }
            }
        }
    }

    //Armazena uma ordem de pagamento
    public function storePaymentOrder(Request $request)
    {
        $paymentOrder = new PartnerPaymentOrder();

        $paymentOrder->invoice_id = $request['invoice_id'];
        $paymentOrder->par_value_order = $request['par_value_order'];
        $paymentOrder->save();
    }

    //Calcula a comissão a ser paga ao REVENDEDOR
    public function calculateCommissionPartner($commissionPercentage, $invoice)
    {
        $totalInvoiceFee = 0.0;
        $totalCommission = 0.0;

        $companyInvoiceFees = CompanyInvoiceFee::where('invoice_id', $invoice['id'])
                                                ->where('com_status', 'A')
                                                ->get();
        //Para cada taxa associada à fatura                                                
        foreach($companyInvoiceFees AS $invoiceFee) {
            $totalInvoiceFee = $totalInvoiceFee + $invoiceFee->com_total_value_fee;
        }

        $totalCommission = ($totalInvoiceFee * $commissionPercentage) /100;

        return $totalCommission;
    }

    //Traz uma ordem de pagamento de acordo com o id da fatura
    public function getPaymentOrderByInvoiceId($invoiceId)
    {
        $paymentOrder = PartnerPaymentOrder::where('invoice_id', $invoiceId)
                                            ->first();

        return $paymentOrder;
    }

    //Retorma em que nível o parceiro REVENDEDOR está
    public function getCommissionPartner($partnerId)
    {
        $companyController = new CompanyController();

        //Traz o total de empresas associadas ao parceiro
        $totalCompaniesPartner = $companyController->getTotalCompaniesByPartner($partnerId);

        $commission = self::getPartnerCommission($partnerId);

        //Se o REVENDEDOR está no nível 1
        if($totalCompaniesPartner >= $commission->par_initial_quantity_level_1 &&  $totalCompaniesPartner <= $commission->par_final_quantity_level_1) {
            return $commission->par_percentage_level_1;
        }//Se o REVENDEDOR está no nível 2
        else if($totalCompaniesPartner >= $commission->par_initial_quantity_level_2 &&  $totalCompaniesPartner <= $commission->par_final_quantity_level_2) {
            return $commission->par_percentage_level_2;
        }//Se o REVENDEDOR está no nível 3
        else if($totalCompaniesPartner >= $commission->par_initial_quantity_level_3 &&  $totalCompaniesPartner <= $commission->par_final_quantity_level_3) {
            return $commission->par_percentage_level_3;
        }
    }

    //Retorna a tabela de comissões asssociada a um usuário
    public function getPartnerCommission($partnerId)
    {
        $commission = PartnerCommission::where('partner_id', $partnerId)
                                        ->where('par_status', 'A')
                                        ->first();

        return $commission;
    }

    //Retorna os parceiros de acordo com o seu tipo
    public function getPartnersByType($typeId)
    {   
        $partners = Partner::with('fees')
                            ->where('type_partner_id', $typeId)
                            ->where('par_status', 'A')
                            ->get();

        return $partners;
    }

    //Traz as ordens de pagamento
    public function fetchPaymentOrders()
    {
        $paymentOrders = PartnerPaymentOrder::with('status', 'invoice.company')
                                            ->get();


        $baseUrlStorage = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC');

        return response()->json([
            'paymentOrders' => $paymentOrders,
            'total' => count($paymentOrders),
            'baseUrlStorage' => $baseUrlStorage
        ], 200);
    }

    public function updatePaymentOrder(Request $request)
    {
        $paymentOrder = PartnerPaymentOrder::find($request['id']);
        $paymentOrder->status_id = $request['status']['id'];
        $paymentOrder->save();
    }

    public function fetchPaymentOrderStatus()
    {
        $paymentOrderStatus = PartnerPaymentOrderStatus::where('par_status', 'A')
                                                        ->get();

        return response()->json([
            'paymentOrderStatus' => $paymentOrderStatus,
        ], 200);
    }

    //Faz o upload do comprovante do pagamento ao REVENDEDOR
    public function uploadPaymentReceipt(Request $request)
    {
        Log::debug('uploadPaymentReceipt request');
        Log::debug($request);
        $paymentOrderData = json_decode($request['paymentOrderData'], true);
        $filepath = null;

        if($request->file()) {
            $file = $request->file;   
            $filename = $file->getClientOriginalName();

            $dateTimeNow = Carbon::now();

            $dataSplit = explode(".", $filename);
            $extensionContent = '.'.$dataSplit[1];

            //Formata a data/hora adicionando milissegundos
            $dateTimeNowFormatted = $dateTimeNow->format('Y-m-d H:i:s.u');
            //Deixa apenas os números no nome do arquivo
            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNowFormatted).$extensionContent;
            
            //Salva o arquivo
            Storage::disk('spaces')->putFileAs('public/paymentOrders/paymentOrder'.$paymentOrderData['id'].'/', $request->file, $contentName);

            //Pega o caminho do arquivo
            $filepath = 'public/paymentOrders/paymentOrder'.$paymentOrderData['id'].'/'. $contentName;
        }

        $contract = PartnerPaymentOrder::find($paymentOrderData['id']);
        $contract->par_link_payment_receipt = $filepath;
        $contract->save();
    }

    //Atualiza o id do cliente na API de pagamentos
    public function updatePaymentApiCustomerId($partnerId, $paymentApiCustomerId)
    {
        $partner = Partner::find($partnerId);
        $partner->payment_api_customer_id = $paymentApiCustomerId;
        $partner->save();
    }

    //Gera a fatura a ser cobrada de um parceiro WHITE LABEL
    public function generateInvoicePartner()
    {   
        $companyController = new CompanyController();
        //Traz os parceiros White Labels
        $partners = self::getPartnersByType(2);

        $currentDate = Carbon::now()->startOfDay();
        

        //GERAR A FATURA ATUAL COM 5 DIAS DE ANTECEDÊNCIA EM RELAÇÃO A DATA DE VENCIMENTO
        //Para cada parceiro
        foreach($partners AS $partner) {
            
            $lastInvoice = self::getLastInvoicePartner($partner->id);

            if($lastInvoice) {
                //Pega a data de fechamento da última fatura
                $dataClosingLastInvoice = Carbon::parse($lastInvoice['par_closing']);

                $yearOpeningInvoice = $dataClosingLastInvoice->year;
                $monthOpeningInvoice = $dataClosingLastInvoice->month;
                $MonthAndYearInvoice = $monthOpeningInvoice.'/'.$yearOpeningInvoice;

                //Cria o dia do vencimento
                $dueDate = Carbon::createFromFormat('Y-m-d H:i:s', $yearOpeningInvoice.'-'.$monthOpeningInvoice.'-'.$partner->par_due_date.' 00:00:00');
                
            }
            else {
                $initialDateInvoiceProcessing = Carbon::parse($partner->par_partnership_started);
                $dayStartPartnership = $initialDateInvoiceProcessing->day;

                //Se o dia que o parceiro iniciou a parceria for maior que o dia de vencimento
                if($dayStartPartnership > $partner->par_due_date) {
                    //Pega o mês subsequente ao mês de início
                    $nextMonthDue = $initialDateInvoiceProcessing->addMonthsNoOverflow(1);

                    $yearOpeningInvoice = $nextMonthDue->year;
                    $monthOpeningInvoice = $nextMonthDue->month;
                    $MonthAndYearInvoice = $monthOpeningInvoice.'/'.$yearOpeningInvoice;
                    //Traz o dia do vencimento
                    
                    $dueDate = Carbon::createFromFormat('Y-m-d H:i:s', $yearOpeningInvoice.'-'.$monthOpeningInvoice.'-'.$partner->par_due_date.' 00:00:00');
                }
                else {
                    $yearOpeningInvoice = $initialDateInvoiceProcessing->year;
                    $monthOpeningInvoice = $initialDateInvoiceProcessing->month;
                    $MonthAndYearInvoice = $monthOpeningInvoice.'/'.$yearOpeningInvoice;
                    //Traz o dia do vencimento
                    
                    $dueDate = Carbon::createFromFormat('Y-m-d H:i:s', $yearOpeningInvoice.'-'.$monthOpeningInvoice.'-'.$partner->par_due_date.' 00:00:00');
                }
            }

            //Se a data atual é menor que a data de vencimento
            if($currentDate->lt($dueDate)) {
                //Dias até o vencimento da fatura
                $dayUntilDue = $dueDate->diff($currentDate)->days;
            }
            else {
                $dayUntilDue = -1;
            }

            Log::debug('$dayUntilDue');
            Log::debug($dayUntilDue);
            
            //Se não tiver fatura anterior gerada e estiver faltando 5 ou menos dias dias para o vencimento ou
            //se tiver faltando 5 dias para o vencimento
            if(($dayUntilDue <= 5 && !$lastInvoice) || $dayUntilDue == 5) {
                $paymentController = new PaymentController();
                $invoiceData = [];
                $invoiceTotalValue = 0.0;

                $dueDateAux = $dueDate->toDateTimeString();
                //Data de início de processamento da fatura
                $initialDate = Carbon::parse($dueDateAux)->addDay();
                //Data de final de processamento da fatura
                $closingDate = Carbon::parse($dueDateAux)->addMonth();

                Log::debug('dueDate');
                Log::debug($dueDate);
                Log::debug('initialDate');
                Log::debug($initialDate);
                Log::debug('closingDate');
                Log::debug($closingDate);
                Log::debug('partner');
                Log::debug($partner);

                //Traz as empresas associadas ao parceiro
                $companiesPartner = $companyController->getCompaniesByPartner($partner->id);

                $partnerFeesArray = [];
                //Para cada empresa
                foreach($companiesPartner AS $company) {
                    Log::debug('$company partner');
                    Log::debug($company);
                    //Se houver algum USUÁRIO contratado para o plano
                    if($company['plan']->com_total_users > 0) {
                        $partnerFee = [];
                        $partnerFee['company_id'] = $company->id;
                        $partnerFee['type_fee_id'] = 2; //Usuário
                        $partnerFee['par_total_resource'] = $company['plan']->com_total_users;
                        $partnerFee['par_unit_value_fee'] = $partner['fees'][1]->par_value;
                        $partnerFee['par_total_value_fee'] = $company['plan']->com_total_users * $partner['fees'][1]->par_value;

                        array_push($partnerFeesArray, $partnerFee);
                        //Soma ao valor total da fatura
                        $invoiceTotalValue += $partnerFee['par_total_value_fee'];
                    } //Se houver CANAIS OFICIAIS
                    if($company['plan']->com_total_official_channels > 0) {
                        $partnerFee = [];
                        $partnerFee['company_id'] = $company->id;
                        $partnerFee['type_fee_id'] = 3; //Canal Oficial
                        $partnerFee['par_total_resource'] = $company['plan']->com_total_official_channels;
                        $partnerFee['par_unit_value_fee'] = $partner['fees'][2]->par_value;
                        $partnerFee['par_total_value_fee'] = $company['plan']->com_total_official_channels * $partner['fees'][2]->par_value;

                        array_push($partnerFeesArray, $partnerFee);
                        //Soma ao valor total da fatura
                        $invoiceTotalValue += $partnerFee['par_total_value_fee'];
                    } //Se houver CANAIS NÃO OFICIAIS
                    if($company['plan']->com_total_unofficial_channels > 0) {
                        $partnerFee = [];
                        $partnerFee['company_id'] = $company->id;
                        $partnerFee['type_fee_id'] = 4; //Canal Não Oficial
                        $partnerFee['par_total_resource'] = $company['plan']->com_total_unofficial_channels;
                        $partnerFee['par_unit_value_fee'] = $partner['fees'][3]->par_value;
                        $partnerFee['par_total_value_fee'] = $company['plan']->com_total_unofficial_channels * $partner['fees'][3]->par_value;

                        array_push($partnerFeesArray, $partnerFee);
                        //Soma ao valor total da fatura
                        $invoiceTotalValue += $partnerFee['par_total_value_fee'];
                    }
                }

                $invoiceDataArray['partner'] = $partner;
                $invoiceDataArray['invoice_opening'] = $initialDate;
                $invoiceDataArray['invoice_closing'] = $closingDate;
                $invoiceDataArray['invoice_due'] = $dueDate;
                $invoiceDataArray['invoice_total_value'] = $invoiceTotalValue;
                
                
                $invoiceApiData = $paymentController->generateInvoicePartner($invoiceDataArray);
                
                $invoiceData['partner_id'] = $partner->id;
                $invoiceData['api_payment_invoice_id'] = $invoiceApiData['id'];
                $invoiceData['par_url_invoice'] = $invoiceApiData['bankSlipUrl'];
                $invoiceData['par_month_year'] = $MonthAndYearInvoice;
                $invoiceData['par_opening'] = $initialDate;
                $invoiceData['par_closing'] = $closingDate;
                $invoiceData['par_due'] = $dueDate;
                $invoiceData['status_id'] = 1;
                
                //Se a fatura foi salva
                if($invoiceData = self::storeInvoicePartner($invoiceData)) {
                    //Para cada taxa da fatura
                    foreach($partnerFeesArray AS $partnerFeeData) {
                        self::storeInvoiceFeePartner($invoiceData['id'], $partnerFeeData);
                    }
                }
            }
        }
    }

    public function storeInvoiceFeePartner($invoiceId, $invoiceFee)
    {
        $newInvoiceFee = new PartnerInvoiceFee();
        $newInvoiceFee->invoice_id = $invoiceId;
        $newInvoiceFee->company_id = $invoiceFee['company_id'];
        $newInvoiceFee->type_fee_id = $invoiceFee['type_fee_id'];
        $newInvoiceFee->par_total_resource = $invoiceFee['par_total_resource'];
        $newInvoiceFee->par_unit_value_fee = $invoiceFee['par_unit_value_fee'];
        $newInvoiceFee->par_total_value_fee = $invoiceFee['par_total_value_fee'];
        $newInvoiceFee->save();
    }

    //Salva uma fatura associada ao parceiro
    public function storeInvoicePartner($invoiceData)
    {
        $partnerInvoice = new PartnerInvoice();
        $partnerInvoice->partner_id = $invoiceData['partner_id'];
        $partnerInvoice->api_payment_invoice_id = $invoiceData['api_payment_invoice_id'];
        $partnerInvoice->par_url_invoice = $invoiceData['par_url_invoice'];
        $partnerInvoice->par_month_year = $invoiceData['par_month_year'];
        $partnerInvoice->par_opening = $invoiceData['par_opening'];
        $partnerInvoice->par_closing = $invoiceData['par_closing'];
        $partnerInvoice->par_due = $invoiceData['par_due'];
        $partnerInvoice->status_id = $invoiceData['status_id'];
        $partnerInvoice->save();

        return $partnerInvoice;
    }

    //Traz a última fatura criada, se houver
    public function getLastInvoicePartner($partnerId)
    {
        $lastInvoice = PartnerInvoice::where('partner_id', $partnerId)
                                    ->orderBy('created_at', 'DESC')
                                    ->first();

        return $lastInvoice;
    }

    //Traz as faturas de parceiros WHITE LABELS
    public function fetchInvoices(Request $request)
    {
        Log::debug('fetchInvoices partners');
        Log::debug($request);

        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($request['page']-1) * $request['perPage']);

        $invoices = PartnerInvoice::with('status', 'invoiceFees', 'partner')
                            ->select(
                                'id',
                                'partner_id',
                                'api_payment_invoice_id',
                                'par_url_invoice',
                                'par_month_year',
                                'par_opening',
                                'par_closing',
                                'par_due',
                                'status_id',
                                DB::raw("(SELECT SUM(par_total_value_fee) FROM adm_partners_invoices_fees WHERE adm_partners_invoices.id = adm_partners_invoices_fees.invoice_id) AS total_invoice_value"),
                                DB::raw("(SELECT par_total_value_fee FROM adm_partners_invoices_fees WHERE adm_partners_invoices.id = adm_partners_invoices_fees.invoice_id AND type_fee_id = 1) AS total_monthly_value"),
                                DB::raw("(SELECT SUM(par_total_value_fee) FROM adm_partners_invoices_fees WHERE adm_partners_invoices.id = adm_partners_invoices_fees.invoice_id AND type_fee_id = 2) AS total_user_value"),
                                DB::raw("(SELECT SUM(par_total_value_fee) FROM adm_partners_invoices_fees WHERE adm_partners_invoices.id = adm_partners_invoices_fees.invoice_id AND type_fee_id = 3) AS total_official_channel_value"),
                                DB::raw("(SELECT SUM(par_total_value_fee) FROM adm_partners_invoices_fees WHERE adm_partners_invoices.id = adm_partners_invoices_fees.invoice_id AND type_fee_id = 4) AS total_unofficial_channel_value"),
                            )
                            //Busca os contatos de acordo com a paginação
                            ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                            ->take($request['perPage']); //Quantidade de itens trazidos
        if($request['status']) {
            //$status = json_decode($request['status'], true);
            $invoices = $invoices->where('status_id', $request['status']['id']);
        }
        //Se foi selecionado algum parceiro
        if($request['partner']) {
            //$status = json_decode($request['status'], true);
            $invoices = $invoices->where('partner_id', $request['partner']);
        }

        //$total = $invoices->count();
        $invoices = $invoices->orderBy('id', 'DESC');
        $invoices = $invoices->get();

        return response()->json([
            'invoices' => $invoices,
            'total' => 0
        ], 200);
    }

    //Atualiza o status de pagamento do fatura do parceiro WHITE LABEL
    public function updateStatusInvoicePartnerPaymentChargeApiId($paymentId, $statusPayment)
    {
        $invoicePartner = PartnerInvoice::where('api_payment_invoice_id', $paymentId)->first();
        //Se for o status de pagamento de um canal
        if($invoicePartner) {
            $invoicePartner->status_id = $statusPayment;
            $invoicePartner->save();
        }
    }
}
