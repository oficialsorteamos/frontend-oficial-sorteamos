<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Api\Dialers\IpBoxController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Models\Integration\Dialer\Dialer;
use App\Models\Integration\Dialer\FowardingSetting;
use App\Models\Integration\Voip\Voip;
use App\Models\Integration\Voip\VoipSetting;
use App\Models\Management\Call\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DialerController extends Controller
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


    public function fetchDialers(Request $request)
    {   
        $skip = (($request['page']-1) * $request['perPage']);

        $dialers = Dialer::with('fowardingSettings.channel', 'fowardingSettings.chatbot', 'fowardingSettings.department', 
                                'fowardingSettings.template', 'fowardingSettings.fairDistribution')
                    ->where('dia_status', '!=','D');
        $total = $dialers->count();
        $dialers = $dialers->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();

        return response()->json([
            'dialers' => $dialers,
            'total' => $total,
        ], 200);
    }

    public function fetchFowardingSettings(Request $request)
    {
        $skip = (($request['page']-1) * $request['perPage']);

        $fowardingSettings = FowardingSetting::with('channel', 'chatbot', 'department', 'template', 'fairDistribution')
                    ->where('dialer_id', $request['dialerId'])
                    ->where('dia_status', 'A');
        $total = $fowardingSettings->count();
        $fowardingSettings = $fowardingSettings->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();
        
        $baseUrlStorage = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC');

        return response()->json([
            'fowardingSettings' => $fowardingSettings,
            'total' => $total,
            'baseUrlStorage' => $baseUrlStorage,
        ], 200);
    }


    //Traz os canais e verifica se eles lá possuem configuração de encaminhamento
    public function fetchChannelsFowarding($dialerId)
    {
        $channelController = new ChannelController();
        //Traz os canais ativos
        $channelsFowarding = $channelController->getChannelsByStatus('A');
        //$channelsFowarding = $channelController->getChannelByApi(5);

        //Para cada canal, verfifica se o mesmo já tem configuração e encaminhamento associada
        foreach($channelsFowarding as $key => $channel) {
            $channelInUse = null;
            
            //Armaeza alguma informação sobre o canal
            $infoMessage = null;
            //Se o canal já está associado a uma configuração de encaminhamento
            $channelInUse = FowardingSetting::where('dialer_id', $dialerId)
                                            ->where('channel_id', $channel->id)
                                            ->where('dia_status', 'A')
                                            ->first();
            
            //Se o canal já está em uso por outro chatbot
            if($channelInUse) {
                $channelsFowarding[$key]->setAttribute('inUse', true);
                $infoMessage = "Canal já possui configuração de encaminhamento";
            }
            else {
                $infoMessage .= "Disponível";
            }
            
            $channelsFowarding[$key]->setAttribute('infoMessage', $infoMessage);
        }

        return response()->json([
            'channels' => $channelsFowarding
        ], 200);
    }

    public function addFowardingSetting(Request $request)
    {
        Log::debug('addFowardingSetting $request');
        Log::debug($request);
        $newFowardingSetting = new FowardingSetting();
        $newFowardingSetting->dialer_id = $request['dialerId'];
        $newFowardingSetting->channel_id = $request['channel']['id'];
        $newFowardingSetting->chatbot_id = isset($request['chatbot']['id'])? $request['chatbot']['id'] : null;
        $newFowardingSetting->department_id = $request['department']['id'];
        $newFowardingSetting->fair_distribution_id = $request['fair_distribution']['id'];
        $newFowardingSetting->dia_message = $request['dia_message'];
        $newFowardingSetting->template_id = isset($request['template']['id'])? $request['template']['id'] : null;
        $newFowardingSetting->save();
    }

    public function updateFowardingSetting(Request $request)
    {
        Log::debug('updateFowardingSetting request');
        Log::debug($request);

        $fowardingSetting = FowardingSetting::find($request['fowardingSettingId']);
        $fowardingSetting->channel_id = $request['channel']['id'];
        $fowardingSetting->chatbot_id = isset($request['chatbot']['id'])? $request['chatbot']['id'] : null;
        $fowardingSetting->department_id = $request['department']['id'];
        $fowardingSetting->fair_distribution_id = isset($request['fair_distribution']['id'])? $request['fair_distribution']['id'] : null;
        $fowardingSetting->send_message = $request['send_message'];
        $fowardingSetting->dia_message = $request['send_message'] == 1? $request['dia_message'] : NULL;
        //Se o envio de mensagem automática estiver ATIVO
        if($request['send_message'] == 1) {
            $fowardingSetting->template_id = isset($request['template']['id'])? $request['template']['id'] : null;
        }
        else{
            $fowardingSetting->template_id = NULL;
        }
        
        $fowardingSetting->save();
    }


    //Remove uma configuração de encaminhamento
    public function removeFowardingSetting($fowardingSettingId)
    {
        $fowardingSetting = FowardingSetting::find($fowardingSettingId);
        $fowardingSetting->dia_status = 'I';
        $fowardingSetting->save();
    }

    //Traz aleatoriamente uma configuração de encaminhamento
    public function getRandomFowardingSetting()
    {
        $fowardingSetting = FowardingSetting::where('dia_status', 'A')
                                    ->get()
                                    ->random(1)//Traz uma configuração de forma aleatória
                                    ->values();

        return $fowardingSetting;
    }

    //Traz uma configuração de encaminhamento de acordo com um canal 
    public function getFowardingSettingByChannel($channelPhoneNumber)
    {
        $fowardingSetting = FowardingSetting::select('int_dialers_fowarding_settings.id', 'dialer_id', 'channel_id', 'chatbot_id', 'department_id',
                                                    'dia_message', 'dia_status', 'template_id', 'send_message', 'fair_distribution_id')
                                            ->where('dia_status', 'A')
                                            ->join('man_channels', 'int_dialers_fowarding_settings.channel_id', 'man_channels.id')
                                            ->where('man_channels.cha_phone_number',$channelPhoneNumber)
                                            ->get();

        return $fowardingSetting;
    }
}
