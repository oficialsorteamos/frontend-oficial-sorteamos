<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Api\CommunicatorController;
use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Chat\TemplateCampaign;
use App\Models\Chat\TemplateCategory;
use App\Models\Chat\TemplateLanguage;
use App\Models\Chat\TemplateMessage;
use App\Models\Chat\TemplateParameter;
use App\Models\Chat\TemplateStatus;
use App\Models\Chat\TemplateTypeButton;
use App\Models\Chat\TemplateTypeCallAction;
use App\Models\Chat\TemplateTypeHeader;
use App\Models\Chat\TemplateTypeVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Auth;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
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
    public function store(Request $templateDataPost)
    {
        try {
            $communicatorController = new CommunicatorController();
            $utilsController = new UtilsController();
            $onlyText = true;
            $messageData['text'] = '';
            $messageData['error'] = false;
            $request = json_decode($templateDataPost['messageData'], true);
            Log::debug('dados da mensagem template');
            Log::debug($request);

            //Se o usuário está editando um template que ocorreu falha no envio 
            if(isset($request['id'])) {
                //Remove o template com falha
                TemplateMessage::find($request['id'])->delete();
            }

            $newTemplateMessage = new TemplateMessage();
            $newTemplateMessage->tem_name = $request['tem_name'];
            $newTemplateMessage->tem_namespace = isset($request['namespace'])? $request['namespace'] : null;
            $newTemplateMessage->category_id = $request['category']['id'];
            $newTemplateMessage->language_id = $request['language']['id'];
            $newTemplateMessage->tem_body = $request['body'];
            $newTemplateMessage->tem_footer = isset($request['footer'])? $request['footer'] : null;
            $newTemplateMessage->tem_header = isset($request['header'])? $request['header'] : null;
            $newTemplateMessage->status_id = 1; //Enviado
            $newTemplateMessage->user_id = Auth::user()->id;
            $newTemplateMessage->channel_id = $request['channel']['id'];

            //Caso o template tenha sido salvo
            if($newTemplateMessage->save()) {
                //Caso tenha sido feito algum upload de mídia para o cabeçalho
                if($templateDataPost->file()) {
                    //Marca o conteúdo do template como NÃO sendo somente texto
                    $onlyText = false;
                    $dateTimeNow = Carbon::now();
                    $extensionContent = '.'.$templateDataPost->file->extension();
                    //Deixa apenas os números no nome do arquivo
                    $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).$extensionContent;
                    //$templateDataPost->file->storeAs('public/templates/template'.$newTemplateMessage->id.'/header/', $contentName);
                    Storage::disk('spaces')->putFileAs('public/templates/template'.$newTemplateMessage->id.'/header/', $templateDataPost->file, $contentName, 'public');
                    //Pega o tipo de mídia
                    $request['mediaType'] = $templateDataPost->file->getMimeType();
                    $request['mediaName'] = $contentName;
                    $request['templateId'] = $newTemplateMessage->id;

                    $dataType = explode("/", $templateDataPost->file->getMimeType());
                    //Se for um documento
                    if($dataType[0] == 'application') {
                        $typeVariableId = 5;
                    } //Se for uma imagem
                    else if($dataType[0] == 'image') {
                        $typeVariableId = 3;
                    } //Se for um vídeo
                    else {
                        $typeVariableId = 4;
                    }

                    $parameter = new TemplateParameter();
                    $parameter->template_id = $newTemplateMessage['id'];
                    $parameter->type_parameter_id = $typeVariableId;
                    $parameter->location_parameter_id = 1; //Header
                    $parameter->tem_media_name = $contentName; //Nome da mídia

                    $parameter->save();

                    /*$urlMedia = "https://" . $_SERVER['SERVER_NAME'];

                    //Se o template estiver sendo criado localmente
                    if($urlMedia == 'https://127.0.0.1') {
                        $urlMedia = env('NGROK_URL').'/storage/templates/template'.$newTemplateMessage->id.'/header/'.$contentName;
                    }
                    else {
                        $urlMedia .= '/storage/templates/template'.$newTemplateMessage->id.'/header/'.$contentName;
                    }*/
                    $urlMedia = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC').'public/templates/template'.$newTemplateMessage->id.'/header/'.$contentName;
                    //Cria a URL que será passada para a 360 Dialog
                    $request['mediaUrl'] = $urlMedia;
                    //Tipo de mídia upada
                    $request['typeMediaId'] = $typeVariableId;
                }

                //Para cada variável do template
                foreach($request['parameters'] as $key => $variable) {
                    $parameter = new TemplateParameter();
                    $parameter->template_id = $newTemplateMessage['id'];
                    $parameter->type_parameter_id = 1; //Variável
                    $parameter->type_variable_id = $variable['id'];
                    $parameter->location_parameter_id = 2; //Body
                    $parameter->tem_variable_tag = $request['variablesTags'][$key]; //Body

                    $parameter->save();
                }

                //Se algum botão foi adicionado
                if($request['typeButton'] != '') {
                    //Marca o conteúdo do template como NÃO sendo somente texto
                    $onlyText = false;
                    //Se o botão adicionado foi de RESPOSTA RÁPIDA
                    if($request['typeButton']['id'] == 1) {
                        foreach($request['buttonLabel'] as $key => $button) {
                            $parameter = new TemplateParameter();
                            $parameter->template_id = $newTemplateMessage['id'];
                            $parameter->type_parameter_id = 2; //Botão
                            $parameter->type_button_id = 1; //Botão de Resposta Rápida
                            $parameter->tem_content = $button; //Conteúdo (label) do botão
                            $parameter->location_parameter_id = 2; //Body

                            $parameter->save();
                        }
                    }
                    //Se o botão adicionado foi de CHAMADA PARA AÇÃO
                    else if($request['typeButton']['id'] == 2) {
                        foreach($request['callActions'] as $key => $callAction) {
                            $parameter = new TemplateParameter();
                            $parameter->template_id = $newTemplateMessage['id'];
                            $parameter->type_parameter_id = 2; //Botão
                            $parameter->type_button_id = 2; //Botão de Chamada para Ação
                            $parameter->tem_content = $request['buttonLabel'][$key]; //Conteúdo (label) do botão
                            $parameter->location_parameter_id = 2; //Body
                            //Caso a ação seja uma URL
                            if($callAction['id'] == 1) {
                                $parameter->tem_url = $request['buttonUrl'];
                            } //Caso a ação seja um NÚMERO DE TELEFONE
                            else {
                                $parameter->tem_phone_number = $request['phoneNumber'];
                            }

                            $parameter->save();
                        }
                    }
                }
                
                //Se o conteúdo contem cabeçalho ou rodapé
                if($request['footer'] || $request['header']) {
                    //Marca o conteúdo como NÃO contendo apenas texto simples (body)
                    $onlyText = false;
                }
            }
            //Se o conteúdo NÃO contiver apenas texto simples
            if(!$onlyText) {
                //Caso tenha mais de uma quebra de linha, limita a uma quebra de linha entre parágrafos
                $request['body'] = $utilsController->limitOneBreakLine($request['body']);
            }

            $request['body'] = $utilsController->changeParagraphContent($request['body']);
            $request['body'] = $utilsController->convertTextWhatsappFormat($request['body']);
            Log::debug('body after formatted');
            Log::debug($request['body']);
            //Cria o template na 360 Dialog (Facebook)
            $response = $communicatorController->createTemplate($request);

            Log::debug('resposta sobre a criação do template');
            Log::debug($response);

            //Se o template foi criado com sucesso
            if($response['status'] == 'success') {
                $updateTemplateMessage = TemplateMessage::find($newTemplateMessage['id']);

                if(isset($response['id'])) {
                    //Atualiza o ID do template
                    $updateTemplateMessage->template_id_api = $response['id'];
                } //Atualiza o namespace do template (360 Dialog) 
                else if(isset($response['namespace'])) {
                    $updateTemplateMessage->tem_namespace = $response['namespace'];
                }
                
                $updateTemplateMessage->save();
                $messageData['text'] = $response['message'];
            } //Se houve algum erro ao adicionar o template
            else if($response['status'] == 'error') { 
                $messageData['text'] = $response['message']; 
                $messageData['error'] = true;
                
                //Atualiza o status do template para ERRO AO ENVIAR
                self::updateStatusTemplateMessage($newTemplateMessage->id, null, 7);
            }
            return response()->json([
                'message' => $messageData['text'],
                'error' => $messageData['error'],
            ], 200);
            
        }catch(Exception $e) {
            Log::debug('Erro ao enviar o template');
            Log::debug($e);
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

    //Traz os tipos de variáveis que um template pode possuir
    public function fetchTypeVariables($status)
    {
        $typeVariables = TemplateTypeVariable::where('tem_status', $status)->get();

        return response()->json([
            'typeVariables' => $typeVariables,
        ], 200);
    }
    
    //Traz os tipos de botões que um template pode possuir
    public function fetchTypeButtons($status)
    {
        $typeButtons = TemplateTypeButton::where('tem_status', $status)->get();

        return response()->json([
            'typeButtons' => $typeButtons,
        ], 200);
    }

    //Traz os tipos de botões que um template pode possuir
    public function fetchTypeCallActions($status)
    {
        Log::debug('chamou fetchTypeCallActions');
        $typeCallActions = TemplateTypeCallAction::where('tem_status', $status)->get();

        return response()->json([
            'typeCallActions' => $typeCallActions,
        ], 200);
    }

    //Traz os tipos de cabeçalhos que um template pode possuir
    public function fetchTypeHeaders($status)
    {
        $typeHeaders = TemplateTypeHeader::where('tem_status', $status)->get();

        return response()->json([
            'typeHeaders' => $typeHeaders,
        ], 200);
    }

    //Traz as categorias que um template pode possuir
    public function fetchCategories()
    {
        $categories = TemplateCategory::where('tem_status', 'A')->get();

        return response()->json([
            'categories' => $categories,
        ], 200);
    }

    //Traz todas as liguagens (idiomas) que um template pode ter
    public function fetchLanguages($status)
    {
        $languages = TemplateLanguage::where('tem_status', $status)->get();

        return response()->json([
            'languages' => $languages,
        ], 200);
    }

    //Traz todos os status exceto o DELETADO
    public function fetchStatusTemplate()
    {   
        $statusTemplate = TemplateStatus::where('id', '!=', 8)
                                        ->where('tem_status', 'A')
                                        ->get();

        return response()->json([
            'statusTemplate' => $statusTemplate,
        ], 200);
    }

    //Atualiza o status de um template
    public function updateStatusTemplateMessage($templateId, $templateApiId, $statusId)
    {
        if($templateApiId) {
            $templateMessage = TemplateMessage::where('template_id_api', $templateApiId)->first();
        }
        else {
            $templateMessage = TemplateMessage::find($templateId);    
        }
        $templateMessage->status_id = $statusId;
        $templateMessage->save();
    }


    //Lista as mensagens templates cadastradas no Facebook e atualiza o status de cada uma
    public function listAndUpdateStatusTemplateFacebook()
    {
        Log::debug('chamou a função listAndUpdateStatusTemplateFacebook');
        $channelController = new ChannelController();
        $communicatorController = new CommunicatorController();
        //Traz os canais da 360Dialog
        $OfficialsChannels = $channelController->getChannelByApi(3);
        //Para cada canal oficial
        foreach($OfficialsChannels as $channel) {
            //Traz os templates associados ao canal
            $templates = $communicatorController->listTemplates($channel);
            //Se existem templates cadastrados
            if(isset($templates['waba_templates'])) {
                //Para cada template
                foreach($templates['waba_templates'] as $templateCreated) {
                    $templateMessage = null;
                    //Filtra pelo template
                    $templateMessage = TemplateMessage::where('tem_namespace', $templateCreated['namespace'])
                                                        ->where('tem_name', $templateCreated['name'])
                                                        ->where('status_id', '!=', 8) //Onde não é um template deletado
                                                        ->first();
                    //Se o template existir no sistema
                    if(isset($templateMessage)) {
                        $templateMessage->status_id = self::getStatusIdTemplateCorrelation($templateCreated['status']);

                        $templateMessage->save();
                    }
                    else {
                        /*$templateData = null;
                        $category = self::getCategoryByTag($templateCreated['category']);
                        $language = self::getLanguageByCode($templateCreated['language']);
                        //Se encontrou a categoria e o idioma no sistema
                        if(isset($category) && isset($language)) {
                            $templateData = new Request([
                                'name'   => $templateCreated['name'],
                                'namespace'   => $templateCreated['namespace'],
                                'category' => $category,
                                'language' => $language,
                                'body' => $language,
                                'statusId' => ,
                            ]);
                        }
                        */
                    }
                }
            }
        }
        
    }

    //Traz a categoria pela TAG
    public function getCategoryByTag($tag)
    {
        $category = TemplateCategory::where('tem_tag', $tag)->first();

        return $category;
    }

    //Traz o idioma através do código
    public function getLanguageByCode($code)
    {
        $language = TemplateLanguage::where('tem_code', $code)->first();

        return $language;
    }

    //Pega o id status que tem mesma correlação no sistema e no facebook
    public function getStatusIdTemplateCorrelation($status)
    {
        if(strtolower($status) == 'submitted') {
            return 1; //Enviada
        }
        else if(strtolower($status) == 'approved') {
            return 2; // Aprovada
        }
        else if(strtolower($status) == 'rejected') {
            return 3; //Reprovada
        }
        else if(strtolower($status) == 'pending') {
            return 4; //Pendente
        }
        else if(strtolower($status) == 'flagged') {
            return 5; //Sinalizada
        }
        else if(strtolower($status) == 'disabled') {
            return 6; //Desativada
        }
        else if(strtolower($status) == 'pending_deletion' || strtolower($status) == 'deleted') {
            return 8; //Remove o template
        }
    }

    //Traz as mensagens de um determinado chat de contato
    public function fetchTemplates(Request $request)
    {   
        try {
            Log::debug('dados fetchTemplate');
            Log::debug($request); 

            //Se o botão de atualizar templates foi clicado, chama a função que atualiza os templates da 360Dialog
            if($request['updateTemplate'] == 'true') {
                $channelController = new ChannelController();
                //Verifica se existem algum canal da 360Dialog ativo
                $channels = $channelController->getChannelByApi(3);
                if(count($channels) > 0) {
                    //Atualiza os templates
                    self::listAndUpdateStatusTemplateFacebook();
                }
            }

            //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
            $skip = (($request['page']-1) * $request['perPage']);
            

            $templates = TemplateMessage::with('parameters', 'status', 'category', 'language', 'campaigns', 'chatbots', 'channel')
                                        ->select('cha_templates_messages.id', 'cha_templates_messages.tem_name as tem_name', 'cha_templates_messages.tem_body as body', 'cha_templates_messages_categories.tem_name as category_name', 
                                        'cha_templates_messages_languages.tem_name as language_name', 'cha_templates_messages.status_id', 'cha_templates_messages.tem_namespace', 'cha_templates_messages_languages.tem_code',
                                        'cha_templates_messages.category_id', 'cha_templates_messages.language_id', 'cha_templates_messages.channel_id', 'cha_templates_messages.tem_header as header', 'cha_templates_messages.tem_footer as footer', 'template_id_api')
                                        ->join('cha_templates_messages_categories', 'cha_templates_messages.category_id', 'cha_templates_messages_categories.id')
                                        ->join('cha_templates_messages_languages', 'cha_templates_messages.language_id', 'cha_templates_messages_languages.id')
                                        //->join('cha_templates_messages_type_status', 'cha_templates_messages.status_id', 'cha_templates_messages_type_status.id')
                                        //->leftJoin('cha_templates_messages_parameters', 'cha_templates_messages.id', 'cha_templates_messages_parameters.template_id')
                                        //->leftJoin('cha_templates_messages_type_variables', 'cha_templates_messages_parameters.type_variable_id', 'cha_templates_messages_type_variables.id')
                                        ->where('cha_templates_messages.status_id', '!=', 8); //Onde o template não foi deletado
            if(isset($request['channelId']) && $request['channelId'] != '') {
                $templates = $templates->where('cha_templates_messages.channel_id', $request['channelId']);
            } //Se houver mais de um canal no chatbot
            if((isset($request['channels']) && $request['channels'] != null)) {
                if(is_array($request['channels'])) {
                    foreach($request['channels'] as $key => $channelJson) {
                        $channelArray = json_decode($channelJson, true);
                        $channelsId[$key] = $channelArray['id'];
                    }
                }
                else {
                    $channelArray = json_decode($request['channels'], true);
                    $channelsId[0] = $channelArray['id'];
                }      
                
                $templates = $templates->whereIn('cha_templates_messages.channel_id', $channelsId);
            }
            if($request['category'] != '') {
                $categoryData = json_decode($request['category'], true);
                //Filtra pela categoria
                $templates = $templates->where('cha_templates_messages_categories.id', $categoryData['id']);
            }
            if($request['status'] != '') {
                $statusData = json_decode($request['status'], true);
                //Filtra pelo status
                $templates = $templates->whereHas('status', function($q) use($statusData)
                {
                    $q->where('cha_templates_messages_type_status.id', $statusData['id']);	
                });
            }
            if(isset($request['campaignId'])) {
                //Traz os templates de uma campanha
                $templatesCampaign = self::getTemplatesCampaign($request['campaignId']);
                //Extra o id desses templates
                $templatesIdCampaign = $templatesCampaign->pluck('template_id')->toArray();
                //Filtra os templates, excluindo os templates que já fazem parte dessa campanha
                $templates = $templates->whereNotIn('cha_templates_messages.id', $templatesIdCampaign);
            }
            $total = $templates->count();
            $templates = $templates->orderBy('id', 'DESC')
                                    //Busca os contatos de acordo com a paginação
                                    ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                    ->take($request['perPage']) //Quantidade de itens trazidos
                                    ->get();

            //Log::debug('templates');
            //Log::debug($templates);

            return response()->json([
                'templates' => $templates,
                'totalTemplates' => $total
            ], 201);
        } catch(e) {

        } 
    }

    //Traz os templates de uma campanha
    public function getTemplatesCampaign($campaignId)
    {
        $templates = TemplateCampaign::where('campaign_id', $campaignId)
                                    ->where('tem_status', 'A')
                                    ->get();

        return $templates;
    }

    //Remove um template
    public function removeTemplate(Request $request)
    {
        $communicatorController = new CommunicatorController();
        $channelController = new ChannelController();
        $error = false;
        //Log::debug('removeTemplate');
        //Log::debug($request);
        
        $template = self::getTemplate($request['id']);

        //Traz o canal associado ao template
        $channel = $channelController->getChannel($template['channel_id']);
        //Remove o tempalte
        $response = $communicatorController->removeTemplate($channel, $request['templateName']);
        //Se o template foi removido com sucesso da 360Dialog
        //if(isset($response['success']) && $response['success'] == true) {
        if($response['status'] == 'success') {
            //Atualiza o status do template para DELETADO
            self::updateStatusTemplateMessage($request['id'], null, 8);
        } //Se houve algum erro ao remover o template no broker
        else if($response['status'] == 'error') {
            //Se o template não existe no broker
            //if($response['meta']['developer_message'] == 'Object not found') {
            if($response['message'] == 'Template Does not exists') {
                //Atualiza o status do template para DELETADO 
                self::updateStatusTemplateMessage($request['id'], null, 8);
            }
            else {
                $error = true;
            }
        }
        return response()->json([
            'error' => $error
        ], 201);
    }

    //Retorna o template de acordo com o seu id
    public function getTemplate($templateId)
    {
        $template = TemplateMessage::with('parameters.typeVariable', 'status', 'language')->find($templateId);
        
        return $template;
    }

    //Traz os templates associados a uma campanha
    public function fetchCampaignTemplates(Request $request)
    {
        $campaignController = new CampaignController();
        
        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($request['page']-1) * $request['perPage']);
            

        $templates = TemplateCampaign::with('template.parameters.typeVariable', 'template.status')
                                    ->select('cam_templates.id', 'cha_templates_messages.tem_name', 'cha_templates_messages.tem_body as body', 'cha_templates_messages_categories.tem_name as category', 
                                    'cha_templates_messages_languages.tem_name as language', 'cha_templates_messages.status_id', 'cha_templates_messages.tem_namespace', 'cha_templates_messages_languages.tem_code', 
                                    'cam_templates.template_id', 'cha_templates_messages.tem_header as header', 'cha_templates_messages.tem_footer as footer', 'template_id_api')
                                    ->join('cha_templates_messages', 'cam_templates.template_id', 'cha_templates_messages.id')
                                    ->join('cha_templates_messages_categories', 'cha_templates_messages.category_id', 'cha_templates_messages_categories.id')
                                    ->join('cha_templates_messages_languages', 'cha_templates_messages.language_id', 'cha_templates_messages_languages.id')
                                    //->join('cha_templates_messages_type_status', 'cha_templates_messages.status_id', 'cha_templates_messages_type_status.id')
                                    //->leftJoin('cha_templates_messages_parameters', 'cha_templates_messages.id', 'cha_templates_messages_parameters.template_id')
                                    //->leftJoin('cha_templates_messages_type_variables', 'cha_templates_messages_parameters.type_variable_id', 'cha_templates_messages_type_variables.id')
                                    ->where('cha_templates_messages.status_id', '!=', 8) //Onde o template não esteja deletedo na tabela de templates
                                    ->where('cam_templates.campaign_id', $request['campaignId']) //Filtra os tempplates pela campanha
                                    ->where('cam_templates.tem_status', 'A') //Es
                                    ->orderBy('id', 'DESC')
                                    //Busca os contatos de acordo com a paginação
                                    ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                    ->take($request['perPage']) //Quantidade de itens trazidos
                                    ->get();

        $campaign = $campaignController->show($request['campaignId']);

        return response()->json([
            'templates' => $templates,
            'totalTemplates' => count($templates),
            'campaign' => $campaign
        ], 201);

    }

    public function removeCampaignTemplate(Request $request)
    {
        //Deleta o template associado a campanha
        $templateCampaign = TemplateCampaign::find($request['id']);
        $templateCampaign->tem_status = 'I';
        $templateCampaign->save();

        return response()->json([
            ''
        ], 201);
    }

    //Adiciona um template em uma campanha
    public function addCampaignTemplate(Request $request)
    {
        Log::debug('addCampaignTemplate');
        Log::debug($request);
        $templateData = json_decode($request['templateData'], true);
        //Verifica se o template a ser adicionado já tinha sido adicionado anteriormente, porém, se encontra inativado
        $templateExist = TemplateCampaign::where('template_id', $templateData['id'])
                                        ->where('campaign_id', $request['campaignId'])
                                        ->first();
        if($templateExist) {
            $templateExist->tem_status = 'A';
            $templateExist->save();
        }
        else {
            $newCampaignTemplate = new TemplateCampaign();

            $newCampaignTemplate->campaign_id = $request['campaignId'];
            $newCampaignTemplate->template_id = $templateData['id'];
            $newCampaignTemplate->user_id = Auth::user()->id;
            $newCampaignTemplate->save();
        }

        return response()->json([
            ''
        ], 201);
    }

    public function checkTemplateNameExist($templateName)
    {
        $error = false;

        $templateExist =  self::getTemplateByName($templateName);
        //Caso o template já esteja cadastrado
        if($templateExist) {
            $error = true;
        }

        return response()->json([
            'error' => $error
        ], 201);
        
    }

    public function getTemplateByName($templateName)
    {   
        //Traz o template pelo nome, onde o mesmo não esteja deletado 
        $template = TemplateMessage::where('tem_name', $templateName)
                        ->where('status_id', '!=', 8)
                        ->first();
        
        return $template;
    }

    //Traz os parâmetros associados ao template
    public function fetchParametersTemplate($templateId)
    {
        $parameters = TemplateParameter::where('template_id', $templateId)
                                        ->get();
        
        return $parameters;
    }

    //Traz os parâmetros associados ao template com as colunas renomeadas
    public function getParametersTemplateRenamed($templateId)
    {
        $parameters = TemplateParameter::select('template_id', 'type_parameter_id', 'type_button_id', 'tem_url AS url', 'tem_phone_number AS phone_number',
                                                'tem_content AS content', 'tem_media_name AS media_name')
                                        ->where('template_id', $templateId)
                                        ->get();
        
        return $parameters;
    }

}
