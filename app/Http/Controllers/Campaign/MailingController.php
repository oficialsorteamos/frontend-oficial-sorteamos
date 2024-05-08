<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\System\AddressController;
use App\Http\Controllers\System\DddStateController;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignTagHistory;
use App\Models\Campaign\Mailing;
use App\Models\Campaign\MailingStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Contact\Contact;
use Auth;
use Excel;
use Storage;
use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MailingController extends Controller
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
        try {
            $mailing = new Mailing();
            $mailing->campaign_id = $mailingData['campaignId'];
            $mailing->contact_id = $mailingData['contactId'];
            $mailing->status_id = $mailingData['statusId'];
            $mailing->mai_additional_data_message = mb_convert_encoding($mailingData['additionalDataMessage'] , 'UTF-8' , 'ASCII');
            $mailing->save();

            return response()->json([
                
            ], 200);
        } catch(e) {

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
        $chat = new ChatController();
        $address = new AddressController();
        $socialNetwork = new SocialNetworkController();

        $contact = Contact::with('tag', 'colorAvatar', 'gender')
                            ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'tag_id', 'status_id', 'con_phone', 'con_email as email', 
                            'con_birthday as birthday', 'created_at', 'con_avatar', 'color_avatar_id')
                            ->where('id', $id)
                            ->get();
        
        $chat->chatsAndContactsDetails($contact);
        
        //Quantidade de endereços associados ao contato
        $amountAddresses = $address->countAddressesUser($contact[0]->id, 2);
        $contact[0]['amountAddresses'] = $amountAddresses;

        //Quantidade de redes sociais associadas ao contato
        $amountSocialNetworks = $socialNetwork->countSocialNetworksContact($contact[0]->id);
        $contact[0]['amountSocialNetworks'] = $amountSocialNetworks;

        return response()->json(
            $contact[0]
        , 200);                    
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
        try {
            //Log::debug('update mailing');
            //Log::debug($mailingData);
            $mailing = Mailing::find($mailingData['mailingId']);
            if($mailingData['channel']) {
                //Se for um canal NÃO OFICIAL
                if($mailingData['channel']['cha_api_official'] == false) {
                    $mailing->message_id = $mailingData['messageId'];
                } //Se for um canal oficial, considera a mensagem como template
                else {
                    $mailing->template_id = $mailingData['messageId'];
                }
            } //Se for campanha para ENVIO de SMS
            else if($mailingData['campaignTypeId'] == 2) {
                $mailing->message_id = $mailingData['messageId'];
            }
            $mailing->channel_id = isset($mailingData['channel']['id'])? $mailingData['channel']['id'] : null;
            $mailing->mai_dt_sending = $mailingData['statusId'] == 2? now() : null;
            $mailing->status_id = $mailingData['statusId'];
            $mailing->save();
            
            return response()->json(
                []
            , 200);

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
        //
    }

    //Traz o mailing de acordo a campanha e o id do contato
    public function getMailingCampaignContact($campaignId, $contactId)
    {
        $mailing = Mailing::where('campaign_id', $campaignId)
                        ->where('contact_id', $contactId)
                        ->orderBy('id', 'DESC')
                        ->first();
        
        return $mailing;
    }

    //Traz os contatos do mailing de uma campanha de acordo com um ou mais status 
    public function getMailingCampaignByStatus($campaignId, $statusId)
    {
        $mailing = Mailing::with('contact')
                            ->where('campaign_id', $campaignId)
                            ->whereIn('status_id', $statusId)
                            ->get();
        
        return $mailing;
    }

    //Retorna a quantidade total de contatos no mailing de uma campanha
    public function getTotalContactMailingCampaign($campaignId)
    {
        $totalContactMailing = Mailing::where('campaign_id', $campaignId)->count();

        return $totalContactMailing;
    }

    //Traz os status que um mailing pode ter
    public function fetchStatusMailing()
    {
        $statusMailing = MailingStatus::where('mai_status', 'A')
                                        ->get();

        return $statusMailing;
    }

    //Retorna o total de contatos de acordo com o status no mailing (enviado, falha ao enviar, etc.)
    public function getTotalContactsMailingByStatus($campaignId, $statusId)
    {
        $totalContactStatus = Mailing::where('campaign_id', $campaignId)
                                    ->where('status_id', $statusId)
                                    ->count();

        return $totalContactStatus;
    }

    public function fetchMailing(Request $params)
    {
        Log::debug('fetchMailing $params');
        Log::debug($params);
        //Se o usuário não digitou nada no campo de pesquisa
        if($params['q'] == '') {
            //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
            $skip = (($params['page']-1) * $params['perPage']);
        }

        $mailing = Mailing::select('cam_mailings.id', 'con_contacts.con_name', 'con_contacts.con_phone', 'cam_messages.mes_content', 'cha_templates_messages.tem_body',
                                    'man_channels.cha_name', 'cam_mailing_status.mai_description', 'cam_mailings.mai_contact_returned', 'cam_mailings.status_id',
                                    'cam_mailings.contact_id', 'cam_mailings.campaign_id', 'cha_quick_messages.qui_content', 'mai_dt_sending')
                            ->join('con_contacts', 'cam_mailings.contact_id', 'con_contacts.id')
                            ->join('cam_mailing_status', 'cam_mailings.status_id', 'cam_mailing_status.id')
                            ->leftJoin('cam_messages', 'cam_mailings.message_id', 'cam_messages.id')
                            ->leftJoin('cam_templates', 'cam_mailings.template_id', 'cam_templates.template_id')
                            ->leftJoin('cha_templates_messages', 'cam_templates.template_id', 'cha_templates_messages.id')
                            ->leftJoin('man_channels', 'cam_mailings.channel_id', 'man_channels.id')
                            ->leftJoin('cha_quick_messages', 'cam_messages.quick_message_id', 'cha_quick_messages.id')
                            ->groupBy('cam_mailings.id', 'con_contacts.con_name', 'con_contacts.con_phone', 'cam_messages.mes_content', 'cha_templates_messages.tem_body',
                            'man_channels.cha_name', 'cam_mailing_status.mai_description', 'cam_mailings.mai_contact_returned',
                            'cam_mailings.contact_id', 'cam_mailings.campaign_id', 'cha_quick_messages.qui_content', 'mai_dt_sending');

        if($params['q'] != '') {
            $mailing = $mailing->where(function ($query) use($params) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_contacts.con_name', 'like', '%'.trim($params['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('con_contacts.con_phone', 'like', '%'.trim($params['q']).'%');
            });
        }
        $mailing = $mailing->where('cam_mailings.campaign_id', $params['campaignId']);
        $mailing = $mailing->orderBy('cam_mailings.id', 'ASC');
        if($params['status'] != '') {
            $mailing = $mailing->where('cam_mailings.status_id', $params['status']);
        }
        if($params['q'] == '') {
            //Busca os contatos de acordo com a paginação
            $mailing = $mailing->skip($skip); //Quantidade de itens que serão desconsiderados por já terem sido carregados 
            $mailing = $mailing->take($params['perPage']); //Quantidade de itens trazidos
            $mailing = $mailing->get();

            $total = Mailing::where('campaign_id', $params['campaignId'])->count();
        }
        else {
            $mailing = $mailing->get();
            //Pega o total de contatos de acordo com a busca
            $total = count($mailing);
        }

        self::getTagsCampaignMailing($mailing);

        return response()->json([
            'mailing'=> $mailing,
            'total'=> $total,
        ], 201);
    }

    //Traz as tags associadas aos contatos do mailing da campanha
    public function getTagsCampaignMailing($mailing)
    {   
        foreach($mailing as $key => $contactData) {
            $tags = null;
            $tags = CampaignTagHistory::join('man_tags', 'cam_tags_history.tag_id', 'man_tags.id')
                                        ->where('campaign_id', $contactData->campaign_id)
                                        ->where('contact_id', $contactData->contact_id)
                                        ->get();
            
            $mailing[$key]->setAttribute('tags', $tags);
        }
    }

    //Traz a última operação realizada no mailing de uma campanha
    public function getLastOperationMailingCampaign($campaignId)
    {   
        $lastOperationMailing = Mailing::where('campaign_id', $campaignId)
                            ->whereNotNull('mai_dt_sending') //Onde existe a data/hora de envio
                            ->orderBy('mai_dt_sending', 'DESC')
                            ->first();
        
        return $lastOperationMailing;
    }

    //Marca como o contato retornou o a mensagem da campanha
    public function setContactReturn($campaignId, $contactId)
    {
        $contactMailing = Mailing::where('campaign_id', $campaignId)
                                    ->where('contact_id', $contactId)
                                    ->first();

        //Se encontrou o contato no mailing
        if($contactMailing) {
            $contactMailing->mai_contact_returned = true;
            $contactMailing->save();
        }
    }

    //Retorna o total de contatos que retornaram a mensagem da campanha
    public function getTotalContactsReturned($campaignId)
    {
        $totalContactReturned = Mailing::where('campaign_id', $campaignId)
                                        ->where('mai_contact_returned', 1)
                                        ->count();

        return $totalContactReturned;
    }

    public function downloadMailing(Request $request)
    {
        Log::debug('download mailing');
        Log::debug($request);
        $campaign = Campaign::find($request->campaignId);

        $filename = 'Relatório - '.$campaign->cam_name.'.xlsx';
        Excel::store(new MailingExport($campaign, $request), $filename);

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
    }

    //Download do modelo de mailing
    public function downloadMailingModel(Request $request)
    {
        $filename = 'Mailing - Modelo.csv';
        //$filePath = storage_path().'/app/public/'.$filename;

        //$file = Storage::get($filename);
        $file = file_get_contents(storage_path('app/public/'.$filename));
        if ($file) {
            $fileLink = 'data:text/csv;charset=utf-8;base64,' . base64_encode($file);
            @chmod(Storage::disk('local')->path($filename), 0755);
            @unlink(Storage::disk('local')->path($filename));
        }

        return response()->json([
            'linkData' => $fileLink,
            'filename' => $filename,
        ], 200);
    }

    //Atualiza o status de entrega de uma mensagem para um contato em um mailing
    public function updateMailingStatus($mailingId, $statusId)
    {
        $mailingUpdated = Mailing::find($mailingId);
        $mailingUpdated->status_id = $statusId;
        $mailingUpdated->save();
    }

    //Traz um mailing associado a uma campanha
    public function getMailing($campaignId)
    {
        $mailing = Mailing::with('contact')
                            ->where('campaign_id', $campaignId)
                            ->get();

        return $mailing;
    }

}

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class MailingExport implements FromCollection, Responsable, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    use Exportable;
    
    public $campaignId;
    public $campaign;
    public $params;

    public function __construct($campaign, $params)
    {
        $this->campaign = $campaign;
        $this->params = $params;
    }
    
    /**
    * Optional Writer Type
    */
    private $writerType = \Maatwebsite\Excel\Excel::XLSX;
    
    /**
    * Optional headers
    */
    /*private $headers = [
        'Content-Type' => 'text/csv',
    ];

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'use_bom' => false,
            'output_encoding' => 'ISO-8859-1',
        ];
    }*/
    public function styles(Worksheet $sheet)
    {   
        //Faz um colspan das colunas
        $sheet->mergeCells('A1:I1');

        return [
            // Style the first row as bold text.
            3    => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            'A1' => ['font' => ['size' => 14, 'bold' => true]],

            // Styling an entire column.
            3  => ['font' => ['size' => 12]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                //Centraliza a linha referente ao título da planilha
                $event->sheet->getDelegate()->getStyle('A:D')
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                $event->sheet->getDelegate()->getStyle('E3')
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                
                 //Centraliza a linha referente ao título da planilha
                 $event->sheet->getDelegate()->getStyle('F:J')
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                
                $event->sheet->getDelegate()->getStyle('A3:J3')
                                ->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('E2E2E2');
   
            },
        ];
    }

    //Largura das colunas das tabelas
    public function columnWidths(): array
    {
        return [
            'A' => 60,
            'B' => 20,            
            'C' => 20,
            'D' => 20,            
            'E' => 30,            
            'F' => 20,            
            'G' => 25,            
            'H' => 15,            
            'I' => 25,            
            'J' => 25,            
        ];
    }

    public function collection()
    {
        $mailingController = new MailingController();
        
        $mailing = Mailing::select('con_contacts.con_name', 'con_contacts.con_phone', 'con_contacts.con_cpf', 'con_contacts.con_cnpj', DB::raw('COALESCE(cam_messages.mes_content, COALESCE(cha_templates_messages.tem_body, cha_quick_messages.qui_content)) mes_content'), 'man_channels.cha_name', 
                                'cam_mailing_status.mai_description', 'cam_mailings.mai_contact_returned', 'cam_mailings.contact_id', 'cam_mailings.campaign_id', 'mai_dt_sending')
                            ->join('cam_campaigns', 'cam_mailings.campaign_id', 'cam_campaigns.id')
                            ->join('con_contacts', 'cam_mailings.contact_id', 'con_contacts.id')
                            ->join('cam_mailing_status', 'cam_mailings.status_id', 'cam_mailing_status.id')
                            ->leftJoin('cam_messages', 'cam_mailings.message_id', 'cam_messages.id')
                            ->leftJoin('cam_templates', 'cam_mailings.template_id', 'cam_templates.template_id')
                            ->leftJoin('cha_templates_messages', 'cam_templates.template_id', 'cha_templates_messages.id')
                            ->leftJoin('man_channels', 'cam_mailings.channel_id', 'man_channels.id')
                            ->leftJoin('cha_quick_messages', 'cam_messages.quick_message_id', 'cha_quick_messages.id')
                            ->where('cam_mailings.campaign_id', $this->campaign->id)
                            //->groupBy('con_contacts.con_name', 'con_contacts.con_phone', 'con_contacts.con_cpf', 'con_contacts.con_cnpj',
                            //            'mes_content', 'cam_mailing_status.mai_description', 'cam_mailings.mai_contact_returned', 'cam_mailings.contact_id', 'cam_mailings.campaign_id')
                            ->groupBy('cam_mailings.id', 'con_contacts.con_name', 'con_contacts.con_phone', 'cam_messages.mes_content', 'cha_templates_messages.tem_body',
                            'man_channels.cha_name', 'cam_mailing_status.mai_description', 'cam_mailings.mai_contact_returned',
                            'cam_mailings.contact_id', 'cam_mailings.campaign_id', 'cha_quick_messages.qui_content', 'mai_dt_sending');
        if($this->params['status'] != '') {
            $mailing = $mailing->where('cam_mailings.status_id', $this->params['status']);
        }
        $mailing = $mailing->orderBy('cam_mailings.id', 'ASC')
                            ->get();
        
        $mailingController->getTagsCampaignMailing($mailing);
        
        //Para cada contato
        foreach($mailing as $key => $contactTag) {
            $tagsContact = null;
            //Pega as tags associadas ao mesmo
            foreach($contactTag['tags'] as $key2 => $tag) {
                if($key2 == 0) {
                    $tagsContact = $tag->tag_name;
                }
                else {
                    $tagsContact .= ', '.$tag->tag_name;
                }
            }
            $mailing[$key]->setAttribute('tagsContact', $tagsContact);
        }

        $mailing->transform(function ($result) {
            $attributes = $result->getAttributes(); //Pega os atributos da Collection
            unset($attributes['campaign_id']); //Remove o atributo campaign_id
            unset($attributes['contact_id']); //Remove o atributo contact_id
            unset($attributes['tags']); //Remove o atributo tags
            $result->setRawAttributes($attributes, true);
            return $result;
        });
        
        
        return $mailing;
    }

    //Cabeçalho
    public function headings(): array
    {
        return [
            ['CAMPANHA: '. $this->campaign->cam_name . ' - ' .$this->campaign->cam_description],
            [],
            ["NOME", "TELEFONE", "CPF", "CNPJ", "MENSAGEM","CANAL", "STATUS DO ENVIO", "RETORNOU", 'DATA ENVIO', 'TAGS']
        ];
                
    }

    


    public function toResponse($request)
    {
        
    }
}
