<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Integration\Voip\Voip;
use App\Models\Integration\Voip\VoipSetting;
use App\Models\Management\Call\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Management\Extension\Extension;

class VoipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $voips = Voip::where('voi_status', '!=','D')
                    ->get();

        return response()->json([
            'voips' => $voips,
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
        try{
            Log::debug('dados ligação');
            Log::debug($request);
            $newcall = new Call();
            $newcall->user_id = $request['userId'];
            $newcall->contact_id = $request['contactId'];
            $newcall->service_id = $request['serviceId'];
            $newcall->extension_id = $request['extensionId'];
            $newcall->cal_phone_contact = $request['calPhoneContact'];
            $newcall->cal_record_name = $request['calRecordName'];
            $newcall->cal_call_time = $request['calCallTime'];
            $newcall->cal_call_made = $request['calCallMade'];
            $newcall->cal_call_date = $request['calCallDate'];
            $newcall->save();

            return response()->json([
                ''
            ], 200);

        } catch (e) {

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
        try {
            

        } catch (e) {

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
        try {
            
        } catch(e) {

        }
    }


    public function fetchVoips(Request $request)
    {   
        $skip = (($request['page']-1) * $request['perPage']);

        $voips = Voip::with('setting')
                    ->where('voi_status', '!=','D');
        $total = $voips->count();
        $voips = $voips->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();

        return response()->json([
            'voips' => $voips,
            'total' => $total,
        ], 200);
    }

    public function updateAuthenticationVoip(Request $request)
    {
        $isVoipSetting = VoipSetting::where('voip_id', $request['id'])->first();
        //Se já houver configuração de voip para a referida empresa
        if($isVoipSetting) {
            $isVoipSetting->voi_user = $request['setting']['voi_user'];
            $isVoipSetting->voi_secret = $request['setting']['voi_secret'];
            $isVoipSetting->save();
        }
        else {
           $newVoipSetting = new VoipSetting();
           $newVoipSetting->voip_id = $request['id'];
           $newVoipSetting->voi_user = $request['setting']['voi_user'];
           $newVoipSetting->voi_secret = $request['setting']['voi_secret'];
           $newVoipSetting->save();
        }

        return response()->json([
            'setting' => $request['setting'],
        ], 200);
    }

    //Atualiza o chatbot com um determinado status (Rodando ou Pausada)
    public function updateStatusVoip(Request $request)
    {
        $voip = Voip::find($request['voipId']);

        $voip->voi_status = $request['statusId'];
        $voip->save();

        return response()->json([
            'statusId' => $request['statusId']
        ], 200);
    }

    //Retorna uma empresa voip por seu id
    public function getVoip($voipId)
    {
        $voip = Voip::with('setting')->find($voipId);

        return $voip;
    }

}
