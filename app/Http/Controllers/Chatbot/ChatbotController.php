<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\Chat\ActionController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Chatbot\Chatbot;
use App\Models\Chatbot\Bloc;
use App\Models\Chatbot\BlocAction;
use App\Models\Chatbot\TypeBlocAction;
use App\Models\Chatbot\ChatbotControl;

use App\Models\Management\UserDepartment;

use App\Models\Chat\Service;
use App\Models\Chat\ServiceEvaluation;
use App\Models\Chat\Action;

use Log;
use DB;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Chat\TemplateController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\DepartmentController;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\GeneralMessageController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Utils\UtilsController;
use App\Http\Controllers\Utils\WebPushController;
use App\Models\Campaign\ChannelChatbot;
use App\Models\Chatbot\ChatbotChannel;
use App\Models\Chatbot\TypeChatbot;
use App\Models\Integration\Dialer\FowardingSetting;
use App\Models\Management\Channel\Channel;

class ChatbotController extends Controller
{
    private $eventController;
    private $serviceController;

    public function __construct()
    {
        $this->eventController = new EventController();
        $this->serviceController = new ServiceController();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //Se o usuário não digitou nada no campo de pesquisa
        if($request['q'] == '') {
            //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
            $skip = (($request['page']-1) * $request['perPage']);
        }

        $chatbots = Chatbot::with('channels', 'typeChatbot')
                            ->where('cha_status', '!=','D');
        if($request['q'] != '') {
            //Verifica se a busca coincide com o nome de algum usuário
            $chatbots = $chatbots->where('cha_name', 'like', '%'.trim($request['q']).'%');
            //Verifica se busca coincide com o telefone de algum usuário
            $chatbots = $chatbots->orWhere('cha_description', 'like', '%'.trim($request['q']).'%');
        }
        if($request['typeChatbot'] != '') {
            $chatbots = $chatbots->where('type_chatbot_id', $request['typeChatbot']);
        }
        $total = $chatbots->count();
        $chatbots = $chatbots->orderBy('created_at', 'DESC')
                            ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                            ->take($request['perPage']) //Quantidade de itens trazidos
                            ->get();

        //Para cada chatbot
        foreach($chatbots as $chatbot) {
            //Traz os detalhes e pendências desse chatbot
            self::getDetailsChatbot($chatbot);
            self::checkListChatbot($chatbot);
        }
        
        return response()->json([
            'chatbots'=> $chatbots,
            'total'=> $total,
        ], 201);
    }

    public function getDetailsChatbot($chatbot)
    {   //Traz o total de blocos associados ao chatbot
        $totalBlocs = self::getTotalBlocsChatbot($chatbot->id);
        $chatbot->setAttribute('totalBlocs', $totalBlocs);

        //Traz o total de ações associadas ao chatbot
        $totalActions = self::getTotalActionsChatbot($chatbot->id);
        $chatbot->setAttribute('totalActions', $totalActions);
    }

    //Verifica se há alguma pendência relacionada a um chatbot
    public function checkListChatbot($chatbot)
    {   
        $pendenciesMessage = null;
        //Traz o bloco iniciação do chatbot
        $InitialBloc = self::getBlocByTypeBloc($chatbot->id, 2);
        //Traz o bloco de finalização do chatbot
        $finalBloc = self::getBlocByTypeBloc($chatbot->id, 3);
        //Traz o bloco de avaliação de atendimento do chatbot
        //$evaluationBloc = self::getBlocByTypeBloc($chatbot->id, 4);
        //Se o chatbot não tem um bloco inicial
        if(!$InitialBloc) {
            $pendenciesMessage.= "<li>Nenhum Bloco Inicial cadastrado para o chatbot;</li>";
        } //Se não há bloco de finalização para o chatbot
        if(!$finalBloc) {
            $pendenciesMessage.= "<li>Nenhum Bloco de Finalização cadastrado para o chatbot;</li>";
        }
        /*if(!$evaluationBloc) {
            $pendenciesMessage.= "<li>Nenhum Bloco de Avaliação de Atendimento cadastrado para o chatbot;</li>";
        }*/

        $chatbot->setAttribute('pendencies', $pendenciesMessage);
    }

    //Traz um bloco de um chatbot de acordo com o tipo de bloco
    public function getBlocByTypeBloc($chatbotId, $typeBlocId)
    {
        $bloc = Bloc::where('chatbot_id', $chatbotId)
                    ->where('type_bloc_id', $typeBlocId)
                    ->first();

        return $bloc; 
    }

    //Traz um bloco de acordo com o tipo independente do chatbot
    public function getBlocByType($typeBlocId)
    {
        $bloc = Bloc::where('type_bloc_id', $typeBlocId)
                    ->first();

        return $bloc; 
    }

    //Retorna um bloco pelo seu id
    public function getBloc($blocId)
    {
        $bloc = Bloc::find($blocId);

        return $bloc;
    }

    //Retorna o total de blocos associados a um chatbot
    public function getTotalBlocsChatbot($chatbotId)
    {
        $totalBlocs = Bloc::where('chatbot_id', $chatbotId)->count();

        return $totalBlocs;
    }

    //Traz a quantidade de ações associadas a um chatbot
    public function getTotalActionsChatbot($chatbotId)
    {
        $totalActions = BlocAction::join('cha_chatbot_blocs', 'cha_bloc_actions.main_bloc_id', 'cha_chatbot_blocs.id')
                                    ->where('chatbot_id', $chatbotId)
                                    ->count();
        return $totalActions;
    }

    //Atualiza o chatbot com um determinado status (Rodando ou Pausada)
    public function updateStatusChatbot(Request $request)
    {
        $chatbot = Chatbot::find($request['chatbotId']);

        $chatbot->cha_status = $request['statusId'];
        $chatbot->save();

        return response()->json([
            'statusId' => $request['statusId']
        ], 200);
    }

    //Armazena um novo chatbot
    public function store(Request $request)
    {
        try {
            //Salva um novo chatbot
            $chatbot = new Chatbot();
            $chatbot->type_chatbot_id = $request->chatbotData['type_chatbot']['id'];
            $chatbot->cha_name = $request->chatbotData['cha_name'];
            $chatbot->cha_description = $request->chatbotData['cha_description'];
            $chatbot->cha_only_official_channel = $request->chatbotData['cha_only_official_channel'];
            $chatbot->cha_status = 'I';
            $chatbot->save();
                
            return response()->json([
                
            ], 200);
        } catch(e) {

        }
    }

    //Atualiza um chatbot
    public function update(Request $request)
    {
        try {
            $chatbot = Chatbot::find($request['id']);
            $chatbot->type_chatbot_id = $request['type_chatbot']['id'];
            $chatbot->cha_name = $request['cha_name'];
            $chatbot->cha_description = $request['cha_description'];
            $chatbot->cha_only_official_channel = $request['cha_only_official_channel'];
            $chatbot->save();
            
            return response()->json(
                $request
            , 200);
            
        } catch (e) {

        }
    }

    //Retorna um chatbot específico
    public function show($id)
    {
        $chatbot = Chatbot::with('channels')->find($id);

        return response()->json(
            $chatbot
        , 200);
    }

    //Remove um chatbot
    public function destroy($id)
    {
        $campaignController = new CampaignController();

        $chatbot = Chatbot::find($id);

        //Coloca o status da campanha como DELETADO
        $chatbot->cha_status = 'D'; 
        $chatbot->save();

        //Traz os canais associados ao chatbot
        $channels = self::getChannelsByChatbot($chatbot->id);
        //Deleta os canais associados ao chatbot
        foreach($channels as $channel) {
            $channel->delete();     
        }

        //Traz os canais que possue o chatbot em específico dentro de  uma campanha
        $channelsChatbotsCampaigns = $campaignController->fetchChannelsChatbotsCampaignsByChatbot($id, null, null);

        //Desativa a associação em um  canal e um chatbot dentro das campanhas que possuem esse chatbot 
        foreach($channelsChatbotsCampaigns  as $channelChatbotCampaign) {
            $channelsChatbotsCampaigns = $campaignController->updateStatusChannelChatbotCampaign($channelChatbotCampaign->id, 'I');
        }

        return response()->json(
            ''
        , 200);

    }

    public function getChannelsByChatbot($chatbotId)
    {
        $channels = ChatbotChannel::where('chatbot_id', $chatbotId)
                                    ->get();

        return $channels;
    }

    //Traz os canais que podem ser adicionados em um chatbot
    public function fetchChannelsChatbot($chatbotId, $officialChannel)
    {
        $channelsChatbot = Channel::where('man_channels.cha_status', 'A')
                                    ->where('cha_api_official', $officialChannel)
                                    ->get();

        foreach($channelsChatbot as $key => $channel) {
            $channelInUse = null;
            $channelInUseChatbot = null;
            //Armaeza alguma informação sobre o canal
            $infoMessage = null;
            //Se o canal está associado a algum outro chatbot que não seja onde usuário deseja adicioná-lo
            $channelInUse =  ChatbotChannel::join('cha_chatbots', 'cha_chatbots_channels.chatbot_id', 'cha_chatbots.id')
                                            ->where('cha_chatbots.type_chatbot_id', 1) //Onde é um chatbot de atendimento
                                            ->where('channel_id', $channel->id)
                                            ->where('chatbot_id','!=', $chatbotId)
                                            ->where('cha_chatbots_channels.cha_status', 'A')
                                            ->first();
            
            //Se o canal já está em uso por outro chatbot
            if($channelInUse) {
                $channelsChatbot[$key]->setAttribute('inUse', true);
                $chatbot = self::getChatbot($channelInUse->chatbot_id);
                $infoMessage = "Restrição: O canal está associado ao chatbot '$chatbot->cha_name'";
            }
            else {
                $infoMessage .= "Disponível";
            }
            /*
            else {
                //Verifica se o canal está em uso pelo referido chatbot
                $channelInUseChatbot =  ChatbotChannel::where('channel_id', $channel->id)
                                                    ->where('chatbot_id', $chatbotId)
                                                    ->where('cha_status', 'A')
                                                    ->first();
            }
            
            
            //Se o canal está em uso por outro chatbot ou não está em uso por nenhum chatbot
            if($channelInUse || !$channelInUseChatbot) {
                //Verifica se existe algum autoatendimento associado ao canal
                $totalSelfServicesChannel = $this->serviceController->getCountSelfServiceChats($channel->id);
                //Se existe algum autoatendimento associado ao canal
                if($totalSelfServicesChannel > 0) {
                    $channelsChatbot[$key]->setAttribute('inUse', true);
                    if(!$infoMessage) {
                        $infoMessage .= "Restrições: ";
                    }
                    $infoMessage .= "Existem autoatendimentos associados ao canal.";
                }
            }
            */

            

            $channelsChatbot[$key]->setAttribute('infoMessage', $infoMessage);
        }
        Log::debug('$channelsChatbot');
        Log::debug($channelsChatbot);

        return response()->json([
            'channels' => $channelsChatbot
        ], 200);
    }

    //Retorna um chatbot específico
    public function getChatbot($chatbotId)
    {
        $chatbot = Chatbot::find($chatbotId);

        return $chatbot;
    }

    //Atualiza os canais associados a um chatbot
    public function updateChannelChatbot(Request $request)
    {
        //Log::debug('$request updateChannelChatbot');
        //Log::debug($request);

        $campaignController = new CampaignController();
        
        //Remove todos os canais associados ao chatbot
        ChatbotChannel::where('chatbot_id', $request['id'])->delete();

        //Traz os canais que possue o chatbot em específico dentro de  uma campanha
        $channelsChatbotsCampaigns = $campaignController->fetchChannelsChatbotsCampaignsByChatbot($request['id'], null, null);

        //Desativa a associação em um  canal e um chatbot dentro das campanhas que possuem esse chatbot 
        foreach($channelsChatbotsCampaigns  as $channelChatbotCampaign) {
            $campaignController->updateStatusChannelChatbotCampaign($channelChatbotCampaign->id, 'I');
        }

        //Se algum canal foi selecionado, associando o mesmo ao chatbot
        if($request['channels']) {
            //Se for um array de canais
            if(isset($request['channels'][0])) {
                //Para cada canal a ser associado ao chatbot
                foreach($request['channels'] as $channel) {
                    $chatbotChannel = new ChatbotChannel();
                    $chatbotChannel->chatbot_id = $request['id'];
                    $chatbotChannel->channel_id = $channel['id'];
                    $chatbotChannel->save();

                    foreach($channelsChatbotsCampaigns  as $channelChatbotCampaign) {
                        //Se o canal se mantém associado ao chatbot, ativa a associação entre ele o chatbot em uma campanha
                        if($channelChatbotCampaign->channel_id == $channel['id']) {
                            $campaignController->updateStatusChannelChatbotCampaign($channelChatbotCampaign->id, 'A');
                        }
                    }
                }
            }
            else {
                $chatbotChannel = new ChatbotChannel();
                $chatbotChannel->chatbot_id = $request['id'];
                $chatbotChannel->channel_id = $request['channels']['id'];
                $chatbotChannel->save();
            }
        }
    }

    //Retorna o chatbot associado a um canal
    public function getChatbotByChannel($channelId)
    {
        $chatbot = null;
        $chatbotChannel =  ChatbotChannel::join('cha_chatbots', 'cha_chatbots_channels.chatbot_id', 'cha_chatbots.id')
                                        ->where('channel_id', $channelId)
                                        ->where('cha_chatbots.type_chatbot_id', 1) //Onde o chatbot é de atendimento
                                        ->first();

        //Se tiver um chatbot associado ao canal
        if($chatbotChannel) {
            $chatbot = Chatbot::where('id', $chatbotChannel->chatbot_id)
                                ->where('cha_status', 'A')
                                ->first();
        }

        return $chatbot;
    }

    //Retorna o chatbot associado a um canal dentro de uma campanha
    public function getChatbotCampaign($campaignId, $channelId)
    {
        $chatbot = null;
        $channelChatbot =  ChannelChatbot::where('campaign_id', $campaignId)
                                        ->where('channel_id', $channelId)
                                        ->where('cha_status', 'A')
                                        ->first();

        //Se tiver um chatbot associado ao canal dentro da campanha
        if($channelChatbot) {
            $chatbot = Chatbot::where('id', $channelChatbot->chatbot_id)
                                ->where('cha_status', 'A')
                                ->first();
        }

        return $chatbot;
    }

    public function addBloc(Request $request)
    {
        $utils = new UtilsController();
        $bloc = json_encode($request->blocData);
        $bloc = json_decode($bloc, true);
        
        Log::debug($request);

        //Verifica se existe algum Bloco cadastrado
        $blocCount = Bloc::where('chatbot_id', $bloc['chatbotId'])
                        ->count();
        
        $typeBlocId = 1;
        //Caso não exista blocos cadastrados, coloca o presente bloco como o primeiro 
        if($blocCount == 0) {
            $typeBlocId = 2;
        } //Caso já exista algum bloco cadastrado
        else {
            //Se não for um bloco padrão
            if(isset($bloc['typeBlocId'])) {
                //Se o bloco a ser adicionado seja colocado como primeiro bloco da sequência
                if($bloc['typeBlocId'] == 2) {
                    //Atualiza todos os blocos para não serem o primeiro bloco
                    Bloc::where('chatbot_id', $bloc['chatbotId'])
                        ->where('type_bloc_id', 2)
                        ->update([
                            'type_bloc_id' => 1
                        ]);
                    
                    $typeBlocId = 2;
                } 
                else if($bloc['typeBlocId'] == 3) {
                    //Atualiza todos os blocos para não serem o final bloco
                    Bloc::where('chatbot_id', $bloc['chatbotId'])
                        ->where('type_bloc_id', 3)
                        ->update([
                            'type_bloc_id' => 1
                        ]);
                    
                    $typeBlocId = 3;
                }
                else if($bloc['typeBlocId'] == 4) {
                    //Atualiza todos os blocos para não serem o bloco de finalização
                    Bloc::where('chatbot_id', $bloc['chatbotId'])
                        ->where('type_bloc_id', 4)
                        ->update([
                            'type_bloc_id' => 1
                        ]);
                    
                    $typeBlocId = 4;
                }
            }
        }

        $bloc['body'] = $utils->changeParagraphContent($bloc['body']);

        $newBloc = new Bloc();

        $newBloc->chatbot_id = $bloc['chatbotId'];
        $newBloc->cha_title = $bloc['title'];
        $newBloc->cha_content = isset($bloc['template'][0]['id']) || isset($bloc['quickMessage'][0]['id'])? null : $bloc['body']; //Se for um template, coloca o conteúdo como nulo
        $newBloc->template_id = isset($bloc['template'][0]['id'])? $bloc['template'][0]['id'] : null;
        $newBloc->quick_message_id = isset($bloc['quickMessage'][0]['id'])? $bloc['quickMessage'][0]['id'] : null;
        $newBloc->cha_send_option_error_message = $bloc['cha_send_option_error_message'];
        $newBloc->type_bloc_id = $typeBlocId;
        $newBloc->save();
        
        $task['task'] = array(['id' => $newBloc->id, 'title' => $bloc['title'], 'content' => $bloc['body'], 'content' => $typeBlocId]);

        return response()->json(
            $task
        , 201);
    }

    //Traz todos os blocos associados a um determinado chatbot
    public function fetchBlocs(Request $request)
    {   
        Log::debug('fetchBlocs');
        Log::debug($request);
        $templateController = new TemplateController();
        
        $blocs = Bloc::with('typeBloc', 'actions', 'actions.typeAction', 'actions.department', 'actions.destinationBloc', 'actions.fairDistribution', 'quickMessage.parameters')
                        ->select('cha_chatbot_blocs.id', 'cha_title as title', 'type_bloc_id as typeBlocId', 'template_id', 'quick_message_id',
                                'cha_send_option_error_message', DB::raw("COALESCE(cha_chatbot_blocs.cha_content, cha_templates_messages.tem_body) as body"), 
                                'cha_templates_messages.tem_header as header', 'cha_templates_messages.tem_footer as footer')
                        ->leftJoin('cha_templates_messages', 'cha_chatbot_blocs.template_id', 'cha_templates_messages.id');
                        //Se o usuário pesquisou por algum bloco específico
                        if($request['q'] != '') {
                            $blocs = $blocs->where(function ($query) use($request) {
                                //Verifica se a busca coincide com o nome de algum usuário
                                $query->where('cha_title', 'like', '%'.trim($request['q']).'%')
                                        //Verifica se busca coincide com o telefone de algum usuário
                                        ->orWhere('cha_chatbot_blocs.cha_content', 'like', '%'.trim($request['q']).'%')
                                        ->orWhere('cha_templates_messages.tem_body', 'like', '%'.trim($request['q']).'%');
                            });
                        }
                        $blocs = $blocs->where('chatbot_id', $request['chatbotId'])
                                        ->get();
        
        foreach($blocs as $bloc) {
            //Se o bloco é um template
            if($bloc->template_id) {
                $templateParameters = $templateController->fetchParametersTemplate($bloc->template_id);
                $bloc->setAttribute('parameters', $templateParameters);
            }
        }

        
        $baseUrlStorage = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC');

        return response()->json([
            'blocs' => $blocs,
            'baseUrlStorage' => $baseUrlStorage,
        ], 201);

    }

    public function updateBloc(Request $request, $id)
    {
        Log::debug('$updateBloc');
        Log::debug($request);
        //$utils = new UtilsController();
        $bloc = json_encode($request->bloc);
        $bloc = json_decode($bloc, true);

        //$bloc['body'] = $utils->changeParagraphContent($bloc['body']);

        $typeBlocId = 1; 
        //Se o bloco a ser adicionado seja colocado como primeiro bloco da sequência
        if($bloc['typeBlocId'] == 2) {
            //Atualiza todos os blocos para não serem o primeiro bloco
            Bloc::where('chatbot_id', $request['chatbotId'])
                ->where('type_bloc_id', 2)
                ->update([
                    'type_bloc_id' => 1
                ]);
            
            $typeBlocId = 2;
        } 
        else if($bloc['typeBlocId'] == 3) {
            //Atualiza todos os blocos para não serem o final bloco
            Bloc::where('chatbot_id', $request['chatbotId'])
                ->where('type_bloc_id', 3)
                ->update([
                    'type_bloc_id' => 1
                ]);
            
            $typeBlocId = 3;
        }
        else if($bloc['typeBlocId'] == 4) {
            //Atualiza todos os blocos para não serem o final bloco
            Bloc::where('chatbot_id', $request['chatbotId'])
                ->where('type_bloc_id', 4)
                ->update([
                    'type_bloc_id' => 1
                ]);
            
            $typeBlocId = 4;
        }

        $blocUpdated = Bloc::find($id);

        $templateId = isset($bloc['template'][0]['id'])? $bloc['template'][0]['id'] : $bloc['template_id'];

        $blocUpdated->cha_title = $bloc['title'];
        $blocUpdated->cha_content = null;
        $blocUpdated->type_bloc_id = $typeBlocId;
        $blocUpdated->template_id = isset($bloc['template'][0]['id'])? $templateId : null; //Se for uma mensagem template
        $blocUpdated->quick_message_id = isset($bloc['quickMessage'][0]['id'])? $bloc['quickMessage'][0]['id'] : null; //Se for uma mensagem template
        $blocUpdated->cha_send_option_error_message = $bloc['cha_send_option_error_message'];
        $blocUpdated->save();

        return response()->json(
            $bloc
        , 200);
    }

    public function removeBloc($id)
    {
        $bloc = self::getBloc($id);
        //Se o bloco excluído é um bloco de FINALIZAÇÃO
        if($bloc->type_bloc_id == 3) {
            //Pausa o chatbot
            $chatbot = self::getChatbot($bloc->chatbot_id);
            $chatbot->cha_status = 'I';
            $chatbot->save();
        }
        //Deleta o bloco
        Bloc::find($id)->delete();

        return response()->json([], 200);
    }

    public function fetchTypeActions()
    {
        $typeActions = TypeBlocAction::where('typ_status', 'A')
                                    ->where('id', '!=', 5) //Onde o tipo não seja comunicação ativa
                                    ->get();
        
        return response()->json([
            'typeActions' => $typeActions 
        ], 200);
    }

    //Traz os blocos de um determinado chatbot
    public function fetchDestinationBlocs($chatbotId)
    {
        Log::debug('fetchDestinationBlocs');
        Log::debug($chatbotId);
        $blocs = Bloc::where('chatbot_id', $chatbotId)->get();

        return response()->json([
            'blocs' => $blocs
        ], 200);
    }

    public function addAction(Request $request)
    {   
        $blocAction = json_encode($request->action['action']);
        $blocAction = json_decode($blocAction, true);
        Log::debug('$blocAction');   
        Log::debug($blocAction);   
        $newBlocAction = new BlocAction();
        
        $newBlocAction->main_bloc_id = $blocAction['mainBlocId'];
        $newBlocAction->action_id = $blocAction['action']['id'];
        $newBlocAction->destination_bloc_id = isset($blocAction['bloc']['id'])? $blocAction['bloc']['id'] : null;
        $newBlocAction->department_id = isset($blocAction['department']['id'])? $blocAction['department']['id'] : null;
        $newBlocAction->fair_distribution_id = isset($blocAction['fair_distribution']['id'])? $blocAction['fair_distribution']['id'] : null;
        $newBlocAction->blo_free_key = isset($blocAction['blo_free_key'])? trim($blocAction['blo_free_key']) : null;

        //Se for chave livre
        if(isset($blocAction['blo_free_key']) && $blocAction['blo_free_key'] == 1) {
            $newBlocAction->blo_key = NULL;
        }
        else {
            $newBlocAction->blo_key = isset($blocAction['key'])? trim($blocAction['key']) : null;
        }

        $newBlocAction->save();
        
        //$task['task'] = array(['id' => $newBloc->id, 'title' => $bloc['title'], 'content' => $bloc['content']]);

        //Log::debug($task);

        return response()->json(
            ''
        , 201);
    }

    //Realiza a atualização das ações em um bloco
    public function updateAction(Request $request, $id)
    {
        Log::debug('$request updateAction');
        Log::debug($request);

        //Log::debug($bloc['content']);
        
        $ActionUpdated = BlocAction::find($id);

        $ActionUpdated->fair_distribution_id = NULL;
        $ActionUpdated->action_id = $request->action['action']['id'];
        //Se a ação for uma transferência
        if($request->action['action']['id'] == 1)
        {
            $ActionUpdated->department_id = $request->action['department']['id'];
        }
        //Se for uma chamada de bloco
        else if($request->action['action']['id'] == 2)
        {
            $ActionUpdated->destination_bloc_id = $request->action['bloc']['id'];
            $ActionUpdated->department_id = null;
            $ActionUpdated->blo_free_key = isset($request->action['blo_free_key'])? $request->action['blo_free_key'] : NULL;
        }
        //Se for uma ação de rastreamento
        else if($request->action['action']['id'] == 3)
        {
            $ActionUpdated->destination_bloc_id = null;
            $ActionUpdated->department_id = null;
        }
        //Se for uma transferência igualitária
        else if($request->action['action']['id'] == 7)
        {
            $ActionUpdated->fair_distribution_id = $request->action['fair_distribution']['id'];
            $ActionUpdated->department_id = null;
        }
        
        //Se for chave livre
        if(isset($request->action['blo_free_key']) && $request->action['blo_free_key'] == 1) {
            $ActionUpdated->blo_key = NULL;
        }
        else {
            $ActionUpdated->blo_key = isset($request->action['key'])? trim($request->action['key']): null;
        }
        
        $ActionUpdated->save();

        return response()->json(
            $ActionUpdated
        , 200);
    }

    public function removeAction($id)
    {
        //Deleta o bloco
        BlocAction::find($id)->delete();

        return response()->json([], 200);
    }

    //Salva um controle de chatbot
    public function storeChatbotControl($chatId, $blocId)
    {
        $chatbotControl = new ChatbotControl();
        $chatbotControl->chat_id = $chatId;
        $chatbotControl->bloc_id = $blocId;
        $chatbotControl->save();
    }

    //Função que realiza o atendimento automático do contato
    public function autoAttendant($chatId, $contactId, $actionKey, $newMessageData, $closeServiceOperator, $service, $serviceId, $sendEvaluation, $chatbotData)
    {
        //Objeto com funções comuns a várias classes
        $utils = new UtilsController();
        $channelController = new ChannelController();

        //Log::debug('service');
        //Log::debug($service);

        /*$chatbotActive = Chatbot::join('cha_chatbots_channels', 'cha_chatbots.id', 'cha_chatbots_channels.chatbot_id')
                                ->where('cha_chatbots_channels.channel_id', $service['channel_id']) //Onde o canal faz está associado a um chatbot
                                ->where('cha_chatbots.cha_status', 'A') //E o chatbot está ativo
                                ->first();
        */
        $action = null;
        $infoMessage = null;

        //Se o canal estiver associado a algum chatbot e o chatbot está ativo
        if($chatbotData) {
            //Caso o atendimento tenha sido fechado pelo operador
            if($closeServiceOperator) {
                //Bloco com a mensagem de finalização 
                //$bloc =Bloc::where('type_bloc_id', 3)->first();
                $bloc = self::getBlocByTypeBloc($chatbotData->id, 3);
                //Caso o bloco de avaliação do atendimento deva ser enviado ao contato 
                if($sendEvaluation == 'true') {
                    //Converte a mensage de finalização para ser apresentada no Whatsapp
                    $bloc->cha_content = $utils->convertTextWhatsappFormat($bloc->cha_content);
                    if($bloc) {
                        $bloc->cha_content = $utils->convertTextWhatsappFormat($bloc->cha_content);
                        //Dispara a mensagem de finalização do atendimento
                        self::dispatchMessageAutoAttendant($bloc, $contactId, null, $action, $chatId);
                    }

                    //Pega o bloco de avaliação do atendimento
                    //$blocEvaluation = Bloc::where('type_bloc_id', 4)->first();
                    $blocEvaluation = self::getBlocByTypeBloc($chatbotData->id, 4);
                    
                    //Se existir mensagem de avaliação do atendimento
                    if($blocEvaluation) {
                        $blocEvaluation->cha_content = $utils->convertTextWhatsappFormat($blocEvaluation->cha_content);
                        //Dispara a mensagem de avaliação
                        self::dispatchMessageAutoAttendant($blocEvaluation, $contactId, null, $action, $chatId);
                    }
                }
                else {
                    ChatbotControl::create([
                        'chat_id' => $chatId,
                        'bloc_id' => $bloc->id,
                    ]);
                    //Fecha o atendimento
                    $this->serviceController->updateStatusService($service->id, 3);
                } 
            }
            else {
                //Caso algum atendimento esteja sendo processado
                if($service) {
                    //Caso o atendimento esteja na fase de avaliação
                    if($service->type_status_service_id == 4) {
                        //Dispara a mensagem de avaliação
                        //self::dispatchMessageAutoAttendant($blocEvaluation, $contactId, null, $action, $chatId);
                        $contactRating = null;
                        //Caso o usuário tenha digitado uma nota com vírgula, troca a mesma por ponto
                        $contactRating = str_replace(",", ".", $newMessageData['mes_message']);
                        //Pega a nota do atendimento digitada pelo usuário
                        $contactRating = number_format($contactRating, 2);
                        //Log::debug($contactRating);
                        //Caso o usuário tenha digitado uma nota válida, salva a mesma no banco
                        if($contactRating) {
                            //Caso a nota atribuída seja maior que 10
                            if($contactRating > 10.0) {
                                $contactRating = 10.00;
                            }//Caso a nota atribuída seja menor que 10
                            else if($contactRating < 0.0) {
                                $contactRating = 0.00;
                            }
                            //Salva a nota no banco de dados
                            ServiceEvaluation::create([
                                'service_id' => $service->id,
                                'ser_rating' => $contactRating,
                            ]);

                            //Coloca o status do atendimento como FECHADO
                            Service::find($service->id)
                                    ->update([
                                        'type_status_service_id' => 3
                                    ]);
                            
                            $bloc = new Bloc();
                            $infoMessage = "Obrigado por avaliar o nosso atendimento!";
                            self::dispatchMessageAutoAttendant($bloc, $contactId, $infoMessage, $action, $chatId);
                        }
                    }
                }
                else {
                    //Verifica se existe algum bloco para ser processado. Se existir, pega o último bloco
                    $chatbotControl = ChatbotControl::where('chat_id', $chatId)
                                                    ->orderBy('created_at', 'DESC')
                                                    ->first();
                    //Log::debug('$chatbotControl');
                    //Log::debug($chatbotControl);
                    //Caso exista algum bloco de controle
                    if($chatbotControl) {
                        $bloc = self::getBloc($chatbotControl->bloc_id);
                        //Log::debug('$bloc para análise');
                        //Log::debug($bloc);
                        //Caso já exista algum atendimento automático em andamento (Se não for o último bloco ou o bloco de avaliação)
                        if($bloc && ($bloc->type_bloc_id != 3 && $bloc->type_bloc_id != 4)) {
                            //Traz a ação escolhida pelo contato
                            $chosenAction = BlocAction::where('main_bloc_id', $bloc->id);
                                                    //->whereRaw('LOWER(`blo_key`) LIKE ? ',[trim(strtolower($actionkey)).'%'])
                            $chosenAction = $chosenAction->where(function ($query) use($actionKey) {
                                //Verifica se a busca coincide com o nome de algum usuário
                                $query->where('blo_key', $actionKey)
                                        //Verifica se busca coincide com o telefone de algum usuário
                                        ->orWhere('blo_free_key', 1);
                            });                                                                                                        
                            $chosenAction = $chosenAction->first();

                            
                            //Log::debug('$chosenAction');
                            //Log::debug($chosenAction);
                            //Caso o contato tenha digitado alguma opção válida
                            if($chosenAction) {
                                $generalMessageController = new GeneralMessageController();
                                $contactController = new ContactController();
                                
                                //Caso a ação seja uma TRANSFERÊNCIA para um determinado setor
                                if($chosenAction->action_id == 1) {
                                    //Salva a ação de transferência 
                                    $action = new Action();
                                    $action->service_id = $serviceId;
                                    $action->chat_id = $chatId;
                                    $action->type_action_id = 1;
                                    $action->department_id = $chosenAction->department_id;
                                    $action->user_id = null;
                                    $action->save();
                                    
                                    $bloc = null;

                                    //Traz a mensagem de transferência para um SETOR
                                    $generalMessage = $generalMessageController->getgeneralMessageByType(1);

                                    //Se o envio da mensagem de transferência para um SETOR estiver habilitada
                                    if($generalMessage->gen_status == 'A') {
                                        $contact =  $contactController->getContactById($contactId);

                                        $bloc = new Bloc();
                                        //Traz o texto formatado
                                        $infoMessage = $generalMessageController->generalMessageContentFormatted($generalMessage->gen_content, $contact, 1, $chatId, $chosenAction->department_id);
                                    }
                                    

                                    //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
                                    $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                                                                    ->join('users', 'man_users_departments.user_id', 'users.id')
                                                                    ->where('department_id', $chosenAction->department_id)
                                                                    ->where('users.status', 'A') //Onde o status do usuário é ativo
                                                                    ->where('use_status', 'A')
                                                                    ->get();

                                    //Caso exista algum usuário no departamento
                                    if($usersSendEvent) {
                                        $pushController = new WebPushController();
                                        $userController = new UserController();
                                        //Envia uma notificação de transferência para o mesmo
                                        foreach ($usersSendEvent as $userSendEvent) {
                                            $this->eventController->sendMessageChat($newMessageData, $userSendEvent->user_id);
                                            
                                            $userWebPush = $userController->getUser($userSendEvent->user_id);
                                            //Se o usuário estiver logado
                                            if($userWebPush['statusLogin']) {
                                                //Envia um webpush para os usuários para onde o contato foi encaminhado
                                                $pushController->sendWebPushNewChat($userWebPush);
                                            }
                                        }
                                    }

                                    //Chama o evento que atualiza a tela com os atendimentos em progresso
                                    $this->serviceController->updateServiceProgressEvent();
                                    
                                    //Chama o evento que atualiza a situação do atendimento
                                    $this->serviceController->updateSituationServiceOperatorEvent($chatId);
                                } //Caso seja uma TRANSFERÊNCIA IGUALITÁRIA
                                else if($chosenAction->action_id == 7) {
                                    $bloc = null;
                                    //Verifica se a distribuição igualitária está configurada, se estiver, TRANSFERE de acordo  com a distribuição
                                    $farTransferResponse = self::fairTransfer($serviceId, $chatId, $newMessageData, $contactId, $chosenAction->fair_distribution_id);
                                    //Se NÃO houver DISTRIBUIÇÃO IGUALITÁRIA CONFIGURADA
                                    if(!$farTransferResponse) {
                                        //Transfere o atendimento para o DEPARTAMENTO PADRÃO
                                        self::transferDefaultDepartment($serviceId, $chatId, $contactId, $newMessageData);
                                    }
                                }
                                //Caso a ação seja de finalização do autoatendimento COM CHAVE
                                else if($chosenAction->action_id == 4) {
                                    //$bloc = Bloc::where('type_bloc_id', 3)->first();
                                    $bloc = self::getBlocByTypeBloc($chatbotData->id, 3);

                                    //Fecha o atendimento
                                    $this->serviceController->updateStatusService($serviceId, 3);

                                    //Pega o bloco de avaliação do atendimento
                                    //$blocEvaluation = Bloc::where('type_bloc_id', 4)->first();
                                    //$blocEvaluation = self::getBlocByTypeBloc($chatbotData->id, 4);
                                }
                                else {
                                    //Pega o bloco de destino, de acordo com a opção digitada pelo contato
                                    //$bloc = Bloc::find($chosenAction->destination_bloc_id);
                                    $bloc = self::getBloc($chosenAction->destination_bloc_id);
                                }
                            }
                            else {
                                //Se o envio de erro ao digitar uma inválida estiver ativado
                                if($bloc->cha_send_option_error_message) {
                                    $infoMessage = 'Digite uma opção válida.';
                                }
                                else {
                                    $bloc = null;
                                    $infoMessage = null;
                                }
                            }
                        } //Se NÃO tiver atendimento em andamento
                        else {
                            //Log::debug("chamou o primeiro bloco");
                            //Pega o primeiro bloco
                            //$bloc = Bloc::where('type_bloc_id', 2)->first();
                            $bloc = self::getBlocByTypeBloc($chatbotData->id, 2);

                            //Chama o evento que atualiza a tela com os atendimentos em progresso
                            $this->serviceController->updateServiceProgressEvent();

                            //Chama o evento que atualiza a situação do atendimento
                            $this->serviceController->updateSituationServiceOperatorEvent($chatId);
                        }
                        
                    } //Se não tiver atendimento em andamento, pega o PRIMEIRO BLOCO
                    else {
                        $bloc = self::getBlocByTypeBloc($chatbotData->id, 2);
                        
                        //Chama o evento que atualiza a tela com os atendimentos em progresso
                        $this->serviceController->updateServiceProgressEvent();

                        //Chama o evento que atualiza a situação do atendimento
                        $this->serviceController->updateSituationServiceOperatorEvent($chatId);
                    }

                    //Log::debug('bloc do momento');
                    //Log::debug($bloc);

                    //Caso exista um bloco
                    if($bloc || $infoMessage) {    
                        do {
                            $bloc->cha_content = $utils->convertTextWhatsappFormat($bloc->cha_content);
                            
                            //Dispara a mensagem de autoatendimento
                            self::dispatchMessageAutoAttendant($bloc, $contactId, $infoMessage, $action, $chatId);

                            $blocAction = null;
                            //Verifica se o bloco atual possui alguma chamada de bloco sequencial
                            $blocAction = BlocAction::where('main_bloc_id', $bloc->id)
                                                ->where('action_id', 6)
                                                ->orderBy('id', 'DESC')
                                                ->first();
                            
                            //Log::debug("blocAction sequencial");
                            //Log::debug($blocAction);
                            //Se possui uma chamada de bloco sequencial
                            if($blocAction) {
                                //Chama o próximo bloco
                                $bloc = self::getBloc($blocAction->destination_bloc_id);
                                sleep(rand(2, 5));
                            }
                            
                        } while ($blocAction);             

                        if($bloc) {
                            //Verifica se o bloco atual possui uma ação de FINALIZAÇÃO DE ATENDIMENTO SEM CHAVE
                            $FinishingWithoutKey = BlocAction::where('main_bloc_id', $bloc->id)
                                                            ->where('action_id', 4) // Ação de finalização
                                                            ->whereNull('blo_key') //Sem chave
                                                            ->orderBy('id', 'DESC')
                                                            ->first();
                            
                            //Se houver uma ação de finalização SEM CHAVE
                            if($FinishingWithoutKey) {
                                //Fecha o atendimento
                                $this->serviceController->updateStatusService($serviceId, 3);
                                $blocFinishing = self::getBlocByTypeBloc($chatbotData->id, 3);
                                self::storeChatbotControl($chatId, $blocFinishing->id);
                            }
                        }
                        

                        //Se existir mensagem de avaliação do atendimento
                        if(isset($blocEvaluation)) {
                            $blocEvaluation->cha_content = $utils->convertTextWhatsappFormat($blocEvaluation->cha_content);
                            //Dispara a mensagem de avaliação
                            self::dispatchMessageAutoAttendant($blocEvaluation, $contactId, $infoMessage, $action, $chatId);
                        }   
                    }
                }
            }
        } //Transfere o atendimento para o DEPARTAMENTO PADRÃO ou DISTRIBUIÇÃO IGUALITÁRIA
        else {
            Log::debug('ID DO ATENDIMENTO');
            Log::debug($serviceId);
            //Se o atendimento ainda está ABERTO
            if(isset($serviceId)) {
                $service = $this->serviceController->getServiceById($serviceId);
                $fairDistributionParameterChannel = $channelController->getParameterChannelByTypeFairDistribution($service->channel_id, 4);
                Log::debug('$fairDistributionParameterChannel');
                Log::debug($fairDistributionParameterChannel);
                //Se houver alguma configuração de transferência igualitária configurada para o canal
                if($fairDistributionParameterChannel) {
                    //Verifica se a distribuição igualitária está configurada, se estiver, TRANSFERE de acordo  com a distribuição
                    $farTransferResponse = self::fairTransfer($serviceId, $chatId, $newMessageData, $contactId, $fairDistributionParameterChannel->fair_distribution_id);
                }
                else {
                    //Transfere o atendimento para o DEPARTAMENTO PADRÃO
                    self::transferDefaultDepartment($serviceId, $chatId, $contactId, $newMessageData);
                }
            }
            else {
                //Atualiza o atendimento para fechado
                $this->serviceController->updateStatusService($service->id, 3);

                //$bloc = new Bloc();
                //$infoMessage = "Seu atendimento foi finalizado. Obrigado por entrar em contato!";
                //Dispara a mensagem de autoatendimento
                //self::dispatchMessageAutoAttendant($bloc, $contactId, $infoMessage, $action, $chatId);
            }
        }
    }

    //Transfere o atendimento para o DEPARTAMENTO PADRÃO
    public function transferDefaultDepartment($serviceId, $chatId, $contactId, $newMessageData)
    {
        $channelController = new ChannelController();
        $generalMessageController = new GeneralMessageController();
        $contactController = new ContactController();

        //Traz o atendimento pelo ID
        $service = $this->serviceController->getServiceById($serviceId);

        //Traz o canal responsável pelo atendimento
        $channel = $channelController->getChannel($service->channel_id);
        //Caso tenha sido definido algum DEPARTAMENTO PADRÃO
        if(isset($channel['parameters'][2]->department_id)) {
            
            if(!$newMessageData) {
                $newMessageData = '';
            }

            $bloc = new Bloc();
            $action = self::transferService($serviceId, $chatId, 1, $channel['parameters'][2]->department_id, $newMessageData);

            //Traz a mensagem de transferência para um SETOR
            $generalMessage = $generalMessageController->getgeneralMessageByType(1);

            //Se o envio da mensagem de transferência para um SETOR estiver habilitada
            if($generalMessage->gen_status == 'A') {
                $contact =  $contactController->getContactById($contactId);
                //Traz o texto formatado
                $infoMessage = $generalMessageController->generalMessageContentFormatted($generalMessage->gen_content, $contact, 1, $chatId, $channel['parameters'][2]->department_id);

                //Dispara a mensagem de autoatendimento
                self::dispatchMessageAutoAttendant($bloc, $contactId, $infoMessage, $action, $chatId);
            }
            
        }
    }

    //Transfere o atendimento de forma igualitária
    public function fairTransfer($serviceId, $chatId, $newMessageData, $contactId, $fairDistributionId)
    {
        $service = $this->serviceController->getServiceById($serviceId);

        $fairDistributionChannel = $this->serviceController->getFairDistributionChannel($fairDistributionId, $service->channel_id);
        $userChoosen = $this->serviceController->choiceFairDistributionUser($fairDistributionId);
        
        //Se o canal faz parte da distribuição igualitária de atendimentos e existem usuários cadastrados para o encaminhamento
        if($fairDistributionChannel && $userChoosen) {
            $departmentController = new DepartmentController();
            //Traz o departamento onde o usuário está lotado
            $userDepartment = $departmentController->fetchDepartmentsUser($userChoosen['user_id']);
            $action = self::transferService($serviceId, $chatId, 1, $userDepartment[0]['id'], $newMessageData, $userChoosen['user_id']);

            $userFairDistribution = $this->serviceController->getFairDistribution($userChoosen->user_fair_distribution_id);
            //Registra o encaminhamento do atendimento
            $userFairDistribution->fai_total_forwarding = $userFairDistribution->fai_total_forwarding + 1;
            $userFairDistribution->fai_dt_last_forwarding = now();
            $userFairDistribution->save();

            //Coloca a TAG de novo atendimento
            $service->ser_new_service = true;
            $service->save();

            $bloc = new Bloc();

            $generalMessageController = new GeneralMessageController();
            $contactController = new ContactController();

            //Traz a mensagem de transferência para um OPERADOR
            $generalMessage = $generalMessageController->getgeneralMessageByType(2);

            //Se o envio da mensagem de transferência para um OPERADOR estiver habilitada
            if($generalMessage->gen_status == 'A') {
                $contact =  $contactController->getContactById($contactId);
                //Traz o texto formatado
                $infoMessage = $generalMessageController->generalMessageContentFormatted($generalMessage->gen_content, $contact, $userChoosen['user_id'], $chatId, $userDepartment[0]['id']);

                //Dispara a mensagem de autoatendimento
                self::dispatchMessageAutoAttendant($bloc, $contactId, $infoMessage, $action, $chatId);
            }

            return true;
        } //Se a distribuição igualitária NÃO está configurada
        else {
            return false;
        }
    }

    //Transfere um atendimento
    public function transferService($serviceId, $chatId, $typeActionId, $departmentId, $newMessageData, $userId=null) 
    {
        $actionController = new ActionController();

        $request = new Request([
            'serviceId' => $serviceId,
            'chatId' => $chatId,
            'typeActionId' => $typeActionId, 
            'departmentId' => $departmentId, 
            'userId' => $userId,
        ]);

        $newAction = $actionController->store($request);

        $newActionData = json_encode($newAction);
        $newActionData = json_decode($newActionData, true);
        //Pega os dados do novo contato
        $newAction = $newActionData['original']['action'];
        
        //Log::debug('usuário que receberão o evento');
        //Log::debug($usersSendEvent);
        //Se o atendimento NÃO está sendo transferido para um departamento específico
        if(!$userId) {
            //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
            $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                                            ->join('users', 'man_users_departments.user_id', 'users.id')
                                            ->where('department_id', $departmentId)
                                            ->where('users.status', 'A') //Onde o status do usuário é ativo
                                            ->where('use_status', 'A')
                                            ->get();

            //Caso exista algum usuário no departamento
            if($usersSendEvent) {
                //Envia uma notificação de transferência para o mesmo
                foreach ($usersSendEvent as $userSendEvent) {
                    $this->eventController->sendMessageChat($newMessageData, $userSendEvent->user_id);
                }
            }
        } //Caso o atendimento esteja sendo transferido para um USUÁRIO ESPECÍFICO
        else {
            $this->eventController->sendMessageChat($newMessageData, $userId);
        }

        //Chama o evento que atualiza a tela com os atendimentos em progresso
        $this->serviceController->updateServiceProgressEvent();

        //Chama o evento que atualiza a situação do atendimento
        $this->serviceController->updateSituationServiceOperatorEvent($chatId);

        return $newAction;
    }

    public function dispatchMessageAutoAttendant($bloc, $contactId, $infoMessage, $action, $chatId)
    {
        //Log::debug("dispatchMessageAutoAttendant bloc");
        //Log::debug($bloc);

        $request = new Request([
            'contactId' => $contactId,
            'typeUserId' => 3, //Coloca o tipo do usuário como robô
            'message' => isset($infoMessage)? $infoMessage : $bloc->cha_content,
            'template_id' => isset($infoMessage)? null : $bloc->template_id,
            'quick_message_id' => isset($infoMessage)? null : $bloc->quick_message_id,
            'senderId' => 1, //Colocar aqui o ID do usuário que será o robô
            'actionId' => isset($action)? $action['id'] : null,
            'privateMessage' => 'false', //Coloca a mensagem como pública
        ]);
        //Log::debug('$request dispatch');
        //Log::debug($request);
        $chatController = new ChatController();
        //Caso a mensagem tenha sido enviada
        if($chatController->sendMessage($request)) {
            //Caso seja uma mensagem válida (o usuário tenha escolhido uma opção corretamente) 
            if($bloc->id) {
                ChatbotControl::create([
                    'chat_id' => $chatId,
                    'bloc_id' => $bloc->id,
                ]);
            }
        }
    }

    public function fetchTypeChatbots()
    {
        $typeChatbots = TypeChatbot::where('typ_status', 'A')
                                    ->get();
        
        return response()->json([
            'typeChatbots' => $typeChatbots
        ], 200);
    }

    //Traz os dados do chatbot associado a configuração de encaminhamento
    public function getChatbotFowardingSetting($fowardingSettingId)
    {
        $fowardingSetting = FowardingSetting::find($fowardingSettingId);
        
        $chatbot = Chatbot::where('id', $fowardingSetting->chatbot_id)
                                ->where('cha_status', 'A')
                                ->first();
        
        return $chatbot;
    }

    //Traz todos os chatbots que possuem uma determinada configuração igualitária
    public function getChatbotByFairDistribution($fairDistributionId)
    {
        $chatbots = Chatbot::select('cha_chatbots.id', 'cha_chatbots.cha_name')
                            ->join('cha_chatbot_blocs', 'cha_chatbots.id', 'cha_chatbot_blocs.chatbot_id')
                            ->join('cha_bloc_actions', 'cha_chatbot_blocs.id', 'cha_bloc_actions.main_bloc_id')
                            ->where('cha_bloc_actions.fair_distribution_id', $fairDistributionId)
                            ->where('cha_chatbots.cha_status', '!=', 'D') //Onde o chatbot não foi deletado
                            ->groupBy('cha_chatbots.id', 'cha_chatbots.cha_name')
                            ->get();

        return $chatbots;
    }
}
