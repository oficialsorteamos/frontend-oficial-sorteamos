<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\DepartmentController;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Setting\CustomerController;
use App\Models\Chat\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Auth;

use App\Models\Chat\Chat;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\FairDistribution;
use App\Models\Chat\FairDistributionChannel;
use App\Models\Chat\FairDistributionSetup;
use App\Models\Chat\Service;
use App\Models\Chat\ServiceEvaluation;
use App\Models\Chat\TypeStatusService;
use App\Models\Chatbot\ChatbotControl;
use App\Models\Contact\Contact;
use App\Models\Management\Channel\ParameterChannel;
use App\Models\Management\UserDepartment;
use Carbon\Carbon;

class ServiceController extends Controller
{
    private $userController;
    private $eventController;
    private $departmentController;

    public function __construct()
    {
        $this->userController = new UserController();
        $this->eventController = new EventController();
        $this->departmentController = new DepartmentController;
    }
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
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       
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
    public function update(Request $request, $id)
    {
        //
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

    //Traz os atendimentos realizados de um determinado chat de um contato
    public function getServicesContact(Request $request)
    {   
        try {
            //Quantidade de itens exibidos a cada click
            $amountPerClick = 2;
            //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
            $skip = ($request['offset'] * $amountPerClick);
            //Log::debug('skip');
            //Log::debug($skip);
            //Traz os chats associados ao contato (O usuário pode ter mais de um chat quando ele tem mais de um número de telefone associado a ele)
            $chatsId = Chat::where('contact_id', $request['id'])
                            ->pluck('id')
                            ->toArray(); 

            $services = Service::with('actions', 'actions.userReceive', 'actions.userSender', 'actions.departmentReceive', 'actions.departmentSender', 'evaluation',
                                        'userEndService')
                                ->whereIn('chat_id', [$chatsId]);
            if($request['serviceId'] != null) {
                $services = $services->where('id', $request['serviceId']);
            }
            $services = $services->orderBy('id', 'desc')
                                ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                ->take($amountPerClick) //Quantidade de itens trazidos
                                ->get();
            
            foreach ($services as $service) {
                //Pega a primeira mensagem enviada pelo operador para o contato
                $firstMessageOperator = ChatMessage::with('userSender')
                                                    ->where('service_id', $service->id)
                                                    ->where('type_user_id', 1)
                                                    ->orderBy('id', 'ASC')
                                                    ->first();

                $service->setAttribute('startComunicationOperator', $firstMessageOperator);

                //Calcula há quanto tempo o atendimento foi iniciado
                $timeDiff = Carbon::parse($service->created_at)->diffForHumans(Carbon::now());
                $service->setAttribute('timeDiff', $timeDiff);
            }
            
            Log::debug('atendimentos');
            Log::debug($services);

            return response()->json(
                $services
            , 201);
        } catch(e) {

        }
        
    }

    //Traz os atendimentos realizados por um determinado usuário do sistema
    public function getServicesUser(Request $request)
    {   
        try {
            //Quantidade de itens exibidos a cada click
            $amountPerClick = 2;
            //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
            $skip = ($request['offset'] * $amountPerClick);
            Log::debug('skip');
            Log::debug($skip);
            //Traz os chats associados ao contato (O usuário pode ter mais de um chat quando ele tem mais de um número de telefone associado a ele)
            /*$chatsId = Chat::where('contact_id', $request['id'])
                            ->pluck('id')
                            ->toArray();*/ 

            $services = Service::with('actions', 'actions.userReceive', 'actions.userSender', 'actions.departmentReceive', 'actions.departmentSender', 'evaluation',
                                        'userEndService');
            //Filtra pelos atendimentos que o usuário realizou (participou)
            $services = $services->whereHas('actions', function($q) use($request)
            {
                $q->where('cha_actions.user_id', $request['id']);	
            });
                                //->whereIn('chat_id', [$chatsId])
            $services = $services->orderBy('id', 'desc')
                                ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                ->take($amountPerClick) //Quantidade de itens trazidos
                                ->get();
            
            Log::debug('atendimentos realizados pelo operador');
            Log::debug($services);
            foreach ($services as $service) {
                //Pega a primeira mensagem enviada pelo operador para o contato
                $firstMessageOperator = ChatMessage::with('userSender')
                                                    ->where('service_id', $service->id)
                                                    ->where('type_user_id', 1)
                                                    ->orderBy('id', 'ASC')
                                                    ->first();

                $service->setAttribute('startComunicationOperator', $firstMessageOperator);

                //Calcula há quanto tempo o atendimento foi iniciado
                $timeDiff = Carbon::parse($service->created_at)->diffForHumans(Carbon::now());
                $service->setAttribute('timeDiff', $timeDiff);
            }
            
            Log::debug('atendimentos');
            Log::debug($services);

            return response()->json(
                $services
            , 201);
        } catch(e) {

        }
        
    }

    public function getServices($chatId, $statusService)
    {
        //Traz atendimentos a um chat de acordo com o seu status
        $services = Service::whereIn('chat_id', [$chatId])
                            ->where('type_status_service_id', $statusService)
                            ->get();
        return $services;
    }

    //Retorna a nota média das avaliações de atendimento realizadas por um usuário
    public function getAvgServicesEvaluations($servicesId)
    {
        $AvgServicesEvaluations = ServiceEvaluation::whereIn('service_id', $servicesId)
                                                ->avg('ser_rating');
        
        
        return $AvgServicesEvaluations;
    }

    //Retorna o total de atendimentos avaliados com base nos ID's de atendimentos
    public function getTotalServicesEvaluationsByServicesId($servicesId)
    {
        $AvgServicesEvaluations = ServiceEvaluation::whereIn('service_id', $servicesId)
                                                ->count();
        
        
        return $AvgServicesEvaluations;
    }

    //Retorna quantos atendimentos já foram realizados para um CONTATO específico
    public function getCountServicesContact($chatsId)
    {
        $countServices = Service::whereIn('chat_id', $chatsId)
                                ->count();
        
        return $countServices;
    }

    //Retorna quantos atendimentos já foram realizados por um USUÁRIO específico
    public function getCountServicesUser($userId)
    {
        $countServices = Service::with('actions');
        //Filtra pelos atendimentos realizados pelo usuário
        $countServices = $countServices->whereHas('actions', function($q) use($userId)
        {
            $q->where('cha_actions.user_id', $userId);	
        });
        $countServices = $countServices->count();
        
        return $countServices;
    }

    public function getServicesIdUser($userId)
    {
        $servicesId = Service::with('actions');
        //Filtra pelos atendimentos realizados pelo usuário
        $servicesId = $servicesId->whereHas('actions', function($q) use($userId)
        {
            $q->where('cha_actions.user_id', $userId);	
        });
        $servicesId = $servicesId->pluck('id');

        return $servicesId;
    }


    public function getCountServicesByStatus($statusService)
    {   
        Log::debug('Total Atendimentos');
        
        //Se foi passado algum status como parâmetro
        if($statusService) {
            //Conta os atendimentos a um chat de acordo com o seu status
            $servicesAmount = Service::where('type_status_service_id', $statusService)
                                    ->count();
        }
        else {
            //Conta todos os atendimentos realizados até o presente momento
            $servicesAmount = Service::count();
        }
        
        return $servicesAmount;
    }

    //Traz a quantidade de atendimentos realizados nos últimos meses
    public function getCountServicesLastMonths($monthsAmount)
    {   
        $servicesLastMonths = [];
        $today = Carbon::now();
        $year = $today->format('Y');
        $month = $today->format('m');
        for($i=1; $i <= $monthsAmount; $i++) {
            
            $servicesLastMonths[$i]['year'] = $year;
            $servicesLastMonths[$i]['month'] = $month;
            $servicesLastMonths[$i]['countServices'] = Service::whereYear('created_at', $year)
                                                                ->whereMonth('created_at', $month)
                                                                ->count();
            //Subtrai a data atual em 1 mês a cada girada no loop
            $lastDate = $today->subMonth(); 
            $year = $lastDate->format('Y');
            $month = $lastDate->format('m');
        }
        $servicesLastMonths = array_reverse($servicesLastMonths, false);

        return $servicesLastMonths;
    }

    public function getTypesStatusServices()
    {
        $typesStatusServices = TypeStatusService::where('typ_status', 'A')
                                        ->get();

        return $typesStatusServices;
    }
    
    //Traz o total de chats em autoatendimento
    public function getCountSelfServiceChats($channelId=null)
    {
        $totalSelfServicesChats = Service::doesntHave('actions')
                                            ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                            ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id')
                                            ->join('man_channels', 'cha_services.channel_id', 'man_channels.id')
                                            //->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id')
                                            ->where('type_status_service_id', 1);
        //Se foi especificado o canal, filtra quantidade autoatendimentos por canal
        if($channelId) {
            $totalSelfServicesChats = $totalSelfServicesChats->where('man_channels.id', $channelId);
        }
        $totalSelfServicesChats = $totalSelfServicesChats->orderBy('cha_services.id', 'desc')
                                                        ->count();
        return $totalSelfServicesChats;
    }

    //Traz os chats que estão em autoatendimento
    public function fetchSelfServiceChats(Request $request, $searchData=false)
    {   
        //Log::debug('request selfservice');
        //Log::debug($request);
        $skip = null;
        
        //Se o usuário NÃO estiver pesquisando por algum atendimento específico
        if($searchData == false) {
            //Quantidade de itens exibidos a cada click
            $amountPerClick = 10;

            if($request['skip'] == 'true') {
                //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
                //extraSkipValue é quantidade de atendimentos extras que serão pulados para que os atendimentos que já estavam sendo exibidos continuem sendo exibidos
                //$skip = ($request['offset'] * $amountPerClick) + $request['extraSkipValue'];
                $skip = ($request['offset'] * $amountPerClick);
            }
            else {
                //Traz a quantidade que já estava sendo exibida na página + 1
                //$amountPerClick = ((($request['offset']+1) * $amountPerClick)+1);
                $amountPerClick = ((($request['offset']+1) * $amountPerClick));
            }
        }
        
        

        //Traz os atendimentos que e estão em autoatendimento, ou seja, que estão absertos e não tem ações associadas ao mesmo
        $selfServiceChats = Service::select('cha_services.id', 'con_contacts.id as contactId', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 
                                            'ser_protocol_number', 'man_channels.cha_name', 'cam_campaigns.cam_name', 'cam_campaigns.cam_description', 
                                            'int_dialers.dia_name', 'cam_campaigns.campaign_type_id')
                                    ->doesntHave('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id')
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id')
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    ->leftJoin('int_dialers_fowarding_settings', 'cha_services.dialer_fowarding_setting_id', 'int_dialers_fowarding_settings.id') //Traz a campanha associada ao atendimento, se existir
                                    ->leftJoin('int_dialers', 'int_dialers_fowarding_settings.dialer_id', 'int_dialers.id') //Traz a campanha associada ao atendimento, se existir
                                    //->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id')
                                    ->where('type_status_service_id', 1);
        if($request['q'] != '' && strlen($request['q']) > 3) {
            $selfServiceChats = $selfServiceChats->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('con_phone', 'like', '%'.trim($request['q']).'%');
            });
        }
        if($request['channel'] != '') {
            $selfServiceChats = $selfServiceChats->where('man_channels.id', $request['channel']);
        }
        //Se o usuário estiver filtrando por atendimentos com uma determinada origem
        if($request['origin'] != '') {
            if($request['origin'] == 'C') {
                $selfServiceChats = $selfServiceChats->whereNotNull('cam_campaigns.cam_name');
            }
            else if($request['origin'] == 'U') {
                $selfServiceChats = $selfServiceChats->whereNotNull('cha_services.dialer_fowarding_setting_id');
            }
            else if($request['origin'] == 'S') {
                $selfServiceChats = $selfServiceChats->whereNull('cam_campaigns.cam_name');
            }
        }
        //Se for para pular algum contato
        if($skip) {
            $selfServiceChats = $selfServiceChats->skip($skip); //Quantidade de itens que serão desconsiderados por já terem sido carregados 
        }
        
        if(isset($amountPerClick)) {
            $selfServiceChats = $selfServiceChats->take($amountPerClick); //Quantidade de itens trazidos
        }
        
        $selfServiceChats = $selfServiceChats->orderBy('cha_services.id', 'desc')
                                            ->get();
        
        //Log::debug('$selfServiceChats');
        //Log::debug($selfServiceChats);
        if($searchData == false) {
            return response()->json([
                'selfServices' => $selfServiceChats,
                'totalSelfServices' => self::getCountSelfServiceChats(),
                'skip' => $skip,
            ], 201);
        }
        else {
            return $selfServiceChats;
        }
    }

    //Traz os chats que estão com atendimento Em Andamento (ativos)
    /*public function getCountActiveServiceChats()
    {   
        //Traz os atendimentos que e estão em andamento
        $totalActiveServices = Service::select('cha_services.id', 'con_contacts.id as contactId', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 
                                            'name', 'dep_name', 'ser_protocol_number', 'man_channels.cha_name', 'cam_campaigns.cam_name', 'cam_campaigns.cam_description')
                                    ->has('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id') //Traz os dados do contato
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id') //Traz os dados do canal
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    ->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->where('cha_actions.id', function($query) {
                                        //Traz a última ação realizada no chat
                                        $query->select('id')
                                            ->from('cha_actions')
                                            ->whereColumn('chat_id', 'cha_chats.id')
                                            ->whereColumn('service_id', 'cha_services.id')
                                            ->latest()
                                            ->limit(1);
                                        })
                                    ->join('users', 'cha_actions.user_id', 'users.id') //Usuário que está realizando o atendimento
                                    ->join('man_departments', 'cha_actions.department_id', 'man_departments.id') //Departamento responsável pelo atendimento
                                    ->where('type_status_service_id', 1) //Onde o atendimento está aberto
                                    ->orderBy('cha_services.id', 'desc')
                                    ->count();
        
        return $totalActiveServices;
    }*/


    //Traz os chats que estão com atendimento Em Andamento (ativos)
    public function fetchActiveServiceChats(Request $request, $searchData=false)
    {
        $skip = null;

        if($searchData == false) {
            //Quantidade de itens exibidos a cada click
            $amountPerClick = 10;

            if($request['skip'] == 'true') {
                //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
                //extraSkipValue é quantidade de atendimentos extras que serão pulados para que os atendimentos que já estavam sendo exibidos continuem sendo exibidos
                //$skip = ($request['offset'] * $amountPerClick) + $request['extraSkipValue'];
                $skip = ($request['offset'] * $amountPerClick);
            }
            else {
                //Traz a quantidade que já estava sendo exibida na página + 1
                //$amountPerClick = ((($request['offset']+1) * $amountPerClick)+1);
                $amountPerClick = ((($request['offset']+1) * $amountPerClick));
            }
        }

        //Traz os atendimentos que e estão em andamento
        $activeServices = Service::select('cha_services.id', 'con_contacts.id as contactId', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 
                                            'name', 'dep_name', 'ser_protocol_number', 'man_channels.cha_name', 'cam_campaigns.cam_name', 'cam_campaigns.cam_description',
                                            'int_dialers.dia_name', 'cam_campaigns.campaign_type_id')
                                    ->has('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id') //Traz os dados do contato
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id') //Traz os dados do canal
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    ->leftJoin('int_dialers_fowarding_settings', 'cha_services.dialer_fowarding_setting_id', 'int_dialers_fowarding_settings.id') //Traz a campanha associada ao atendimento, se existir
                                    ->leftJoin('int_dialers', 'int_dialers_fowarding_settings.dialer_id', 'int_dialers.id') //Traz a campanha associada ao atendimento, se existir
                                    /*->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->where('cha_actions.id', function($query) {
                                        //Traz a última ação realizada no chat
                                        $query->select('id')
                                            ->from('cha_actions')
                                            ->whereColumn('chat_id', 'cha_chats.id')
                                            ->whereColumn('service_id', 'cha_services.id')
                                            ->latest()
                                            ->limit(1);
                                        })*/
                                    ->join('cha_actions', function($join) {
                                        $join->on('cha_chats.id', '=', 'cha_actions.chat_id')
                                            ->whereColumn('service_id', 'cha_services.id');
                                    })
                                    ->join('users', 'cha_actions.user_id', 'users.id') //Usuário que está realizando o atendimento
                                    ->join('man_departments', 'cha_actions.department_id', 'man_departments.id') //Departamento responsável pelo atendimento
                                    ->where('type_status_service_id', 1) //Onde o atendimento está aberto
                                    ->whereRaw('cha_actions.id = (
                                        SELECT MAX(id) 
                                        FROM cha_actions 
                                        WHERE chat_id = cha_chats.id 
                                        AND service_id = cha_services.id
                                    )');
        if($request['q'] != '' && strlen($request['q']) > 3) {
            $activeServices = $activeServices->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('con_phone', 'like', '%'.trim($request['q']).'%');
            });
        }
        if($request['department'] != '') {
            $activeServices = $activeServices->where('man_departments.id', $request['department']);
        }
        if($request['user'] != '') {
            $activeServices = $activeServices->where('users.id', $request['user']);
        }
        if($request['channel'] != '') {
            $activeServices = $activeServices->where('man_channels.id', $request['channel']);
        }
        //Se o usuário estiver filtrando por atendimentos com uma determinada origem
        if($request['origin'] != '') {
            if($request['origin'] == 'C') {
                $activeServices = $activeServices->whereNotNull('cam_campaigns.cam_name');
            }
            else if($request['origin'] == 'U') {
                $activeServices = $activeServices->whereNotNull('cha_services.dialer_fowarding_setting_id');
            }
            else if($request['origin'] == 'S') {
                $activeServices = $activeServices->whereNull('cam_campaigns.cam_name');
            }
        }

        $total = $activeServices->count();

        //Se for para pular algum contato
        if($request['skip'] == 'true') {
            $activeServices = $activeServices->skip($skip); //Quantidade de itens que serão desconsiderados por já terem sido carregados 
        }
        if(isset($amountPerClick)) {
            $activeServices = $activeServices->take($amountPerClick); //Quantidade de itens trazidos
        }
        
        $activeServices = $activeServices->orderBy('cha_services.id', 'desc')
                                        ->get();
        
        if($searchData == false) {
            return response()->json([
                'activeServices' => $activeServices,
                'totalActiveServices' => $total,
                'skip' => $skip,
            ], 201);
        }
        else {
            return $activeServices;
        }
    }

    //Retorna o total de chats pendentes
    public function getCountPendingServiceChats()
    {
        //Traz os atendimentos que e estão em andamento
        $totalPendingServices = Service::select('cha_services.id', 'con_contacts.id as contactId', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 
                                            'dep_name', 'ser_protocol_number', 'man_channels.cha_name', 'cam_campaigns.cam_name', 'cam_campaigns.cam_description')
                                    ->has('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id') //Traz os dados do contato
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id') //Traz os dados do canal
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    ->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->where('cha_actions.id', function($query) {
                                        //Traz a última ação realizada no chat
                                        $query->select('id')
                                            ->from('cha_actions')
                                            ->whereColumn('chat_id', 'cha_chats.id')
                                            ->whereColumn('service_id', 'cha_services.id')
                                            ->latest()
                                            ->limit(1);
                                        })
                                    ->join('man_departments', 'cha_actions.department_id', 'man_departments.id') //Departamento responsável pelo atendimento
                                    ->whereNull('cha_actions.user_id') //Onde nenhum operador assuniu o atendimento
                                    ->where('type_status_service_id', 1) //Onde o atendimento está aberto
                                    ->orderBy('cha_services.id', 'desc')
                                    ->count();

        return $totalPendingServices;
    }

    //Traz os chats que estão com atendimento Pendentes (Aguardando um operador assumir o atendimento)
    public function fetchPendingServiceChats(Request $request, $searchData=false)
    { 
        $skip = null;

        //Se o usuário NÃO estiver pesquisando por algum atendimento específico
        if($searchData == false) {
            //Quantidade de itens exibidos a cada click
            $amountPerClick = 10;

            if($request['skip'] == 'true') {
                //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
                //extraSkipValue é quantidade de atendimentos extras que serão pulados para que os atendimentos que já estavam sendo exibidos continuem sendo exibidos
                //$skip = ($request['offset'] * $amountPerClick) + $request['extraSkipValue'];
                $skip = ($request['offset'] * $amountPerClick) + $request['extraSkipValue'];
            }
            else {
                //Traz a quantidade que já estava sendo exibida na página + 1
                //$amountPerClick = ((($request['offset']+1) * $amountPerClick)+1);
                $amountPerClick = ((($request['offset']+1) * $amountPerClick));
            }
        }
        //Traz os atendimentos que e estão em andamento
        $pendingServices = Service::select('cha_services.id', 'con_contacts.id as contactId', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 
                                            'dep_name', 'ser_protocol_number', 'man_channels.cha_name', 'cam_campaigns.cam_name', 'cam_campaigns.cam_description', 
                                            'int_dialers.dia_name', 'cam_campaigns.campaign_type_id')
                                    ->has('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id') //Traz os dados do contato
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id') //Traz os dados do canal
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    ->leftJoin('int_dialers_fowarding_settings', 'cha_services.dialer_fowarding_setting_id', 'int_dialers_fowarding_settings.id') //Traz a campanha associada ao atendimento, se existir
                                    ->leftJoin('int_dialers', 'int_dialers_fowarding_settings.dialer_id', 'int_dialers.id') //Traz a campanha associada ao atendimento, se existir
                                    /*->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->where('cha_actions.id', function($query) {
                                        //Traz a última ação realizada no chat
                                        $query->select('id')
                                            ->from('cha_actions')
                                            ->whereColumn('chat_id', 'cha_chats.id')
                                            ->whereColumn('service_id', 'cha_services.id')
                                            ->latest()
                                            ->limit(1);
                                        })*/
                                    ->join('cha_actions', function($join) {
                                        $join->on('cha_chats.id', '=', 'cha_actions.chat_id')
                                            ->whereColumn('service_id', 'cha_services.id');
                                    })
                                    ->join('man_departments', 'cha_actions.department_id', 'man_departments.id') //Departamento responsável pelo atendimento
                                    ->whereNull('cha_actions.user_id') //Onde nenhum operador assuniu o atendimento
                                    ->where('type_status_service_id', 1) //Onde o atendimento está aberto
                                    ->whereRaw('cha_actions.id = (
                                        SELECT MAX(id) 
                                        FROM cha_actions 
                                        WHERE chat_id = cha_chats.id 
                                        AND service_id = cha_services.id
                                    )');
        if($request['q'] != '' && strlen($request['q']) > 3) {
            $pendingServices = $pendingServices->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('con_phone', 'like', '%'.trim($request['q']).'%');
            });
        }
        if($request['department'] != '') {
            $pendingServices = $pendingServices->where('man_departments.id', $request['department']);
        }
        if($request['channel'] != '') {
            $pendingServices = $pendingServices->where('man_channels.id', $request['channel']);
        }
        //Se o usuário estiver filtrando por atendimentos com uma determinada origem
        if($request['origin'] != '') {
            if($request['origin'] == 'C') {
                $pendingServices = $pendingServices->whereNotNull('cam_campaigns.cam_name');
            }
            else if($request['origin'] == 'U') {
                $pendingServices = $pendingServices->whereNotNull('cha_services.dialer_fowarding_setting_id');
            }
            else if($request['origin'] == 'S') {
                $pendingServices = $pendingServices->whereNull('cam_campaigns.cam_name');
            }
        }

        $total = $pendingServices->count();
        
        //Se for para pular algum contato
        if($skip) {
            $pendingServices = $pendingServices->skip($skip); //Quantidade de itens que serão desconsiderados por já terem sido carregados 
        }

        if(isset($amountPerClick)) {
            $pendingServices = $pendingServices->take($amountPerClick); //Quantidade de itens trazidos
        }
        
        $pendingServices = $pendingServices->orderBy('cha_services.id', 'desc')
                                            ->get();
        
        if($searchData == false) {
            return response()->json([
                'pendingServices' => $pendingServices,
                'totalPendingServices' => $total,
                'skip' => $skip,
            ], 201);
        }
        else {
            return $pendingServices;
        }
    }

    //Traz todos os atendimentos que estão em progresso (autoatendimento, Pendentes ou Em Antendimento)
    public function fetchServicesInProgress(Request $request)
    {
        //Log::debug('dados dos serviços');
        //Log::debug($request);
        //Caso algum departamento tenha sido selecionado (retorna autoatendimento vazio pois autoatendimento não tem departamento associado)
        if($request['department'] != '' || $request['user'] != '' || (isset($request['origin']) && $request['origin'] != '')) {
            $selfServices = [];    
        }
        else {
            $selfServices = self::fetchSelfServiceChats($request, true);
        }
        //Se tiver algum usuário selecionado (Retorna atendimentos pendentes como vazio já que não existe usuário associado a um atendimento pendente)
        if($request['user'] != '') {
            $pendingServices = [];    
        }
        else {
            $pendingServices = self::fetchPendingServiceChats($request, true);
        }
        
        $activeServices = self::fetchActiveServiceChats($request, true);

        return response()->json([
            'selfServices' => $selfServices,
            'pendingServices' => $pendingServices,
            'activeServices' => $activeServices,
        ], 201);
    }

    //Chama o evento que atualiza a tela com os atendimentos em progresso
    public function updateServiceProgressEvent()
    {
        //Traz todos os usuários que são gestores
        $usersSendServiceProgress = $this->userController->getUsersByRoles([1, 3]);
                
        //Caso exista algum usuário com perfil de GESTOR
        if($usersSendServiceProgress) {
            //Atualiza a tela de serviços em progresso, espeficiamente, colocando os serviços pendentes como em andamento
            foreach ($usersSendServiceProgress as $user) {
                $this->eventController->updateServiceProgress($user->id);
            }
        }
    }

    //Transfere um atendimento para um departamento ou usuário
    public function transferService(Request $request, $manager = false)
    {
        //Traz o atendimento aberto no momento
        $opennedService = self::getServices($request->transferData['chatId'], 1);
        
        //Traz os dados da última transferência durante o atendimento atual, se houver
        $lastTransfer = Action::where('chat_id', $request->transferData['chatId'])
                                ->where('service_id', $opennedService[0]->id)
                                ->orderBy('id', 'desc')
                                ->first();
        
        Log::debug('última transferência');
        Log::debug($lastTransfer);
        //Se houver alguma transferência anterior ou caso seja GESTOR que esteja realizando a transferência
        if($lastTransfer || $manager) {
            $transferData = new Action();
            $transferData->service_id = $opennedService[0]->id;
            $transferData->chat_id = $request->transferData['chatId'];
            $transferData->type_action_id = 1;
            $transferData->department_id = $request->transferData['department']['id']; //Departamento para onde o atendimento será transferido
            $transferData->user_id = isset($request->transferData['user']['id'])? $request->transferData['user']['id'] : null; //Se algum usuário foi selecionado, envia o atendimento para o mesmo
            $transferData->department_id_sender = isset($lastTransfer->department_id)? $lastTransfer->department_id : null; //Departamento que está transferindo o atendimento ( é nulo caso seja o GESTOR quem esteja transferindo)
            $transferData->user_id_sender = Auth::check()? Auth::user()->id : 1; //Usuário ou robô que está transferindo o atendimento
            
            //Se o atendimento foi transferido
            if($transferData->save()) {
                //Se foi um usuário logado que transferiu
                if(Auth::check()) {
                    //Atualiza a lista de contatos na tela do operador que está transferindo
                    $this->eventController->updateChats(Auth::user()->id);
                }
                
                //Se o atendimento foi transferido para um usuário específico
                if(isset($request->transferData['user'])) {
                    //Marca o atendimento como um chat NOVO
                    $opennedService[0]->ser_new_service = true;
                    $opennedService[0]->save();
                    //Atualiza a área de chats de quem recebeu o atendimento e quem transferiu o mesmo
                    $this->eventController->updateChats($request->transferData['user']['id']);

                    if(isset($lastTransfer)) {
                        //Atualiza a área de chats de quem transferiu o atendimento (em caso do gestor ter transferido)
                        $this->eventController->updateChats($lastTransfer->user_id);
                    }
                } else {
                    
                    if(isset($lastTransfer)) {
                        if($lastTransfer->user_id) {
                            //Atualiza a área de chats de quem transferiu o atendimento (em caso do gestor ter transferido)
                            $this->eventController->updateChats($lastTransfer->user_id);
                        }
                    }
                    
                    //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
                    $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                                                    ->join('users', 'man_users_departments.user_id', 'users.id')
                                                    ->where('department_id', $request->transferData['department']['id'])
                                                    ->where('users.status', 'A') //Onde o status do usuário é ativo
                                                    ->where('use_status', 'A')
                                                    ->get();
                    
                    //Caso exista algum usuário no departamento para onde o contato foi transferido
                    if($usersSendEvent) {
                        //Atualiza os chats para o contato em específico só aparecer como ativo para o operador que capturou o atendimento
                        foreach ($usersSendEvent as $userSendEvent) {
                            $this->eventController->updateChats($userSendEvent->user_id);
                        }
                    }

                    //Chama o evento que atualiza a tela com os atendimentos em progresso
                    self::updateServiceProgressEvent();

                    //Chama o evento que atualiza a situação do atendimento
                    self::updateSituationServiceOperatorEvent($request->transferData['chatId']);
                }
            }
        }
    }

    //Transfere um atendimento para um departamento ou usuário de acordo a sistuação do atendimento 
    public function transferServiceProgress(Request $request)
    {
        //Log::debug('transfer gestor');
        //Log::debug($request);
        //Caso o atendimento esteja em AUTOATENDIMENTO ou EM ANDAMENTO
        if($request->transferData['situationService'] == 1 || $request->transferData['situationService'] == 3) {
            self::transferService($request, true);
        }
        //Caso o atendimento esteja PENDENTE
        else if($request->transferData['situationService'] == 2) {
            //Remove a última transferência realizada no atendimento atual
            Action::where('chat_id', $request->transferData['chatId'])
                    ->where('service_id', $request->transferData['serviceId'])
                    ->orderBy('id', 'desc')
                    ->delete();
            
            //Transfere para o departamento/usuário
            self::transferService($request, true);
        }

        return response()->json(
            []
        , 201);
    }

    //Adiciona um novo atendimento para um departamento ou usuário
    public function addService(Request $request)
    {
        
        //Cria novo atendimento para cada contato selecionado
        foreach ($request['contacts'] as $contact) {
            
            $chatController = new ChatController();
            $service = new Service();
            $action = new ActionController();

            $service->chat_id = $contact['chat']['id'];
            $service->channel_id = $request['channel']['id']; //Modificar para trazer qualquer canal ativo que não seja usado para mensagem em massa
            $service->type_status_service_id = 1;
            $service->ser_protocol_number = $chatController->generateProtocolNumber();

            //Armazena o id do usuario caso foi selecionado um para encaminhar o atendimento
            $userId = !$request['user'] ? null : $request['user']['id'];

            if($service->save()) {
                //Se o atendimento NÃO foi transferido para um usuário específico
                if(!$request['user']) {
                    //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
                    $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                                                    ->join('users', 'man_users_departments.user_id', 'users.id')
                                                    ->where('department_id', $request['department']['id'])
                                                    ->where('users.status', 'A') //Onde o status do usuário é ativo
                                                    ->where('use_status', 'A')
                                                    ->get();

                    //Caso exista algum usuário no departamento para onde o contato foi transferido
                    if($usersSendEvent) {
                        //Atualiza os chats para o contato em específico só aparecer como ativo para o operador que capturou o atendimento
                        foreach ($usersSendEvent as $userSendEvent) {
                            $this->eventController->updateChats($userSendEvent->user_id);
                        }
                    }
                } //Se o atendimento foi transferido para um USUÁRIO ESPECÍFICO
                else {
                    $this->eventController->updateChats($request['user']['id']);
                }
                
                // Atualiza os chats para o usuario logado
                $this->eventController->updateChats(Auth::user()->id);

            }

            $actionRequest = new Request([
                'serviceId'   => $service->id,
                'chatId' => $contact['chat']['id'],
                'typeActionId' => 5, //Cria uma ação do tipo Comunicação Ativa
                'departmentId' => $request['department']['id'],
                'userId' => $userId,
            ]);

            $action->store($actionRequest);

            //Chama o evento que atualiza a tela com os atendimentos em progresso
            self::updateServiceProgressEvent();

            // Caso queira atualizar a tela dos atendimentos para outros usuarios
            // Lembrando que a funcao updateServiceProgressEvent atualiza apenas
            // Gestores
            // $this->eventController->updateServiceProgress($user->id);

        }

        return response()->json(
            []
        , 201);
    }

    //Situação do atendimento em relação ao operador
    public function situationServiceOperator($chatId, $operatorId)
    {
        $customerController = new CustomerController();
        
        $chat = Chat::find($chatId);
        $serviceData['responsibleUserIdService'] = null;
        $departmentsUser = [];

        //Traz o último atendimento
        $lastService = Service::where('chat_id', $chat->id)
                                ->orderBy('id', 'desc')
                                ->first();
        
        $company = $customerController->getCustomer();
        
        //Se o sistema estiver ATIVO
        if($company[0]->status_id == 1) {
            //Caso exista alguma ação
            if($lastService) {
                $serviceData['contactId'] = $chat->contact_id;
                //Caso esse último atendimento esteja FECHADO
                if($lastService->type_status_service_id == 3) {
                    $serviceData['situationService'] = 'close';
                }
                else if($lastService->type_status_service_id == 1) {
                    
                    //Traz todos os departamentos onde o usuário está lotado
                    $departmentsUser = $this->departmentController->fetchDepartmentsUser($operatorId);

                    //Traz a última ação realizada para o chat
                    $lastAction = Action::where('service_id', $lastService->id)
                                        ->where('chat_id', $chat->id)
                                        //->whereNull('user_id')
                                        //->whereIn('department_id', $departmentsUser->pluck('id')->toArray())
                                        ->orderBy('created_at', 'DESC')
                                        ->first();

                    //Caso exista algum ação realizada 
                    if($lastAction) {
                        //Caso o operador em questão é quem está realizando o atendimento
                        if($lastAction->user_id == $operatorId) {
                            $serviceData['situationService'] = 'activeService';
                            //Pega o id do usuário responsável pelo atendimento
                            $serviceData['responsibleUserIdService'] = $operatorId;
                        }
                        //Se o atendimento foi transferido para o departamento do operador e ninguém capturou ainda
                        else if($lastAction->user_id == null && in_array($lastAction->department_id, $departmentsUser->pluck('id')->toArray()) ) {
                            $serviceData['situationService'] = 'pending';
                            //Pega o id do usuário cujo setor tem a responsabilidade sobre o atendimento
                            $serviceData['responsibleUserIdService'] = $operatorId;
                        }
                        //Caso o atendimento não tenha sido transferido para o departamento
                        else {
                            //Bloqueia o atendimento para o OPERADOR
                            $serviceData['situationService'] = 'blockService';
                        }
                    } //Caso o contato esteja em autoatendimento
                    else {
                        //Coloca o atendimento como autoatendimento para o OPERADOR
                        $serviceData['situationService'] = 'autoService';
                    }
                } //Se o atendimento estiver em avaliação
                else {
                    $serviceData['situationService'] = 'close';
                }      
            }//Se nunca foi realizado um atendimento para o contato
            else {
                $serviceData['situationService'] = 'close';
            }
        } //Caso os sistema tenha sido BLOQUEADO POR FALTA DE PAGAMENTO, PAUSADO, etc.
        else {
            $serviceData['situationService'] = 'blockSystem';
        }
        

        //Log::debug('atualizou os atendimentos');
        //Log::debug($serviceData);

        return  $serviceData;
    }

    //Chama o evento que atualiza a situação dos atendimentos para cada operador
    public function updateSituationServiceOperatorEvent($chatId)
    {
        //Traz todos os usuários que são OPERADORES
        $usersSendSituonService = $this->userController->getUsersByRole(2);
                
        //Caso exista algum usuário com perfil de GESTOR
        if($usersSendSituonService) {
            //Atualiza a tela de serviços em progresso, espeficiamente, colocando os serviços pendentes como em andamento
            foreach ($usersSendSituonService as $user) {
                $serviceData = self::situationServiceOperator($chatId, $user->id);
                $this->eventController->updateSituationService($user->id, $serviceData);
            }
        }
    }

    public function getServiceById($serviceId)
    {
        $service = Service::find($serviceId);

        return $service;
    }

    //Traz o último atendimento associado a um chat
    public function getServiceByChatId($chatId)
    {
        //Traz o último atendimento
        $lastService = Service::where('chat_id', $chatId)
                                ->orderBy('id', 'desc')
                                ->first();
        
        return $lastService;
    }

    public function updateStatusService($serviceId, $status)
    {
        $service = Service::find($serviceId);
        $service->type_status_service_id = $status;
        //Se o atendimento está sendo fechado
        if($status == 3) {
            //Armazena a data de fechamento do atendimento
            $service->ser_dt_end_service = Carbon::now();
        }
        
        $service->save();
    }

    //Fecha os atendimentos em avaliação
    public function closeServicesEvaluation() {
        $chatbotController = new ChatbotController();
        $channelController = new ChannelController();

        $message = "O período para avaliação do atendimento expirou.";
        //Pega a data e hora atual
        $currentDateTime = Carbon::now();

        //Traz todos os atendimentos em avaliação
        $servicesEvaluation = Service::where('type_status_service_id', 4)
                                    ->get();

        //Para cada atendimento em avaliação
        foreach($servicesEvaluation as $serviceEvaluation) {
            
            $totalDurationCloseService = $currentDateTime->diffInSeconds($serviceEvaluation->ser_dt_end_service);

            //Pega o tempo máximo para avaliação de um atendimento configurado em um canal
            $evaluationTimeChannel = $channelController->getParameterChannelByType($serviceEvaluation->channel_id, 5);

            //Se já deu o tempo máximo para avaliação de atendimento
            if($totalDurationCloseService >= $evaluationTimeChannel->par_value) {
                //Fecha o atendimento
                $service = Service::find($serviceEvaluation->id);
                $service->type_status_service_id = 3;
                $service->save();
        
                //Envia uma mensagem de expiração do prazo de avaliação
                //$chatbotController->dispatchMessageAutoAttendant(null, 3, $message, null, $service->chat_id);
                
                //Aguarda entre 1 à 3 segundos para enviar a próxima mensagem
                //sleep( rand( 1, 3) );
            }
        }

        Log::debug('Atendimentos em avaliação foram fechados'); 
    }

    //Traz o o último atendimento de acordo com uma determinada situação
    public function getLastServiceSiuation($chatId, $typeServiceId, $channelId)
    {
        $lastService = Service::where('chat_id', $chatId)
                                ->where('type_status_service_id', $typeServiceId) //Situação do atendimento
                                ->where('channel_id', $channelId) //Onde o atendimento está sendo realizado em um determinado canal
                                ->orderBy('id', 'desc')
                                ->first();

        return $lastService;
    }

    //Fecha os atendimentos de um determinado operador
    public function closeServicesOperator()
    {
        //Traz os usuários OPERADORES
        $usersOperators = $this->userController->getUsersByRole(2);

        foreach($usersOperators as $operator) {
            if($operator == 51) {
                $activeContacts = Contact::select('con_contacts.id as id' ,'con_name', 'gender_id', 'con_contacts.status_id', 'color_avatar_id', 'con_avatar', 
                                            'cha_chats.id as chatId', 'cha_services.id as serviceId')
                                        ->join('cha_chats', 'cha_chats.contact_id', 'con_contacts.id')
                                        ->join('cha_services', 'cha_chats.id', 'cha_services.chat_id')
                                        ->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                        //Filtra pela última linha da tabela de ações (transferência) de um chat e de um determinado atendimento
                                        ->where('cha_actions.id', function($query) {
                                            $query->select('id')
                                                ->from('cha_actions')
                                                ->whereColumn('chat_id', 'cha_chats.id')
                                                ->whereColumn('service_id', 'cha_services.id')
                                                ->latest()
                                                ->limit(1);
                                            })
                                        //->whereIn('cha_actions.department_id', $userDepartmentsId) //Se a última transferência foi feita para o departamento do usuário logado
                                        ->where('cha_services.created_at', '<=', Carbon::now()->subDays(15)->toDateTimeString()) //Traz os atendimentos com mais de 15 dias que estão abertos
                                        ->where('cha_actions.user_id', $operator->id) //Se o usuário é o titular do atendimento
                                        ->whereIn('cha_actions.type_action_id', [1, 5]) // Se o atendimento foi iniciado por uma transferência de atendimento ou por comunicação ativa
                                        ->where('type_status_service_id', 1) //Onde o atendimento está em aberto
                                        ->get();
                
                //Para cada atendimento com mais de X dias aberto para um determinado operador, fecha o atendimento
                foreach($activeContacts as $serviceData) {
                    ChatbotControl::create([
                        'chat_id' => $serviceData->chatId,
                        'bloc_id' => 6, //CORRIGIR para puxar dinamicamente o bloco de finalização de acordo com o chatbot associado
                    ]);

                    //Fecha o atendimento
                    self::updateStatusService($serviceData->serviceId, 3);
                }
            }
            
        }
    }

    public function closeSelfServices()
    {
        $chatbotController = new ChatbotController();
        $finishingBloc = null;

        $selfServices = Service::select('cha_services.id', 'con_contacts.id as contactId', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 
                                        'ser_protocol_number', 'man_channels.cha_name')
                                    ->doesntHave('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id')
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id')
                                    //->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id')
                                    ->where('cha_services.created_at', '<=', Carbon::now()->subDays(1)->toDateTimeString()) //Traz os atendimentos com mais de 15 dias que estão abertos
                                    ->where('type_status_service_id', 1)
                                    ->get();
        
        //Traz um bloco de finalização
        $finishingBloc = $chatbotController->getBlocByType(3);
        //Se houver algum bloco de finalização
        if($finishingBloc) {
            //Para cada atendimento com mais de X dias aberto para um determinado operador, fecha o atendimento
            foreach($selfServices as $selfService) {
                ChatbotControl::create([
                    'chat_id' => $selfService->chat_id,
                    'bloc_id' => $finishingBloc->id, //CORRIGIR para puxar dinamicamente o bloco de finalização de acordo com o chatbot associado
                ]);

                //Fecha o atendimento
                self::updateStatusService($selfService->id, 3);
            }
        }
        
        
        //Log::debug('$selfServices');
        //Log::debug($selfServices);
    }


    //Fecha todos os atendimentos pendentes
    public function closePendingServices()
    {
        $chatbotController = new ChatbotController();
        $finishingBloc = null;

        //Traz os atendimentos pendentes
        $pendingServices = Service::select('cha_services.id', 'con_contacts.id as contactId', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 
                                            'dep_name', 'ser_protocol_number', 'man_channels.cha_name', 'cam_campaigns.cam_name', 'cam_campaigns.cam_description')
                                    ->has('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id') //Traz os dados do contato
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id') //Traz os dados do canal
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    ->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->where('cha_actions.id', function($query) {
                                        //Traz a última ação realizada no chat
                                        $query->select('id')
                                            ->from('cha_actions')
                                            ->whereColumn('chat_id', 'cha_chats.id')
                                            ->whereColumn('service_id', 'cha_services.id')
                                            ->latest()
                                            ->limit(1);
                                        })
                                    ->join('man_departments', 'cha_actions.department_id', 'man_departments.id') //Departamento responsável pelo atendimento
                                    //->where('cha_actions.department_id', 1) //Onde o departamento é tem id 1
                                    ->where('cha_services.created_at', '<=', Carbon::now()->subDays(2)->toDateTimeString())
                                    ->whereNull('cha_actions.user_id') //Onde nenhum operador assuniu o atendimento
                                    ->where('type_status_service_id', 1) //Onde o atendimento está aberto
                                    ->get();
        
        
        //Traz um bloco de finalização
        $finishingBloc = $chatbotController->getBlocByType(3);
        //Se houver algum bloco de finalização
        if($finishingBloc) {
            //Para cada atendimento com mais de X dias aberto para um determinado operador, fecha o atendimento
            foreach($pendingServices as $pendingService) {

                //Caso o atendimento esteja PENDENTE ( tenha sido transferido para um departamento e nenhum operador ainda assumiu o mesmo)
                Action::where('service_id', $pendingService->id)
                    ->where('chat_id', $pendingService->chat_id)
                    ->whereNull('user_id') //Nenhum operador assumiu o atendimento
                    ->whereNotNull('department_id')
                    ->where('type_action_id', 1) //Tipo de ação seja TRANSFERÊNCIA
                    ->delete();

                ChatbotControl::create([
                    'chat_id' => $pendingService->chat_id,
                    'bloc_id' => $finishingBloc->id, //CORRIGIR para puxar dinamicamente o bloco de finalização de acordo com o chatbot associado
                ]);

                //Fecha o atendimento
                self::updateStatusService($pendingService->id, 3);
            }
        }
        
        //Log::debug('$pendingServices');
        //Log::debug($pendingServices);
    }

    public function fetchStatusServices()
    {
        $statusServices = TypeStatusService::where('typ_status', 'A')
                                        ->get();

        return response()->json([
            'statusServices' => $statusServices    
        ], 201);
    }

    //Transfere os atendimentos automaticamente em caso de inatividade do contato
    public function transferSelfService()
    {   
        $channelController = new ChannelController();
        $chatController = new ChatController();
        $channelStatus = ['A', 'I'];
        $channels = $channelController->getChannelsByMultipleStatus($channelStatus);
        
        //Log::debug('$channels');
        //Log::debug($channels);
        //Para cada canal ativo
        foreach($channels AS $channel) {
            if(isset($channel['parameters'][2])) {
                //Se foi definido algum departamento padrão de transferência
                if($channel['parameters'][2]['department_id']) {
                    
                    //Se foi definido pelo usuário o tempo máximo de inatividade
                    if(isset($channel['parameters'][1]) && $channel['parameters'][1]['par_value'] != '0' && $channel['parameters'][1]['par_value'] != null) {
                        //Traz todos os autoatendimentos associados a um determinado canal
                        $selfServices = Service::doesntHave('actions')
                        //->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id')
                                                ->where('type_status_service_id', 1)
                                                ->where('channel_id', $channel['id'])
                                                ->get();
                        
                        foreach($selfServices AS $selfService) {
                            //Pega a última mensagem enviada pelo CONTATO
                            $lastMessageContact = $chatController->getLastMessageByTypeUserSender($selfService->chat_id, 2);
                            $now = Carbon::now();
                            //Se não existe mensagem enviada pelo CONTATO
                            if(!$lastMessageContact) {
                                //Pega a última mensagem enviada pelo USUÁRIO DO SISTEMA
                                $lastMessageContact = $chatController->getLastMessageByTypeUserSender($selfService->chat_id, 1);
                                //Se não existe mensagem enviada pelo USUÁRIO DO SISTEMA
                                if(!$lastMessageContact) {
                                    //Pega a última mensagem enviada pelo ROBÔ
                                    $lastMessageContact = $chatController->getLastMessageByTypeUserSender($selfService->chat_id, 3);
                                }
                            }
                            //Calcula quantos segundos o autoatendimento está inativo
                            $downtime = $now->diffInSeconds($lastMessageContact->created_at);
                            //Se o tempo de inatividade é maior igual ao tempo máximo para inatividade
                            if($downtime >= $channel['parameters'][1]['par_value']) {
                                $chatbotController = new ChatbotController();

                                $chat = $chatController->getChatById($selfService->chat_id);
                                $fairDistributionParameterChannel = $channelController->getParameterChannelByTypeFairDistribution($selfService->channel_id, 4);
                                //Se houver alguma configuração de transferência igualitária configurada para o canal 
                                if($fairDistributionParameterChannel) {
                                    //Verifica se a distrinuição igualitária está configurada, se estiver, TRANSFERE de acordo  com a distribuição
                                    $farTransferResponse = $chatbotController->fairTransfer($selfService->id, $selfService->chat_id, null, $chat->contact_id, $fairDistributionParameterChannel->fair_distribution_id);
                                }
                                else {
                                    //Transfere o atendimento para o DEPARTAMENTO PADRÃO
                                    $chatbotController->transferDefaultDepartment($selfService->id, $selfService->chat_id, $chat->contact_id, null);
                                }
                            }
                        }
                    }
                }
            } 
        }
    }

    public function getServiceByContactAndStatus($chatId, $statusId)
    {
        $service = Service::where('chat_id', $chatId)
                        ->where('type_status_service_id', $statusId)
                        ->first();

        return $service;
    }

    //Traz configurações de distribuição igualitária associadas a um canal 
    public function getFairDistributionsByChannel($channelId) {
        $fairDistributionsChannel = FairDistributionSetup::select('cha_fair_distributions_setup.id', 'cha_fair_distributions_setup.fai_name', 'cha_fair_distributions_setup.fai_description')
                                                        ->join('cha_fair_distribution_channels', 'cha_fair_distributions_setup.id', 'cha_fair_distribution_channels.fair_distribution_id')
                                                        ->where('cha_fair_distribution_channels.channel_id', $channelId)
                                                        ->where('cha_fair_distribution_channels.fai_status', 'A')
                                                        ->where('cha_fair_distributions_setup.fai_status', 'A')
                                                        ->get();
        
        return $fairDistributionsChannel;
    }

    //Traz as configurações de transferência igualitária de acordo com os canais associados a uma campanha
    public function getFairDistributionsByCampaign($campaignId)
    {
        $campaignController = new CampaignController();

        $channelsCampaign = $campaignController->getChannelsCampaign($campaignId);
        $channelsId = $channelsCampaign->pluck('channel_id')->toArray();

        $fairDistributionsChannel = FairDistributionSetup::select('cha_fair_distributions_setup.id', 'cha_fair_distributions_setup.fai_name', 'cha_fair_distributions_setup.fai_description')
                                                        ->join('cha_fair_distribution_channels', 'cha_fair_distributions_setup.id', 'cha_fair_distribution_channels.fair_distribution_id')
                                                        ->whereIn('cha_fair_distribution_channels.channel_id', $channelsId)
                                                        ->where('cha_fair_distribution_channels.fai_status', 'A')
                                                        ->where('cha_fair_distributions_setup.fai_status', 'A')
                                                        ->get();

        return $fairDistributionsChannel;
    }

    //Traz a o canal associado a distribuição igualitária de atendimentos
    public function getFairDistributionChannel($fairDistributionId, $channelId)
    {
        $fairDistributionChannel = FairDistributionChannel::join('man_channels', 'cha_fair_distribution_channels.channel_id', 'man_channels.id')
                                                            ->where('fair_distribution_id', $fairDistributionId)
                                                            ->where('channel_id', $channelId)
                                                            ->where('cha_fair_distribution_channels.fai_status', 'A')
                                                            ->whereIn('man_channels.cha_status', ['A', 'I'])
                                                            ->first();
        return $fairDistributionChannel;
    }

    //Traz todos os canais que fazem parte da distribuição igualitária
    public function fetchFairDistributionChannels($fairDistributionId)
    {
        $fairDistributionChannels = FairDistributionChannel::select('man_channels.id', 'cha_fair_distribution_channels.id as fair_distribution_channels_id', 'cha_fair_distribution_channels.fai_status', 'cha_fair_distribution_channels.channel_id', 'man_channels.cha_name')
                                                            ->join('man_channels', 'cha_fair_distribution_channels.channel_id', 'man_channels.id')
                                                            ->where('fai_status', 'A')
                                                            ->where('fair_distribution_id', $fairDistributionId)
                                                            ->whereIn('cha_status', ['A', 'I'])
                                                            ->get();
        return $fairDistributionChannels;
    }

    //Traz as configurações de distribuição igualitária
    public function fetchFairDistribution(Request $request)
    {
        $restrictionDeleteChannelsMesage = null;
        $restrictionDeleteChatbotsMesage = null;
        $restrictionDeleteCampaignsMesage = null;
        
        $channelController = new ChannelController();
        $chatbotController = new ChatbotController();
        $campaignController = new CampaignController();

        $fairDistribution = FairDistributionSetup::with('channels', 'users')
                                                ->where('cha_fair_distributions_setup.fai_status', 'A')
                                                ->get();

        //Para cada configuração de distribuição igualitária
        foreach($fairDistribution AS $distribution) {
            $channelsParameter = $channelController->getChannelsParameterByFairDistibutionId($distribution->id);

            $chatbots = $chatbotController->getChatbotByFairDistribution($distribution->id);

            $campaigns = $campaignController->getCampaignSettingByFairDistribution($distribution->id);

            Log::debug('$channelParameter');
            Log::debug($channelsParameter);
            $generalMessage = NULL;
            //Se existe algum canal associado a uma distribuição igualitária em caso de INATVIDADE do usuário
            if(count($channelsParameter) > 0) {
                $generalMessage = 'Não é possível deletar a configuração de transferência igualitária pois a mesma está associada a um ou mais recursos. Para remover essa configuração, você precisa primeiro remover as seguintes associações:';
                $restrictionDeleteChannelsMesage = '<li><u><b>Canais:</b>';
                //Para cada canal
                foreach($channelsParameter AS $key => $channel) {
                    $restrictionDeleteChannelsMesage .= ' '.$channel['cha_name'];
                    if($key+1 == count($channelsParameter)) {
                        $restrictionDeleteChannelsMesage .= ';';
                    }
                    else {
                        $restrictionDeleteChannelsMesage .= ',';
                    }
                }
                $restrictionDeleteChannelsMesage .= '</u></li>';
            }
            //Se a distribuição igualitária estiver asssociada a algum chatbot
            if(count($chatbots) > 0) {
                $generalMessage = 'Não é possível deletar a configuração de transferência igualitária pois a mesma está associada a um ou mais recursos. Para remover essa configuração, você precisa primeiro remover as seguintes associações:';
                $restrictionDeleteChatbotsMesage = '<li><u><b>Chatbots</b>:';
                //Para cada canal
                foreach($chatbots AS $key2 => $chatbot) {
                    $restrictionDeleteChatbotsMesage .= ' '.$chatbot['cha_name'];
                    if($key2+1 == count($chatbots)) {
                        $restrictionDeleteChatbotsMesage .= ';';
                    }
                    else {
                        $restrictionDeleteChatbotsMesage .= ',';
                    }
                }
                $restrictionDeleteChatbotsMesage .= '</u></li>';
            }
            //Se a distribuição igualitária estiver asssociada a alguma campanha
            if(count($campaigns) > 0) {
                $generalMessage = 'Não é possível deletar a configuração de transferência igualitária pois a mesma está associada a um ou mais recursos. Para remover essa configuração, você precisa primeiro remover as seguintes associações:';
                $restrictionDeleteCampaignsMesage = '<li><u><b>Campanhas</b>:';
                //Para cada canal
                foreach($campaigns AS $key3 => $campaign) {
                    $restrictionDeleteCampaignsMesage .= ' '.$campaign['cam_name'];
                    if($key3+1 == count($campaigns)) {
                        $restrictionDeleteCampaignsMesage .= ';';
                    }
                    else {
                        $restrictionDeleteCampaignsMesage .= ',';
                    }
                }
                $restrictionDeleteCampaignsMesage .= '</u></li>';
            }

            $generalMessage = $generalMessage . $restrictionDeleteChannelsMesage . $restrictionDeleteChatbotsMesage . $restrictionDeleteCampaignsMesage;

            Log::debug($generalMessage);
            $distribution->setAttribute('restrictionDelete', $generalMessage);
        }
        
        
        return response()->json([
            'fairDistribution' => $fairDistribution,
            //'users' => $users,
        ], 200);
    }

    //Traz as configurações de distribuição igualitária
    public function getFairDistributions()
    {
        $fairDistribution = FairDistributionSetup::where('cha_fair_distributions_setup.fai_status', 'A')
                                                ->get();

        return $fairDistribution;
    }

    //Traz os usuários cadastrados na DISTRIBUIÇÃO IGUALITÁRIA
    public function fetchFairDistributionUsers($fairDistributionId)
    {
        $users = FairDistribution::select('users.id', 'cha_fair_distributions.id as user_fair_distribution_id', 'fair_distribution_id', 'cha_fair_distributions.user_id as user_id', 'users.name', 
                                        'cha_fair_distributions.fai_status', 'cha_fair_distributions.fai_total_forwarding', 'cha_fair_distributions.fai_dt_last_forwarding')
                                ->join('users', 'cha_fair_distributions.user_id', 'users.id')
                                ->where('fair_distribution_id', $fairDistributionId)
                                ->where('fai_status', 'A')
                                ->where('users.status', 'A')
                                ->get();

        return $users;
    }

    public function removeFairDistribution($fairDistributionId)
    {
        $fairDistribution = FairDistributionSetup::find($fairDistributionId);
        $fairDistribution->fai_status = 'I';
        $fairDistribution->save();
    }

    //Traz os usuários cadastrados na DISTRIBUIÇÃO IGUALITÁRIA
    public function getFairDistribution($fairDistributionId)
    {
        $fairDistribution = FairDistribution::find($fairDistributionId);

        return $fairDistribution;
    }

    /*public function getResourcesFairDistribution()
    {
        $channels = self::fetchFairDistributionChannels();
        $users = self::fetchFairDistributionUsers();

        return response()->json([
            'channels' => $channels,
            'users' => $users,
            //'campaign' => $campaign
        ], 200);
    }*/

    //Traz a última operação realizada no mailing de uma campanha
    public function getLastForwardingFairDistribution($fairDistributionId)
    {
        $lastForwardingFairDistribution = FairDistribution::where('fair_distribution_id', $fairDistributionId)
                                                            ->whereNotNull('fai_dt_last_forwarding') //Onde existe a data/hora de encaminhamento
                                                            ->orderBy('fai_dt_last_forwarding', 'DESC')
                                                            ->first();
        
        return $lastForwardingFairDistribution;
    }

    //Traz o usuário para onde o atendimento será encaminhado 
    public function choiceFairDistributionUser($fairDistributionId)
    {
        $chosenUser = null;

        $usersForwarding = self::fetchFairDistributionUsers($fairDistributionId);

        $lastForwardingFairDistribution = self::getLastForwardingFairDistribution($fairDistributionId);
        
        //Se a campanha só tem um canal ativo ou se a campanha tem ao menos um canal e não tem nenhuma operação realizada ainda 
        if(count($usersForwarding) == 1 || (count($usersForwarding) > 0 && !isset($lastForwardingFairDistribution))) {
            //Retorna o primeiro canal do array
            $chosenUser = $usersForwarding[0];
        } //Se tiver mais de um canal ativo e ao menos uma operação realizada
        else if(count($usersForwarding) > 1 && isset($lastForwardingFairDistribution)) {
            //Pega o index do canal onde ocorreu a última operação 
            $keyLastUser = $usersForwarding->search(function($userForwarding) use ($lastForwardingFairDistribution) {
                return $userForwarding['user_id'] == $lastForwardingFairDistribution->user_id;
            });

            //Caso o último canal que realizou a operação ainda faça parte da lista de canais da campanha
            if(isset($keyLastUser)) {
                //Caso seja o último canal do array, pega o primeiro canal da array
                if(count($usersForwarding) == ($keyLastUser+1)) {
                    $chosenUser = $usersForwarding[0];
                }
                else { //Pega o próximo canal do array (index do último canal que fez a operação +1)
                    $chosenUser = $usersForwarding[$keyLastUser+1];
                }
            } //Caso o canal que realizou a última operação não faça mais parte da lista de canais da campanha
            else {
                //Retorna o primeiro canal do array
                $chosenUser = $usersForwarding[0];
            } 
        }


        return $chosenUser;
    }

    //Traz o total de configuração de encaminhamento igualitário
    public function getTotalFairDistributionSetup()
    {
        $totalFairDistributionSetup = FairDistributionSetup::where('fai_status', 'A')
                                                            ->count();
        
        return $totalFairDistributionSetup;
    }

    //Adiciona uma configuração de distribuição igualitária
    public function addFairDistribution(Request $request)
    {
        Log::debug('addFairDistribution $request');
        Log::debug($request);
        //Traz o total de configuração de encaminhamento igualitário
        //$totalFairDistributionSetup = self::getTotalFairDistributionSetup();

        $fairDistributionSetup = new FairDistributionSetup();

        $fairDistributionSetup->fai_name = $request['fai_name'];
        $fairDistributionSetup->fai_description = $request['fai_description'];
        $fairDistributionSetup->fai_main = NULL; //Se não tiver configuração encaminamento igualitário configurado, torna obrigatoriamente a configuração como principal

        if($fairDistributionSetup->save()) {
            //Se a configuração recém adicionada for a principal, remove o status de principal de qualquer outra configuração
            /*if($request['fai_main'] == 1) {
                FairDistributionSetup::where('id','!=', $fairDistributionSetup->id)
                                    ->update([
                                        'fai_main' => NULL
                                    ]);
            }*/

            //Para cada novo canal adicionado
            foreach($request['channels'] as $channel) {
                $newChannel = new FairDistributionChannel();
                $newChannel->fair_distribution_id = $fairDistributionSetup->id;
                $newChannel->channel_id = $channel['id'];
                $newChannel->save();
            }

            //Para cada novo usuário adicionado
            foreach($request['users'] as $user) {
                $newUser = new FairDistribution();
                $newUser->fair_distribution_id = $fairDistributionSetup->id;
                $newUser->user_id = $user['id'];
                $newUser->save();
            }
        }
    }

    //Atualiza a configuração de distribuição igualitária
    public function updateFairDistribution(Request $request)
    {
        Log::debug('updateFairDistribution $request');
        Log::debug($request);

        //$totalFairDistributionSetup = self::getTotalFairDistributionSetup();

        $fairDistribution = FairDistributionSetup::find($request['id']);

        $fairDistribution->fai_name = $request['fai_name'];
        $fairDistribution->fai_description = $request['fai_description'];
        $fairDistribution->fai_main = NULL;

        //Caso a configuração de distribuição igualitária seja salva
        if($fairDistribution->save()) {
            $channelsFairDistribution = self::fetchFairDistributionChannels($fairDistribution->id);
            //Inativa cada canal usado para distribuição igualitária
            foreach($channelsFairDistribution as $channelFairDistribution) {
                $channelFairDistributionUpdate = FairDistributionChannel::find($channelFairDistribution->fair_distribution_channels_id);
                $channelFairDistributionUpdate->fai_status = 'I';
                $channelFairDistributionUpdate->save();
            }

            //Para cada novo canal adicionado
            foreach($request['channels'] as $channel) {
                $newChannel = FairDistributionChannel::where('fair_distribution_id', $fairDistribution->id)
                                                    ->where('channel_id', $channel['id'])
                                                    ->first();
                
                //Se o canal se encontra inativo (o canal já estava adicionado anteriormente)
                if($newChannel) {
                    $newChannel->fai_status = 'A';
                    $newChannel->save();
                } //Adiciona o novo canal no banco de dados
                else {
                    $newChannel = new FairDistributionChannel();
                    $newChannel->fair_distribution_id = $fairDistribution->id;
                    $newChannel->channel_id = $channel['id'];
                    $newChannel->save();
                }
            }

            $usersFairDistribution = self::fetchFairDistributionUsers($fairDistribution->id);
            Log::debug('Id do usuário que alterou a configuração');
            Log::debug(Auth::user()->id);
            Log::debug('$fairDistribution');
            Log::debug($fairDistribution);
            Log::debug('$usersFairDistribution');
            Log::debug($usersFairDistribution);

            //Inativa cada canal usado para distrinuição igualitária
            foreach($usersFairDistribution as $userFairDistribution) {
                $userFairDistributionUpdate = FairDistribution::where('user_id', $userFairDistribution->id)
                                                                ->where('fair_distribution_id', $userFairDistribution->fair_distribution_id)
                                                                ->first();
                $userFairDistributionUpdate->fai_status = 'I';
                $userFairDistributionUpdate->save();
            }

            //Se tem algum canal adicionado na distribuição igualitária
            if(!empty($request['channels'])) {
                //Para cada novo usuário adicionado
                foreach($request['users'] as $user) {
                    $newUser = FairDistribution::where('fair_distribution_id', $fairDistribution->id)
                                                ->where('user_id', $user['id'])
                                                ->first();
                
                    //Se o usuário já se encontra inativo
                    if($newUser) {
                        $newUser->fai_status = 'A';
                        $newUser->save();
                    } //Adiciona um novo usuário na distribuição igualitária
                    else {
                        $newUser = new FairDistribution();
                        $newUser->fair_distribution_id = $fairDistribution->id;
                        $newUser->user_id = $user['id'];
                        $newUser->save();
                    }
                }
            }
        }        
        
        return response()->json([
            'channels' => $request['channels'],
            'users' => $request['users'],
        ], 200);

    }

    //Fecha os atendimentos de um determinado operador
    public function closeServicesByLastInteraction()
    {
        $activeContacts = Contact::select('con_contacts.id as id' ,'con_name', 'gender_id', 'con_contacts.status_id', 'color_avatar_id', 'con_avatar', 
                                        'cha_chats.id as chatId', 'cha_services.id as serviceId')
                                ->join('cha_chats', 'cha_chats.contact_id', 'con_contacts.id')
                                ->join('cha_services', 'cha_chats.id', 'cha_services.chat_id')
                                ->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                //Filtra pela última linha da tabela de ações (transferência) de um chat e de um determinado atendimento
                                ->where('cha_actions.id', function($query) {
                                    $query->select('id')
                                        ->from('cha_actions')
                                        ->whereColumn('chat_id', 'cha_chats.id')
                                        ->whereColumn('service_id', 'cha_services.id')
                                        ->latest()
                                        ->limit(1);
                                    })
                                ->join('cha_messages', 'cha_chats.id', 'cha_messages.chat_id')
                                ->where('cha_messages.created_at', function($query) {
                                        $query->select('created_at')
                                            ->from('cha_messages')
                                            ->whereColumn('chat_id', 'cha_chats.id')
                                            ->latest()
                                            ->limit(1);
                                        })
                                //->whereIn('cha_actions.department_id', $userDepartmentsId) //Se a última transferência foi feita para o departamento do usuário logado
                                ->where('cha_messages.created_at', '<=', '2024-01-10 00:00:00') //Traz os atendimentos com mais de 15 dias que estão abertos
                                ->whereIn('cha_actions.type_action_id', [1, 5]) // Se o atendimento foi iniciado por uma transferência de atendimento ou por comunicação ativa
                                ->where('type_status_service_id', 1); //Onde o atendimento está em aberto
        $total = $activeContacts->count();
        Log::debug('total de contatos a serem fechados');
        Log::debug($total);
        $activeContacts = $activeContacts->get();
        

        //Para cada atendimento com mais de X dias aberto para um determinado operador, fecha o atendimento
        foreach($activeContacts as $serviceData) {
            ChatbotControl::create([
                'chat_id' => $serviceData->chatId,
                'bloc_id' => 41, //CORRIGIR para puxar dinamicamente o bloco de finalização de acordo com o chatbot associado
            ]);

            //Fecha o atendimento
            self::updateStatusService($serviceData->serviceId, 3);
        }
        
    }
}
