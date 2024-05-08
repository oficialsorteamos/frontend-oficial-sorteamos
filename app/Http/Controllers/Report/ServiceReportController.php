<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignTagHistory;
use App\Models\Campaign\Mailing;
use App\Models\Chat\Service;
use App\Models\Contact\ContactTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;
use Excel;
use Storage;
use DB;
use Carbon\Carbon;

class ServiceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $params)
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
    public function store($mailingData)
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
    public function update($mailingData)
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
        //
    }

    public function fetchServices(Request $params)
    {
        Log::debug('params fetchServices');
        Log::debug($params);
        //Se o usuário não digitou nada no campo de pesquisa
        if($params['q'] == '') {
            //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
            $skip = (($params['page']-1) * $params['perPage']);
        }

        $services = Service::select('cha_services.id', 'con_contacts.id as contactId', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 'name', 'dep_name', 'ser_protocol_number', 
                                    'man_channels.cha_name', 'cam_campaigns.cam_name', 'cha_services.created_at', 'type_status_service_id', 'cha_type_status_services.typ_description',
                                    'ser_dt_end_service')
                                    //->has('actions')
                                    //->doesntHave('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id') //Traz os dados do contato
                                    ->leftJoin('con_contacts_tags', 'con_contacts.id', 'con_contacts_tags.contact_id')
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id') //Traz os dados do canal
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    //->leftJoin('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->leftJoin('cha_actions', function($join)
                                    {
                                        $join->on('cha_chats.id', '=', 'cha_actions.chat_id');
                                        $join->where('cha_actions.id', function($query) {
                                            //Traz a última ação realizada no chat
                                            $query->select('id')
                                                ->from('cha_actions')
                                                ->whereColumn('chat_id', 'cha_chats.id')
                                                ->whereColumn('service_id', 'cha_services.id')
                                                ->latest()
                                                ->limit(1);
                                            });
                                    })
                                    
                                    ->leftJoin('users', 'cha_actions.user_id', 'users.id') //Usuário que está realizando o atendimento
                                    ->leftJoin('man_departments', 'cha_actions.department_id', 'man_departments.id') //Departamento responsável pelo atendimento
                                    ->join('cha_type_status_services', 'cha_services.type_status_service_id', 'cha_type_status_services.id'); //Departamento responsável pelo atendimento
                                    //->where('type_status_service_id', 1); //Onde o atendimento está aberto
        //Filtra por data de interação do usuário
        if($params['userSystemInteractionDate'] != '') {
            $services = $services->leftJoin('cha_messages', 'cha_messages.service_id', 'cha_services.id')
                                ->whereNotIn('cha_messages.type_user_id', [2, 3]) //Onde quem enviou a mensagem não é um usuário ou robê
                                ->whereBetween('cha_messages.created_at', [$params['userSystemInteractionDate'].' 00:00:00', $params['userSystemInteractionDate'].' 23:59:59']);

        }
        //Filtra por departamento
        if($params['department'] != '') {
            $services = $services->where('man_departments.id', $params['department']);
        }//Filtra por operador
        if($params['user'] != '') {
            $services = $services->where('users.id', $params['user']);
        } //Filtra por canal
        if($params['channel'] != '') {
            $services = $services->where('man_channels.id', $params['channel']);
        }
        if($params['status'] != '') {
            $services = $services->where('type_status_service_id', $params['status']);
        }
        if($params['contact'] != '') {
            $contact = json_decode($params['contact'], true);
            $services = $services->where('con_contacts.id', $contact['id']);
        }
        if($params['tags'] != '') {
            $services = $services->whereIn('con_contacts_tags.tag_id', $params['tags']);
        }
        //Se o usuário estiver filtrando por atendimentos de campanha ou não
        if($params['origin'] != '') {
            if($params['origin'] == 'C') {
                $services = $services->whereNotNull('cam_campaigns.cam_name');
            }
            else if($params['origin'] == 'U') {
                $services = $services->whereNotNull('cha_services.dialer_fowarding_setting_id');
            }
            else if($params['origin'] == 'S') {
                $services = $services->whereNull('cam_campaigns.cam_name');
            }
        }
        if($params['period']) {
            $periodDivided = explode('até', $params['period']);
            //Se foi selecionada apenas a data inicial
            if(count($periodDivided) == 1) {
                $services = $services->whereBetween('cha_services.created_at', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
            }//Se foi selecionada a data inicial e final
            else {
                $services = $services->whereBetween('cha_services.created_at', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
            }
        }
        $services = $services->orderBy('cha_services.id', 'DESC');
        //$services = $services->groupBy('cha_services.id', 'con_contacts.id', 'cha_services.chat_id', 'con_name', 'con_avatar', 'con_phone', 'name', 'dep_name', 'ser_protocol_number', 
        //    'man_channels.cha_name', 'cam_campaigns.cam_name', 'cha_services.created_at', 'type_status_service_id', 'cha_type_status_services.typ_description');
        $total = $services->distinct()->count('cha_services.id');
        //Busca os contatos de acordo com a paginação
        $services = $services->skip($skip); //Quantidade de itens que serão desconsiderados por já terem sido carregados 
        $services = $services->take($params['perPage']); //Quantidade de itens trazidos
        $services = $services->get();

        self::getTagsContact($services);

        //Log::debug('services');
        //Log::debug($services);

        return response()->json([
            'services'=> $services,
            'total'=> $total,
        ], 201);
    }

    //Traz as tags associadas aos contatos
    public function getTagsContact($services)
    {   
        foreach($services as $key => $contactData) {
            $tags = null;
            $tags = ContactTag::join('man_tags', 'con_contacts_tags.tag_id', 'man_tags.id')
                                        ->where('con_contacts_tags.contact_id', $contactData->contactId)
                                        ->get();
            
            $services[$key]->setAttribute('tags', $tags);
        }
    }

    /*public function downloadServiceReport(Request $request)
    {
        Log::debug('download services Report');
        Log::debug($request);

        $filename = 'Relatório - Atendimentos.xlsx';
        Excel::store(new ServiceExport($request), $filename);

        $file = Storage::get($filename);
        if ($file) {
            $fileLink = 'data:application/vnd.ms-excel;base64,' . base64_encode($file);
            @chmod(Storage::disk('local')->path($filename), 0755);
            @unlink(Storage::disk('local')->path($filename));
        }

        return response()->json([
            'linkData' => $fileLink,
            'filename' => $filename,
        ], 200);
    }*/

    public function downloadServiceReport(Request $request)
    {
        Log::debug('download services Report');
        Log::debug($request);

        $filename = 'Relatório - Atendimentos.xlsx';
        //Excel::store(new ServiceExport($request), $filename); 
        //$users = Service::all();
        $services = self::getServices($request);
        //Log::debug('$services');
        //Log::debug($services);
        
        (new FastExcel($services))->export(storage_path('app/public/'.$filename), function ($service) {
            return [
                'Nº PROTOCOLO' => $service->ser_protocol_number,
                'CONTATO' => $service->con_name,
                'Nº CONTATO' => $service->con_phone,
                'OPERADOR' => $service->name,
                'DEPARTAMENTO' => $service->dep_name,
                'CANAL' => $service->cha_name,
                'CAMPANHA' => $service->cam_name,
                'STATUS' => $service->typ_description,
                'DATA INÍCIO' => $service->created_at->format('d/m/Y H:i:s'),
                'DATA DE ENCERRAMENTO' => $service->ser_dt_end_service? Carbon::parse($service->ser_dt_end_service)->format('d/m/Y H:i:s'): null,
                'CEP' => $service->add_zip_code,
                'RUA' => $service->add_street,
                'Nº' => $service->add_number,
                'COMPLEMENTO' => $service->add_address_complement,
                'CIDADE' => $service->add_city,
                'ESTADO' => $service->sta_name,
                'PAÍS' => $service->cou_name,
                'TAGS' => $service->tagsContact,
            ];
        });

        //$file = Storage::disk('public')->path('app/public/'.$filename);
        $file = file_get_contents(storage_path('app/public/'.$filename));
        if ($file) {
            $fileLink = 'data:application/vnd.ms-excel;base64,' . base64_encode($file);
            @chmod(Storage::disk('local')->path($filename), 0755);
            @unlink(Storage::disk('local')->path($filename));
        }

        return response()->json([
            'linkData' => $fileLink,
            'filename' => $filename,
        ], 200);
    }

    public function getServices($params)
    {
        $serviceReportController = new ServiceReportController();
        
        
        $services = Service::select('ser_protocol_number', 'con_contacts.id as contactId','con_name', 'con_phone', 'name', 'dep_name', 'man_channels.cha_name',
                                    'cam_campaigns.cam_name', 'cha_type_status_services.typ_description', 'cha_services.created_at', 'add_zip_code', 'add_street', 'add_number', 'add_address_complement',
                                    'add_city', 'sta_name', 'cou_name', 'ser_dt_end_service') 
                                    //->has('actions')
                                    //->doesntHave('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id') //Traz os dados do contato
                                    ->leftJoin('con_contacts_tags', 'con_contacts.id', 'con_contacts_tags.contact_id')
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id') //Traz os dados do canal
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    //->leftJoin('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->leftJoin('cha_actions', function($join)
                                    {
                                        $join->on('cha_chats.id', '=', 'cha_actions.chat_id');
                                        $join->where('cha_actions.id', function($query) {
                                            //Traz a última ação realizada no chat
                                            $query->select('id')
                                                ->from('cha_actions')
                                                ->whereColumn('chat_id', 'cha_chats.id')
                                                ->whereColumn('service_id', 'cha_services.id')
                                                ->latest()
                                                ->limit(1);
                                            });
                                    })
                                    ->leftJoin('sys_addresses', function($join)
                                    {
                                        $join->on('con_contacts.id', '=', 'sys_addresses.user_id');
                                        $join->where('sys_addresses.id', function($query) {
                                            //Traz a última ação realizada no chat
                                            $query->select('id')
                                                ->from('sys_addresses')
                                                ->whereColumn('sys_addresses.user_id', 'con_contacts.id')
                                                ->where('type_user_id', 2)
                                                ->orderBy('id', 'ASC')
                                                ->limit(1);
                                            });
                                    })
                                    ->leftJoin('sys_states_country', 'sys_addresses.state_id', 'sys_states_country.id')
                                    ->leftJoin('sys_countries', 'sys_addresses.country_id', 'sys_countries.id')
                                    ->leftJoin('users', 'cha_actions.user_id', 'users.id') //Usuário que está realizando o atendimento
                                    ->leftJoin('man_departments', 'cha_actions.department_id', 'man_departments.id') //Departamento responsável pelo atendimento
                                    ->join('cha_type_status_services', 'cha_services.type_status_service_id', 'cha_type_status_services.id'); //Departamento responsável pelo atendimento
                                    //->where('type_status_service_id', 1); //Onde o atendimento está aberto
        //Filtra por data de interação do usuário
        if($params['userSystemInteractionDate'] != '') {
            $services = $services->leftJoin('cha_messages', 'cha_messages.service_id', 'cha_services.id')
                                ->whereNotIn('cha_messages.type_user_id', [2, 3]) //Onde quem enviou a mensagem não é um usuário ou robê
                                ->whereBetween('cha_messages.created_at', [$params['userSystemInteractionDate'].' 00:00:00', $params['userSystemInteractionDate'].' 23:59:59']);

        }
        //Filtra por departamento
        if($params['department'] != '') {
            $services = $services->where('man_departments.id', $params['department']);
        }//Filtra por operador
        if($params['user'] != '') {
            $services = $services->where('users.id', $params['user']);
        } //Filtra por canal
        if($params['channel'] != '') {
            $services = $services->where('man_channels.id', $params['channel']);
        }
        if($params['status'] != '') {
            $services = $services->where('type_status_service_id', $params['status']);
        }
        if($params['contact'] != '') {
            $contact = json_decode($params['contact'], true);
            $services = $services->where('con_contacts.id', $contact['id']);
        }
        if($params['tags'] != '') {
            $services = $services->whereIn('con_contacts_tags.tag_id', $params['tags']);
        }
        //Se o usuário estiver filtrando por atendimentos com uma determinada origem
        if($params['origin'] != '') {
            if($params['origin'] == 'C') {
                $services = $services->whereNotNull('cam_campaigns.cam_name');
            }
            else if($params['origin'] == 'U') {
                $services = $services->whereNotNull('cha_services.dialer_fowarding_setting_id');
            }
            else if($params['origin'] == 'S') {
                $services = $services->whereNull('cam_campaigns.cam_name');
            }
        }
        if($params['period']) {
            $periodDivided = explode('até', $params['period']);
            //Se foi selecionada apenas a data inicial
            if(count($periodDivided) == 1) {
                $services = $services->whereBetween('cha_services.created_at', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
            }//Se foi selecionada a data inicial e final
            else {
                $services = $services->whereBetween('cha_services.created_at', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
            }
        }
        $services = $services->groupBy('ser_protocol_number', 'contactId','con_name', 'con_phone', 'name', 'dep_name', 'man_channels.cha_name',
                                        'cam_campaigns.cam_name', 'cha_type_status_services.typ_description', 'cha_services.created_at', 'add_zip_code', 'add_street', 'add_number', 'add_address_complement',
                                        'add_city', 'sta_name', 'cou_name', 'ser_dt_end_service');
        $services = $services->orderBy('cha_services.id', 'DESC')
                            ->get();

        $serviceReportController->getTagsContact($services);

        //Para cada contato
        foreach($services as $key => $contactTag) {
            $tagsContact = null;
            //Pega as tags associadas ao mesmo
            foreach($contactTag['tags'] as $key2 => $tag) {
                if($key2 > 0) {
                    $tagsContact .= ', '.$tag->tag_name;
                }
                else {
                    $tagsContact = $tag->tag_name;
                }
            }
            $services[$key]->setAttribute('tagsContact', $tagsContact);
        }

        $services->transform(function ($result) {
            $attributes = $result->getAttributes(); //Pega os atributos da Collection
            //unset($attributes['campaign_id']); //Remove o atributo campaign_id
            unset($attributes['contactId']); //Remove o atributo contact_id
            unset($attributes['tags']); //Remove o atributo tags
            $result->setRawAttributes($attributes, true);
            return $result;
        });
        
        
        return $services;
    }

}









use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ServiceExport implements FromView
{

    public function view(): View
    {
        $services = self::getServices();

        return view('report.services', [
            'services' => $services
        ]);
    }

    use Exportable;
    
    public $campaignId;
    public $params;

    public function __construct($params)
    {
        $this->params = $params;
    }
    
    /**
    * Optional Writer Type
    */
    private $writerType = \Maatwebsite\Excel\Excel::XLSX;
    /* 
    public function getServices()
    {
        $serviceReportController = new ServiceReportController();
        
        Log::debug('variáveis report');
        Log::debug($this->params);
        
        $services = Service::select('ser_protocol_number', 'con_contacts.id as contactId','con_name', 'con_phone', 'name', 'dep_name', 'man_channels.cha_name',
                                    'cam_campaigns.cam_name', 'cha_type_status_services.typ_description', 'cha_services.created_at', 'add_zip_code', 'add_street', 'add_number', 'add_address_complement',
                                    'add_city', 'sta_name', 'cou_name') 
                                    //->has('actions')
                                    //->doesntHave('actions')
                                    ->join('cha_chats', 'cha_services.chat_id', 'cha_chats.id')
                                    ->join('con_contacts', 'cha_chats.contact_id', 'con_contacts.id') //Traz os dados do contato
                                    ->leftJoin('con_contacts_tags', 'con_contacts.id', 'con_contacts_tags.contact_id')
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id') //Traz os dados do canal
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Traz a campanha associada ao atendimento, se existir
                                    //->leftJoin('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->leftJoin('cha_actions', function($join)
                                    {
                                        $join->on('cha_chats.id', '=', 'cha_actions.chat_id');
                                        $join->where('cha_actions.id', function($query) {
                                            //Traz a última ação realizada no chat
                                            $query->select('id')
                                                ->from('cha_actions')
                                                ->whereColumn('chat_id', 'cha_chats.id')
                                                ->whereColumn('service_id', 'cha_services.id')
                                                ->latest()
                                                ->limit(1);
                                            });
                                    })
                                    ->leftJoin('sys_addresses', function($join)
                                    {
                                        $join->on('con_contacts.id', '=', 'sys_addresses.user_id');
                                        $join->where('sys_addresses.id', function($query) {
                                            //Traz a última ação realizada no chat
                                            $query->select('id')
                                                ->from('sys_addresses')
                                                ->whereColumn('sys_addresses.user_id', 'con_contacts.id')
                                                ->where('type_user_id', 2)
                                                ->orderBy('id', 'ASC')
                                                ->limit(1);
                                            });
                                    })
                                    ->leftJoin('sys_states_country', 'sys_addresses.state_id', 'sys_states_country.id')
                                    ->leftJoin('sys_countries', 'sys_addresses.country_id', 'sys_countries.id')
                                    ->leftJoin('users', 'cha_actions.user_id', 'users.id') //Usuário que está realizando o atendimento
                                    ->leftJoin('man_departments', 'cha_actions.department_id', 'man_departments.id') //Departamento responsável pelo atendimento
                                    ->join('cha_type_status_services', 'cha_services.type_status_service_id', 'cha_type_status_services.id'); //Departamento responsável pelo atendimento
                                    //->where('type_status_service_id', 1); //Onde o atendimento está aberto
        //Filtra por departamento
        if($this->params['department'] != '') {
            $services = $services->where('man_departments.id', $this->params['department']);
        }//Filtra por operador
        if($this->params['user'] != '') {
            $services = $services->where('users.id', $this->params['user']);
        } //Filtra por canal
        if($this->params['channel'] != '') {
            $services = $services->where('man_channels.id', $this->params['channel']);
        }
        if($this->params['status'] != '') {
            $services = $services->where('type_status_service_id', $this->params['status']);
        }
        if($this->params['contact'] != '') {
            $contact = json_decode($this->params['contact'], true);
            $services = $services->where('con_contacts.id', $contact['id']);
        }
        if($this->params['tags'] != '') {
            $services = $services->whereIn('con_contacts_tags.tag_id', $this->params['tags']);
        }
        //Se o usuário estiver filtrando por atendimentos de campanha ou não
        if($this->params['campaign'] != '') {
            if($this->params['campaign'] == 'S') {
                $services = $services->whereNotNull('cam_campaigns.cam_name');
            }
            else {
                $services = $services->whereNull('cam_campaigns.cam_name');
            }
        }
        if($this->params['period']) {
            $periodDivided = explode('até', $this->params['period']);
            //Se foi selecionada apenas a data inicial
            if(count($periodDivided) == 1) {
                $services = $services->whereBetween('cha_services.created_at', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
            }//Se foi selecionada a data inicial e final
            else {
                $services = $services->whereBetween('cha_services.created_at', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
            }
        }
        $services = $services->groupBy('ser_protocol_number', 'contactId','con_name', 'con_phone', 'name', 'dep_name', 'man_channels.cha_name',
                                        'cam_campaigns.cam_name', 'cha_type_status_services.typ_description', 'cha_services.created_at', 'add_zip_code', 'add_street', 'add_number', 'add_address_complement',
                                        'add_city', 'sta_name', 'cou_name');
        $services = $services->orderBy('cha_services.id', 'DESC')
                            ->get();

        $serviceReportController->getTagsContact($services);

        //Para cada contato
        foreach($services as $key => $contactTag) {
            $tagsContact = null;
            //Pega as tags associadas ao mesmo
            foreach($contactTag['tags'] as $key2 => $tag) {
                if($key2 > 0) {
                    $tagsContact .= ', '.$tag->tag_name;
                }
                else {
                    $tagsContact = $tag->tag_name;
                }
            }
            $services[$key]->setAttribute('tagsContact', $tagsContact);
        }

        $services->transform(function ($result) {
            $attributes = $result->getAttributes(); //Pega os atributos da Collection
            //unset($attributes['campaign_id']); //Remove o atributo campaign_id
            unset($attributes['contactId']); //Remove o atributo contact_id
            unset($attributes['tags']); //Remove o atributo tags
            $result->setRawAttributes($attributes, true);
            return $result;
        });
        
        
        return $services;
    }*/
}
