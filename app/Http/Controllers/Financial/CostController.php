<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\Mailing;
use App\Models\Financial\BlockedBalance;
use App\Models\Financial\Cost;
use App\Models\Financial\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use DB;


class CostController extends Controller
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

        $costs = Cost::with('contactMailing', 'typeCost', 'contactMailing.campaign', 'contactMailing.contact',
                            'contactMailing.channel')
                    //Busca os contatos de acordo com a paginação
                    ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']); //Quantidade de itens trazidos
        if($request['period']) {
            $periodDivided = explode('até', $request['period']);
            //Se foi selecionada apenas a data inicial
            if(count($periodDivided) == 1) {
                $costs = $costs->whereBetween('created_at', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
            }//Se foi selecionada a data inicial e final
            else {
                $costs = $costs->whereBetween('created_at', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
            }

            $costs = $costs->orderBy('id', 'DESC');
            $costs = $costs->get();
            $total = self::getTotalCostsFiltered($request);
        }
        else {
            $costs = $costs->orderBy('id', 'DESC');
            $costs = $costs->get();
            
            //Pega o total de créditos de acordo com a busca
            $total = Cost::count();
        }
                
        return response()->json([
            'costs'=> $costs,
            'total'=> $total,
        ], 200);
    }

    //Pega o total de custo quando um filtro é aplicado
    public function getTotalCostsFiltered($request)
    {
        $costs = new Cost();
        $periodDivided = explode('até', $request['period']);
            //Se foi selecionada apenas a data inicial
            if(count($periodDivided) == 1) {
                $costs = $costs->whereBetween('created_at', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
            }//Se foi selecionada a data inicial e final
            else {
                $costs = $costs->whereBetween('created_at', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
            }
            $costs = $costs->count();

            return $costs;
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
        $feeController = new FeeController();
        //Se o custo for de envio de uma mensagem via WhatsApp
        if($request['typeCostId'] == 1) {
            //Pega a taxa de envio por WhatsApp
            $fee = $feeController->getFeeByType(5);
        }//Se o custo for de envio de uma mensagem via SMS
        else if($request['typeCostId'] == 2) {
            //Pega a taxa de envio por WhatsApp
            $fee = $feeController->getFeeByType(6);
        }//Se o custo for de retorno de uma mensagem via SMS
        else if($request['typeCostId'] == 3) {
            //Pega a taxa de envio por WhatsApp
            $fee = $feeController->getFeeByType(7);
        }//Se o custo for de ligação via WhatsApp
        else if($request['typeCostId'] == 4) {
            //Pega a taxa de Ligação via WhatsApp
            $fee = $feeController->getFeeByType(8);
        }
        $parameter = new Cost();
        $parameter->type_cost_id = $request['typeCostId'];
        $parameter->mailing_id = $request['mailingId'];
        $parameter->cos_value = $fee['fee_value'];
        $parameter->cos_status = isset($fee['cosStatus'])? $fee['cosStatus'] : 'A';
        $parameter->save();
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

    //Traz o total de custos do cliente na plataforma
    public function getTotalCosts()
    {
        $totalCosts =  Cost::select(DB::raw("SUM(cos_value) as total_costs"))
                            ->where('cos_status', 'A')
                            ->first();
        
        return $totalCosts;
    }
    
    //Traz o custo estimado de uma campanha, considerando 
    public function getEstimateCostCampaign($campaignId)
    {
        $feeController = new FeeController();
        //Total de contatos em um mailing de uma campanha
        $totalMailingCampaign = Mailing::where('campaign_id', $campaignId)
                                        ->count();
        
        $fee = $feeController->getFeeByType(8);

        $estimateCost = $totalMailingCampaign * $fee->fee_value;

        return $estimateCost;
    }

    //Traz o valor total de saldo bloqueado
    public function getTotalBlockedBalance()
    {
        $totalBlockedBalance = BlockedBalance::select(DB::raw("SUM(blo_value) as total_blocked_balance"))
                                            ->where('blo_status', 'A')
                                            ->first();

        return $totalBlockedBalance;
    }

    //Salva um bloqueio de saldo
    public function storeBlockedBalance($campaignId)
    {
        $estimateCost = self::getEstimateCostCampaign($campaignId);

        $blockedBalance = new BlockedBalance();
        $blockedBalance->campaign_id = $campaignId;
        $blockedBalance->blo_value = $estimateCost;
        $blockedBalance->blo_status = 'A';
        $blockedBalance->save();
    }

    //Atualiza o status do bloqueio de saldo
    public function updateBlockedBalanceStatus($campaignId, $statusId)
    {
        $blockedBalance = BlockedBalance::where('campaign_id', $campaignId)
                                        ->where('blo_status', 'A')
                                        ->first();
        
        $blockedBalance->blo_status = $statusId;
        $blockedBalance->save();
    }

    //Atualiza o status de todos os custos de uma campanha
    public function updateStatusAllCostsCampaign($campaignId, $statusId)
    {
        Cost::join('cam_mailings', 'fin_costs.mailing_id', 'cam_mailings.id')
            ->join('cam_campaigns', 'cam_mailings.campaign_id', 'cam_campaigns.id')
            ->where('campaign_id', $campaignId)
            ->update([
                'cos_status' => $statusId
            ]);
    }
}
