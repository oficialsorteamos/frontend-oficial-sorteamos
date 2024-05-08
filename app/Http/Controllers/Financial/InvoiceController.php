<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Setting\CustomerController;
use App\Http\Controllers\Setting\PlanController;
use App\Http\Controllers\Utils\UtilsController;
use App\Mail\NotifyCost;
use App\Models\Financial\Credit;
use App\Models\Financial\Invoice;
use App\Models\Financial\InvoiceFee;
use App\Models\Financial\InvoiceResourceControl;
use App\Models\Financial\StatusPayment;
use App\Models\Financial\TypeInvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DB;
USE Mail;


class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
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
        $invoice = new Invoice();
        $invoice->api_payment_invoice_id = $request['api_payment_invoice_id'];
        $invoice->inv_url_invoice = $request['inv_url_invoice'];
        $invoice->inv_month_year = $request['inv_month_year'];
        $invoice->inv_opening = $request['inv_opening'];
        $invoice->inv_closing = $request['inv_closing'];
        $invoice->inv_due = $request['inv_due'];
        $invoice->status_id = 1; //Aguardando Pagamento
        $invoice->save();

        return $invoice;
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


    //Traz as faturas processadas
    public function fetchInvoices(Request $request)
    {
        Log::debug('fetchInvoices');
        Log::debug($request);
        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($request['page']-1) * $request['perPage']);

        $invoices = Invoice::with('status', 'invoiceFees')
                            ->select(
                                'id',
                                'api_payment_invoice_id',
                                'inv_url_invoice',
                                'inv_month_year',
                                'inv_opening',
                                'inv_closing',
                                'inv_due',
                                'status_id',
                                DB::raw("(SELECT SUM(inv_total_value_fee) FROM fin_invoices_fees WHERE fin_invoices.id = fin_invoices_fees.invoice_id) AS total_invoice_value"),
                                DB::raw("(SELECT inv_total_value_fee FROM fin_invoices_fees WHERE fin_invoices.id = fin_invoices_fees.invoice_id AND type_fee_id = 1) AS total_monthly_value"),
                                DB::raw("(SELECT SUM(inv_total_value_fee) FROM fin_invoices_fees WHERE fin_invoices.id = fin_invoices_fees.invoice_id AND type_fee_id = 2) AS total_user_value"),
                                DB::raw("(SELECT SUM(inv_total_value_fee) FROM fin_invoices_fees WHERE fin_invoices.id = fin_invoices_fees.invoice_id AND type_fee_id = 3) AS total_official_channel_value"),
                                DB::raw("(SELECT SUM(inv_total_value_fee) FROM fin_invoices_fees WHERE fin_invoices.id = fin_invoices_fees.invoice_id AND type_fee_id = 4) AS total_unofficial_channel_value"),
                            )
                            //Busca os contatos de acordo com a paginação
                            ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                            ->take($request['perPage']); //Quantidade de itens trazidos
        if($request['status']) {
            $status = json_decode($request['status'], true);
            $invoices = $invoices->where('status_id', $status['id']);

            $invoices = $invoices->orderBy('id', 'DESC');
            $invoices = $invoices->get();
            $total = self::getTotalInvoicesFiltered($request);
        }
        else {
            $invoices = $invoices->orderBy('id', 'DESC');
            $invoices = $invoices->get();
            
            //Pega o total de créditos de acordo com a busca
            $total = Invoice::count();
        }

        return response()->json([
            'invoices' => $invoices,
            'total' => $total
        ], 200);
    }

    public function getTotalInvoicesFiltered($request)
    {
        $status = json_decode($request['status'], true);   
        $totalInvoices = Invoice::where('status_id', $status['id'])
                                ->count();
        
        return $totalInvoices;
    }

    public function getInvoiceByCompetency($competency)
    {
        $invoice = Invoice::where('inv_month_year', $competency)
                            ->first();
        
        return $invoice;
    }

    public function getInvoiceByClosingDate($closingDate)
    {
        $invoice = Invoice::where('inv_closing', $closingDate)
                            ->first();
        
        return $invoice;
    }

    public function generateInvoice()
    {   
        $parameterController = new ParameterController();
        $parameterData = null;
        $lastInvoice = null;
        $createInvoice = false;
        //É uma pagamento de forma antecipada
        $isAdvancePayment = $parameterController->getParameterByType(13);

        //Traz o parâmetro de cobrança de mensalidade
        $feeMonth = $parameterController->getParameterByType(6);
        //Traz o parâmetro de cobrança por usuário
        $feeUser = $parameterController->getParameterByType(7);

        $lastInvoice = self::getLastInvoice();
        //Se houver alguma fatura anterior
        if($lastInvoice) {
            //Pega a data de de fechamento da última fatura
            $dataClosingLastInvoice = Carbon::parse($lastInvoice['inv_closing']);
            $dataOpening = $dataClosingLastInvoice->addDay();
        }
        else {
            //Traz a data que a empresa iniciou o uso do sistema
            $parameterData = $parameterController->getParameterByType(4);
            //Converte a data de início de operações para um formato datetime
            $dataOpening = Carbon::parse($parameterData['par_value']);
        }
        //Se foi cadastrada a data de início de operações da empresa
        if($dataOpening) {
            //Traz a data atual
            $CurrentDataTime = Carbon::now()->startOfDay();            
            //$CurrentDataTime = Carbon::parse('2024-05-01 00:00:00');

            $diffMonth = 0;
            //Flag que representa a geração ou não de uma fatura antecipada
            $generateAdvancedInvoice = false;
            //Se a cobrança NÃO for antecipada
            if($isAdvancePayment['par_value'] == 0) {
                $diffMonth = $dataOpening->diffInMonths($CurrentDataTime);
                $lastMonth = $dataOpening->month;
                $currentMonth = $CurrentDataTime->month;
                $diffMonth = $currentMonth - $lastMonth;
            } //Se a cobrança FOR ANTECIPADA
            else {
                $lastInvoice = self::getLastInvoice();
                //Se exista alguma fatura anterior gerada
                if($lastInvoice) {
                    //Pega a data de fechamento da última fatura
                    $dataClosingLastInvoice = Carbon::parse($lastInvoice['inv_closing']);
                    //Onde a data de geração da próxima fatura é um dia depois do fechamento da última fatura
                    $dateGenerateInvoice = $dataClosingLastInvoice->addDay()->startOfDay();
                    
                    //Pega o início do próximo mês
                    $nextMonthDue = $dataClosingLastInvoice->addMonthsNoOverflow(1);

                    $yearDue = $nextMonthDue->year;
                    $monthCompetency = $nextMonthDue->month;
                    $dayClosing = Carbon::parse($lastInvoice['inv_closing'])->day;
                    
                    //Data de fechamento da fatura
                    $invoiceClosingDate = Carbon::createFromFormat('Y-m-d H:i:s', $yearDue.'-'.$monthCompetency.'-'.$dayClosing.' 23:59:59');
                }
                else {
                    //Traz a data que a empresa iniciou o uso do sistema
                    $parameterData = $parameterController->getParameterByType(4);
                    //Onde a data de início de processamento de fatura é igual a data de início de utilização do sistema por parte do cliente
                    $initialDateInvoiceProcessing = Carbon::parse($parameterData['par_value']);
                    //Onde a data de geração da próxima fatura é um dia depois do fechamento da última fatura
                    $dateGenerateInvoice = $initialDateInvoiceProcessing->addMonth();

                    $dateGenerateInvoiceAux = $dateGenerateInvoice->toDateTimeString();
                    //Pega data de fechamento da fatura a ser gerada
                    $invoiceClosingDate = Carbon::parse($dateGenerateInvoiceAux)->subDay();
                }

                Log::debug('$invoiceClosingDate');
                Log::debug($invoiceClosingDate);

                $invoiceAlreadyExist = self::getInvoiceByClosingDate($invoiceClosingDate);

                Log::debug('$invoiceAlreadyExist');
                Log::debug($invoiceAlreadyExist);
                Log::debug('$dateGenerateInvoice');
                Log::debug($dateGenerateInvoice);
                Log::debug('$CurrentDataTime');
                Log::debug($CurrentDataTime);

                //Se for o dia de gerar a FATURA e a fatura ainda não foi gerada
                if($CurrentDataTime->equalTo($dateGenerateInvoice) && ! $invoiceAlreadyExist) {
                    $generateAdvancedInvoice = true;
                }
            }

            
            Log::debug('$generateAdvancedInvoice');
            Log::debug($generateAdvancedInvoice);

            //Se já o mês subsequente a última fatura ou início de operação da empresa e a cobrança mensal ou por usuário está ATIVADA e a cobrança NÃO é antecipada
            if($diffMonth !=0 && ($feeMonth['par_value'] == 1 || $feeUser['par_value'] == 1) && $isAdvancePayment['par_value'] == 0) {
                //Habilita a criação da fatura
                $createInvoice = true;
            } //Se uma uma COBRANÇA ANTECIPADA e a cobrança mensal ou por usuário está ATIVADA e chegou o dia de gerar a fatura
            else if($isAdvancePayment['par_value'] == 1 && ($feeMonth['par_value'] == 1 || $feeUser['par_value'] == 1) && $generateAdvancedInvoice) {
                $createInvoice = true;
            }
        }

        Log::debug('$createInvoice');
        Log::debug($createInvoice);
        
        //Se for para criar a fatura
        if($createInvoice) {
            
            $paymentController = new PaymentController();
            //Se NÃO for cobrança antecipada
            if($isAdvancePayment['par_value'] == 0) {
                $invoiceData = self::invoiceCalculation();
            }
            else {
                $invoiceData = self::invoiceCalculationAdvancePayment();
            }
            
            $invoiceApiData = $paymentController->generateInvoice($invoiceData);

            
            $requestInvoiceCreate = new Request([
                'api_payment_invoice_id'   => $invoiceApiData['id'],
                'inv_url_invoice' => $invoiceApiData['bankSlipUrl'],
                'inv_month_year' => $invoiceData['invoice_month_year'], 
                'inv_opening' => $invoiceData['invoice_opening'],
                'inv_closing' => $invoiceData['invoice_closing'],
                'inv_due' => $invoiceData['invoice_due'],
            ]);

            //Salva a invoice no banco
            $invoiceSaved = self::store($requestInvoiceCreate);

            foreach($invoiceData['invoice_fees'] as $invoiceFee) {
                $invoiceFee['invoice_id'] = $invoiceSaved['id'];
                self::createInvoiceFee($invoiceFee);
            }

            //Inicia o controle de recursos da fatura do próximo mês
            self::storeInvoiceResource();
        }
    }

    public function getPixQrcode($chargeIdApi)
    {
        $paymentController = new PaymentController();
        Log::debug('chamou getPixQrcode');
        Log::debug($chargeIdApi);
        $pixQrcodeData = $paymentController->getPixQrcode($chargeIdApi);

        return response()->json([
            'qrcode' => $pixQrcodeData['encodedImage']
        ], 200);
    }

    //Traz a última fatura criada, se houver
    public function getLastInvoice()
    {
        $lastInvoice = Invoice::orderBy('created_at', 'DESC')->first();

        return $lastInvoice;
    }

    //Atualiza o status de um pagamento pelo id da API de cobrança
    public function invoiceCalculation()
    {
        $parameterController = new ParameterController();
        $feeController = new FeeController();
        $planController = new PlanController();
        $totalInvoiceValue = 0;

        //Data inicial de processamento da fatura
        $initialDateInvoiceProcessing = null;
        $lastInvoice = self::getLastInvoice();
        //Se exista alguma fatura anterior gerada
        if($lastInvoice) {
            //Pega a data de fechamento da última fatura
            $dataClosingLastInvoice = Carbon::parse($lastInvoice['inv_closing']);
            //Onde a data de processamento da fatura atual começa no dia seguinte a data de fechamento da última fatura
            $initialDateInvoiceProcessing = $dataClosingLastInvoice->addDay();
            $monthOpening = $initialDateInvoiceProcessing->month;
            $initialDateInvoiceProcessingAux = $initialDateInvoiceProcessing->toDateTimeString();
             //Pega o mês subsequente ao vencimento da última fatura
             $nextMonthDue = Carbon::parse($lastInvoice['inv_closing'])->addDay()->addMonth();
             
        }
        else {
            //Traz a data que a empresa iniciou o uso do sistema
            $parameterData = $parameterController->getParameterByType(4);
            //Onde a data de início de processamento de fatura é igual a data de início de utilização do sistema por parte do cliente
            $initialDateInvoiceProcessing = Carbon::parse($parameterData['par_value']);
            $monthOpening = $initialDateInvoiceProcessing->month;
            $initialDateInvoiceProcessingAux = $initialDateInvoiceProcessing->toDateTimeString();
             //Pega o mês subsequente ao mês de início
            $nextMonthDue = $initialDateInvoiceProcessing->addMonthsNoOverflow(1);
        }

        $yearDue = $nextMonthDue->year;
        $monthDue = $nextMonthDue->month;
        $MonthAndYearInvoice = $monthDue.'/'.$yearDue;
        //Traz o dia do vencimento
        $parameterDateDue = $parameterController->getParameterByType(2);
        $dueDate = Carbon::createFromFormat('Y-m-d H:i:s', $yearDue.'-'.$monthDue.'-'.$parameterDateDue['par_value'].' 00:00:00');
        //Pega a data atual
        $CurrentDataTime = Carbon::now()->startOfDay();
        //Se a data de vencimento padrão é maior ou igual a data atual
        if($dueDate->gte($CurrentDataTime)) {
            //Pega a data padrão como data de vencimento
            $dueDateAux = $dueDate->toDateTimeString();
        }
        else {
            //Senão, pega a data atual mais 3 dias como vencimento
            $dueDateAux = $CurrentDataTime->addDays(3)->toDateTimeString();
        }

        //traz a diferença de dias entre a data de vencimento e a data de fechamento da fatura
        //$parameterDateProcessing = $parameterController->getParameterByType(3);
        //calcula a data de fechamento com base na data de vencimento
        $closingDate = Carbon::parse($initialDateInvoiceProcessingAux)->endOfMonth();
        $closingDate = $closingDate->startOfDay();
        //$closingDate = $dueDate->subDays($parameterDateProcessing['par_value']);
        //Pega o mês de fechamento
        $monthClosing = $closingDate->month;
        //Caso o mês de fechamento seja fevereiro e vencimento seja março
        /*if($monthClosing == 2 && $monthDue == 3) {
            //Coloca a data de processamento em 2 dias à frente
            $closingDate->addDays(2);
        }*/
        //Calcula a diferença de dias entre a data de fechamento e a data de fechamento da fatura
        $diffDaysProcessing = $closingDate->diffInDays($initialDateInvoiceProcessingAux);
        //Pega a diferença de dias entre as datas e soma mais 1 (que representa o dia de fechamento)
        
        $daysProcessing = $diffDaysProcessing + 1;
        Log::debug('$daysProcessing');
        Log::debug($daysProcessing);

        //Traz o valor da mensalidade fixa
        $fixedMonthlyFee = $feeController->getFeeByType(1);
        $requestInvoiceFeeCreateCount = 0;

        $feeMonthlyCharge = $parameterController->getParameterByType(6);
        //Se estiver habilitada a cobrança mensalldade
        if($feeMonthlyCharge->par_value == 1) {
            //Se for cobrança cheia
            if($feeMonthlyCharge->par_proportional_charge == 0) {
                $totalInvoiceValue += $fixedMonthlyFee['fee_value'];
            } //Se for cobrança proporcional
            else {
                //Caso seja uma fatura cheia (um mês completo, considerando para caso o período de da fatura estja em fevereiro)
                if($daysProcessing == 30 || $daysProcessing == 31 || ( ($monthOpening == 2 && $monthClosing == 3) && ($daysProcessing == 28 || $daysProcessing == 29) )) {
                    $totalInvoiceValue += $fixedMonthlyFee['fee_value'];
                } //Quando o cliente alterou a data de vencimento ou acabou de entrar na empresa
                else {
                    //Calcula o valor da fatura de forma proporcional (divide o valor cheio pelo período de 1 mês e multipla pelo total dias a serem processados)
                    $proportionalMonthlyValue = ($fixedMonthlyFee['fee_value'] / 30) * $daysProcessing;
                    $totalInvoiceValue += number_format($proportionalMonthlyValue, 2, '.', '');
                }
            }
            
            $requestInvoiceFeeCreate[$requestInvoiceFeeCreateCount] = new Request([
                //'invoice_id'   => ,
                'type_fee_id' => 1,
                //'inv_total_units' => 1, //Total de unidades da taxa
                'inv_unit_value_fee' => $fixedMonthlyFee['fee_value'],
                'inv_total_value_fee' => isset($proportionalMonthlyValue)? number_format($proportionalMonthlyValue, 2, '.', '') : $fixedMonthlyFee['fee_value'],
            ]);
            $requestInvoiceFeeCreateCount++;
        } //Se a cobrança da mensalidade NÃO estiver habilitada
        else {
            $totalInvoiceValue += 0.00;

            $requestInvoiceFeeCreate[$requestInvoiceFeeCreateCount] = new Request([
                //'invoice_id'   => ,
                'type_fee_id' => 1,
                //'inv_total_units' => 1, //Total de unidades da taxa
                'inv_unit_value_fee' => $fixedMonthlyFee['fee_value'],
                'inv_total_value_fee' => 0.00,
            ]);
            $requestInvoiceFeeCreateCount++;
        }
            

        //Traz os tipos de recursos de uma fatura
        //$typeResources = self::fetchTypeResources();
        //Para cada tipo de recurso a ser controlado
        //foreach($typeResources as $typeResource) {
        $typeResource['id'] = 1;
        $resourcesTaxed = self::getTotalResourcesTaxed($typeResource['id'], $initialDateInvoiceProcessingAux, $closingDate);
        Log::debug('$resourcesTaxed');
        Log::debug($resourcesTaxed);
        
        $typeFeeId = null;
        //Se existe algum usuário além do plano para ser taxado
        if(count($resourcesTaxed) > 0) {
            //Se o tipo de recurso for um usuário
            if($typeResource['id'] == 1) {
                $typeFeeId = 2;
                //Traz o valor da taxa por usuário
                $feePerResource = $feeController->getFeeByType($typeFeeId);
                $feeParameterCharge = $parameterController->getParameterByType(7);
            } //Se o tipo de recurso for um CANAL OFICIAL
            /*else if($typeResource['id'] == 2) {
                $typeFeeId = 3;
                $feePerResource = $feeController->getFeeByType($typeFeeId);
            } //Se o tipo de recurso for um CANAL NÃO OFICIAL
            else if($typeResource['id'] == 3) {
                $typeFeeId = 4;
                $feePerResource = $feeController->getFeeByType($typeFeeId);
            }*/
            
            $totalValueResourceFee = 0;
            //Se a cobrança do recurso estiver habilitada
            if($feeParameterCharge->par_value == 1) {
                foreach($resourcesTaxed as $resourceTaxed) {
                    //Se é um recurso a ser taxado proporcionalmente
                    if($feeParameterCharge->par_proportional_charge == 1 && $resourceTaxed->proportional_fee) {
                        //Calcula a diferença de dias entre a data de criação do usuário e a data de fechamento da fatura
                        $totalDaysToUserProcessing = $closingDate->diffInDays($resourceTaxed->created_at->format('Y-m-d'));
                        $totalDaysToUserProcessing += 1;
                        //Log::debug('$totalDaysToUserProcessing');
                        //Log::debug($totalDaysToUserProcessing);
                        $totalValueResourceFee = ($feePerResource['fee_value'] / 30) * $totalDaysToUserProcessing;
                    } //Se for cobrar o valor cheio do cliente
                    else {
                        $totalValueResourceFee = $feePerResource['fee_value'];
                    }
                    //Soma o valor da taxa do usuário ao valor total da fatura
                    $totalInvoiceValue += number_format($totalValueResourceFee, 2, '.', '');
                
                    $requestInvoiceFeeCreate[$requestInvoiceFeeCreateCount] = new Request([
                        //'invoice_id'   => ,
                        'type_fee_id' => $typeFeeId,
                        'user_id' => $typeResource['id'] == 1? $resourceTaxed->id : null, //Id do usuário que ao ser adicionado, gerou o custo
                        'channel_id' => $typeResource['id'] != 1? $resourceTaxed->id : null, //Id do canal que ao ser adicionado, gerou o custo
                        'inv_unit_value_fee' => $feePerResource['fee_value'],
                        'inv_total_value_fee' => number_format($totalValueResourceFee, 2, '.', ''),
                    ]);
    
                    $requestInvoiceFeeCreateCount++;
                }
            }
            
        }
        //}

        //Log::debug('$requestInvoiceFeeCreate');
        //Log::debug($requestInvoiceFeeCreate);

        $calculationDataArray['invoice_total_value'] = number_format($totalInvoiceValue, 2, '.', ''); //Valor total da fatura
        $calculationDataArray['invoice_fees'] = $requestInvoiceFeeCreate; //Taxas associdas a fatura
        $calculationDataArray['invoice_opening'] = $initialDateInvoiceProcessingAux; //Data de abertura da fatura a ser gerada
        $calculationDataArray['invoice_closing'] = $closingDate; //Data de fechamento da fatura a ser gerada
        $calculationDataArray['invoice_due'] = $dueDateAux; //Data de vencimento da fatura a ser gerada
        $calculationDataArray['invoice_month_year'] = $MonthAndYearInvoice; //Competência da fatura (mês e ano)

        //Log::debug('$calculationDataArray');
        //Log::debug($calculationDataArray);
        
        return $calculationDataArray;
    }

    //Calcula o valor fatura com base nos recursos e período contratado
    public function invoiceCalculationAdvancePayment()
    {
        $parameterController = new ParameterController();
        $feeController = new FeeController();
        $planController = new PlanController();
        $totalInvoiceValue = 0;

        //Data inicial de processamento da fatura
        $initialDateInvoiceProcessing = null;
        $lastInvoice = self::getLastInvoice();
        //Se exista alguma fatura anterior gerada
        if($lastInvoice) {
            //Pega a data de fechamento da última fatura
            $dataClosingLastInvoice = Carbon::parse($lastInvoice['inv_closing']);
            //Onde a data de processamento da fatura atual começa no dia seguinte a data de fechamento da última fatura
            $initialDateInvoiceProcessing = $dataClosingLastInvoice->addDay();
            $monthOpening = $initialDateInvoiceProcessing->month;
            $initialDateInvoiceProcessingAux = $initialDateInvoiceProcessing->toDateTimeString();
             //Pega o mês subsequente ao vencimento da última fatura
             $nextMonthDue = Carbon::parse($lastInvoice['inv_closing'])->addDay()->addMonth();
             
        }
        else {
            //Traz a data que a empresa iniciou o uso do sistema
            $parameterData = $parameterController->getParameterByType(4);
            //Onde a data de início de processamento de fatura é igual a data de início de utilização do sistema por parte do cliente
            $initialDateInvoiceProcessing = Carbon::parse($parameterData['par_value']);
            $monthOpening = $initialDateInvoiceProcessing->month;
            $initialDateInvoiceProcessingAux = $initialDateInvoiceProcessing->toDateTimeString();
             //Pega o mês subsequente ao mês de início
            $nextMonthDue = $initialDateInvoiceProcessing->addMonthsNoOverflow(1);

        }

        Log::debug('$nextMonthDue');
        Log::debug($nextMonthDue);

        $yearDue = $nextMonthDue->year;
        $monthCompetency = $nextMonthDue->month;
        $MonthAndYearInvoice = $monthCompetency.'/'.$yearDue;

        $initialDateInvoiceProcessingSubDay = $initialDateInvoiceProcessing->subDay();
        $dayClosing = $initialDateInvoiceProcessingSubDay->day;
        
        Log::debug('$dayClosing');
        Log::debug($dayClosing);
        //Data de fechamento da fatura
        $closingDate = Carbon::createFromFormat('Y-m-d H:i:s', $yearDue.'-'.$monthCompetency.'-'.$dayClosing.' 23:59:59');
        Log::debug('$closingDate');
        Log::debug($closingDate);

        //Traz o dia do vencimento
        $parameterDateDue = $parameterController->getParameterByType(2);

        //Se o dia de vencimento é menor que o dia de fechamento da fatura
        if($parameterDateDue['par_value'] < $dayClosing) {
            $nextMonthDueAux = $nextMonthDue->toDateTimeString();
            //O mês de vencimento é o mês seguinte ao de fechamento
            $monthDue = Carbon::parse($nextMonthDueAux)->addMonthsNoOverflow(1)->month;
        }
        else {
            //O vencimento é no mesmo mês de fechamento
            $monthDue = $monthCompetency;
        }

        Log::debug('$monthDue');
        Log::debug($monthDue);

        $dueDate = Carbon::createFromFormat('Y-m-d H:i:s', $yearDue.'-'.$monthDue.'-'.$parameterDateDue['par_value'].' 00:00:00');
        //Pega a data atual
        $CurrentDataTime = Carbon::now()->startOfDay();
        //Se a data de vencimento padrão é maior ou igual a data atual
        if($dueDate->gte($CurrentDataTime)) {
            //Pega a data padrão como data de vencimento
            $dueDateAux = $dueDate->toDateTimeString();
        }
        else {
            //Senão, pega a data atual mais 3 dias como vencimento
            $dueDateAux = $CurrentDataTime->addDays(3)->toDateTimeString();
        }

        //traz a diferença de dias entre a data de vencimento e a data de fechamento da fatura
        //$parameterDateProcessing = $parameterController->getParameterByType(3);
        //calcula a data de fechamento com base na data de vencimento
        //$closingDate = Carbon::parse($initialDateInvoiceProcessingAux)->endOfMonth();
        //Traz o dia de vencimento, que é sempre o dia de abertura menos 1 dia

        $closingDate = $closingDate->startOfDay();
        //$closingDate = $dueDate->subDays($parameterDateProcessing['par_value']);
        //Pega o mês de fechamento
        $monthClosing = $closingDate->month;

        //Calcula a diferença de dias entre a data de fechamento e a data de fechamento da fatura
        $diffDaysProcessing = $closingDate->diffInDays($initialDateInvoiceProcessingAux);
        //Pega a diferença de dias entre as datas e soma mais 1 (que representa o dia de fechamento)
        
        $daysProcessing = $diffDaysProcessing + 1;
        Log::debug('$daysProcessing');
        Log::debug($daysProcessing);

        //Traz o valor da mensalidade fixa
        $fixedMonthlyFee = $feeController->getFeeByType(1);
        $requestInvoiceFeeCreateCount = 0;

        $feeMonthlyCharge = $parameterController->getParameterByType(6);
        //Se estiver habilitada a cobrança mensalldade
        if($feeMonthlyCharge->par_value == 1) {
            //Se for cobrança cheia
            $totalInvoiceValue += $fixedMonthlyFee['fee_value'];
            
            $requestInvoiceFeeCreate[$requestInvoiceFeeCreateCount] = new Request([
                //'invoice_id'   => ,
                'type_fee_id' => 1,
                //'inv_total_units' => 1, //Total de unidades da taxa
                'inv_unit_value_fee' => $fixedMonthlyFee['fee_value'],
                'inv_total_value_fee' => isset($proportionalMonthlyValue)? number_format($proportionalMonthlyValue, 2, '.', '') : $fixedMonthlyFee['fee_value'],
            ]);
            $requestInvoiceFeeCreateCount++;
        } //Se a cobrança da mensalidade NÃO estiver habilitada
        else {
            $totalInvoiceValue += 0.00;

            $requestInvoiceFeeCreate[$requestInvoiceFeeCreateCount] = new Request([
                //'invoice_id'   => ,
                'type_fee_id' => 1,
                //'inv_total_units' => 1, //Total de unidades da taxa
                'inv_unit_value_fee' => $fixedMonthlyFee['fee_value'],
                'inv_total_value_fee' => 0.00,
            ]);
            $requestInvoiceFeeCreateCount++;
        }
        
        //Traz os tipos de recursos de uma fatura
        //$typeResources = self::fetchTypeResources();
        //Para cada tipo de recurso a ser controlado
        //foreach($typeResources as $typeResource) {
        $typeResource['id'] = 1;
        $resourcesTaxed = self::getTotalResourcesTaxed($typeResource['id'], $initialDateInvoiceProcessingAux, $closingDate);
        Log::debug('$resourcesTaxed');
        Log::debug($resourcesTaxed);
        
        $typeFeeId = null;
        //Se existe algum usuário além do plano para ser taxado
        if(count($resourcesTaxed) > 0) {
            //Se o tipo de recurso for um usuário
            if($typeResource['id'] == 1) {
                $typeFeeId = 2;
                //Traz o valor da taxa por usuário
                $feePerResource = $feeController->getFeeByType($typeFeeId);
                $feeParameterCharge = $parameterController->getParameterByType(7);
            } //Se o tipo de recurso for um CANAL OFICIAL
            /*else if($typeResource['id'] == 2) {
                $typeFeeId = 3;
                $feePerResource = $feeController->getFeeByType($typeFeeId);
            } //Se o tipo de recurso for um CANAL NÃO OFICIAL
            else if($typeResource['id'] == 3) {
                $typeFeeId = 4;
                $feePerResource = $feeController->getFeeByType($typeFeeId);
            }*/
            
            $totalValueResourceFee = 0;
            //Se a cobrança do recurso estiver habilitada
            if($feeParameterCharge->par_value == 1) {
                foreach($resourcesTaxed as $resourceTaxed) {
                    //Se é um recurso a ser taxado proporcionalmente
                    if($feeParameterCharge->par_proportional_charge == 1 && $resourceTaxed->proportional_fee) {
                        //Calcula a diferença de dias entre a data de criação do usuário e a data de fechamento da fatura
                        $totalDaysToUserProcessing = $closingDate->diffInDays($resourceTaxed->created_at->format('Y-m-d'));
                        $totalDaysToUserProcessing += 1;
                        //Log::debug('$totalDaysToUserProcessing');
                        //Log::debug($totalDaysToUserProcessing);
                        $totalValueResourceFee = ($feePerResource['fee_value'] / 30) * $totalDaysToUserProcessing;
                    } //Se for cobrar o valor cheio do cliente
                    else {
                        $totalValueResourceFee = $feePerResource['fee_value'];
                    }
                    //Soma o valor da taxa do usuário ao valor total da fatura
                    $totalInvoiceValue += number_format($totalValueResourceFee, 2, '.', '');
                
                    $requestInvoiceFeeCreate[$requestInvoiceFeeCreateCount] = new Request([
                        //'invoice_id'   => ,
                        'type_fee_id' => $typeFeeId,
                        'user_id' => $typeResource['id'] == 1? $resourceTaxed->id : null, //Id do usuário que ao ser adicionado, gerou o custo
                        'channel_id' => $typeResource['id'] != 1? $resourceTaxed->id : null, //Id do canal que ao ser adicionado, gerou o custo
                        'inv_unit_value_fee' => $feePerResource['fee_value'],
                        'inv_total_value_fee' => number_format($totalValueResourceFee, 2, '.', ''),
                    ]);
    
                    $requestInvoiceFeeCreateCount++;
                }
            }
            
        }
        //}

        //Log::debug('$requestInvoiceFeeCreate');
        //Log::debug($requestInvoiceFeeCreate);

        $calculationDataArray['invoice_total_value'] = number_format($totalInvoiceValue, 2, '.', ''); //Valor total da fatura
        $calculationDataArray['invoice_fees'] = $requestInvoiceFeeCreate; //Taxas associdas a fatura
        $calculationDataArray['invoice_opening'] = $initialDateInvoiceProcessingAux; //Data de abertura da fatura a ser gerada
        $calculationDataArray['invoice_closing'] = $closingDate; //Data de fechamento da fatura a ser gerada
        $calculationDataArray['invoice_due'] = $dueDateAux; //Data de vencimento da fatura a ser gerada
        $calculationDataArray['invoice_month_year'] = $MonthAndYearInvoice; //Competência da fatura (mês e ano)

        //Log::debug('$calculationDataArray');
        //Log::debug($calculationDataArray);
        
        return $calculationDataArray;
    }

    //Associa as taxas a uma fatura
    public function createInvoiceFee(Request $request)
    {
        Log::debug('request createInvoiceFee');
        Log::debug($request);
        $invoiceFee = new InvoiceFee();
        $invoiceFee->invoice_id = $request['invoice_id'];
        $invoiceFee->type_fee_id = $request['type_fee_id'];
        $invoiceFee->user_id = $request['user_id'];
        $invoiceFee->channel_id = $request['channel_id'];
        $invoiceFee->inv_unit_value_fee = $request['inv_unit_value_fee'];
        $invoiceFee->inv_total_value_fee = $request['inv_total_value_fee'];
        //$invoiceFee->inv_status = $request['inv_status'];
        $invoiceFee->save();
    }

    //Traz o total de recursos além do plano que serão taxados
    public function getTotalResourcesTaxed($typeResourceId, $invOpening, $invClosing)
    {
        $planController = new PlanController();

        //Traz os usuários do sistema
        $resourcesControl = self::getInvoiceResourceByType($typeResourceId, $invOpening);

        //Traz os dados do plano contratado pelo cliente
        $plan = $planController->getPlanByType(1);
        
        $resourcesTaxedEnabled = [];
        $countResourceTaxedEnabled = 0;
        //Para cada usuário do sistema
        foreach($resourcesControl as $resourceControl) {
            //Se tipo de recurso é um USUÁRIO
            if($typeResourceId == 1) {
                //SELECIONA OS USUÁRIOS QUE ESTÁ HABILITADOS A RECEBEREM A COBRANÇA DA TAXA, SEPARANDO POR AQUELES SE SERÃO COBRADOS PROPORCIONALMENTE OU COBRANÇA CHEIA
                //Se o usuário foi criado após a data de abertura da fatura
                if($resourceControl['user']->created_at > $invOpening) {
                    $resourceControl['user']->proportional_fee = true;
                    $resourcesTaxedEnabled[$countResourceTaxedEnabled] = $resourceControl['user'];
                    $countResourceTaxedEnabled++;
                } //Se a data de criação do usuário for igual ou anterior a data de abertura da fatura
                else {
                    //Se o usuário foi criado antes ou no mesmo dia da abertura da fatura, não foi deletado ou foi deletado em uma data maior ou igual a data
                    // de abertura da fatura e menor ou igual a data de fechamento da fatura (ou seja, se o usuário foi deletado durante a vigência da fatura)
                    //if($resourceControl['user']->date_deleted == null || ($resourceControl['user']->date_deleted >= $invOpening && $resourceControl['user']->date_deleted <= $invClosing) ) {
                        $resourceControl['user']->proportional_fee = false;
                        $resourcesTaxedEnabled[$countResourceTaxedEnabled] = $resourceControl['user'];
                        $countResourceTaxedEnabled++;
                    //}
                }
            } //Se o tipo de recurso for um canal OFICIAL ou NÃO OFICIAL
            /*else if($typeResourceId == 2 || $typeResourceId == 3) {
                //SELECIONA OS USUÁRIOS QUE ESTÁ HABILITADOS A RECEBEREM A COBRANÇA DA TAXA, SEPARANDO POR AQUELES SE SERÃO COBRADOS PROPORCIONALMENTE OU COBRANÇA CHEIA
                //Se o usuário foi criado após a data de abertura da fatura
                if($resourceControl['channel']->created_at > $invOpening) {
                    $resourceControl['channel']->proportional_fee = true;
                    $resourcesTaxedEnabled[$countResourceTaxedEnabled] = $resourceControl['channel'];
                    $countResourceTaxedEnabled++;
                } //Se a data de criação do usuário for igual ou anterior a data de abertura da fatura
                else {
                    //Se o usuário foi criado antes ou no mesmo dia da abertura da fatura, não foi deletado ou foi deletado em uma data maior ou igual a data
                    // de abertura da fatura e menor ou igual a data de fechamento da fatura (ou seja, se o usuário foi deletado durante a vigência da fatura)
                    if($resourceControl['channel']->date_deleted == null || ($resourceControl['channel']->date_deleted >= $invOpening && $resourceControl['channel']->date_deleted <= $invClosing) ) {
                        $resourceControl['channel']->proportional_fee = false;
                        $resourcesTaxedEnabled[$countResourceTaxedEnabled] = $resourceControl['channel'];
                        $countResourceTaxedEnabled++;
                    }
                }
            }*/
            
        }

        $resourcesTaxed = [];
        if($typeResourceId == 1) {
            //Se existem usuários passíveis de taxação (Deixa apenas os usuários que extrapolam o plano)
            if($countResourceTaxedEnabled > $plan->pla_total_user) {
                $resourcesTaxed = array_slice($resourcesTaxedEnabled, $plan->pla_total_user);
            }
        }
        /*else if($typeResourceId == 2) {
            //Se existem usuários passíveis de taxação (Deixa apenas os usuários que extrapolam o plano)
            if($countResourceTaxedEnabled > $plan->pla_total_official_channel) {
                $resourcesTaxed = array_slice($resourcesTaxedEnabled, $plan->pla_total_official_channel);
            }
        }
        else if($typeResourceId == 3) {
            //Se existem usuários passíveis de taxação (Deixa apenas os usuários que extrapolam o plano)
            if($countResourceTaxedEnabled > $plan->pla_total_unofficial_channel) {
                $resourcesTaxed = array_slice($resourcesTaxedEnabled, $plan->pla_total_unofficial_channel);
            }
        }*/
        
        return $resourcesTaxed;
    }

    //Retorna o controle de recursos da fatura corrente
    public function getInvoiceResourceByType($typeResourceId, $invoiceOpening)
    {
        $invoiceResource = InvoiceResourceControl::with('user', 'channel')
                                                ->where('type_invoice_resource_id', $typeResourceId)
                                                ->where('inv_opening', $invoiceOpening)
                                                ->get();
        return $invoiceResource;
    }

    //Retorna um recurso da fatura pelo seu id
    public function getInvoiceResourceControl($invoiceResourceId)
    {
        $invoiceResource = InvoiceResourceControl::find($invoiceResourceId);
        
        return $invoiceResource;
    }

    //Para cada adulção de um recurso, controla a cota desses recursos
    public function storeInvoiceResourceControl($typeResourceId, $resource)
    {
        $parameterController = new ParameterController();
        $planController = new PlanController();
        $userController = new UserController();
        $channelController = new ChannelController();

        //Data inicial de processamento da fatura
        $initialDateInvoiceProcessing = null;
        $lastInvoice = self::getLastInvoice();
        //Se exista alguma fatura anterior gerada
        if($lastInvoice) {
            //Pega a data de fechamento da última fatura
            $dataClosingLastInvoice = Carbon::parse($lastInvoice['inv_closing']);
            //Onde a data de processamento da fatura atual começa no dia seguinte a data de fechamento da última fatura
            $initialDateInvoiceProcessing = $dataClosingLastInvoice->addDay();
        }
        else {
            //Traz a data que a empresa iniciou o uso do sistema
            $parameterData = $parameterController->getParameterByType(4);
            //Onde a data de início de processamento de fatura é igual a data de início de utilização do sistema por parte do cliente
            $initialDateInvoiceProcessing = Carbon::parse($parameterData['par_value']);
        }

        //Traz o controle de recurso
        $invoiceResourceControl = self::getInvoiceResourceByType($typeResourceId, $initialDateInvoiceProcessing);
        //Se o tipo de recurso e um usuário
        if($typeResourceId == 1) {
            //Traz o total de clientes ativos
            $totalUserActives = $userController->getCountUsersByStatus('A');
            //Se o usuário recém adicionado extrapolou a cota de usuários
            if(($totalUserActives > count($invoiceResourceControl))) {
                $invoiceResource = new InvoiceResourceControl();
                $invoiceResource->type_invoice_resource_id = $typeResourceId;
                $invoiceResource->inv_opening = $initialDateInvoiceProcessing;
                $invoiceResource->user_id = $resource->id; //Salva a cota inicial como sendo a cota padrão do plano do usuário
                $invoiceResource->save();

                //Envia um e-mail para o cliente notificando-o sobre a cobrança em relação ao novo recurso contratado
                self::sendCostMail($resource, $typeResourceId);
            }
        } //Se o tipo de recurso é um canal OFICIAL
        /*else if($typeResourceId == 2) {
            //Traz os canais oficiais presentes na plataforma (ativos e inativos, menos os deletados)
            $totalOfficialChannelsActives = $channelController->getCountAllChannelByOfficial(1);

            //Se o usuário recém adicionado extrapolou a cota de canais OFICIAIS
            if(($totalOfficialChannelsActives > count($invoiceResourceControl))) {
                $invoiceResource = new InvoiceResourceControl();
                $invoiceResource->type_invoice_resource_id = $typeResourceId;
                $invoiceResource->inv_opening = $initialDateInvoiceProcessing;
                $invoiceResource->channel_id = $resource->id; //Salva a cota inicial como sendo a cota padrão do plano do usuário
                $invoiceResource->save();

                //Envia um e-mail para o cliente notificando-o sobre a cobrança em relação ao novo recurso contratado
                self::sendCostMail($resource, $typeResourceId);
            }
        } //Se o tipo de recurso é um canal NÃO OFICIAL
        else if($typeResourceId == 3) {
            //Traz os canais NÃO oficiais presentes na plataforma (ativos e inativos, menos os deletados)
            $totalUnOfficialChannelsActives = $channelController->getCountAllChannelByOfficial(0);
            Log::debug('$totalUnOfficialChannelsActives');
            Log::debug($totalUnOfficialChannelsActives);
            Log::debug('$invoiceResourceControl');
            Log::debug($invoiceResourceControl);

            //Se o usuário recém adicionado extrapolou a cota de canais NÃO OFICIAIS
            if(($totalUnOfficialChannelsActives > count($invoiceResourceControl))) {
                $invoiceResource = new InvoiceResourceControl();
                $invoiceResource->type_invoice_resource_id = $typeResourceId;
                $invoiceResource->inv_opening = $initialDateInvoiceProcessing;
                $invoiceResource->channel_id = $resource->id; //Salva a cota inicial como sendo a cota padrão do plano do usuário
                $invoiceResource->save();

                //Envia um e-mail para o cliente notificando-o sobre a cobrança em relação ao novo recurso contratado
                self::sendCostMail($resource, $typeResourceId);
            }
        }*/
        
        
    }

    //Envia um e-mail notificando o cliente sobre o aumento da cota do recurso e, consequentemente, a sua cobrança
    public function sendCostMail($resourceData, $typeResourceId)
    {   
        $subject = '';
        $resourceDescription = '';
        $customerName = '';

        $customerController = new CustomerController();
        //Traz os dados do cliente
        $customerData = $customerController->getCustomer();
        $customerName = isset($customerData[0]->com_name)? $customerData[0]->com_name : '';

        //Se o recurso contratado for um USUÁRIO
        if($typeResourceId == 1) {
            $subject = "Novo Usuário Contratado";
            $resourceDescription = $resourceData->name .' ('.$resourceData->email.')';
        }//Se o recurso contratado for um CANAL OFICIAL
        /*else if($typeResourceId == 2) {
            $subject = "Novo Canal Oficial Contratado";
            $resourceDescription = $resourceData->cha_name .' ('.$resourceData->cha_phone_number.')';
        }//Se o recurso contratado for um CANAL NÃO OFICIAL
        else if($typeResourceId == 3) {
            $subject = "Novo Canal Não Oficial Contratado";
            $resourceDescription = $resourceData->cha_name .' ('.$resourceData->cha_phone_number.')';
        }*/
        //Traz os contatos do tipo e-mail e onde o assunto é o financeiro
        $customerContacts = $customerController->getContactsNotificationByTypeAndSubject(1, 1);
        //Para cada contato cadastrado pelo cliente
        foreach($customerContacts as $contact) {
            Mail::to($contact->cus_contact_value)
                ->send(new NotifyCost($customerName, $resourceDescription, $typeResourceId, $subject));
        }
        
    }

    //Salva um controle de recurso para um determinado período de cobrança
    public function storeInvoiceResource()
    {
        $userController = new UserController();
        $channelController = new ChannelController();

        //Data inicial de processamento da fatura
        $invoiceOpening = null;
        $lastInvoice = self::getLastInvoice();
        //Pega a data de fechamento da última fatura
        $dataClosingLastInvoice = Carbon::parse($lastInvoice['inv_closing']);
        //Onde a data de processamento da fatura atual começa no dia seguinte a data de fechamento da última fatura
        $invoiceOpening = $dataClosingLastInvoice->addDay();


        $typeResources = self::fetchTypeResources();
        //Para cada tipo de recurso a ser controlado
        foreach($typeResources as $typeResource) {
            //Se o tipo de recurso for um usuário
            if($typeResource->id == 1) {
                //Traz a quantidade de usuários ativos no sistema
                $totalUserActives = $userController->getUsersByStatus('A');
                //Para cada usuário ativo no sistema
                foreach($totalUserActives as $userActive) {
                    //Adiciona o usuário no controle de recursos da fatura
                    $invoiceResource = new InvoiceResourceControl();
                    $invoiceResource->type_invoice_resource_id = $typeResource->id;
                    $invoiceResource->inv_opening = $invoiceOpening;
                    $invoiceResource->user_id = $userActive->id; 
                    $invoiceResource->save();
               }
            }
            //Se o tipo de recurso for um Canal OFICIAL
            /*else if($typeResource->id == 2) {
                //Traz a quantidade de usuários ativos no sistema
                $totalOfficialChannelsActives = $channelController->getChannelsByOfficial(1);
                //Para cada usuário ativo no sistema
                foreach($totalOfficialChannelsActives as $officialChannelActive) {
                    //Adiciona o usuário no controle de recursos da fatura
                    $invoiceResource = new InvoiceResourceControl();
                    $invoiceResource->type_invoice_resource_id = $typeResource->id;
                    $invoiceResource->inv_opening = $invoiceOpening;
                    $invoiceResource->channel_id = $officialChannelActive->id; 
                    $invoiceResource->save();
               }
            }
            //Se o tipo de recurso for um Canal NÃO OFICIAL
            else if($typeResource->id == 3) {
                //Traz a quantidade de usuários ativos no sistema
                $totalUnofficialChannelsActives = $channelController->getChannelsByOfficial(0);
                //Para cada usuário ativo no sistema
                foreach($totalUnofficialChannelsActives as $unofficialChannelActive) {
                    //Adiciona o usuário no controle de recursos da fatura
                    $invoiceResource = new InvoiceResourceControl();
                    $invoiceResource->type_invoice_resource_id = $typeResource->id;
                    $invoiceResource->inv_opening = $invoiceOpening;
                    $invoiceResource->channel_id = $unofficialChannelActive->id; 
                    $invoiceResource->save();
               }
            }*/
        }
    }

    //Traz os tipos de recursos de uma fatura
    public function fetchTypeResources()
    {
        $typeResources = TypeInvoiceResource::where('typ_status', 'A')->get();

        return $typeResources;
    }

    //Traz a total de recursos de uma fatura pelo seu tipo e data de abertura
    public function getTotalInvoiceResourceByTypeAndOpening($typeResourceId, $invoiceOpening)
    {   //Se não foi informada a data de abertura da fatura
        if(!$invoiceOpening) {
            //Pega a data de abertura da fatura atual
            $invoiceOpening = self::getCurrentInvoiceOpening();
        }
        
        $totalInvoiceResource = InvoiceResourceControl::where('type_invoice_resource_id', $typeResourceId)
                                                ->where('inv_opening', $invoiceOpening)
                                                ->count();
        return $totalInvoiceResource;
    }

    public function getCurrentQuotaResource($typeResourceId)
    {
        $planController = new PlanController();
        $totalResource = self::getTotalInvoiceResourceByTypeAndOpening($typeResourceId, null);

        //Traz os dados do plano contratado pelo usuário
        $plan = $planController->getPlanByType(1);

        //Se o tipo de recurso for um usuário
        if($typeResourceId == 1) {
            //Se o cliente ainda não ultrapassou a cota do plano para usuários, considera a cota do plano como a cota total, se não, 
            //considera o total de recursos contratados como a nova cota
            $totalCurrentQuotaResource = $plan->pla_total_user >= $totalResource? $plan->pla_total_user : $totalResource;
        } //Se for um canal oficial
        /*else if($typeResourceId == 2) {
            //Se o cliente ainda não ultrapassou a cota do plano para canais OFICIAIS, considera a cota do plano como a cota total, se não, 
            //considera o total de recursos contratados como a nova cota
            $totalCurrentQuotaResource = $plan->pla_total_official_channel  >= $totalResource? $plan->pla_total_official_channel : $totalResource;
        }
        else if($typeResourceId == 3) {
            //Se o cliente ainda não ultrapassou a cota do plano para canais NÃO OFICIAIS, considera a cota do plano como a cota total, se não, 
            //considera o total de recursos contratados como a nova cota
            $totalCurrentQuotaResource = $plan->pla_total_unofficial_channel  >= $totalResource? $plan->pla_total_unofficial_channel : $totalResource;
        }*/

        return $totalCurrentQuotaResource;
    }

    public function getCurrentInvoiceOpening()
    {
        $parameterController = new ParameterController();

        //Data inicial de processamento da fatura
        $invoiceOpening = null;
        $lastInvoice = self::getLastInvoice();
        
        //Se exista alguma fatura anterior gerada
        if($lastInvoice) {
            //Pega a data de fechamento da última fatura
            $dataClosingLastInvoice = Carbon::parse($lastInvoice['inv_closing']);
            //Onde a data de processamento da fatura atual começa no dia seguinte a data de fechamento da última fatura
            $invoiceOpening = $dataClosingLastInvoice->addDay();
        }
        else {
            //Traz a data que a empresa iniciou o uso do sistema
            $parameterData = $parameterController->getParameterByType(4);
            //Onde a data de início de processamento de fatura é igual a data de início de utilização do sistema por parte do cliente
            $invoiceOpening = Carbon::parse($parameterData['par_value']);
        }

        return $invoiceOpening;
    }

    //Atualiza o status de pagamento
    public function updateStatusPaymentChargeApiId($paymentId, $statusPayment, $billingType)
    {
        $charge = Invoice::where('api_payment_invoice_id', $paymentId)->first();
        //Se for o status de pagamento de alguma fatura
        if($charge) {
            $charge->status_id = $statusPayment;
            $charge->save();
        }
        else {
            $charge = Credit::where('api_payment_credit_id', $paymentId)->first();
            //Se for o status de credito inserido na plataforma
            if($charge) {
                $charge->status_id = $statusPayment;
                $charge->save();
            }
        }
    }

    public function fetchStatusPayments()
    {
        $statusPayments = StatusPayment::where('sta_status', 'A')
                                        ->get();
        
        return response()->json([
            'statusPayments' => $statusPayments
        ], 200);
    }


    //Traz a quantidade de faturas vencidas
    public function getCountOverdueBills()
    {
        $overdue = Invoice::whereIn('status_id', [1, 4])
                                ->where('inv_due', '<', Carbon::now()->startOfDay())
                                ->count();
        
        //Log::debug('$overdue data');
        //Log::debug($overdue);
        return response()->json([
            'overdue' => $overdue
        ], 200);
    }

    //Traz todas as faturas vencidas
    public function getOverdueBills()
    {
        $invoicesOverdue = Invoice::whereIn('status_id', [1, 4])
                                ->where('inv_due', '<', Carbon::now()->startOfDay())
                                ->get();
        
        return $invoicesOverdue;
    }

    //Traz o valor total somando todas as faturas vencidas
    public function getTotalOverdueAmount()
    {
        $feeController = new FeeController();
        //Traz as faturas vencidas
        $invoicesOverdue = self::getOverdueBills();
        $totalOverdueAmount = 0.0;

        //Para cada fatura vencida
        foreach($invoicesOverdue AS $invoice) {
            //Traz as taxas de uma fatura
            $fees = $feeController->getFeeByInvoice($invoice->id);

            //Para cada taxa associada a uma fatura
            foreach($fees AS $fee) {
                //Soma essas taxas
                $totalOverdueAmount = $totalOverdueAmount + $fee->inv_total_value_fee;
            }
        }
        
        return  $totalOverdueAmount;
    }

    //Traz as faturas de acordo com um ou mais status
    public function getInvoicesByStatus(Request $request)
    {
        Log::debug('getInvoicesByStatus invoiceData');
        Log::debug($request);
        $invoices = Invoice::whereIn('status_id', $request['invoiceData'])
                            ->get();

        return response()->json([
            'invoices' => $invoices
        ], 200);
    }

    //Traz todas as faturas
    public function fetchAllInvoices()
    {
        $invoices = Invoice::with('invoiceFees')
                            ->get();

        return $invoices;
    }
}
