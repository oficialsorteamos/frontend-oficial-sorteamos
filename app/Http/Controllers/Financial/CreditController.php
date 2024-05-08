<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Controller;
use App\Models\Financial\Card;
use App\Models\Financial\CardHolderInfo;
use App\Models\Financial\Credit;
use App\Models\Financial\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use DB;


class CreditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($request['page']-1) * $request['perPage']);
        

        $credits = Credit::with('status', 'paymentMethod', 'card')
                        //Busca os contatos de acordo com a paginação
                        ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                        ->take($request['perPage']); //Quantidade de itens trazidos
        if($request['period']) {
            $periodDivided = explode('até', $request['period']);
            //Se foi selecionada apenas a data inicial
            if(count($periodDivided) == 1) {
                $credits = $credits->whereBetween('created_at', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
            }//Se foi selecionada a data inicial e final
            else {
                $credits = $credits->whereBetween('created_at', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
            }

            $credits = $credits->orderBy('id', 'DESC');
            $credits = $credits->get();
            $total = self::getTotalCreditsFiltered($request);
        }
        else {
            $credits = $credits->orderBy('id', 'DESC');
            $credits = $credits->get();
            //Pega o total de contatos de acordo com a busca
            $total = Credit::count();
        }


        $balance = self::getTotalBalance();

        return response()->json([
            'credits'=> $credits,
            'total'=> $total,
            'balance'=> $balance,
        ], 200);
    }

    //Pega o total de crédito quando um filtro é aplicado
    public function getTotalCreditsFiltered($request)
    {
        $credits = new Credit();
        $periodDivided = explode('até', $request['period']);
            //Se foi selecionada apenas a data inicial
            if(count($periodDivided) == 1) {
                $credits = $credits->whereBetween('created_at', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
            }//Se foi selecionada a data inicial e final
            else {
                $credits = $credits->whereBetween('created_at', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
            }
            $credits = $credits->count();

            return $credits;
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
    //Processa uma inserção de crédito de campanha
    public function store(Request $request)
    {
        try{
            $paymentController = new PaymentController();
            $paymentMethodData = [];
            $statusPaymentId = null;
            $errorMessage = null;

            //Se o método de pagamento for Cartão de crédito ou débito
            if($request->creditData['payment_method']['id'] == 1 || $request->creditData['payment_method']['id'] == 2) {
                $paymentMethodData = self::getCard($request->creditData['credit_card']['data']['id']);
            }
            $paymentData['paymentMethodData'] = $paymentMethodData;
            $paymentData['generalData'] = $request->creditData;
            
            $paymentData = $paymentController->insertCredit($paymentData);

            //Se foi gerado o token do cartão
            if(isset($paymentData['creditCard']['creditCardToken'])) {
                //Se o cartão ainda não tem um token associado
                if($paymentMethodData->car_token == null || $paymentMethodData->car_token == '') {
                    $paymentMethodData->car_token = $paymentData['creditCard']['creditCardToken'];
                    $paymentMethodData->save();
                }
            }
            Log::debug('$paymentData');
            Log::debug($paymentData);
            //Se existe algum status de pagamento
            if(isset($paymentData['status'])) {
                //Traz o id do status de pagamento
                $statusPaymentId = $paymentController->getStatusPaymentId($paymentData['status']);
            }
            else {
                //Se houve um erro ao processar o pagamento
                if(isset($paymentData['errors'])) {
                    $errorMessage =  $paymentData['errors'][0]['description'];
                }
            }
            
            if($statusPaymentId) {
                $newCredit = new Credit();
                $newCredit->api_payment_credit_id = $paymentData['id'];
                $newCredit->card_id = $request->creditData['credit_card']['data']['id'];
                $newCredit->payment_method_id = $request->creditData['payment_method']['id'];
                $newCredit->user_id = Auth::user()->id;
                $newCredit->url_external_checkout = $paymentData['invoiceUrl'];
                $newCredit->cre_due = $paymentData['dueDate'];
                $newCredit->cre_value = $paymentData['value'];
                $newCredit->status_id = $statusPaymentId? $statusPaymentId : 1; //Caso não tenha status de pagamento, deixa como aguardando pagamento
                $newCredit->save();
            }

            return response()->json([
                'errorMessage'=> $errorMessage,
            ], 200);

        }catch(e) {

        }
       
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

    public function fetchPaymentMethods()
    {
        //Traz os métodos de pagamento disponíveis
        $paymentMethods = PaymentMethod::where('pay_status', 'A')->get();

        return response()->json([
            'paymentMethods'=> $paymentMethods,
        ], 200);
    }

    public function storeCard(Request $request)
    {
        $mainCard = null;
        //Se o usuário marcou o cartão como principal
        if($request->cardData['mainCard'] == true) {
            self::clearMainCard();
            $mainCard = 1;
        }
        else {
            //Traz o total de cartões ativos cadastrados
            $totalCards = self::getTotalCards();
            //Se não existe cartões ativos ou cadastrados
            if($totalCards == 0) {
                //Coloca o cartão ser cadastrado como principal
                $mainCard = 1;
            }
        }

        $card = new Card();

        $card->type_card_id = 1; //Seta como CARTÃO DE CRÉDITO (ALTERAR DEPOIS PARA TAMBÉM PEGAR O CARTÃO DE DÉBITO)
        $card->car_main = $mainCard;
        $card->car_holder_name = $request->cardData['cardName'];
        $card->car_number = preg_replace("/[^0-9]/", "", $request->cardData['cardNumber']);
        $card->car_due_month = $request->cardData['cardMonth'];
        $card->car_due_year = $request->cardData['cardYear'];
        
        //Se o cartão foi salvo
        if($card->save()) {
            $cardHolderInfo = new CardHolderInfo();
            $cardHolderInfo->card_id = $card->id;
            $cardHolderInfo->car_name = $request->cardData['holderFullName'];
            $cardHolderInfo->car_email = $request->cardData['holderEmail'];
            $cardHolderInfo->car_cpf = $request->cardData['corporateCard'] == '0'? preg_replace("/[^0-9]/", "", $request->cardData['cpf']) : null;
            $cardHolderInfo->car_cnpj = $request->cardData['corporateCard'] == '1'? preg_replace("/[^0-9]/", "", $request->cardData['cnpj']): null;
            $cardHolderInfo->car_postal_code = preg_replace("/[^0-9]/", "", $request->cardData['postalCode']);
            $cardHolderInfo->car_address_number = $request->cardData['addressNumber'];
            $cardHolderInfo->car_phone = preg_replace("/[^0-9]/", "", $request->cardData['phoneNumber']);

            $cardHolderInfo->save();
        }
    }

    //Coloca todos os cartões como NÃO principais
    public function clearMainCard()
    {   //Pega o cartão está como principal e o retira dessa condição
        Card::whereNotNull('car_main')->update([
            'car_main' => null
        ]);
    }

    //Traz todos os cartões cadastrados
    public function getTotalCards()
    {
        $totalCards = Card::where('car_status', 'A')->count();

        return $totalCards;
    }

    //Traz os cartões pelo seu tipo (Crédito ou Débito)
    public function fetchCardsByType($typeCardId)
    {
        $cards = Card::where('type_card_id', $typeCardId)
                    ->where('car_status', 'A')
                    ->get();
        
        return response()->json([
            'cards'=> $cards,
        ], 200);
    }

    public function getCard($cardId)
    {
        $card = Card::with('holderInfo')->where('id', $cardId)->first();

        return $card;
    }

    //Traz o total de credito do cliente na plataforma
    public function getTotalCredits()
    {
        $totalCredits =  Credit::select(DB::raw("SUM(cre_value) as total_credits"))
                            ->where('status_id', 2) //Status igual a PAGO
                            ->first();
        
        return $totalCredits;
    }

    //Retorna o saldo total do cliente na plataforma
    public function getTotalBalance()
    {
        $costController = new CostController();
        $balance = 0;
        
        $cost = $costController->getTotalCosts();
        $blockedBalance = $costController->getTotalBlockedBalance();
        $credit = self::getTotalCredits();
        $balance = $credit->total_credits - ($cost->total_costs + $blockedBalance->total_blocked_balance);
        
        return $balance;
    }
}
