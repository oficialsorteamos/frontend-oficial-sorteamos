<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Api\CommunicatorController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Utils\UtilsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\System\AddressController;
use App\Http\Controllers\System\DddStateController;
use App\Http\Controllers\Campaign\SettingController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\Financial\CostController;
use App\Http\Controllers\Financial\CreditController;
use App\Http\Controllers\Financial\FeeController;
use App\Http\Controllers\Financial\ParameterController;
use App\Http\Controllers\Management\EventController;
use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignMessage;
use App\Models\Campaign\CampaignOperatingHour;
use App\Models\Campaign\CampaignType;
use App\Models\Campaign\ChannelCampaign;
use App\Models\Campaign\ChannelChatbot;
use App\Models\Campaign\Mailing;
use App\Models\Campaign\Setting;
use App\Models\Chat\TemplateCampaign;
use App\Models\Chat\TemplateMessage;
use App\Models\Chatbot\Chatbot;
use App\Models\Chatbot\ChatbotChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Contact\Contact;
use Auth;
use DB;

class CampaignController extends Controller
{
    private $dddStateController;
    private $chatController;

    public function __construct()
    {
        $this->dddStateController = new DddStateController();
    }
    /**
     * Display a listing of the resource. 
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $params)
    {
        $utils = new UtilsController();
        $chat = new ChatController();
        $creditController = new CreditController();
        
        $campaigns = Campaign::with('channels', 'settings.operatingFrequency', 'settings.numberShotFrequency', 'operatingHours', 'settings.department', 'settings.fairDistribution', 'messages',
                                    'typeCampaign')
                            //->select('cam_campaigns.id')
                            ->orderBy('cam_campaigns.created_at', 'DESC');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($params['q'] != '') {
            //Verifica se a busca coincide com o nome de algum usuário
            $campaigns = $campaigns->where('cam_name', 'like', '%'.trim($params['q']).'%');
            //Verifica se busca coincide com o telefone de algum usuário
            $campaigns = $campaigns->orWhere('cam_description', 'like', '%'.trim($params['q']).'%');
        }
        //Onde a campanha não esteja com status de removida
        $campaigns = $campaigns->where('status_id', '!=', 5);
        $campaigns = $campaigns->get();

        $balance = $creditController->getTotalBalance();

        //Se existir alguma campanha
        if($campaigns) {
            //Para cada campanha
            foreach($campaigns as $campaign) {
                self::getDetailsCampaign($campaign, $balance);
            }
        }

        //Log::debug('dados campanha');
        //Log::debug($campaigns);
        $baseUrlStorage = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC');

        return response()->json([
            'campaigns'=> $utils->paginateArray($campaigns->toArray(), $params['perPage'], $params['page']),
            'total'=> count($campaigns),
            'baseUrlStorage' => $baseUrlStorage,
            'balance' => $balance,
        ], 201);
    }

    //Traz os detalhes de uma campanha
    public function getDetailsCampaign($campaign, $balance)
    {   
        $mailingController = new MailingController();
        
        //Retorna o total de contatos contidos no mailing
        $totalContactMailing = $mailingController->getTotalContactMailingCampaign($campaign->id);
        $campaign->setAttribute('totalContactMailing', $totalContactMailing);

        //Retorna o total de contatos que a mensagem ainda não foi enviada (Aguardando Envio)
        $totalContactPendingSending = $mailingController->getTotalContactsMailingByStatus($campaign->id, 1);
        $campaign->setAttribute('totalContactPendingSending', $totalContactPendingSending);

        //Retorna o total de contatos que a mensagem já foi entregue (Entregue)
        $totalContactDeliveredMessage = $mailingController->getTotalContactsMailingByStatus($campaign->id, 6);
        $campaign->setAttribute('totalContactDeliveredMessage', $totalContactDeliveredMessage);

        //Retorna o total de contatos que a mensagem já foi enviada (Enviado)
        $totalContactSentMessage = $mailingController->getTotalContactsMailingByStatus($campaign->id, 2);
        
        //Para efeito de gráfico da campanha, considera ENVIADOS e ENTREGUES como uma coisa só
        $totalContactSentMessage = $totalContactSentMessage + $totalContactDeliveredMessage;
        $campaign->setAttribute('totalContactSentMessage', $totalContactSentMessage);

        //Retorna o total de contatos em que a mensagem falhou a ser enviada (Falha)
        $totalContactSentFailure = $mailingController->getTotalContactsMailingByStatus($campaign->id, 3);
        $campaign->setAttribute('totalContactSentFailure', $totalContactSentFailure);

        //Retorna o total de contatos que já estão em atendimento
        $totalContactInService = $mailingController->getTotalContactsMailingByStatus($campaign->id, 4);
        $campaign->setAttribute('totalContactInService', $totalContactInService);

        //Retorna o total de contatos que já estão em atendimento
        $totalContactBlacklist = $mailingController->getTotalContactsMailingByStatus($campaign->id, 5);
        $campaign->setAttribute('totalContactBlacklist', $totalContactBlacklist);
        
        //Total de contatos já prcessados (contatos com mensagem enviada + falha no envio + contatos em atendimento) no mailing da campanha
        $totalContactsProcessed = $totalContactSentMessage + $totalContactSentFailure + $totalContactInService + $totalContactBlacklist;
        $campaign->setAttribute('totalContactsProcessed', $totalContactsProcessed);

        //Retorna o total de contatos que retornaram a mensagem
        $totalContactReturnedMessage = $mailingController->getTotalContactsReturned($campaign->id);
        $campaign->setAttribute('totalContactReturnedMessage', $totalContactReturnedMessage);

        //Caso a campanha não esteja EM ANDAMENTO e FINALIZADA
        if($campaign->status_id != 2 && $campaign->status_id != 3) {
            //Verifica se há alguma pendência na campanha que impede que ela seja iniciada
            self::checklistCampaign($campaign, $balance);
        }
    }

    //Faz um checklist, verificando se há alguma pendência na configuração da campanha
    public function checklistCampaign($campaign, $balance)
    {
        $parameterController = new ParameterController();
        $costController = new CostController();

        $infoMessage = null;
        if(count($campaign['channels']) > 0) {
            $channelOfficial = self::getChannelCampaignByOfficialStatus($campaign->id, 1);
            $channelUnofficial = self::getChannelCampaignByOfficialStatus($campaign->id, 0);
            if($channelOfficial) {
                if(count($campaign['templateMessages']) == 0) {
                    $infoMessage .= "<li>Como a campanha possui um canal conectado a API Oficial, você deverá cadastrar, ao menos, uma mensagem modelo;</li> ";        
                }
            }
            if($channelUnofficial) {
                if(count($campaign['messages']) == 0) {
                    $infoMessage .= "<li>Como a campanha possui um canal conectado a API NÃO oficial, você deverá cadastrar, ao menos, uma mensagem;</li> ";        
                }
            }
        }
        else {
            //Se a campanha não possui mensagem cadastrada
            if(count($campaign['messages']) == 0 && count($campaign['templateMessages']) == 0) {
                $infoMessage .= "<li>Nenhuma mensagem cadastrada para a campanha;</li> ";
            }
        }
        
        //Se a campanha não tiver um setor cadastradado para encaminhamento dos contatos
        if($campaign['settings'][0]['department_id'] == null) {
            $infoMessage .= "<li>Nenhum setor cadastrado para encaminhamento de contatos;</li>";
        }
        //Se a campanha não possui mensagem cadastrada
        if(count($campaign['channels']) == 0) {
            $infoMessage .= "<li>Nenhuma canal adicionado para a campanha;</li> ";
        }
        //Caso a campanha não tenha horário de funcionamento cadastrado e NÃO seja uma campanha de LIGAÇÃO VIA WHATSAPP
        if( ($campaign['operatingHours'][0]['ope_hr_start'] == '00:00' && $campaign['operatingHours'][0]['ope_hr_end'] == '00:00' && $campaign['campaign_type_id'] != 4) 
            && ($campaign['operatingHours'][1]['ope_hr_start'] == '00:00' && $campaign['operatingHours'][1]['ope_hr_end'] == '00:00')
            && ($campaign['operatingHours'][2]['ope_hr_start'] == '00:00' && $campaign['operatingHours'][2]['ope_hr_end'] == '00:00')) {
            $infoMessage .= "<li>Nenhum horário de funcionamento cadastrado para a campanha;</li> ";
        }
        //Se a campanha não tiver nenhum contato adicionado ao Mailing
        if($campaign['totalContactMailing'] == 0) {
            $infoMessage .= "<li>Nenhum contato adicionado ao Mailing;</li> ";
        } //Se for uma campanha de LIGAÇÃO VIA WHATSAPP e a mesma NÃO tiver sido INCIADA
        if($campaign['campaign_type_id'] == 4 && $campaign['status_id'] == 4) {
            $chargeCampaign = $parameterController->getParameterByType(12);
            //Se a cobrança por LIGAÇÃO VIA WHATSAPP estiver HABILITADA
            if($chargeCampaign->par_value == '1') {
                //Pega o custo da campanha caso todas as mensagens forem enviadas
                $estimateCostCampaign = $costController->getEstimateCostCampaign($campaign['id']);
                //Log::debug('estimate cost');
                //Log::debug($estimateCostCampaign);
                //Se o custo da campanha for maior que o saldo atual do usuário
                if($estimateCostCampaign > $balance) {
                    $infoMessage .= "<li>O custo dessa campanha é de R$ ".number_format($estimateCostCampaign, 2, ',', '').", sendo maior que o seu saldo atual. Adicione mais crédito para poder rodar essa campanha;</li> ";
                }
            }
        }

        $campaign->setAttribute('pendencies', $infoMessage);
    }

    //Traz o canal de uma campanha filtrando por ser oficial ou não
    public function getChannelCampaignByOfficialStatus($campaignId, $officialStatus)
    {
        $channelCampaign = Campaign::join('cam_channels_campaigns', 'cam_campaigns.id', 'cam_channels_campaigns.campaign_id')
                            ->join('man_channels', 'cam_channels_campaigns.channel_id', 'man_channels.id')
                            ->where('cam_channels_campaigns.campaign_id', $campaignId)
                            ->where('man_channels.cha_api_official', $officialStatus)
                            ->where('cam_channels_campaigns.cha_status', 'A') //Onde o canal está ativo na campanha
                            ->first();

        return $channelCampaign;
    }

    //Traz os canais associados a uma campanha
    public function getChannelsCampaign($campaignId)
    {
        $channelsCampaign = ChannelCampaign::where('campaign_id', $campaignId)
                                            ->where('cha_status', 'A')
                                            ->get();

        return $channelsCampaign;
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
        try {
            Log::debug('store campaign');
            Log::debug($request);

            //Salva a nova campanha
            $campaign = new Campaign();
            $campaign->campaign_type_id = $request->campaignData['type_campaign']['id']; //A princípio, seta todas as campanhas como de Whatsapp
            $campaign->cam_name = $request->campaignData['cam_name'];
            $campaign->cam_description = $request->campaignData['cam_description'];
            
            //Se a campanha foi salva
            if($campaign->save()) {
                $settingController = new SettingController();
                
                //Salva as configurações iniciais da campanha
                $settingController->store($campaign->id);
            }

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
    //Traz uma campanha pelo seu id
    public function show($id)
    {
        $campaign = Campaign::with('channels', 'settings.operatingFrequency', 'settings.fairDistribution', 'operatingHours')
                            ->find($id);
        
                            //Se existir alguma campanha
        if($campaign) {
            self::getDetailsCampaign($campaign, null);
        }

        //Caso a campanha não esteja EM ANDAMENTO e FINALIZADA
        if($campaign->status_id != 2 && $campaign->status_id != 3) {
            //Verifica se há alguma pendência na campanha que impede que ela seja iniciada
            self::checklistCampaign($campaign, null);
        }

        return $campaign;
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
            //Log::debug('update campaign');
            //Log::debug($request);
            $campaign = Campaign::find($request['id']);
            $campaign->campaign_type_id = $request['type_campaign']['id'];
            $campaign->cam_name = $request['cam_name'];
            $campaign->cam_description = $request['cam_description'];
            $campaign->save();
            
            return response()->json(
                $request
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
        $mailingController = new MailingController();

        $campaign = Campaign::find($id);
        //Verifica se já foi realizada alguma operação na campanha
        $isOperatingMailing = $mailingController->getTotalContactsMailingByStatus($id, 2);
        //Se nenhuma operação foi realizada
        if($isOperatingMailing == 0) {
            $campaign->delete();
        }
        else {
            //Coloca o status da campanha como INATIVO
            $campaign->status_id = 5; 
            $campaign->save();
        }

    }

    //Traz as mensagens de uma determinada campanha
    public function fetchMessages($campaignId)
    {
        $utilsController = new UtilsController();
        $campaignMessages = CampaignMessage::with('quickMessage.parameters')
                                                ->select('cam_messages.id', DB::raw("COALESCE(cha_quick_messages.qui_content, cam_messages.mes_content) as content"),
                                                    'qui_title', 'quick_message_id')
                                                ->leftJoin('cha_quick_messages', 'cam_messages.quick_message_id', 'cha_quick_messages.id')
                                                ->where('campaign_id', $campaignId)
                                                ->where('mes_status', 'A')
                                                ->get();
        
        foreach($campaignMessages as $message) {
            $message->content = $utilsController->changeParagraphContent($message->content);
        }

        $campaign = self::show($campaignId);
        
        return response()->json([
            'campaignMessages'=> $campaignMessages,
            'campaign'=> $campaign,
        ], 201);
    }

    //Adiciona uma nova mensagem na campanha
    public function addMessage(Request $request)
    {
        Log::debug('addMessage $request');
        Log::debug($request);

        $newMessage = new CampaignMessage();

        $newMessage->quick_message_id = $request['quickMessageData']['id'];
        $newMessage->campaign_id = $request['campaignId'];
        $newMessage->user_id = Auth::user()->id;
        $newMessage->save();

        return response()->json([
            
        ], 200);
    }

    public function removeMessage($messageId)
    {
        //Inativa a mensagem
        $message = CampaignMessage::find($messageId);
        $message->mes_status = 'I';
        $message->save();

        return response()->json([
            'campaignId' => $message->campaign_id
        ], 200);
    }

    //Traz as mensagens de uma determinada campanha
    public function getCampaignMessages($campaignId)
    {
        $utilsController = new UtilsController();
        $campaignMessages = CampaignMessage::select('cam_messages.id', DB::raw("COALESCE(cha_quick_messages.qui_content, cam_messages.mes_content) as content"),
                                                    'qui_title', 'quick_message_id')
                                                ->leftJoin('cha_quick_messages', 'cam_messages.quick_message_id', 'cha_quick_messages.id')
                                                ->where('campaign_id', $campaignId)
                                                ->where('mes_status', 'A')
                                                ->get();
        
        return $campaignMessages;
    }

    //Atualiza uma mensagem de uma campanha
    public function updateMessage(Request $request)
    {
        $message = CampaignMessage::find($request['id']);
        $message->mes_content = $request['content'];
        $message->save();

        return response()->json([
            'campaignId' => $message->campaign_id
        ], 200);
    }

    public function addMailing(Request $request)
    {
        ini_set('max_execution_time', 250);

        $contactController = new ContactController();
        $mailingController = new MailingController();
        
        $totalPhoneIncorrect = 0;
        //Array que vai armazenar cada linha do CSV
        $fileArray = [];

        //Se algum arquivo foi upado
        if($request->file()) {
            $file = $request->file;   
            $filename = $file->getClientOriginalName();
            $count = 0;
            $phoneIncorrectMessage = "As seguinte(s) linha(s) estão com o(s) número(s) de telefone(s) incorretos: "; 
            // Upload file
            //$file->makeDirectory(public_path($location), $mode = 0755, true, true);
            //$file->move($location, $filename);
            //Salva o arquivo
            Storage::disk('public')->putFileAs('uploads', $request->file, $filename);
            //Pega o caminho do arquivo
            $filepath = storage_path('app/public/uploads/'. $filename);
            $fileCsv = file($filepath);
            //Contabiliza o total de linhas no arquivo (O total menos o cabeçalho)
            $totalRows = count($fileCsv) - 1;

            //Se o arquivo tem até 5000 linhas
            if($totalRows <= 5000) {
                // Reading file
                if(($open = fopen($filepath, "r")) !== FALSE) {

                    //Para cada Linha do CSV
                    while (($data = fgetcsv($open, 5001, ";")) !== FALSE) {
                        //Se o mailing possui 14 colunas
                        if(count($data) == 14) {
                            //Se não for a linha do cabeçalho do CSV
                            if($count != 0) {
                                //Se o número de telefone conter 10 (número fixo) ou 11 dígitos (número móvel)
                                if(strlen($data[0]) == 10 || strlen($data[0]) == 11) {
                                    $fileArray[$count] = $data;
                                }
                                else {
                                    $phoneIncorrectMessage = $phoneIncorrectMessage. ($count+1). ';'; //Corrigir o número da linha para quando não houver cabeçalho
                                    $totalPhoneIncorrect++;
                                }
                            }
                            $count++;
                        }
                    }
                    fclose($open);
                }
            }
            else {
                Log::debug('entrou aqui no mailing');
                
                $errorMessage = 'O arquivo deve possuir, no máximo, 5000 linhas para poder ser importado';
                
                return response()->json([
                    'errorMessage' => $errorMessage
                ], 200);
            }
            
        }
        $mailing = [];
        //Se não existe telefones incorretos
        if($totalPhoneIncorrect == 0) {
            //Para cada contato no array
            foreach($fileArray as $contactData) {
                $contact = null;
                $mailing = [];
                $phoneNumberWithDdi = '55'.$contactData[0];
                //Verifica se o contato já existe no banco de dados
                $contact = $contactController->getContactByPhoneNumber($phoneNumberWithDdi);
                //Se o contato já existe no banco de dados
                if($contact) {
                    //Verifica se o contato já foi inserido no mailing da campanha
                    //$mailingExist = $mailingController->getMailingCampaignContact($request->campaignId, $contact->id);
                    //Se o contato ainda não está inserido no mailing da campanha
                    //if(!$mailingExist) {
                        $mailing['campaignId'] = $request->campaignId;
                        $mailing['contactId'] = $contact->id;
                        $mailing['statusId'] = 1; //Aguardando Envio
                        $mailing['additionalDataMessage'] = isset($contactData[13])? $contactData[13] : null;
                        $mailingController->store($mailing);
                    //}    
                }
                else {
                    //Salva o contato no banco de dados
                    $contact = $contactController->storeContactMailing($contactData);

                    //Salva o contato no mailing da campanha
                    $mailing['campaignId'] = $request->campaignId;
                    $mailing['contactId'] = $contact->id;
                    $mailing['statusId'] = 1; //Aguardando Envio
                    $mailing['additionalDataMessage'] = isset($contactData[13])? $contactData[13] : null;
                    $mailingController->store($mailing);
                }   
            }

            //Se alguma tag foi selecionada
            if($request['tags'][0] != 'undefined') {
                //Pega só o id das tags
                $tagsId = array_map(function ($item) {
                    return $item->id;
                }, json_decode($request['tags'][0]));
                
                //Traz oo ids dos contatos que possui possui as tags selecionadas pelo usuário
                $contactsIdTags = $contactController->getContactsByTags($tagsId);
                //Para cada contato que possui a tag
                foreach($contactsIdTags as $contactId) {
                    //Verifica se i contato já foi inserido no mailing da campanha
                    $mailingExist = $mailingController->getMailingCampaignContact($request->campaignId, $contactId['contact_id']);
                    //Se o contato ainda não está inserido no mailing da campanha
                    if(!$mailingExist) {
                        $mailing['campaignId'] = $request->campaignId;
                        $mailing['contactId'] = $contactId['contact_id'];
                        $mailing['statusId'] = 1; //Aguardando Envio
                        $mailingController->store($mailing);
                    }
                }
            }            
            $campaign = self::show($request->campaignId);
            //Se a campanha estiver como FINALIZADA e foram adicionados mais contatos a mesma
            if($campaign->status_id == 3) {
                //Coloca a mesma como pausada
                $requestStatusCampaign = new Request([
                    'campaignId' => $campaign->id,
                    'statusId' => 1, //Pausada
                ]);

                self::updateStatusCampaign($requestStatusCampaign);
                $campaign->status_id = 1;
            }

            return response()->json([
                'campaign' => $campaign
            ], 200);
        }
        else {
            $campaign = self::show($request->campaignId);
            
            return response()->json([
                'errorMessage' => $phoneIncorrectMessage,
                'campaign' => $campaign
            ], 200);
        }
        

        //Log::debug('Array CSV');
        //Log::debug($fileArray);
        
    }

    public function removeContactMailing(Request $request)
    {
        Log::debug('ids dos contatos');
        Log::debug($request);

        //Se o usuário selecionou algum contato no mailing para ser removido 
        if(count($request['contactIds']) > 0) {
            foreach($request['contactIds']  as $mailingId) {
                Mailing::where('id', $mailingId)
                        ->where('status_id', 1) //Onde o status é aguardando envio
                        ->delete();
            }
        }
        else { //Remove todos os contatos do mailing
            Mailing::where('campaign_id', $request['campaignId'])
                    ->where('status_id', 1) //Onde o status é aguardando envio
                    ->delete();
        }

        return response()->json([
            
        ], 200);
    }

    //Faz a campanha iniciar
    public function startCampaign()
    {   
        $chatController = new ChatController();
        $serviceController = new ServiceController();
        $mailingController = new MailingController();
        $contactController = new ContactController();
        $creditController = new CreditController();
        $parameterController = new ParameterController();
        $feeController = new FeeController();

        //Variável que armazena se a campanha está dentro do horário de operação
        $checkTime = false;
        $checkFrequency = false;

        //Traz as campanhas em andamento
        $campaigns = Campaign::with('settings.numberShotFrequency', 'operatingHours')
                            ->where('campaign_type_id', '!=', 4) //Onde NÃO é campanha de Ligação via WhatsApp
                            ->where('status_id', 2)
                            ->get();
        
        //Para cada campanha
        foreach($campaigns as $campaign) {
            Log::debug("campaign data");
            Log::debug($campaign);

            //Dispara as mensagens de acordo com a quantidade de disparos por frequência
            for($i=0; $i < $campaign['settings'][0]['numberShotFrequency']['num_shots']; $i++) {

                //Traz o próximo contato do mailing que receberá a mensagem da campanha
                $contactMailing = Mailing::where('status_id', 1) //Status de Aguardando Envio
                                        ->where('campaign_id', $campaign->id)
                                        ->orderBy('id', 'ASC')
                                        ->first();

                //Se existe algum contato no mailing para ser processado
                if($contactMailing) {
                    //Verifica se é para cobrar do cliente o envio de mensagens de campanha
                    //Se a campanha é de WHATSAPP
                    if($campaign->campaign_type_id == 1) {
                        $chargeCampaign = $parameterController->getParameterByType(5);
                    }//Se a campanha é de SMS
                    else if($campaign->campaign_type_id == 2) {
                        $chargeCampaign = $parameterController->getParameterByType(10);
                    }
                    
                    $balance = 0;
                    $fee = 0;
                    //Se a cobrança estiver habilitada
                    if($chargeCampaign->par_value == '1') {
                        //Traz o saldo do cliente na plataforma
                        $balance = $creditController->getTotalBalance();
                        //Pega a taxa de envio por WhatsApp
                        $fee = $feeController->getFeeByType(5);
                    }
                    //Log::debug('$chargeCampaign->par_value');
                    //Log::debug($chargeCampaign->par_value);
                    // Se a cobrança por envio está DESATIVADA ou a cobrança por envio está ATIVADA e existe saldo suficiente para enviar 
                    if($chargeCampaign->par_value == '0' || ($chargeCampaign->par_value == '1' && (abs(($balance - $fee['fee_value'])/$fee['fee_value']) < 0.00001 || $balance > $fee['fee_value']) )) {
                        $weekMap = [
                            0 => 'SU',
                            1 => 'MO',
                            2 => 'TU',
                            3 => 'WE',
                            4 => 'TH',
                            5 => 'FR',
                            6 => 'SA',
                        ];
                        $dayOfTheWeek = Carbon::now()->dayOfWeek;
                        $weekDay = $weekMap[$dayOfTheWeek];

                        $currentDateTime = Carbon::now();
                        $currentTime = $currentDateTime->format('H:i');
                        //Caso seja ou segunda ou terça ou quarta ou quinta oi sexta
                        if($weekDay == 'MO' || $weekDay == 'TU' || $weekDay == 'WE' || $weekDay == 'TH' || $weekDay == 'FR') {
                            //Se estiver dentro do horário de operação e horário tanto de inicio quanto de finalização da operação for diferente de 00:00
                            if( ($currentTime >= $campaign['operatingHours'][0]['ope_hr_start'] && $currentTime <= $campaign['operatingHours'][0]['ope_hr_end']) 
                                && ($campaign['operatingHours'][0]['ope_hr_start'] != '00:00' && $campaign['operatingHours'][0]['ope_hr_end'] != '00:00') ) {
                                Log::debug('está dentro do horário');
                                //Coloca a campanha como dentro do horário de operação
                                $checkTime = true;
                            }
                        } //Se for um sábado
                        else if($weekDay == 'SA') {
                            //Se estiver dentro do horário de operação e horário tanto de inicio quanto de finalização da operação for diferente de 00:00
                            if( ($currentTime >= $campaign['operatingHours'][1]['ope_hr_start'] && $currentTime <= $campaign['operatingHours'][1]['ope_hr_end'])
                                && ($campaign['operatingHours'][1]['ope_hr_start'] != '00:00' && $campaign['operatingHours'][1]['ope_hr_end'] != '00:00') ) {
                                Log::debug('está dentro do horário');
                                //Coloca a campanha como dentro do horário de operação
                                $checkTime = true;
                            }
                        } //Se for um domingo
                        else if($weekDay == 'SU') {
                            //Se estiver dentro do horário de operação e horário tanto de inicio quanto de finalização da operação for diferente de 00:00
                            if( ($currentTime >= $campaign['operatingHours'][2]['ope_hr_start'] && $currentTime <= $campaign['operatingHours'][2]['ope_hr_end'])
                                && ($campaign['operatingHours'][2]['ope_hr_start'] != '00:00' && $campaign['operatingHours'][2]['ope_hr_end'] != '00:00') ) {
                                Log::debug('está dentro do horário');
                                //Coloca a campanha como dentro do horário de operação
                                $checkTime = true;
                            }
                        }

                        //Traz a última operação realizada no mailing, se houver
                        $lastOperationMailing = $mailingController->getLastOperationMailingCampaign($campaign['id']);
                        //Caso exista alguma operação realizada no mailing da campanha
                        if($lastOperationMailing) {
                            //Calcula quantos segundos se passaram desde a última operação
                            $totalDuration = $currentDateTime->diffInSeconds($lastOperationMailing->mai_dt_sending);
                            //Calcula a frequência de operação em segundos
                            $secondsFrequency = $campaign['settings'][0]['operatingFrequency']['ope_minutes']*60;
                            Log::debug('$totalDuration');
                            Log::debug($totalDuration);
                            //Se já se passou mais tempo que a frequência de operação
                            if($totalDuration >= $secondsFrequency-5) {
                                $checkFrequency = true;
                            }
                        } //Caso não tenha sido realizada alguma operação
                        else {
                            $checkFrequency = true;
                        }
                        
                        //Se está dentro do horário e frequência de operação da campanha
                        if($checkTime && $checkFrequency) {
                            //Verifica se contato está na blacklist
                            $isContactBlacklist = $contactController->getContactBlacklistCampaign($contactMailing->contact_id);
                            Log::debug('$isContactBlacklist');
                            Log::debug($isContactBlacklist);
                            //Se o contato NÃO estiver na blacklist
                            if(!$isContactBlacklist) {
                                $chat = $chatController->getChatsContact($contactMailing->contact_id);
                                
                                $serviceOpen = $serviceController->getServices($chat[0]->id, 1);
                                $serviceEvaluation = $serviceController->getServices($chat[0]->id, 4); 
                                //Se o contato não estiver em atendimento e nem pendente de avaliação no momento do envio da mensagem e tiver alguma mensagem cadastrada 
                                if($serviceOpen->isEmpty() && $serviceEvaluation->isEmpty()) {
                                    $channelController = new ChannelController();

                                    $channelCampaign = null;
                                    //Se for uma campanha de WhatsApp
                                    if($campaign->campaign_type_id == 1) {
                                        //Pega o canal associado a campanha com menor números de operações
                                        $channelCampaign = self::getChannelCampaign($campaign->id);
                                    }

                                    //Se for uma campanha de WhatsApp e existir algum canal ativo na campanha, ou se for uma campanha de SMS
                                    if($channelCampaign || $campaign->campaign_type_id == 2) {
                                        $channel = null;
                                        if($channelCampaign) {
                                            //Traz o canal o canal que será usado para transmitir a mensagem
                                            $channel = $channelController->getChannel($channelCampaign->channel_id);

                                            //Caso NÃO seja um canal oficial
                                            if($channel->cha_api_official == false) {
                                                //Traz uma mensagem da campanha de forma aleatória 
                                                $message = CampaignMessage::select('cam_messages.id', 'cha_quick_messages.id AS quick_message_id', 'cha_quick_messages.qui_content AS mes_content')
                                                                        ->leftJoin('cha_quick_messages', 'cam_messages.quick_message_id', 'cha_quick_messages.id')
                                                                        ->where('campaign_id', $campaign->id)
                                                                        ->where('mes_status', 'A')
                                                                        ->get()
                                                                        ->random(1)//Traz uma mensagem aleatória
                                                                        ->values();
                                            } //Caso SEJA um canal oficial
                                            else { //Traz uma mensagem template cadastrada
                                                $message = TemplateMessage::select('cha_templates_messages.id', 'cha_templates_messages.tem_body AS mes_content')
                                                                            ->join('cam_templates', 'cha_templates_messages.id', 'cam_templates.template_id')
                                                                            ->whereIn('cha_templates_messages.status_id', [2, 5] )//Se o template estiver aprovado ou sinalizado
                                                                            ->where('cam_templates.campaign_id', $campaign->id)
                                                                            ->where('cam_templates.tem_status', 'A')
                                                                            ->get()
                                                                            ->random(1)//Traz uma mensagem aleatória
                                                                            ->values();
                                            }
                                        } //Se for uma campanha de SMS
                                        else if($campaign->campaign_type_id == 2) {
                                            //Traz uma mensagem da campanha de forma aleatória 
                                            $message = CampaignMessage::select('cam_messages.id', 'cha_quick_messages.id AS quick_message_id', 'cha_quick_messages.qui_content AS mes_content')
                                                                    ->leftJoin('cha_quick_messages', 'cam_messages.quick_message_id', 'cha_quick_messages.id')
                                                                    ->where('campaign_id', $campaign->id)
                                                                    ->where('mes_status', 'A')
                                                                    ->get()
                                                                    ->random(1)//Traz uma mensagem aleatória
                                                                    ->values();
                                        }
                                        
                                        
                                        

                                        $campaignMessageData = array(
                                            'chatId' => $chat[0]->id,
                                            'contactId' => $contactMailing->contact_id,
                                            'typeUserId' => 3, //Coloca o tipo do usuário como robô
                                            'type_message_chat_id' => 1, //Mensagem de texto
                                            'mes_message' => $message[0]->mes_content,
                                            'senderId' => 1, //Colocar aqui o ID do usuário que será o robô
                                            'actionId' => null,
                                            'privateMessage' => 'false', //Coloca a mensagem como pública
                                            'campaignId' => $campaign->id, //Id da campanha
                                            'campaignTypeId' => $campaign->campaign_type_id, //Id do tipo de campanha (WhatsApp, SMS, etc.)
                                            'mailingId' => $contactMailing->id, //Id do mailing
                                            'messageId' => $message[0]->id, //Id da mensagem enviada
                                            'quickMessageId' => isset($message[0]->quick_message_id)? $message[0]->quick_message_id : null, //Id da mensagem enviada
                                            'channel' => $channel, //Dados do canal de onde a mensagem partirá
                                            'channelCampaign' => $channelCampaign, //Dados do canal/campanha
                                            'isCharged' => $chargeCampaign->par_value, //Flag se o envio será cobrado ou não
                                        );

                                        //Se NÃO for uma campanha de SMS
                                        if($campaign->campaign_type_id != 2) {
                                            //Se ao menos uma operação já tenha sido realizada no mailing da campanha
                                            if(isset($secondsFrequency)) {
                                                //Caso tenha passado até 25 segundos da frequência de operação, inativa a função por até 15 segundos
                                                if($totalDuration <= ($secondsFrequency+25)) {
                                                    sleep(rand(0, 15));
                                                }
                                                else {
                                                    sleep(rand(0, 5));
                                                }
                                            }
                                        }
                                        
                                        //Se a mensagem foi enviada
                                        if($chatController->sendMessageCampaign($campaignMessageData)) {

                                        }
                                    } //Caso nenhum canal esteja conectado (disponível) a API, PAUSA a campanha 
                                    else {

                                        $requestStatusCampaign = new Request([
                                            'campaignId' => $campaign->id,
                                            'statusId' => 1, //Pausada
                                        ]);

                                        self::updateStatusCampaign($requestStatusCampaign);
                                    }
                                } //Se o contato já está em atendimento
                                else if($serviceOpen->isNotEmpty() || $serviceEvaluation->isNotEmpty()) {
                                    //Log::debug('Contato em atendimento');
                                    $mailingController = new MailingController();
                                    //Seta o status como EM ATENDIMENTO
                                    $campaignMessageData['mailingId'] = $contactMailing->id;
                                    $campaignMessageData['statusId'] = 4;
                                    $campaignMessageData['messageId'] = null;
                                    $campaignMessageData['channel'] = null;
                                    $campaignMessageData['campaignTypeId'] = $campaign->campaign_type_id;

                                    //Atualiza o mailing
                                    $mailingController->update($campaignMessageData);
                                }
                            } //Se o contato estiver na blacklist
                            else {
                                $mailingController = new MailingController();
                                //Seta o status como BLACKLIST
                                $campaignMessageData['mailingId'] = $contactMailing->id;
                                $campaignMessageData['statusId'] = 5;
                                $campaignMessageData['messageId'] = null;
                                $campaignMessageData['channel'] = null;
                                $campaignMessageData['campaignTypeId'] = $campaign->campaign_type_id;

                                //Atualiza o mailing
                                $mailingController->update($campaignMessageData);
                            }
                        }
                    }
                    else {
                        //Se é para cobrar e não tem saldo suficiente para mandar mensagem
                        if($chargeCampaign->par_value == '1' && $balance < $fee['fee_value']) {
                            //Log::debug('Não vai executar AAAAAAAAAAAAA');
                            //Atualiza o status da campanha como Pausada - Saldo Insuficiente
                            $requestStatusCampaign = new Request([
                                'campaignId' => $campaign->id,
                                'statusId' => 6, //Pausada - Saldo Insuficiente
                            ]);

                            self::updateStatusCampaign($requestStatusCampaign);
                        }
                    }            
                }
                else {
                    $currentCampaign = Campaign::find($campaign->id);
                    $currentCampaign->status_id = 3; //Coloca o status da campanha como finalizada
                    $currentCampaign->save();
                }

                //Se a campanha realiza mais de um disparo por frequência
                if($campaign['settings'][0]['numberShotFrequency']['num_shots'] > 1) {
                    //Sorteia um tempo de espera entre um disparo e outro
                    sleep(rand(1, 4));
                }
            }
        }
    }

    //Traz o canal com menos operações na campanha
    public function getChannelCampaign($campaignId)
    {
        $channelController = new ChannelController();
        $communicatorController = new CommunicatorController();
        //Procura por um canal ativo com menos operações até encontrar ou até quando não houver mais canais para procurar
        do {
            $channelCampaign = null;
            /*$channelCampaign = ChannelCampaign::where('campaign_id', $campaignId)
                                            ->where('cha_status', 'A')
                                            //->orderBy('id', 'ASC')
                                            ->orderBy('cha_total_operations', 'ASC')
                                            ->first();*/

            $channelCampaign = self::choiceChannelCampaign($campaignId);
            //Se existe algum canal ativo
            if(isset($channelCampaign)) {
                $channel = $channelController->getChannel($channelCampaign->channel_id);
                //Log::debug('dados do canal para campanha');
                //Log::debug($channel);
                //Só verifica conexão do canal caso seja um canal NÃO OFICIAL (No canal oficial, a conexão é verificada em uma rotina a cada 5 minutos)
                if($channel->cha_api_official == false) {
                    $channelConnection = $communicatorController->checkConnectionSession($channel);
                    //Log::debug('status de conexão do canal');
                    //Log::debug($channelConnection);
                    //Se não foi possível trazer o status do canal ou o canal está desconectado
                    if(!isset($channelConnection) || $channelConnection['status'] == 'DISCONNECTED') {
                        //Coloca o canal na campanha como inativo
                        self::updateStatusChannelCampaign($channelCampaign->id, 'I');

                        //Desativa o canal
                        $requestStatusChannel = new Request([
                            'channelId' => $channel->id,
                            'statusId' => 'I', //Pausada
                        ]);
                        $channelController->updateStatusChannel($requestStatusChannel);
                    }
                }
            }
        
        } while((isset($channelConnection) && $channelConnection['status'] == 'DISCONNECTED') && isset($channelCampaign));
           
        return $channelCampaign;
    }

    //Traz o canal que será usado para enviar a mensagem de campanha 
    public function choiceChannelCampaign($campaignId)
    {
        $mailingController = new MailingController();
        $chosenChannel = null;

        $channelsCampaign = self::fetchChannelsCampaign($campaignId);

        $lastOperationMailing = $mailingController->getLastOperationMailingCampaign($campaignId);
        
        //Se a campanha só tem um canal ativo ou se a campanha tem ao menos um canal e não tem nenhuma operação realizada ainda 
        if(count($channelsCampaign) == 1 || (count($channelsCampaign) > 0 && !isset($lastOperationMailing))) {
            //Retorna o primeiro canal do array
            $chosenChannel = $channelsCampaign[0];
        } //Se tiver mais de um canal ativo e ao menos uma operação realizada
        else if(count($channelsCampaign) > 1 && isset($lastOperationMailing)) {
            //Pega o index do canal onde ocorreu a última operação 
            $keyLastChannel = $channelsCampaign->search(function($channelCampaign) use ($lastOperationMailing) {
                return $channelCampaign['channel_id'] == $lastOperationMailing->channel_id;
            });

            //Caso o último canal que realizou a operação ainda faça parte da lista de canais da campanha
            if(isset($keyLastChannel)) {
                //Caso seja o último canal do array, pega o primeiro canal da array
                if(count($channelsCampaign) == ($keyLastChannel+1)) {
                    $chosenChannel = $channelsCampaign[0];
                }
                else { //Pega o próximo canal do array (index do último canal que fez a operação +1)
                    $chosenChannel = $channelsCampaign[$keyLastChannel+1];
                }
            } //Caso o canal que realizou a última operação não faça mais parte da lista de canais da campanha
            else {
                //Retorna o primeiro canal do array
                $chosenChannel = $channelsCampaign[0];
            } 
        }

        return $chosenChannel;
    }

    //Retorna todos os canais ativos de uma campanha
    public function fetchChannelsCampaign($campaignId)
    {
        $channelsCampaign = ChannelCampaign::where('campaign_id', $campaignId)
                                            ->where('cha_status', 'A')
                                            ->orderBy('id', 'ASC')
                                            ->get();
        return $channelsCampaign;
    }

    public function updateStatusChannelCampaign($channelCampaignId, $statusId)
    {
        $channelCampaign = ChannelCampaign::find($channelCampaignId);
        $channelCampaign->cha_status = $statusId;
        $channelCampaign->save();
    }

    //Incrementa o total de operação de canal
    public function setIncrementOperationChannel($id)
    {
        $channelCampaign = ChannelCampaign::find($id);
        $channelCampaign->cha_total_operations = $channelCampaign->cha_total_operations + 1;
        $channelCampaign->save();
    }

    //Atualiza os canais que farão parte de uma campanha
    public function updateChannelCampaign(Request $request)
    {
        $channelsCampaign = ChannelCampaign::where('campaign_id', $request['id'])->get();
        //Para cada canal já cadastrado, se o mesmo não tiver feito nenhuma operação, remove o mesmo, caso contrário, apenas inativa o mesmo
        foreach($channelsCampaign as $channelCampaign) {
            //Se o canal não realizou nenhuma operação na campanha
            if($channelCampaign->cha_total_operations == 0) {
                ChannelCampaign::find($channelCampaign->id)->delete();
            } //Se tiver feito pelo menos uma operação na campanha
            else {
                $channelCampaignUpdate = ChannelCampaign::find($channelCampaign->id);
                $channelCampaignUpdate->cha_status = 'I';
                $channelCampaignUpdate->save();
            }
        }

        $channelsChatbotsCampaigns = self::fetchChannelsChatbotsCampaignsByChatbot(null, null, $request['id']);

        //Desativa a associação em um  canal e um chatbot dentro das campanhas que possuem esse chatbot 
        foreach($channelsChatbotsCampaigns  as $channelChatbotCampaign) {
            self::updateStatusChannelChatbotCampaign($channelChatbotCampaign->id, 'I');
        }

        //Para cada novo canal adicionado
        foreach($request['channels'] as $channel) {
            $newChannel = ChannelCampaign::where('campaign_id', $request['id'])
                                        ->where('channel_id', $channel['id'])
                                        ->first();
            
            //Se o canal se encontra inativo (o canal já estava adicionado anteriormente)
            if($newChannel) {
                $newChannel->cha_status = 'A';
                $newChannel->save();
            } //Adiciona o novo canal no banco de dados
            else {
                $newChannel = new ChannelCampaign();
                $newChannel->campaign_id = $request['id'];
                $newChannel->channel_id = $channel['id'];
                $newChannel->save();
            }

            foreach($channelsChatbotsCampaigns  as $channelChatbotCampaign) {
                //Se o canal se mantém associado a campanha, ativa a associação entre ele o chatbot na campanha
                if($channelChatbotCampaign->channel_id == $channel['id']) {
                    self::updateStatusChannelChatbotCampaign($channelChatbotCampaign->id, 'A');
                }
            }
        }

        $campaign = self::show($request['id']);

        return response()->json([
            'channels' => $request['channels'],
            'campaign' => $campaign
        ], 200);
           
    }

    //Atualiza a campanha com um determinado status (Em Andamento, Pausada, etc.)
    public function updateStatusCampaign(Request $request)
    {
        $campaign = Campaign::find($request['campaignId']);

        $campaign->status_id = $request['statusId'];
        $campaign->save();

        //Se o status da campanha foi atualizado para EM ANDAMENTO e se for uma campanha de Ligação via WhatsApp e ainda não foi criada a campanha na ZapLigue
        if($request['statusId'] == 2 && $campaign->campaign_type_id == 4 && !$campaign->campaign_id_api) {
            $communicatorController = new CommunicatorController();
            $costController = new CostController();
            //Cria a campanha na API
            $response = $communicatorController->addCampaignApi($campaign);
            //Se a campanha foi criada com sucesso na API
            if($response['success']) {
                $campaign->campaign_id_api = $response['campaignIdApi'];
                $campaign->status_id = $response['statusId'];
                $campaign->save();

                //Bloqueia o saldo necessário para rodar a campanha
                $costController->storeBlockedBalance($campaign->id);
            }
            else {
                $eventController = new EventController();
                $statusMessage = 'Não foi possível iniciar a campanha. Tente daqui a pouco';
                //Envia a mensagem de errro para o usuário
                $eventController->statusMessage($statusMessage, true, Auth::user()->id);

                $campaign->status_id = 4;
                $campaign->save();
            }
        }

        return response()->json([
            'statusId' => $campaign->status_id
        ], 200);
    }

    //Atualiza a frequência de operação de um canal
    public function updateOperatingFrequency(Request $request)
    {
        $settings = Setting::where('campaign_id',$request['id'])->first();

        $settings->operation_frequency_id = $request['settings'][0]['operating_frequency']['id'];
        $settings->number_shot_frequency_id = $request['settings'][0]['number_shot_frequency']['id'];
        $settings->save();

        return response()->json([
            'operatingFrequency' => $request['settings'][0]['operating_frequency'],
            'numberShotFrequency' => $request['settings'][0]['number_shot_frequency']
        ], 200);
    }

    //Atualiza os horários de operação de uma campanha
    public function updateOperatingHours(Request $request)
    {
        Log::debug('Dados horários');
        Log::debug($request);
        //Para cada dia de operação da campanha, atualiza os horários
        foreach($request['operating_hours'] as $operatingHour) {
            $campaignOperationHour = CampaignOperatingHour::find($operatingHour['id']);
            $campaignOperationHour->ope_hr_start = $operatingHour['ope_hr_start'];
            $campaignOperationHour->ope_hr_end = $operatingHour['ope_hr_end'];
            $campaignOperationHour->save();
        }
        $campaign = self::show($request['id']);
        return response()->json([
            'operatingHours' => $request['operating_hours'],
            'campaign' => $campaign
        ], 200);
    }

    //Atualiza O departamento para onde os contatos serão encaminhados em uma campanha
    public function updateForwarding(Request $request)
    {
        $settings = Setting::where('campaign_id',$request['id'])->first();

        $settings->department_id = $request['settings'][0]['department']['id'];
        $settings->fair_distribution_id = isset($request['settings'][0]['fair_distribution']['id'])? $request['settings'][0]['fair_distribution']['id'] : NULL;
        $settings->save();

        $campaign = self::show($request['id']);

        return response()->json([
            'department' => $request['settings'][0]['department'],
            'campaign' => $campaign
        ], 200);
    }

    //Traz os canais e verifica se eles lá possuem chatbot associado
    public function fetchChannelsChatbots(Request $request)
    {
        $chatbotController = new ChatbotController();
        $channelsCampaign = ChannelCampaign::join('man_channels', 'cam_channels_campaigns.channel_id', 'man_channels.id')
                                            ->where('campaign_id', $request['campaignId'])
                                            ->where('cam_channels_campaigns.cha_status', 'A')
                                            ->get();

        foreach($channelsCampaign as $key => $channel) {
            $channelInUse = null;
            $channelInUseChatbot = null;
            //Armaeza alguma informação sobre o canal
            $infoMessage = null;
            //Se o canal já está associado a um chatbot
            $channelInUse =  ChannelChatbot::where('campaign_id', $request['campaignId'])
                                            ->where('channel_id', $channel->id)
                                            ->where('cha_status', 'A')
                                            ->first();
            
            //Se o canal já está em uso por outro chatbot
            if($channelInUse) {
                $channelsCampaign[$key]->setAttribute('inUse', true);
                $chatbot = $chatbotController->getChatbot($channelInUse->chatbot_id);
                $infoMessage = "Canal está associado ao chatbot '$chatbot->cha_name'";
            }
            else {
                $infoMessage .= "Disponível";
            }
            
            $channelsCampaign[$key]->setAttribute('infoMessage', $infoMessage);
        }

        return response()->json([
            'channels' => $channelsCampaign
        ], 200);
    }


    //Traz os canais e seus chatbots associados dentro de uma campanha
    public function fetchChannelsChatbotsCampaign(Request $request)
    {
        $channelsChatbots =  ChannelChatbot::with('channel', 'chatbot')
                                            ->where('campaign_id', $request['campaignId'])
                                            ->where('cha_status', 'A')
                                            ->get();

        return response()->json([
            'channelsChatbots' => $channelsChatbots
        ], 200);
    }

    //Remove um chatbot associado a um canal dentro de uma campanha
    public function removeChannelChatbotCampaign($channelChatbotId)
    {
        $channelChatbot = ChannelChatbot::find($channelChatbotId);
        $channelChatbot->cha_status = 'I';
        $channelChatbot->save();

        return response()->json([
            ''
        ], 200);
    }

    //Traz os chatbots que podem ser asociados a um canal emuma campanha
    public function fetchChatbotsCampaign($channelId)
    {
        //Traz todos os id's chatbots em que um canal esteja associado
        $chatbotsId = ChatbotChannel::where('channel_id', $channelId)
                                            ->where('cha_status', 'A')
                                            ->pluck('chatbot_id')
                                            ->toArray();
        
        //Traz chatbots
        $chatbotsCampaign = Chatbot::whereIn('id', $chatbotsId)
                                    ->get();

        return response()->json([
            'chatbotsCampaign' => $chatbotsCampaign
        ], 200);
    }

    //Associa um chatbot a um canal dentro de uma campanha
    public function addChannelChatbotCampaign(Request $request)
    {
        Log::debug('addChannelChatbotCampaign $request');
        Log::debug($request);

        $channelChatbot = new ChannelChatbot();
        $channelChatbot->campaign_id = $request['campaignId'];
        $channelChatbot->channel_id = $request['channel']['id'];
        $channelChatbot->chatbot_id = $request['chatbot']['id'];
        $channelChatbot->save();

        return response()->json([
            ''
        ], 200);
    }

    //Traz os canais e seus chatbots associados dentro de uma campanha que possui um determinado chatbot associado
    public function fetchChannelsChatbotsCampaignsByChatbot($chatbotId, $channelId, $campaignId)
    {
        $channelsChatbotsCampaigns =  ChannelChatbot::where('cha_status', 'A');
        if($chatbotId) {
            $channelsChatbotsCampaigns = $channelsChatbotsCampaigns->where('chatbot_id', $chatbotId);
        }
        if($channelId) {
            $channelsChatbotsCampaigns = $channelsChatbotsCampaigns->where('channel_id', $channelId);
        }
        if($campaignId) {
            $channelsChatbotsCampaigns = $channelsChatbotsCampaigns->where('campaign_id', $campaignId);
        }
        $channelsChatbotsCampaigns = $channelsChatbotsCampaigns->get();

        return $channelsChatbotsCampaigns;
    }


    //Atualiza o status de um chatbot associado a um canal dentro de uma campanha
    public function updateStatusChannelChatbotCampaign($channelChatbotCampaignId, $statusId)
    {
        $channelChatbotCampaign = ChannelChatbot::find($channelChatbotCampaignId);
        $channelChatbotCampaign->cha_status = $statusId;
        $channelChatbotCampaign->save();
    }

    //Sorteia um canal de uma campanha aleatóriamente
    public function randomChannelCampaign($campaignId)
    {   
        $channelCampaign = ChannelCampaign::where('campaign_id', $campaignId)
                                        ->where('cha_status', 'A')
                                        ->get()
                                        ->random(1)//Traz uma mensagem aleatória
                                        ->values();

        return $channelCampaign;
    }

    //Retorna os tipos de campanhas (WhatsApp, SMS, etc.)
    public function fetchTypeCampaigns()
    {
        $typeCampaigns = CampaignType::where('cam_status', 'A')
                                    ->get();
        
        return response()->json([
            'typeCampaigns' => $typeCampaigns
        ], 200);
    }

    public function getFirstCampaignMessage($campaignId)
    {
        $campaignMessages = CampaignMessage::with('quickMessage.parameters')
                                                ->where('campaign_id', $campaignId)
                                                ->where('mes_status', 'A')
                                                ->first();
        
        return $campaignMessages;
    }

    //Traz os status de uma campanha em uma API
    public function checkStatusCampaignsApi()
    {   
        $communicatorController = new CommunicatorController();
        $mailingController = new MailingController();
        $costController = new CostController();
        $parameterController = new ParameterController();

        //Traz as campanhas com disparos de ligação que não tenha status de finalização
        $campaigns = Campaign::where('campaign_type_id', 4)
                            ->whereNotNull('campaign_id_api')
                            ->whereNotIn('status_id', [3, 5, 9])
                            ->get();
        
        //Para cada campanha
        foreach($campaigns AS $campaign) {
            //Traz os contatos do mailing que estão AGUARDANDO ENVIO
            $waitingSendContacts = $mailingController->getMailingCampaignByStatus($campaign->id, [1]);
            //Log::debug('$waitingSendContacts');
            //Log::debug($waitingSendContacts);
            //Se algum contato no mailing que ainda não processado
            if(count($waitingSendContacts)) {
                $mailingResponseJson = $communicatorController->getMailingCampaignApi($campaign);
                
                $mailingResponse = json_decode($mailingResponseJson, true);

                //Para contato que ainda não foi processado
                foreach($waitingSendContacts AS $contact) {
                    $contactResponse = null;
                    //Filtra a array de números pelo número de telefone específico
                    $contactResponse = array_filter($mailingResponse, function ($item) use($contact) {              
                        return $item['destino'] == $contact['contact']->con_phone;
                    });
                    
                    $contactResponse = array_values($contactResponse);
                    
                    //Se encontrou o contato
                    if($contactResponse) {
                        $mailingStatusId = $communicatorController->getMailingStatusId($contactResponse[0]['status']);
                        //Se o status da campanha for diferente do status atual
                        if($contact->status_id != $mailingStatusId) {
                            //Atualiza o status do mailing
                            $contact->status_id = $mailingStatusId;
                            $contact->save();

                            //Se a Ligação foi Realizada
                            if($mailingStatusId == 2) {
                                $chargeCampaign = $parameterController->getParameterByType(12);
                                //Se a cobrança para retorno via SMS estiver habilitada
                                if($chargeCampaign->par_value == '1') {
                                    //$concactMailing = $mailingController->getMailingCampaignContact($campaign->id, $contact->id);
                                    //Registra a cobrança pela Ligação via WhatsApp, no entanto, a deixa inativada
                                    $costData = new Request([
                                        'typeCostId'   => 4, //Custo de Ligação via WhatsApp
                                        'mailingId' => $contact->id, //Id do mailing
                                        'cosStatus' => 'I',
                                    ]);

                                    $costController->store($costData);
                                }
                            }
                        }
                    }       
                }
            }
            

            //Traz os dados da campanha que constam na API
            $response = $communicatorController->getCampaignApi($campaign);
            //Se a API retornou os dados de campanha
            if($response['success']) {
                //Se o status da campanha alterou
                if($campaign->status_id != $response['statusId']) {
                    //Atualiza o status da campanha
                    $campaign->status_id = $response['statusId'];

                    if($campaign->save()) {
                        //Se campanha foi FINALIZADA ou é uma campanha INVÁLIDA
                        if($response['statusId'] == 3 || $response['statusId'] == 9) {
                            //Desbloqueia o valor
                            $costController->updateBlockedBalanceStatus($campaign->id, 'I');
                            $costController->updateStatusAllCostsCampaign($campaign->id, 'A');

                        }
                    }
                }
            }
        }
    }

    //Traz as campanhas que possuem uma determinada distribuição igualitária
    public function getCampaignSettingByFairDistribution($fairDistributionId)
    {
        $campaigns = Campaign::join('cam_settings', 'cam_campaigns.id', 'cam_settings.campaign_id')
                            ->where('cam_settings.fair_distribution_id', $fairDistributionId)
                            ->where('cam_campaigns.status_id', '!=', 5) //Onde não é uma campanha removida
                            ->get();

        return $campaigns;
    }
}
