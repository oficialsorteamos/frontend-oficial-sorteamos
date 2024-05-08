<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Api\ApiEHostController;
use App\Http\Controllers\Api\ApiZapController;
use App\Http\Controllers\Api\CommunicatorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Jobs\SendCompanyNotification;
use App\Models\Administration\Instance\Instance;
use App\Models\Administration\Instance\InstanceConnectionStatus;
use App\Models\Administration\Instance\InstanceStatus;
use App\Models\Administration\Notification\Notification;
use App\Models\Administration\Notification\NotificationCompany;
use App\Models\Administration\Notification\NotificationTypeUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class InstanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::debug('instances $request');
        Log::debug($request);

        //Se o usuário não digitou nada no campo de pesquisa
        $skip = (($request['page']-1) * $request['perPage']);

        $instances = Instance::with('status', 'connectionStatus');
                            //->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_avatar');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($request['q'] != '') {
            $instances = $instances->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('adm_instances.ins_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('adm_instances.ins_token', 'like', '%'.trim($request['q']).'%');
            });
        }
        if($request['instanceStatus'] != '') {
            $instances = $instances->where('ins_status_instance_id', $request['instanceStatus']);
        }
        if($request['instanceConnectionStatus'] != '') {
            $instances = $instances->where('ins_status_connection_id', $request['instanceConnectionStatus']);
        }
        if($request['api'] != '') {
            $instances = $instances->where('api_communication_id', $request['api']);
        }
        $instances = $instances->orderBy('adm_instances.created_at', 'DESC');
        $total = $instances->count();
        $instances = $instances->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();

        //Log::debug('notifications');
        //Log::debug($notifications);

        return response()->json([
            'instances'=> $instances,
            'total'=> $total,
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
        
        $instance = new Instance();

        $instance->api_communication_id = $request['api_communication_id'];
        $instance->ins_name = $request['ins_name'];
        $instance->ins_webhook = $request['ins_webhook'];
        $instance->ins_token = $request['ins_token'];
        $instance->ins_status_instance_id = $request['ins_status_instance_id'];
        $instance->ins_status_connection_id = $request['ins_status_connection_id'];
        $instance->ins_dt_created = $request['ins_dt_created'];
        $instance->save();
        
        return $instance;
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
        $instance = Instance::find($request['id']);

        $instance->api_communication_id = $request['api_communication_id'];
        $instance->ins_name = $request['ins_name'];
        $instance->ins_webhook = $request['ins_webhook'];
        $instance->ins_token = $request['ins_token'];
        $instance->ins_status_instance_id = $request['ins_status_instance_id'];
        $instance->ins_status_connection_id = $request['ins_status_connection_id'];
        $instance->ins_dt_created = $request['ins_dt_created'];
        $instance->save();
        
        return $instance;   
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

    //Traz as instâncias associadas a uma API
    public function getInstancesByApi($apiId)
    {
        $instances = Instance::where('api_communication_id', $apiId)
                            ->where('ins_status_instance_id','!=', 3) //Onde a instância não foi removida
                            ->get();

        return $instances;
    }

    //Armazena ou atualiza os dados das instâncias
    public function updateInstances()
    {
        $apiZapController = new ApiZapController();

        $response = $apiZapController->listInstances();
        //Log::debug('updateInstances response');
        //Log::debug($response);

        //Se existirem instâncias
        if(isset($response['result']) && $response['result'] == 'success') {

            //Traz todas as instâncias LOCAIS da API ZAP
            $localInstances = self::getInstancesByApi(6);

            //Para cada instância LOCAL
            foreach($localInstances AS $localInstance) {
                //Verifica se a instância do canal existe na API
                $instanceExist = array_filter($response['instances'], function ($instanceData) use($localInstance) {
                    return ($instanceData['token_key'] == $localInstance['ins_token']);
                });

                //Se a instância NÃO EXISTE MAIS, exclui a mesma
                if(empty($instanceExist)) {
                    $localInstance->ins_status_instance_id = 3; //Remove o canal
                    $localInstance->ins_status_connection_id = 2; //Coloca o canal como desconectado
                    $localInstance->save();
                }
            }

            


            foreach($response['instances'] AS $instance) {
                $hasInstance = null;

                $hasInstance = self::getInstanceByToken($instance['token_key']);

                //Se a instância ainda não foi armazenada no banco de dados
                if(!$hasInstance) {
                    $requestInstanceData = new Request([
                        "api_communication_id" => 6,
                        "ins_name" => $instance['instance_name'],
                        "ins_webhook" => $instance['webhook_url'],
                        "ins_token" => $instance['token_key'],
                        "ins_status_instance_id" => self::getInstanceStatusId($instance['status']),
                        "ins_status_connection_id" => self::getInstanceConnectionStatusId($instance['connection']),
                        "ins_dt_created" => $instance['created_at'],
                    ]);

                    self::store($requestInstanceData);
                } //Se instância já existe na base dados
                else {
                    $requestInstanceData = new Request([
                        "id" => $hasInstance['id'],
                        "api_communication_id" => 6,
                        "ins_name" => $instance['instance_name'],
                        "ins_webhook" => $instance['webhook_url'],
                        "ins_token" => $instance['token_key'],
                        "ins_status_instance_id" => self::getInstanceStatusId($instance['status']),
                        "ins_status_connection_id" => self::getInstanceConnectionStatusId($instance['connection']),
                        "ins_dt_created" => $instance['created_at'],
                    ]);
                    //Atualiza os dados da instância
                    self::update($requestInstanceData);
                }
            }
        }

        //##################### API EHOST ##########################
        $response = null;
        $apiEHostController = new ApiEHostController();

        $response = $apiEHostController->listInstances();

        //Se a requisição foi processada com sucesso
        if($response['code'] == 200 && $response['success'] == true) {
            //Traz todas as instâncias LOCAIS da EHOST
            $localInstances = self::getInstancesByApi(8);

            //Para cada instância LOCAL
            foreach($localInstances AS $localInstance) {
                //Verifica se a instância do canal existe na API
                $instanceExist = array_filter($response['data']['Data'], function ($instanceData) use($localInstance) {
                    return ($instanceData['Token'] == $localInstance['ins_token']);
                });

                //Se a instância NÃO EXISTE MAIS, exclui a mesma
                if(empty($instanceExist)) {
                    $localInstance->ins_status_instance_id = 3; //Remove o canal
                    $localInstance->ins_status_connection_id = 2; //Coloca o canal como desconectado
                    $localInstance->save();
                }
            }

            foreach($response['data']['Data'] AS $instance) {
                $hasInstance = null;

                $hasInstance = self::getInstanceByToken($instance['Token']);

                //Se a instância ainda não foi armazenada no banco de dados
                if(!$hasInstance) {
                    $requestInstanceData = new Request([
                        "api_communication_id" => 8,
                        "ins_name" => $instance['Name'],
                        "ins_webhook" => $instance['Webhook'],
                        "ins_token" => $instance['Token'],
                        "ins_status_instance_id" => 1,
                        "ins_status_connection_id" => $instance['Jid'] != ""? 1 : 2,
                        "ins_dt_created" => NULL,
                    ]);

                    self::store($requestInstanceData);
                } //Se instância já existe na base dados
                else {
                    $requestInstanceData = new Request([
                        "id" => $hasInstance['id'],
                        "api_communication_id" => 8,
                        "ins_name" => $instance['Name'],
                        "ins_webhook" => $instance['Webhook'],
                        "ins_token" => $instance['Token'],
                        "ins_status_instance_id" => 1,
                        "ins_status_connection_id" => $instance['Jid'] != ""? 1 : 2,
                        "ins_dt_created" => NULL,
                    ]);
                    //Atualiza os dados da instância
                    self::update($requestInstanceData);
                }
            }
        }
    }

    //Retorna uma instância pelo seu token
    public function getInstanceByToken($tokenId)
    {
        $instance = Instance::where('ins_token', $tokenId)
                            ->first();

        return $instance;
    }

    //Retorna o id do status da instância
    public function getInstanceStatusId($status)
    {
        if($status == 'active') {
            return 1;
        }
        else if($status == 'block') {
            return 2;
        }
    }

    //Retorna o id do status de conexão de uma instância
    public function getInstanceConnectionStatusId($status)
    {
        if($status == 'connected') {
            return 1;
        }
        else if($status == 'disconnected') {
            return 2;
        }
    }

    //Retorna os status das instâncias
    public function getInstanceStatus()
    {
        $instanceStatus = InstanceStatus::where('ins_status', 'A')
                                        ->get();

        return $instanceStatus;
    }

    //Retorna os status de conexão das instâncias
    public function getInstanceConnectionStatus()
    {
        $instanceConnectionStatus = InstanceConnectionStatus::where('ins_status', 'A')
                                                    ->get();

        return $instanceConnectionStatus;
    }

    //Desconecta uma instância do WhatsApp
    public function disconnectInstance(Request $request)
    {
        $communicatorController = new CommunicatorController();
        
        $communicatorController->disconnectInstance($request);   
    }

    //Remove uma instância da API de WhatsApp
    public function removeInstance(Request $request)
    {
        $communicatorController = new CommunicatorController();

        $communicatorController->disconnectInstance($request);

        $channel['cha_api_official'] = false;
        $channel['api_communication_id'] = $request['api_communication_id'];
        $channel['cha_session_token'] = $request['ins_token'];

        $communicatorController->cancelInstance($channel);
    }
}
