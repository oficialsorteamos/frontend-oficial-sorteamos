<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Api\CommunicatorController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Financial\CardController;
use App\Http\Controllers\Financial\FeeController;
use App\Http\Controllers\Financial\InvoiceController;
use App\Http\Controllers\Financial\ParameterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Management\Channel\Channel;
use App\Models\Management\Channel\ChannelNotification;
use App\Models\Management\Channel\ChannelPayment;
use App\Models\Management\Channel\ChannelSubscription;
use App\Models\Management\Channel\ParameterChannel;
use App\Models\Management\Channel\TypeParameterChannel;
use App\Models\System\ApiCommunication;
use Auth;
use Carbon\Carbon;

class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$invoiceController = new InvoiceController();
        $parameterController = new ParameterController();

        $channels = Channel::with('parameters', 'parameters.typeParameterChannel', 'parameters.department', 'parameters.fairDistribution', 'api', 'notifications.typeNotification',
                                'subscription.card')
                            ->whereIn('cha_status', ['A', 'I']) //Onde o canal está ativo ou inativo (Menos o deletado (D))
                            ->get();
        
        foreach ($channels as $channel) {
            //Caso o número do canal seja brasileiro
            if($channel->cha_phone_ddi == '55') {
                $channel->setAttribute('cha_country_code', 'BR');
            }//Caso o número do canal seja indiano
            else if($channel->cha_phone_ddi == '91') {
                $channel->setAttribute('cha_country_code', 'IN');
            }

        }
        //Traz a quantidade de canais oficiais ativos
        $totalOfficialChannelsActives = self::getCountAllChannelByOfficial(1);
        //Traz a quantidade de canais não oficiais ativos
        $totalUnOfficialChannelsActives = self::getCountAllChannelByOfficial(0);

        $officialChannelParameter = $parameterController->getParameterByType(8);
        $unofficialChannelParameter = $parameterController->getParameterByType(9);

        //Traz a cota de canais OFICIAIS
        //$totalCurrentOfficialChannelQuota = $invoiceController->getCurrentQuotaResource(2);
        //Traz a cota de canais NÃO OFICIAIS
        //$totalCurrentUnofficialChannelQuota = $invoiceController->getCurrentQuotaResource(3);

        //Log::debug('channels');
        //Log::debug($channels); 

        return response()->json([
            'channels' => $channels,
            //'totalCurrentOfficialChannelQuota' => $totalCurrentOfficialChannelQuota, 
            //'totalCurrentUnofficialChannelQuota' => $totalCurrentUnofficialChannelQuota ,
            'totalOfficialChannelsActives' => $totalOfficialChannelsActives,
            'totalUnOfficialChannelsActives' => $totalUnOfficialChannelsActives,
            'officialChannelParameter' => $officialChannelParameter,
            'unofficialChannelParameter' => $unofficialChannelParameter,
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
        $communicatorController = new CommunicatorController();

        try {
            Log::debug('novo canal');
            Log::debug($request->channelData);

            $unofficialApiMain = $communicatorController->getMainApiByOfficial(0);

            //$invoiceController = new InvoiceController();
            $parameterController = new ParameterController();

            $parameterOfficialChannelCharge = $parameterController->getParameterByType(8);

            $channel = new Channel();
       
            $channel->cha_phone_number = preg_replace('/[^0-9]/', '', $request->channelData['cha_phone_number']);
            $channel->type_channel_id = 1; //Whatsapp
            $channel->cha_api_official = $request->channelData['cha_api_official']; //Whatsapp
            $channel->cha_name = $request->channelData['cha_name'];
            $channel->cha_description = $request->channelData['cha_description'];
            $channel->cha_phone_ddi = substr($request->channelData['phoneNumber'], 1, 2);
            $channel->cha_company_name = $request->channelData['cha_company_name'];
            $channel->cha_company_email = $request->channelData['cha_company_email'];
            $channel->cha_company_site = $request->channelData['cha_company_site'];
            $channel->cha_company_address = $request->channelData['cha_company_address'];
            $channel->cha_api_key = isset($request->channelData['cha_api_key'])? $request->channelData['cha_api_key'] : null;
            $channel->cha_app_name_api = $request->channelData['cha_app_name_api'];
            $channel->cha_status = $request->channelData['cha_api_official'] == 1 && $parameterOfficialChannelCharge->par_value == 0? 'A': 'I'; //Se for um canal oficial e a cobrança por canais oficiais estiver desativada
            $channel->api_communication_id = $request->channelData['cha_api_official'] == '1'? $request->channelData['api']['id'] : $unofficialApiMain['id'];
            $channel->whatsapp_business_account_id = $request->channelData['whatsapp_business_account_id'];
            $channel->cha_app_id_api = $request->channelData['cha_app_id_api'];
            $channel->cha_channel_id_api = $request->channelData['cha_channel_id_api'];
            $channel->cha_session_token = $request->channelData['cha_session_token'];

            //Se id do número do WhatsApp ainda não foi preenchido
            /*if(!$channel->cha_channel_id_api) {
                if($request->channelData['cha_api_official'] == 1) {
                    if($request->channelData['api']['id'] == 4) {
                        $channelInfoWhatsapp = $communicatorController->getInfoPhoneNumber($channel);
                        //Se existe as informações do referefido número no WhatsApp
                        if(isset($channelInfoWhatsapp[0])) {
                            $channel->cha_channel_id_api = $channelInfoWhatsapp[0]['id'];
                        }
                    }
                }
            }*/

            //Se o canal for adicionado 
            if($channel->save()) {
                $typeParameters = TypeParameterChannel::where('typ_status', 'A')
                                                        ->get();
                //Para cada tipo de parâmetro
                foreach ($typeParameters as $typeParameter) {

                    //Se o tipo de parâmetro for de temporização
                    if($typeParameter->category_id == 1) {
                        //Adiciona o parâmetro associando o mesmo ao canal recém criado
                        ParameterChannel::create([
                            'channel_id' => $channel->id,
                            'type_parameter_channel_id' => $typeParameter->id,
                            'par_value' => '3600',
                        ]);
                    } //Se o tipo de parâmetro for uma opção
                    else if($typeParameter->category_id == 2) {
                        //Adiciona o parâmetro associando o mesmo ao canal recém criado
                        ParameterChannel::create([
                            'channel_id' => $channel->id,
                            'type_parameter_channel_id' => $typeParameter->id,
                            'par_value' => null,
                            'department_id' => null,
                        ]);
                    }
                    
                }


                //Se por padrão o tipo de recurso como um canal NÃO OFICIAL
                //$typeInvoiceResource = 3;

                //Se for um canal oficial
                if($request->channelData['cha_api_official'] == 1) {
                    //Se for um canal da Gupshup ou da 360Dialog
                    if($request->channelData['api']['id'] == 1 || $request->channelData['api']['id'] == 3) {
                        //Seta o webhook na API
                        $communicatorController->setWebhook($channel);
                    }

                    //Seta o tipo de recurso como um canal OFICIAL
                    //$typeInvoiceResource = 2;
                }//Se for um canal NÃO OFICIAL
                else {
                    //Se for a Z-API
                    if($channel->api_communication_id == 5) {
                        $instanceData = $communicatorController->createInstance($channel);
                        //Se a instância foi criada
                        if(isset($instanceData['due'])) {
                            //Pega o timestamp até os segundos
                            $dueTimestamp = substr($instanceData['due'], 0, 10);
                            //Converte do formato timestamp para data
                            $due = Carbon::createFromTimestamp($dueTimestamp)->toDateTimeString();
                            
                            $channel->cha_due = $due;
                            $channel->cha_channel_id_api = $instanceData['id'];
                            $channel->cha_session_token = $instanceData['token'];
                            //$channel->cha_due = $instanceData['due']; //Vencimento do trial da instância
                            $channel->save();

                            $parameterUnofficialChannelCharge = $parameterController->getParameterByType(9);
                            //Se cobrança por canal não oficial está DESABILITADA
                            if($parameterUnofficialChannelCharge->par_value == 0) {
                                //Assina o canal na Z-API
                                $instanceSubscriptionData = $communicatorController->subscriptionInstance($channel);
                                //Se a instância foi assinada com sucesso
                                if(isset($instanceSubscriptionData['due'])) {
                                    //Pega o timestamp até os segundos
                                    $dueTimestamp = substr($instanceSubscriptionData['due'], 0, 10);
                                    //Converte do formato timestamp para data
                                    $dueSubscription = Carbon::createFromTimestamp($dueTimestamp)->toDateTimeString();
                                    
                                    $channel->cha_due = $dueSubscription; //Vencimento da assinatura da instância
                                    //Marca o canal como assinado
                                    $channel->cha_subscription = 1;
                                    $channel->save();
                                }
                            }
                        }
                        
                        $communicatorController->setWebhook($channel);
                    } //Se for a API ZAP
                    else if($channel->api_communication_id == 6) {
                           
                        $parameterUnofficialChannelCharge = $parameterController->getParameterByType(9);
                        //Se cobrança por canal não oficial está DESABILITADA
                        if($parameterUnofficialChannelCharge->par_value == 0) {
                            //Cria uma instância na API ZAP
                            $instanceData = $communicatorController->createInstance($channel);

                            //Se a instância foi assinada com sucesso
                            if(isset($instanceData['result']) && $instanceData['result'] == 'success') {
                                //$channel->cha_due = $due;
                                $channel->cha_session_token = $instanceData['tokenKey'];
                                //$channel->cha_due = $instanceData['due']; //Vencimento do trial da instância
                                //Marca o canal como assinado
                                $channel->cha_subscription = 1;
                                $channel->save();
                            }
                        }
                    } //Se for a API eHost
                    else if($channel->api_communication_id == 8) {
                           
                        $parameterUnofficialChannelCharge = $parameterController->getParameterByType(9);
                        //Se cobrança por canal não oficial está DESABILITADA
                        if($parameterUnofficialChannelCharge->par_value == 0) {
                            //Cria uma instância na API ZAP
                            $instanceData = $communicatorController->createInstance($channel);

                            //Se a instância foi assinada com sucesso
                            if(isset($instanceData['success']) && $instanceData['success'] == true) {
                                //$channel->cha_due = $due;
                                $channel->cha_session_token = $instanceData['tokenKey'];
                                //$channel->cha_due = $instanceData['due']; //Vencimento do trial da instância
                                //Marca o canal como assinado
                                $channel->cha_subscription = 1;
                                $channel->save();

                                //Seta o webhook
                                $communicatorController->setWebhook($channel);
                            }
                        }
                    }
                }

                //Registra o recurso, caso necessário
                //$invoiceController->storeInvoiceResourceControl($typeInvoiceResource, $channel);
            }

            return response()->json(
                '' 
            , 200);
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
            $communicatorController = new CommunicatorController();
            //Log::debug($request);

            $channel = Channel::find($request->channelData['id']);
            //Guardo o número atual do telefone
            $currentPhoneNumber = $channel->cha_phone_number;
            $onlyNumbersPhone = preg_replace('/[^0-9]/', '', $request->channelData['cha_phone_number']);

            $channel->cha_session_token = $channel->cha_phone_number != $onlyNumbersPhone? null : $channel->cha_session_token; //Caso o número do canal tenha sido alterado, limpa o token da sessão
            $channel->cha_phone_number = $onlyNumbersPhone;
            $channel->cha_name = $request->channelData['cha_name'];
            $channel->cha_api_official = $request->channelData['cha_api_official'];
            $channel->cha_description = $request->channelData['cha_description'];
            $channel->cha_phone_ddi = substr($request->channelData['phoneNumber'], 1, 2);
            $channel->cha_company_name = $request->channelData['cha_company_name'];
            $channel->cha_company_email = $request->channelData['cha_company_email'];
            $channel->cha_company_site = $request->channelData['cha_company_site'];
            $channel->cha_company_address = $request->channelData['cha_company_address'];
            $channel->cha_api_key = $request->channelData['cha_api_key'];
            $channel->cha_app_name_api = $request->channelData['cha_app_name_api'];
            $channel->cha_session_name = preg_replace('/[^0-9]/', '', $request->channelData['phoneNumber']);
            $channel->api_communication_id = isset($request->channelData['api']['id'])? $request->channelData['api']['id'] : 5;
            $channel->whatsapp_business_account_id = $request->channelData['whatsapp_business_account_id'];
            $channel->cha_app_id_api = $request->channelData['cha_app_id_api'];
            $channel->cha_channel_id_api = $request->channelData['cha_channel_id_api'];
            $channel->cha_session_token = $request->channelData['cha_session_token'];

            if($request->channelData['cha_api_official'] == 1) {
                //Se id do número do WhatsApp ainda não foi preenchido (CLOUD API)
                if((!$channel->cha_channel_id_api) && $request->channelData['api']['id'] == 4) {
                    $channelInfoWhatsapp = $communicatorController->getInfoPhoneNumber($channel);
                    //Se existe as informações do referefido número no WhatsApp
                    if(isset($channelInfoWhatsapp[0])) {
                        $channel->cha_channel_id_api = $channelInfoWhatsapp[0]['id'];
                    }
                }
            }
            

            $channel->save();

            //Se for uma API OFICIAL
            if($request->channelData['cha_api_official'] == 1) {
                //$wabaInfo = $communicatorController->getWabaInfo($channel);
                //$communicatorController->setTwoStepVerification($channel);
                $communicatorController->registerPhone($channel);
                //$communicatorController->migratePhone($channel);
                //Se for um canal da Gupshup ou da 360Dialog
                if($request->channelData['api']['id'] == 1 || $request->channelData['api']['id'] == 3) {
                    //Seta o webhook na API
                    $communicatorController->setWebhook($channel);
                }
            } //Se for um canal NÃO OFICIAL
            else {
                //Se for a Z-API
                if($request->channelData['api']['id'] == 5) {
                    $communicatorController->setWebhook($channel);

                    //Se o usuário trocou o número do telefone
                    if($currentPhoneNumber != $onlyNumbersPhone) {
                        //Limpa a fila de mensagens da instância
                        $communicatorController->clearQueue($channel);
                        $communicatorController->updateInstanceName($channel);
                    }
                } //Se for a API ZAP
                else if($request->channelData['api']['id'] == 6) {
                    $communicatorController->setWebhook($channel);
                } 
                //Se for a API eHOST
                else if($request->channelData['api']['id'] == 8) {
                    $communicatorController->setWebhook($channel);
                }//Se for a API WA
                else if($request->channelData['api']['id'] == 7) {
                    $communicatorController->setWebhook($channel);
                }
            }

            return response()->json(
                '' 
            , 200);
        }
        catch (e) {

        }
    }

    //Traz um determinado parâmetro do canal pelo seu tipo
    public function getParameterChannelByTypeFairDistribution($channelId, $typeParameterId)
    {
        $parameterChannel = ParameterChannel::where('channel_id', $channelId)
                                            ->where('type_parameter_channel_id', $typeParameterId)
                                            ->whereNotNull('fair_distribution_id')
                                            ->where('par_status', 'A')
                                            ->first();

        return $parameterChannel;
    }

    //Traz um determinado parâmetro do canal pelo seu tipo
    public function getParameterChannelByType($channelId, $typeParameterId)
    {
        $parameterChannel = ParameterChannel::where('channel_id', $channelId)
                                            ->where('type_parameter_channel_id', $typeParameterId)
                                            ->where('par_status', 'A')
                                            ->first();

        return $parameterChannel;
    }

    public function updateParametersChannel(Request $request)
    {
        try {
            //Log::debug('Parâmetros');
            //Log::debug($request->parametersChannelData['parameters'][3]['fair_distribution']);
            
            //Traz os parâmetros associados ao canal
            $parametersChannel = ParameterChannel::where('channel_id', $request->parametersChannelData['id'])
                                                ->get();
            
            foreach ($parametersChannel  as $key => $parameter) {
                //Caso o parâmetro seja de inatividade durante atendimento
                if($parameter->type_parameter_channel_id == 1) {
                    ParameterChannel::where('channel_id', $request->parametersChannelData['id'])
                                    ->where('type_parameter_channel_id', 1)
                                    ->update([
                                        'par_value' => $request->parametersChannelData['parameters'][$key]['par_value']
                                    ]);    
                } //Caso o parâmetro seja de inatividade durante autoatendimento
                else if($parameter->type_parameter_channel_id == 2) {
                    ParameterChannel::where('channel_id', $request->parametersChannelData['id'])
                                    ->where('type_parameter_channel_id', 2)
                                    ->update([
                                        'par_value' => $request->parametersChannelData['parameters'][$key]['par_value']
                                    ]);
                }
                //Caso o parâmetro seja de departamento padrão de transferência
                else if($parameter->type_parameter_channel_id == 3) {
                    ParameterChannel::where('channel_id', $request->parametersChannelData['id'])
                                    ->where('type_parameter_channel_id', 3)
                                    ->update([
                                        'department_id' => isset($request->parametersChannelData['parameters'][$key]['department']['id'])? $request->parametersChannelData['parameters'][$key]['department']['id'] : NULL
                                    ]);
                }
                //Caso o parâmetro seja uma configuração de transferência igualitária
                else if($parameter->type_parameter_channel_id == 4) {
                    ParameterChannel::where('channel_id', $request->parametersChannelData['id'])
                                    ->where('type_parameter_channel_id', 4)
                                    ->update([
                                        'fair_distribution_id' => isset($request->parametersChannelData['parameters'][$key]['fair_distribution']['id'])? $request->parametersChannelData['parameters'][$key]['fair_distribution']['id'] : NULL
                                    ]);
                }
                //Caso o parâmetro seja uma configuração de tempo para avaliação de atendimento
                else if($parameter->type_parameter_channel_id == 5) {
                    ParameterChannel::where('channel_id', $request->parametersChannelData['id'])
                                    ->where('type_parameter_channel_id', 5)
                                    ->update([
                                        'par_value' => $request->parametersChannelData['parameters'][$key]['par_value']
                                    ]);
                }
            }

            return response()->json(
                '' 
            , 200);
        }
        catch (e) {

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
        //
    }

    public function updateStatusChannel(Request $params)
    {
        try {
            Log::debug('dados atualização canal');
            Log::debug($params);
            $communicatorController = new CommunicatorController();
            $channel = null;

            //Se existir o parâmetro id do canal 
            if(isset($params->channelId)) {
                $channel = Channel::find($params['channelId']);
            }
            else if(isset($params['sessionName']) && $params['sessionName']) {
                $channel = Channel::where('cha_session_name', $params['sessionName'])
                                    ->first();
            }

            //Se existe algum canal com esse ID ou nome de sessão
            if($channel) {
                //Se foi passado o status que o canal deverá ter
                if(isset($params->statusId)) {
                    //Se for uma REMOÇÃO de canal
                    if($params->statusId == 'D') {
                        //Se for a Z-API
                        if($channel->api_communication_id == 5) {
                            $cancelResponse = $communicatorController->cancelInstance($channel);
                            $channel->cha_status = $params['statusId'];
                        } //Se for API ZAP
                        else if($channel->api_communication_id == 6) {
                            $cancelResponse = $communicatorController->cancelInstance($channel);
                            $channel->cha_status = $params['statusId'];
                        } //Se for eHost
                        else if($channel->api_communication_id == 8) {
                            $cancelResponse = $communicatorController->cancelInstance($channel);
                            $channel->cha_status = $params['statusId'];
                        }
                        else {
                            $channel->cha_status = $params['statusId'];    
                        }
                    }
                    else {
                        $channel->cha_status = $params['statusId'];
                    }    
                }
                else {
                    //Se o canal estiver habilitado
                    if($channel->cha_status == 'A') {
                        //Desabilita o mesmo
                        $channel->cha_status = 'I';
                    }
                    else {
                        //Habilita o canal
                        $channel->cha_status = 'A';
                    }
                }

                $channel->save();
            }
            
            return response()->json(
                '' 
            , 200);
            
        } catch (e) {

        }

    }
    
    //Retorna um canal específico
    public function getChannel($channelId)
    {
        $channel = Channel::with('parameters')
                        ->find($channelId);

        return $channel;
    }

    public function checkPhoneExist($phoneNumber)
    {
        //Log::debug('número digitado');
        //Log::debug($phoneNumber);
        $error = false;
        //Só deixa os números 
        $phoneNumber = preg_replace( '/[^0-9]/', '', $phoneNumber);
        $numberExist = Channel::where('cha_phone_number', $phoneNumber)
                                ->where('cha_status','!=', 'D')
                                ->first();
        //Caso já exista um canal com esse número
        if($numberExist) {
            $error = true;
        }

        return response()->json(
            [
                'error' => $error,
            ] 
        , 200);
    }

    public function getFirstChannel()
    {
        //Traz o canal ativo mais antigo
        $channel = Channel::where('cha_status', 'A')
                        ->orderBy('created_at', 'ASC')
                        ->first();
        return $channel;
    }

    public function getChannelBySessionName($sessionName)
    {
        //Traz o canal com o número correspondente
        $channel = Channel::where('cha_session_name', $sessionName)
                        ->first();
        return $channel;
    }

    public function fetchChannelsByOfficialApi($officialApi)
    {
        //Traz os canais com filtrando por ele utilizar api oficial ou não
        $channels = Channel::where('cha_api_official', $officialApi)
                            ->where('cha_status', 'A')
                            ->get();
        
        return response()->json(
            $channels 
        , 200);
    }

    public function fetchChannelsByStatus($status)
    {
        //Traz os canais com um status correspondente
        $channels = Channel::where('cha_status', $status)
                        ->get();
        
        return response()->json([
            'channels' => $channels 
        ], 200);
    }

    //Traz o primeiro canal oficial ou não
    public function getChannelByOfficial($official)
    {
        $channel = Channel::where('cha_api_official', $official)
                        ->where('cha_status', 'A')
                        ->first();

        return $channel;
    }


    //Todos os canais filtrando se o mesmo é oficial ou não
    public function getAllChannelByOfficial($official)
    {
        $channel = Channel::where('cha_api_official', $official)
                        ->where('cha_status', 'A')
                        ->get();

        return $channel;
    }

    //Traz a quantidade total de todos os canais que não foram REMOVIDOS da plataforma
    public function getCountAllChannelByOfficial($official)
    {
        $totalChannel = Channel::where('cha_api_official', $official)
                        ->where('cha_status', '!=','D')
                        ->count();

        return $totalChannel;
    }

    //Traz todos os canais que não foram REMOVIDOS da plataforma
    public function getChannelsByOfficial($official)
    {
        $totalChannel = Channel::where('cha_api_official', $official)
                        ->whereIn('cha_status', ['A', 'I'])
                        ->get();

        return $totalChannel;
    }

    //Traz todos os canais associados a uma determinada API
    public function getChannelsByApi($apiId)
    {
        $channels = Channel::where('api_communication_id', $apiId)
                        ->whereIn('cha_status', ['A', 'I'])
                        ->get();

        return $channels;
    }

    //Pega os status dos canais na API OFICIAL
    public function checkStatusChannel()
    {   
        $communicatorController = new CommunicatorController();
        //Traz todos os canais que estão conectados na API OFICIAL
        $channels = self::getAllChannelByOfficial(1);

        //Para cada canal
        foreach($channels as $channel) {
            $statusChannel = $communicatorController->checkHealthChannel($channel);
            //Se o canal está conectado na API OFICIAL
            if($statusChannel['health']['gateway_status'] == 'connected') {
                //Se o canal estava DESABILITADO anteriormente
                if($channel->cha_status == 'I') {
                    $channelData = new Request([
                        'channelId'   => $channel->id,
                        'sessionName' => null,
                        'statusId' => 'A',
                    ]);

                    //Habilita o canal
                    self::updateStatusChannel($channelData);
                }
            } //Se o canal NÃO está conectado
            else {
                //Se o canal estava HABILITADO anteriormente
                if($channel->cha_status == 'A') {
                    $channelData = new Request([
                        'channelId'   => $channel->id,
                        'sessionName' => null,
                        'statusId' => 'I',
                    ]);

                    //Habilita o canal
                    self::updateStatusChannel($channelData);
                }
            }
        }
    }

    public function getChannelByPhoneNumber($phoneNumber)
    {
        //Traz o canal com o número correspondente
        $channel = Channel::where('cha_phone_number', $phoneNumber)
                        ->first();
        return $channel;
    }

    public function getChannelByAppName($appName)
    {
        //Traz o canal de acordo com o nome do app na GupShup
        $channel = Channel::where('cha_app_name_api', $appName)
                        ->first();
        return $channel;
    }

    //Traz as API's ativas
    public function fetchApisCommunicationByType($officialApi)
    {
        $apis = ApiCommunication::where('api_official', $officialApi)
                                ->where('api_status', 'A')
                                ->get();
        

        return response()->json([
            'apis' => $apis 
        ], 200);
    }

    //Traz os canais por api associada
    public function getChannelByApi($apiId)
    {
        $channels = Channel::where('api_communication_id', $apiId)
                            ->where('cha_status', 'A')
                            ->get();

        return $channels;
    }

    //Traz os canais pelo status
    public function getChannelsByStatus($status)
    {
        //Traz os canais com um status correspondente
        $channels = Channel::with('parameters')
                            ->where('cha_status', $status)
                            ->get();
        
        return $channels;
    }

    //Traz canais por filtrando por múltiplos status
    public function getChannelsByMultipleStatus($status)
    {
        //Traz os canais com um status correspondente
        $channels = Channel::with('parameters')
                            ->whereIn('cha_status', $status)
                            ->get();
        
        return $channels;
    }

    public function updateChannelSubscription($channelId, $subscriptionId)
    {
        $channel = Channel::find($channelId);
        $channel->cha_subscription = $subscriptionId;
        $channel->save();
    }

    //Adiciona uma pagamento associado a um canal
    public function addPayment(Request $request, $automaticSubscriptionRenewal=false)
    {
        Log::debug('addPayment');
        Log::debug($request);

        $paymentController = new PaymentController();
        $cardController = new CardController();
        $paymentMethodData = [];
        $statusPaymentId = null;
        $errorMessage = null;
        $pixQrcodeData['encodedImage'] = null;

        //Se o método de pagamento for Cartão de crédito ou débito
        if($request->paymentData['payment_method']['id'] == 1 || $request->paymentData['payment_method']['id'] == 2) {
            $paymentMethodData = $cardController->getCard($request->paymentData['credit_card']['data']['id']); 
        }
        $paymentData['paymentMethodData'] = $paymentMethodData;
        $paymentData['generalData'] = $request->paymentData;
        
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

        //Se o método de pagamento for Cartão de crédito ou débito
        if($request->paymentData['payment_method']['id'] == 1 || $request->paymentData['payment_method']['id'] == 2) {
            //Se o pagamento foi CONFIRMADO OU RECEBIDO
            if($statusPaymentId == 2) {
                
                //Cria a instância, se necessário e seta a data de vencimento do canal
                self::setChannelDue($request->paymentData['id'], $automaticSubscriptionRenewal);
                

                $newPayment = new ChannelPayment();
                $newPayment->channel_id = $request->paymentData['id'];
                $newPayment->payment_method_id = $request->paymentData['payment_method']['id'];
                $newPayment->card_id = $request->paymentData['credit_card']['data']['id'];
                $newPayment->cha_value = $request->paymentData['credit_value'];
                $newPayment->user_id = Auth::check()? Auth::user()->id : 1; //Caso tenha sido um usuário que tenha assinado o canal, coloca o id do usuário. Senão, coloca o id do robô
                $newPayment->save();
            }
        } //Se o pagamento está sendo relizado no PIX
        else if($request->paymentData['payment_method']['id'] == 3) {
            $pixQrcodeData = $paymentController->getPixQrcode($paymentData['id']);
            
            $newPayment = new ChannelPayment();
            $newPayment->channel_id = $request->paymentData['id'];
            $newPayment->api_payment_charge_id = $paymentData['id'];
            $newPayment->payment_method_id = $request->paymentData['payment_method']['id'];
            $newPayment->cha_value = $request->paymentData['credit_value'];
            $newPayment->cha_status = 'I';
            $newPayment->user_id = Auth::check()? Auth::user()->id : 1; //Caso tenha sido um usuário que tenha assinado o canal, coloca o id do usuário. Senão, coloca o id do robô
            $newPayment->save();
        }
        

        return response()->json([
            'errorMessage'=> $errorMessage,
            'qrcode' => $pixQrcodeData['encodedImage']
        ], 200);
    }

    public function setChannelDue($channelId, $automaticSubscriptionRenewal)
    {
        $communicatorController = new CommunicatorController();
        $instanceSubscriptionData = null;
        $subscriptionSuccess = false;

        //Traz o canal a ser assinado
        $channel = self::getChannel($channelId);

        Log::debug('channel setChannelDue');
        Log::debug($channel);

        $today = Carbon::now()->startOfDay();
        $penultimateDay = Carbon::now()->endOfMonth()->startOfDay()->subDay();
        $lastDay = Carbon::now()->endOfMonth()->startOfDay();
        //$today = Carbon::parse('2022-12-28');
        //Se o canal nunca foi assinado antes ou se já foi assinado, está com a assinatura vencida e data corrente é maior que o vencimento
        if($channel->cha_subscription == null || ($channel->cha_subscription == 0 && $today->gt(Carbon::parse($channel->cha_due)))) {
            //Se o usuário está assinando o canal no penúltimo ou no último dia do mês corrente
            if($today->eq($penultimateDay) || $today->eq($lastDay)) {
                //Coloca a data de vencimento como o penúltimo dia do mês subsequente
                $dueSubscription = Carbon::now()->addMonthsNoOverflow(1)->endOfMonth()->startOfDay()->subDay();
            }
            else {
                //Coloca o vencimento da assinatura como o penúltimo dia do mês atual
                $dueSubscription = Carbon::now()->endOfMonth()->startOfDay()->subDay();
            }
        }
        //Se o canal está assinado ou não e a data corrente é menor ou igual a data de vencimento da assinatura
        else if(($channel->cha_subscription == 1 || $channel->cha_subscription == 0) && $today->lte(Carbon::parse($channel->cha_due))) {
            //Coloca a data de vencimento como o penúltimo dia do mês subsequente
            $dueSubscription = Carbon::parse($channel->cha_due)->addMonthsNoOverflow(1)->endOfMonth()->startOfDay()->subDay();
            Log::debug('Due Current');
            Log::debug($channel->cha_due);
            Log::debug('New Due');
            Log::debug($dueSubscription);
        }
        //Se for um canal não oficial
        if($channel->cha_api_official == 0) {
            //Se o canal NÃO possui assinatura ativa ou nunca foi assinado
            if($channel->cha_subscription == 0 || $channel->cha_subscription == null) {
                //Se a data atual NÃO é o penúltimo nem o último dia do mês atual
                if($today->ne($penultimateDay) && $today->ne($lastDay)) {

                    //Se for uma instância da Z-API
                    if($channel->api_communication_id == 5) {
                        //Assina a instância do canal
                        $instanceSubscriptionData = $communicatorController->subscriptionInstance($channel);
                        //Se não existe instância asociada ao canal na Z-API
                        if(isset($instanceSubscriptionData['error'])) {
                            if($instanceSubscriptionData['error'] == 'Instance not found') {
                                //Cria uma nova instância
                                $instanceData = $communicatorController->createInstance($channel);
                                if(isset($instanceData['due'])) {
                                    $channel->cha_channel_id_api = $instanceData['id'];
                                    $channel->cha_session_token = $instanceData['token'];
                                    $channel->save();

                                    //Assina o canal recém criado
                                    $instanceSubscriptionData = $communicatorController->subscriptionInstance($channel);
                                }
                            }
                        }

                        //Se o canal foi assinado com sucesso
                        if(isset($instanceSubscriptionData['due'])) {
                            $subscriptionSuccess = true;
                        }
                    } //Se for uma instância da API ZAP
                    else if($channel->api_communication_id == 6) {
                        $instanceData = $communicatorController->createInstance($channel);

                        //Se a instância foi assinada com sucesso
                        if(isset($instanceData['result']) && $instanceData['result'] == 'success') {
                            //$channel->cha_due = $due;
                            $channel->cha_session_token = $instanceData['tokenKey'];
                            //$channel->cha_due = $instanceData['due']; //Vencimento do trial da instância
                            //Marca o canal como assinado
                            $channel->cha_subscription = 1;
                            $channel->save();

                            $subscriptionSuccess = true;
                        }
                    }//Se for uma instância da API eHost
                    else if($channel->api_communication_id == 8) {
                        $instanceData = $communicatorController->createInstance($channel);

                        //Se a instância foi assinada com sucesso
                        if(isset($instanceData['success']) && $instanceData['success'] == true) {
                            //$channel->cha_due = $due;
                            $channel->cha_session_token = $instanceData['tokenKey'];
                            //$channel->cha_due = $instanceData['due']; //Vencimento do trial da instância
                            //Marca o canal como assinado
                            $channel->cha_subscription = 1;
                            $channel->save();

                            $subscriptionSuccess = true;
                        }
                    }

                    
                } //Se é o penúltimo ou o último dia do mês
                else {   
                    //Cria uma nova instância
                    $instanceData = $communicatorController->createInstance($channel);

                    //Se for uma instância da Z-API
                    if($channel->api_communication_id == 5) {
                        //Se a instância foi criada
                        if(isset($instanceData['due'])) {
                            $channel->cha_channel_id_api = $instanceData['id'];
                            $channel->cha_session_token = $instanceData['token'];
                            $channel->cha_trial = true;
                            $channel->save();
                            
                            $subscriptionSuccess = true;
                        }
                    } //Se for a API ZAP
                    else if($channel->api_communication_id == 6) {
                        $channel->cha_session_token = $instanceData['tokenKey'];
                            //$channel->cha_due = $instanceData['due']; //Vencimento do trial da instância
                            $channel->save();

                            $subscriptionSuccess = true;
                    } //Se for a API eHost
                    else if($channel->api_communication_id == 8) {
                        $channel->cha_session_token = $instanceData['tokenKey'];
                            //$channel->cha_due = $instanceData['due']; //Vencimento do trial da instância
                            $channel->save();

                            $subscriptionSuccess = true;
                    }
                    
                }
            }
            else {
                $subscriptionSuccess = true;
            }
            //Se a instância foi assinada com sucesso ou se é uma renovação automática
            if($subscriptionSuccess || $automaticSubscriptionRenewal) {
                //Pega o timestamp até os segundos
                //$dueTimestamp = substr($instanceSubscriptionData['due'], 0, 10);
                //Converte do formato timestamp para data
                //$dueSubscription = Carbon::createFromTimestamp($dueTimestamp)->toDateTimeString();

                $channel->cha_due = $dueSubscription; //Vencimento da assinatura da instância
                //Marca o canal como assinado
                $channel->cha_subscription = 1;
                $channel->save();
            }
        }
        else {
            //Coloca a data de vencimento como o último dia do mês
            //$dueSubscription = Carbon::now()->endOfMonth()->startOfDay();

            $channel->cha_due = $dueSubscription; //Vencimento da assinatura da instância
            //Marca o canal como assinado
            $channel->cha_subscription = 1;
            $channel->save();
        }
    }

    public function fetchChannelPayments(Request $request)
    {
        Log::debug('$request fetchChannelPayments');
        Log::debug($request);
        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($request['page']-1) * $request['perPage']);
        

        $channelPayments = ChannelPayment::with('paymentMethod', 'card');
        if($request['channelId'] != '') {
            $channelPayments = $channelPayments->where('channel_id', $request['channelId']);    
        }
        $total = $channelPayments->count();
        //Busca os contatos de acordo com a paginação
        $channelPayments = $channelPayments->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                            ->take($request['perPage']) //Quantidade de itens trazidos
                                            ->where('cha_status', 'A')
                                            ->orderBy('id', 'DESC')
                                            ->get();

        Log::debug('total channelPayments');
        Log::debug($total);

        return response()->json([
            'channelPayments'=> $channelPayments,
            'total'=> $total,
        ], 200);
    }

    //Atualiza o status assinatura de um canal (se a renovação será automática ou não)
    public function updateSubscriptionRenewal(Request $request)
    {   
        $successMessage = '';
        //Se a assinatura automática foi habilitada
        if($request['cha_automatic_subscription_renewal'] == 1) {
            $paymentController = new PaymentController();
            $cardController = new CardController();
            Log::debug('updateSubscriptionRenewal request');
            Log::debug($request);
            //Gera o token do cartão para os próximos pagamentos
            $tokenData = $paymentController->generateTokenCard($request['credit_card_renewal']['data']['id'], $request['ccv_renewal']);
            Log::debug('$tokenData');
            Log::debug($tokenData);
            if(isset($tokenData['creditCardToken'])) {
                $cardController->updateTokenCard($request['credit_card_renewal']['data']['id'], $tokenData['creditCardToken']);
                //Verifica se existe alguma assinatura ativa para o referido canal
                $oldSubscription = ChannelSubscription::where('channel_id', $request['id'])
                                                        ->where('cha_status', 'A')
                                                        ->first();
                //Se existe alguma assinatura ativa anterior
                if($oldSubscription) {
                    $oldSubscription->cha_status = 'I';
                    $oldSubscription->save();
                }
                //Cria a nova assinatura
                $channelSubscription = new ChannelSubscription();
                $channelSubscription->channel_id = $request['id'];
                $channelSubscription->card_id = $request['credit_card_renewal']['data']['id'];
                $channelSubscription->save();

                $channel = Channel::with('parameters', 'parameters.typeParameterChannel', 'parameters.department', 'api', 'notifications.typeNotification',
                'subscription.card')->find($request['id']);
                $channel->cha_automatic_subscription_renewal = $request['cha_automatic_subscription_renewal'];
                $channel->save();

                $successMessage = 'Assinatura adicionada com sucesso!';

                return response()->json([
                    'channel'=> $channel,
                    'successMessage' => $successMessage,
                ], 200);
            }
            else {
                $errorMessage = '';
                foreach($tokenData['errors'] AS $key => $error) {
                    if($key == 0) {
                        $errorMessage = $error['description'];
                    }
                    else {
                        $errorMessage = $errorMessage. '; ' .$error['description'];
                    }
                    
                }

                return response()->json([
                    'errorMessage'=> $errorMessage,
                ], 200);
            }
        } //Se a assinatura estiver sendo desabilitada
        else {
            //Traz a assinatura a ser desabilitada
            $channelSubscription = ChannelSubscription::where('channel_id', $request['id'])
                                                        ->where('cha_status', 'A')
                                                        ->first();

            $channelSubscription->cha_status = 'I';
            $channelSubscription->save();

            $channel = Channel::with('parameters', 'parameters.typeParameterChannel', 'parameters.department', 'api', 'notifications.typeNotification',
                                    'subscription.card')->find($request['id']);
            $channel->cha_automatic_subscription_renewal = $request['cha_automatic_subscription_renewal'];
            $channel->save();

            $successMessage = 'Assinatura desabilitada com sucesso!';

            return response()->json([
                'channel'=> $channel,
                'successMessage' => $successMessage,
            ], 200);
        }
        
    }

    //Desconecta, e desabilita os canais com assinatura expirada
    public function disableExpiredSubscriptionsChannel()
    {
        $parameterController = new ParameterController();
        $communicatorController = new CommunicatorController();

        $channelOfficialCharge = $parameterController->getParameterByType(8);
        $channelUnofficialCharge = $parameterController->getParameterByType(9);

        $chargeChannel = 0;
        $typeChannels = [];
        if($channelOfficialCharge->par_value == 1) {
            array_push($typeChannels, 1);
            $chargeChannel = 1;
        }
        if($channelUnofficialCharge->par_value == 1) {
            array_push($typeChannels, 0);
            $chargeChannel = 1;
        }

        //Se a cobrança por algum tipo de canal estiver habilitada
        if($chargeChannel == 1) {
            $today = Carbon::now()->startOfDay();
            $lastDay = Carbon::now()->endOfMonth()->startOfDay();
            //$today = Carbon::parse('2023-01-31')->startOfDay();
            //Se for o último dia do mês
            if($today->eq($lastDay)) {
                //Traz os canais que deverão ser desativados
                $channels = Channel::whereIn('cha_status', ['A', 'I'])
                                    ->whereIn('cha_api_official', $typeChannels)
                                    ->where('cha_subscription', 1) //Onde o canal está assinado
                                    ->where('cha_due', '<', $today) //Onde canal está vencido
                                    ->whereNotNull('cha_due') //Onde existe alguma data de vencimento
                                    ->get();
                
                Log::debug('channels disabled');
                Log::debug($channels);
                
                foreach($channels AS $channel) {
                    $channelUnsubscription = self::getChannel($channel->id);
                    //Se for um canal não oficial
                    if($channel->cha_api_official == 0) {
                        //Cancela a instância na API
                        $cancelResponse = $communicatorController->cancelInstance($channel);
                        //Se a instância foi cancelada na API
                        if(isset($cancelResponse['due'])) {
                            //Marca o canal como sem assinatura
                            $channelUnsubscription->cha_status = 'I';
                            $channelUnsubscription->cha_subscription = 0;
                            $channelUnsubscription->save();
                        }
                    } //Se for um canal oficial
                    else {
                        //Marca o canal como sem assinatura
                        $channelUnsubscription->cha_status = 'I';
                        $channelUnsubscription->cha_subscription = 0;
                        $channelUnsubscription->save();
                    }
                }    
            }
        }
    }

    //Renova automaticamente a assinatura de um canal
    public function automaticSubscriptionRenewal()
    {   
        $parameterController = new ParameterController();

        $channelOfficialCharge = $parameterController->getParameterByType(8);
        $channelUnofficialCharge = $parameterController->getParameterByType(9);

        $chargeChannel = 0;
        $typeChannels = [];
        if($channelOfficialCharge->par_value == 1) {
            array_push($typeChannels, 1);
            $chargeChannel = 1;
        }
        if($channelUnofficialCharge->par_value == 1) {
            array_push($typeChannels, 0);
            $chargeChannel = 1;
        }

        //Se a cobrança por canais está ativa
        if($chargeChannel) {
            $today = Carbon::now()->startOfDay();
            //$today = Carbon::parse('2022-12-30')->startOfDay();
            //Traz o penúltimo dia do mês
            $penultimateDay = Carbon::now()->endOfMonth()->startOfDay()->subDay();
            //$penultimateDay = Carbon::parse('2022-12-30')->endOfMonth()->startOfDay()->subDay();
            //Se o dia corrente é o penúltimo dia do mês
            if($today->eq($penultimateDay)) {
                $cardController = new CardController();
                $feeController = new FeeController();
                
                $feeOfficialChannel = $feeController->getFeeByType(3);
                $feeUnofficialChannel = $feeController->getFeeByType(4);

                $channels = Channel::whereIn('cha_status', ['A', 'I'])
                                    ->whereIn('cha_api_official', $typeChannels) //Traz de acordo com os tipos de canais a serem cobrados
                                    ->where('cha_automatic_subscription_renewal', 1) //Onde a assinatura automática está habilitada
                                    ->where('cha_due', $today) // Onde a data corrente é igual a data de vencimento
                                    ->get();
                
                Log::debug('$channels automaticSubscriptionRenewal');
                Log::debug($channels);
                foreach($channels AS $channel) {
                    $channelSubscription = self::getChannelSubscription($channel->id);
                    //Traz os dados do cartão usado na assinatura automática
                    $card =  $cardController->getCard($channelSubscription['card_id']);
                    //Seta o id do cartão usado na assinatura automática
                    $paymentData['data']['id'] = $card->id;
                    Log::debug('$paymentData');
                    Log::debug($paymentData);
                    $payData = new Request([
                        'paymentData' => [
                            'id'   => $channel->id,
                            'credit_card' => $paymentData,
                            'paymentMethodData' => $card,
                            'payment_method' => [
                                'id' => $card->type_card_id 
                            ],
                            'credit_value' => $channel->cha_api_official == 0? $feeUnofficialChannel->fee_value : $feeOfficialChannel->fee_value,
                            'credit_description' => 'Assinatura de canal',
                        ]
                    ]);
                    $paymentResponse = self::addPayment($payData, true);
                    Log::debug('$paymentResponse automaticSubscriptionRenewal');
                    Log::debug($paymentResponse);
                }
            }
        }
    }

    //Assina os canais que estão em modo Trial (são canais contratados no penúltimo ou último dia do mês)
    public function subscribeTrialChannels()
    {
        $communicatorController = new CommunicatorController();
        $today = Carbon::now()->startOfDay();
        //$today = Carbon::parse('2022-02-01')->startOfDay();
        $day = $today->day;

        Log::debug('day subscribeTrialChannels');
        Log::debug($day);
        //Se for o primeiro dia do mês
        if($day == 1) {
            $channels = Channel::where('cha_api_official', 0) //Canais não oficiais
                                ->where('cha_trial', 1) //Canais trials
                                ->where('cha_status','!=', 'D') //E que não foram deletados
                                ->get();
            
            Log::debug('canais trials que foram assinados');
            Log::debug($channels);

            foreach($channels AS $channel) {
                //Assina o canal
                $instanceSubscriptionData = $communicatorController->subscriptionInstance($channel);

                //Se o canal foi assinado com sucesso
                if(isset($instanceSubscriptionData['due'])) {
                    //Coloca o canal como NÃO TRIAL
                    $channelTrial = Channel::find($channel['id']);
                    $channelTrial->cha_trial = 0;
                    $channelTrial->save();
                }
            }
        }
    }

    //Desabilita uma notificação específica de um canal
    public function hideNotification(Request $request)
    {
        $channelNotification = ChannelNotification::find($request['notificationId']);
        $channelNotification->cha_status = 'I';
        $channelNotification->save();
    }

    //Traz os dados de assinatura de um canal
    public function getChannelSubscription($channelId)
    {
        $channelSubscription = ChannelSubscription::where('channel_id', $channelId)
                                                    ->where('cha_status', 'A')
                                                    ->first();
        return $channelSubscription;
    }

    //Atualiza o status de uma assinatura automática de um canal
    public function updateChannelSubscriptionStatus($channelId, $cardId, $statusId)
    {
        $channelSubscription = ChannelSubscription::where('cha_status', 'A');
        if($channelId) {
            $channelSubscription = $channelSubscription->where('channel_id', $channelId);
        }
        if($cardId) {
            $channelSubscription = $channelSubscription->where('card_id', $cardId);
        }                                            
                                                    
        $channelSubscription = $channelSubscription->first();

        $channelSubscription->cha_status = $statusId;
        $channelSubscription->save();

        //Se o usuário está desabilitando uma assinatura automática
        if($statusId == 'I') {
            $channel = Channel::find($channelSubscription->channel_id);
            $channel->cha_automatic_subscription_renewal = 0;
            $channel->save();
        }
    }

    public function updateStatusChannelPaymentChargeApiId($paymentId, $statusPayment)
    {
        $channelCharge = ChannelPayment::where('api_payment_charge_id', $paymentId)->first();
        //Se for o status de pagamento de um canal
        if($channelCharge) {
            $channelCharge->cha_status = 'A';
            $channelCharge->save();

            //Cria a instância, se necessário e seta a data de vencimento do canal
            self::setChannelDue($channelCharge->channel_id, false);
        }
    }

    //Traz os canais pelo status
    public function getTotalChannelsByStatus($status)
    {
        //Traz os canais com um status correspondente
        $channels = Channel::where('cha_status', $status)
                            ->count();
        
        return $channels;
    }

    public function addFairDistributionConfChannel()
    {
        $channels = Channel::get();

        foreach($channels AS $channel) {
            $hasFairDistributionChannel = ParameterChannel::where('channel_id', $channel->id)
                                                            ->where('type_parameter_channel_id', 4)
                                                            ->first();
            
            //Se ainda não existe o registro de distribuição igualitária, cria um
            if(!$hasFairDistributionChannel) {
                $FairDistributionChannel = new ParameterChannel();
                $FairDistributionChannel->channel_id = $channel->id;
                $FairDistributionChannel->type_parameter_channel_id = 4; //Distribuição igualitária
                $FairDistributionChannel->par_value = NULL;
                $FairDistributionChannel->save();
            }
        }
    }

    //Cria as configurações de time de avaliação de atendimento para cada canal
    public function addEvaluationTimeConfChannel()
    {
        $channels = Channel::get();

        foreach($channels AS $channel) {
            $hasEvaluationTimeChannel = ParameterChannel::where('channel_id', $channel->id)
                                                            ->where('type_parameter_channel_id', 5)
                                                            ->first();
            
            //Se ainda não existe o registro de tempo de avaliação, cria um
            if(!$hasEvaluationTimeChannel) {
                $hasEvaluationTimeChannel = new ParameterChannel();
                $hasEvaluationTimeChannel->channel_id = $channel->id;
                $hasEvaluationTimeChannel->type_parameter_channel_id = 5; //Tempo de avaliação
                $hasEvaluationTimeChannel->par_value = '900';
                $hasEvaluationTimeChannel->save();
            }
        }
    }

    //Traz um parâmetro de um canal associado a uma distribuição igualitária
    public function getChannelsParameterByFairDistibutionId($fairDistributionId)
    {
        $channels = ParameterChannel::join('man_channels', 'man_parameters_channels.channel_id', 'man_channels.id')
                                    ->where('fair_distribution_id', $fairDistributionId)
                                    ->where('par_status', 'A')
                                    ->get();

        return $channels;
    }
}
