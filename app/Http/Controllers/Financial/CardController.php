<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Models\Financial\Card;
use App\Models\Financial\CardHolderInfo;
use App\Models\Financial\Credit;
use App\Models\Financial\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use DB;


class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cards = Card::with('holderInfo')
                    ->select('fin_cards.id', 'type_card_id', 'car_holder_name AS cardName', 'car_number AS cardNumber', 'car_due_month AS cardMonth', 
                            'car_due_year AS cardYear', 'car_main', 'fin_cards.created_at')
                    ->where('car_status', 'A')
                    ->get();

        return response()->json([
            'cards'=> $cards,
            'total'=> count($cards),
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
    //Processa uma inserção de crédito de campanha
    public function store(Request $request)
    {
        Log::debug('store');
        Log::debug($request);
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
            $cardHolderInfo->car_name = $request->cardData['holder_info']['car_name'];
            $cardHolderInfo->car_email = $request->cardData['holder_info']['car_email'];
            $cardHolderInfo->car_cpf = $request->cardData['corporateCard'] == '0'? preg_replace("/[^0-9]/", "", $request->cardData['holder_info']['car_cpf']) : null;
            $cardHolderInfo->car_cnpj = $request->cardData['corporateCard'] == '1'? preg_replace("/[^0-9]/", "", $request->cardData['holder_info']['car_cnpj']): null;
            $cardHolderInfo->car_postal_code = preg_replace("/[^0-9]/", "", $request->cardData['holder_info']['car_postal_code']);
            $cardHolderInfo->car_address_number = $request->cardData['holder_info']['car_address_number'];
            $cardHolderInfo->car_phone = preg_replace("/[^0-9]/", "", $request->cardData['holder_info']['car_phone']);

            $cardHolderInfo->save();
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
        $channelController = new ChannelController();
        //Inativa o cartão
        $card = Card::find($id);
        $card->car_status = 'I';
        //Caso o cartão tenha sido excluído
        if($card->save()) {
            //Desabilita a assinatura automática associada ao cartão
            $channelController->updateChannelSubscriptionStatus(null, $id, 'I');
        }

        return response()->json([
            
        ], 200);
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

    //Atualiza as informações do titular do cartão
    public function updateHolderInfo(Request $request)
    {
        //Se o cartão foi definido como principal   
        if($request['car_main']) {
            //Pega o cartão definido como principal anteriormente e o remove dessa condição
            self::clearMainCard();
            //Pega o cartão que está sendo atualizado e o coloca como principal
            $card = Card::find($request['id']);
            $card->car_main = $request['car_main'];
            $card->save();
        }
        
        //Atualiza os dados do titular do cartão
        $cardHolderInfo = CardHolderInfo::find($request['holder_info']['id']);
        $cardHolderInfo->car_name = $request['holder_info']['car_name'];
        $cardHolderInfo->car_email = $request['holder_info']['car_email'];
        $cardHolderInfo->car_cpf = $request['corporateCard'] == '0'? preg_replace("/[^0-9]/", "", $request['holder_info']['car_cpf']) : null;
        $cardHolderInfo->car_cnpj = $request['corporateCard'] == '1'? preg_replace("/[^0-9]/", "", $request['holder_info']['car_cnpj']): null;
        $cardHolderInfo->car_postal_code = preg_replace("/[^0-9]/", "", $request['holder_info']['car_postal_code']);
        $cardHolderInfo->car_address_number = $request['holder_info']['car_address_number'];
        $cardHolderInfo->car_phone = preg_replace("/[^0-9]/", "", $request['holder_info']['car_phone']);
        $cardHolderInfo->save();

        return response()->json([
            
        ], 200);
    }

    //Retorna o cartão principal
    public function getMainCard()
    {
        $card = Card::with('holderInfo')->where('car_main', 1)->first();

        return $card;
    }

    //Atualiza o token do cartão
    public function updateTokenCard($cardId, $token)
    {
        $card = Card::find($cardId);
        $card->car_token = $token;
        $card->save();
    }
}
