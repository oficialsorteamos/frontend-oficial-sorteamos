<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Api\System\ApiSystemController;
use App\Http\Controllers\Controller;
use App\Models\Setting\CustomerWhiteLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Setting\Plan;

class WhiteLabelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $whiteLabel = CustomerWhiteLabel::first();

        return response()->json([
            'whiteLabel' => $whiteLabel,
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
        Log::debug(' update $request');
        Log::debug($request);
        $apiSystemController = new ApiSystemController();

        //Se os dados do White Label foram preenchidos
        if($request['whi_document_number']) {
            $hasWhiteLabel = $apiSystemController->getPartnerByDocumentId(env('URL_MANAGEMENT_SERVER'), $request['whi_document_number']);
            Log::debug('$hasWhiteLabel update');
            Log::debug($hasWhiteLabel);
        }

        //Se o número (CNPJ ou CPF) que identifica um white label foi preenchido e existe um parceiro com esse número ou se esse número foi deixado em branco 
        if(($request['whi_document_number'] && $hasWhiteLabel['partner']) || !$request['whi_document_number']) {
            
            $whiteLabel = CustomerWhiteLabel::first();
            $whiteLabel->whi_name = isset($hasWhiteLabel['partner']['par_corporate_name'])? $hasWhiteLabel['partner']['par_corporate_name'] : null;
            $whiteLabel->whi_document_number = $request['whi_document_number']? preg_replace( '/[^0-9]/', '', $request['whi_document_number']) : null;
            $whiteLabel->whi_url = isset($hasWhiteLabel['partner']['par_url'])? $hasWhiteLabel['partner']['par_url'] : null;
            $whiteLabel->save();

            return response()->json([
                'whiteLabel' => $whiteLabel,
                'success' => true,
            ], 200);
        }
        else {
            return response()->json([
                'success' => false,
            ], 200);
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

    //Retorna os dados do White Label, caso houver
    public function getWhiteLabel()
    {
        $whiteLabel = CustomerWhiteLabel::first();

        return $whiteLabel;
    }
}
