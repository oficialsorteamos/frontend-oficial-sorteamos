<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Management\User;

use App\Models\Contact\Contact;
use App\Models\Chat\Chat;
use App\Models\Chat\Service;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\Action;
use App\Models\Chat\QuickMessage;

use App\Models\Management\UserDepartment;

use App\Models\System\DefaultColor;
use App\Models\System\DddState;

use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use SebastianBergmann\Environment\Console;
use FFMpeg;
use \stdClass;
use DB;

use App\Http\Controllers\Api\CommunicatorController;
use App\Http\Controllers\Api\Dialers\DialerInterfaceController;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Campaign\CampaignController;
use App\Http\Controllers\Campaign\MailingController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Management\ChannelController;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Financial\CostController;
use App\Http\Controllers\Management\CallController;
use App\Http\Controllers\Management\DepartmentController;
use App\Http\Controllers\Management\ExtensionController;
use App\Http\Controllers\System\AddressController;
use App\Http\Controllers\Utils\UtilsController;
use App\Http\Controllers\Utils\WebPushController;
use App\Models\Chat\ChatObservation;
use App\Models\Chat\QuickMessageParameter;
use App\Models\Chat\QuickMessageTypeParameter;
use App\Models\Chat\TypeFormatMessage;
use App\Models\Chatbot\ChatbotControl;
use App\Models\Management\Channel\Channel;
use App\Models\Management\Department;
use App\Models\Management\Extension\Extension;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\LogoffAction;
use PAMI\Message\Event\EventMessage;
use PAMI\Message\Action\DeviceStateListAction;

class ChatController extends Controller
{
    private $contactController;
    private $userController;
    private $eventController;
    private $serviceController;
    private $utilsController;
    private $communicatorController;

    public function __construct()
    {
        $this->contactController = new ContactController();
        //Utilizado para comunicação com as API's
        $this->communicatorController = new CommunicatorController();
        $this->userController = new UserController();
        $this->eventController = new EventController();
        $this->serviceController = new ServiceController;
        $this->utilsController = new UtilsController;
    }

    public function receiveMessage(Request $callback_api)
    {   
        //Nome da API de onde os dados estão vindo
        $apiName = '';
        Log::debug('mensagem recebida da WPPConnect');
        Log::debug($callback_api);
        //Log::debug($callback_api->type);
        //Caso seja mensagem da API OFICIAL ou a mensagem seja da API NÃO OFICIAL e não seja mensagem de um grupo
        if($callback_api->type == 'message' || ($callback_api->event == 'onmessage' && $callback_api->isGroupMsg == false)) {
            //Caso os dados recebidos sejam da API NÃO OFICIAL
            if($callback_api->event == 'onmessage') {
                $apiName = 'wppconnect';
                $payloadMessage = [];
                //Pega o id da mensagem
                $payloadMessage['id'] = $callback_api->id;
                //Caso seja mensagem de texto
                if($callback_api->type == 'chat') {
                    $payloadMessage['type'] = 'text';
                    $payloadMessage['payload']['text'] = $callback_api->content;
                } //Se o usuário enviou um contato
                else if($callback_api->type == 'vcard') {
                    $payloadMessage['type'] = 'vcard';
                    $payloadMessage['payload']['contactName'] = $callback_api->vcardFormattedName;
                    
                    //Extrai o telefone do contato compartilhado
                    $contentVcard = explode('waid=',$callback_api->content);
                    $contentVcard = explode(':',$contentVcard[1]);
                    $contactPhoneNumber = $contentVcard[0]; 
                    
                    $payloadMessage['payload']['contactPhoneNumber'] = $contactPhoneNumber;
                }
                else {
                    $payloadMessage['payload']['contentType'] = $callback_api->mimetype;
                    $payloadMessage['payload']['base64'] = $callback_api->body;
                    
                    if($callback_api->type == 'document') {
                        $payloadMessage['type'] = 'file';
                        $payloadMessage['payload']['caption'] = $callback_api->caption;
                    }
                    else if($callback_api->type == 'ptt') {
                        $payloadMessage['type'] = 'audio';
                    }
                    else if($callback_api->type == 'location') {
                        $payloadMessage['type'] = 'location';
                        $payloadMessage['payload']['latitude'] = $callback_api->lat;
                        $payloadMessage['payload']['longitude'] = $callback_api->lng;
                    }
                    else {
                        $payloadMessage['type'] = $callback_api->type;
                    }
                }

                //Pega o número de telefone de quem enviou a mensagem
                if(isset($callback_api->author)) {
                    $dividePhone = explode('@', $callback_api->author);
                }
                else {
                    $dividePhone = explode('@', $callback_api->from);
                }
                $payloadMessage['sender']['phone'] = $dividePhone[0];

                //Dados do contato
                $contactData = new Request([
                    'name'   => isset($callback_api->sender['name'])? $callback_api->sender['name'] : $callback_api->sender['pushname'],
                    'phoneNumber' => $payloadMessage['sender']['phone'],
                    'avatarUrl' => isset($callback_api->sender['profilePicThumbObj']['eurl'])? $callback_api->sender['profilePicThumbObj']['eurl'] : null,
                ]);
            }
            //Se for uma mensagem de um contato via API OFICIAL
            if($callback_api->type == 'message') {
                $apiName = 'gupshup';
                $payloadMessage = $callback_api->payload;
                $contactData = new Request([
                    'name'   => $payloadMessage['sender']['name'],
                    'phoneNumber' => $payloadMessage['sender']['phone'],
                ]);
            }

            //Busca o contato, se houver
            $contact = Contact::with('blocked')->where('con_phone', $payloadMessage['sender']['phone'])->first();

            //Se o contato NÃO existe
            if(!$contact) {
                //Salva o contato no banco de dados
                $contact = $this->contactController->store($contactData);
                $contactData = json_encode($contact);
                $contactData = json_decode($contactData, true);
                //Pega os dados do novo contato
                $contact = $contactData['original']['contact'];
                $chat = $contactData['original']['chat'];
            }
            else {
                //Verifica se já existe chat para este contato
                $chat = Chat::where('contact_id', $contact->id)->first();
            }
            
            //Se o contato NÃO estiver bloqueado
            if($contact['blocked'] == null ) {
                //Verifica se a mensagem já não foi gravada antes
                $checkMessage = ChatMessage::where('api_message_id', $payloadMessage['id'])->first();
                //Se a mensagem ainda não foi gravada
                if(!$checkMessage) {
                    $channelData['sessionName'] = $callback_api->session;
                    //Só grava a mensagem se o contato enviou algum tipo de mensagem suportada pela plataforma
                    if(isset($payloadMessage['type'])) {
                        self::storeMessage($chat['id'], 2, $contact['id'], $payloadMessage, null, $apiName, 'false', $channelData, null, null, null, null);
                        //Incrementa o número de mensagens não visualizadas em 1
                        self::incrementUnseenMessage($chat['id']);
                    }
                }
            }
        }
        //Se for um evento da mensagem (mensagem entregue, enviada, etc.)
        else if($callback_api->type == 'message-event' || $callback_api->event == 'onack') {
            //Se for um evento da API OFICIAL
            if($callback_api->type == 'message-event') {
                //Se a mensagem foi enfileirada
                if($callback_api->payload['type'] == 'enqueued') {
                    $statusMessageChatId = 1;
                    $apiMessageId = $callback_api->payload['id'];
                }
                else {
                    $apiMessageId = $callback_api->payload['gsId'];
                    //Se a mensagem foi enviada
                    if($callback_api->payload['type'] == 'sent') {
                        $statusMessageChatId = 2;
                    } //Se a mensagem foi entregue
                    else if($callback_api->payload['type'] == 'delivered') {
                        $statusMessageChatId = 3;
                    }
                }
            }
            //Se for um evento da API NÃO OFICIAL
            else {
                //Pega o id da mensagem
                $apiMessageId = $callback_api->id['_serialized'];
                //Se a mensagem foi ENVIADA
                if($callback_api->ack == 1) {
                    $statusMessageChatId = 2;
                }
                //Se a mensagem foi ENTREGUE
                else if($callback_api->ack == 2) {
                    $statusMessageChatId = 3;
                }
                //Se a mensagem foi ENTREGUE (As vezes, mensagens entregues vem com o id 3(pode ser que represente o status de mensagem LIDA))
                else if($callback_api->ack == 3) {
                    $statusMessageChatId = 3;
                }
            }

            //Pega a mensagem que deverá ter o status atualizado
            $messageChat = ChatMessage::where('api_message_id', $apiMessageId)->first();
            //Se a mensagem a ser atualizada existe
            if($messageChat)
            {   
                //Se a mensagem já não possui status de entregue
                if($messageChat->status_message_chat_id !=3) {
                    //Atualiza os status da mensagem (Enviado, entregue, etc.)
                    $messageChat->status_message_chat_id = $statusMessageChatId;
                    $messageChat->save();

                    //Atualiza o status da mensagem na tela do OPERADOR que enviou a mensagem
                    $this->eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $messageChat->sender_id);

                    //Traz todos os gestores
                    $managerUsers = $this->userController->getUsersByRoles([1, 3]);
                    //Para cada gestor, envia o status da mensagem
                    foreach($managerUsers as $manager) {
                        $this->eventController->updateStatusMessage($messageChat->id, $statusMessageChatId, $manager->id);
                    }
                }
            }
        }
        //Caso seja o qrCode para autenticação de sessão no Whatsapp
        else if(isset($callback_api->event) && $callback_api->event == 'qrcode') {
            //Caso a resposta da API tenha vindo com o nome da sessão (o nome contém o id do usuário)
            if($callback_api->session) {
                $channelController = new ChannelController();
                $channelLogin = $channelController->getChannelBySessionName($callback_api->session);
                //Envia o qrcode para ser exibido ao usuário
                $this->eventController->sendQrCode('data:image/jpeg;base64,'.$callback_api->qrcode, $channelLogin['user_id_connection']);
            }
            else {
                //Traz todos os gestores
                $managerUsers = $this->userController->getUsersByRoles([1, 3]);
                //Para cada gestor, envia o qrCode
                foreach($managerUsers as $userChannel) {
                    //Envia o qrcode para ser exibido ao usuário
                    $this->eventController->sendQrCode('data:image/jpeg;base64,'.$callback_api->qrcode, $userChannel->id);
                }
            }
             
        }
        else if(isset($callback_api->event) && $callback_api->event == 'status-find') {
            //Caso a resposta da API tenha vindo com o nome da sessão (o nome contém o id do usuário)
            if($callback_api->session) {
                $channelController = new ChannelController();
                $channelLogin = $channelController->getChannelBySessionName($callback_api->session);
                //Envia o status da conexão com o whatsapp para ser exibido no sistema
                $this->eventController->statusConnection($callback_api->status, $callback_api->session, $channelLogin['user_id_connection']);
            }
            else {
                //Traz todos os gestores
                $managerUsers = $this->userController->getUsersByRoles([1, 3]);
                //Para cada gestor, envia o qrCode
                foreach($managerUsers as $userChannel) {
                    //Envia o qrcode para ser exibido ao usuário
                    //$this->eventController->sendQrCode($callback_api->qrcode, $userChannel->id);
                    //Envia o status da conexão com o whatsapp para ser exibido no sistema
                    $this->eventController->statusConnection($callback_api->status, $callback_api->session, $userChannel->id);
                }
            }
        }
    }

    public function storeChat($contactId)
    {   //Cria um chat com o contato
        $chat = new Chat([
            'contact_id'  => $contactId,
            'cha_unseen_messages'  => 0,
        ]);
        $chat->save();

        return $chat;
    }

    //Incrementa a número de messagens não visualizadas em 1
    public function incrementUnseenMessage($chatId)
    {
        $chat = Chat::find($chatId);
        $chat->cha_unseen_messages++;
        $chat->save(); 
    }

    //Zera as mensagens não lidas
    public function readAllUnseenMessage($chatId)
    {
        $chat = Chat::find($chatId);
        $chat->cha_unseen_messages = 0;
        $chat->save();
    }


    //Traz as mensagens de um determinado chat de contato
    public function fetchMessagesChat(Request $request)
    {   
        try {
            $templateController = new TemplateController();
            //Quantidade de itens exibidos a cada click
            $amountPerClick = 15;
            //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados 
            $skip = ($request['offset'] * $amountPerClick);
            //Log::debug('número de mensagens puladas');
            //Log::debug($skip);
            
            /*
            $messages = ChatMessage::where('chat_id', $request['chatId'])
                                    ->orderBy('id', 'DESC')
                                    ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                    ->take($amountPerClick) //Quantidade de itens trazidos
                                    ->get();
            */
            
            $messages = Chat::select( 'cha_messages.id', 'cha_messages.chat_id', 'type_user_id', 'sender_id', 'action_id', 'cha_messages.service_id', 'cha_messages.campaign_id', 'template_id',
                                    'api_message_id', 'answered_message_id', 'mes_message', 'type_message_chat_id', 'mes_url', 'mes_caption', 'mes_media_original_name', 'mes_content_name', 'quick_message_id', 'type_origin_message_id',
                                    'mes_content_type', 'mes_contact_name', 'mes_contact_phone_number', 'mes_lat', 'mes_long', 'mes_phone_channel_received_message', 'mes_phone_channel_sent_message',
                                    'status_message_chat_id', 'mes_private', 'mes_waiting_message', 'cha_messages.created_at', 'cha_messages.updated_at', 'cha_services.ser_protocol_number',
                                    DB::raw("(SELECT cha_messages.id FROM cha_messages WHERE cha_messages.service_id = cha_services.id ORDER BY cha_messages.id ASC LIMIT 1) AS first_message_service"))
                            ->join('cha_messages', 'cha_chats.id', 'cha_messages.chat_id')
                            ->leftJoin('cha_services', 'cha_services.id', 'cha_messages.service_id')
                            ->where('cha_messages.chat_id', $request['chatId']);
            //Se for apenas as conversas de um determinado atendimento
            if($request['serviceId']) {
                $messages = $messages->where('cha_services.id', $request['serviceId']);
            };
            $messages = $messages->orderBy('cha_messages.id', 'DESC')
                                    ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                    ->take($amountPerClick) //Quantidade de itens trazidos
                                    //->groupBy('cha_messages.id', 'cha_services.ser_protocol_number', 'type_message_chat_id', 'cha_messages.chat_id')
                                    ->get();

            
            foreach($messages as $key => $message) {
                //Se é uma mensagem rápida
                if($message->quick_message_id) {
                    $parameters = self::getQuickMessageParameters($message->quick_message_id);
                    $messages[$key]->setAttribute('parameters', $parameters);
                } //Se é um template
                else if($message->template_id) {
                    $parameters = $templateController->getParametersTemplateRenamed($message->template_id);
                    $messages[$key]->setAttribute('parameters', $parameters);
                }

                //Se a mensagem é uma resposta outra mensagem
                if($message->answered_message_id) {
                    $answeredMessage = self::getMessageByApiId($message->answered_message_id);

                    //Se a mensagem respondida existir no sistema
                    if($answeredMessage) {
                        if($answeredMessage->quick_message_id) {
                            $parameters = self::getQuickMessageParameters($answeredMessage->quick_message_id);
                            $answeredMessage->setAttribute('parameters', $parameters);
                        } //Se é um template
                        else if($answeredMessage->template_id) {
                            $parameters = $templateController->getParametersTemplateRenamed($answeredMessage->template_id);
                            $answeredMessage->setAttribute('parameters', $parameters);
                        }
    
                        if($answeredMessage) {
                            $messages[$key]->setAttribute('answeredMessage', $answeredMessage);
                        }
                    }
                }
            }
            //Log::debug('messagesData');
            //Log::debug($messages);
            //Se for a primeira 'leva' de mensagens
            if($request['offset'] == 0) {
                //Inverte a ordem das mesmas
                $messages = $messages->reverse();
                $messages = $messages->values()->all();
            }

            return response()->json(
                $messages
            , 201);
        } catch(e) {

        }
        
    }

    //Traz a mensagem pelo id da API
    public function getMessageByApiId($apiMessageId)
    {
        $message = ChatMessage::where('api_message_id', $apiMessageId)
                                        ->first();
        
        return $message;
    }

    //Traz os parâmetros de uma mensagem rápida
    public function getQuickMessageParameters($quickMessageId)
    {
        $parameters = QuickMessageParameter::select('quick_message_id', 'quick_message_id', 'type_parameter_id', 'type_parameter_id', 'qui_content AS content', 'qui_url AS url',
                                                    'qui_phone_number AS phone_number', 'qui_media_name AS media_name', 'qui_media_original_name AS media_original_name')
                                            ->where('quick_message_id', $quickMessageId)
                                            ->where('qui_status', 'A')
                                            ->get();
        return $parameters;
    }

    //Retorna as mensagens que contém algum tipo de mídia (imagens, arquivos, etc.)
    public function getMediaChatMessage($chatId, $typeMedia)
    {
        $mediasChatMessage = ChatMessage::where('chat_id', $chatId)
                                        ->where('type_message_chat_id', $typeMedia)
                                        ->orderBy('created_at', 'DESC')
                                        ->get();
        return $mediasChatMessage;
    }

    //Busca os dados do chat ativo 
    public function getChat(Request $request)
    {  
        //Log::debug('request getChat');
        //Log::debug($request);
        $addressController = new AddressController();
        $serviceController = new ServiceController();
        
        $userLogged = auth()->user();
        //Busca os dados do Chat
        $chat = Chat::where('contact_id', $request['contactId'])->first();
        //Busca as mensagens relacionadas ao chat
        $requestChat = new Request([
            'chatId' => $chat->id,
            'serviceId' => isset($request['serviceId'])? $request['serviceId'] : null,
            'offset' => 0, //Conjunto de mensagens que serão carregadas
        ]);
        $messagesData = self::fetchMessagesChat($requestChat);
        $messagesData = json_encode($messagesData);
        $messagesData = json_decode($messagesData, true);
        $messages  = $messagesData['original'];
        //Se o usuário que abriu o chat NÃO é um GESTOR
        if($request['isManager'] == 'false') {
            //Coloca a quantidade de mensagens não visualizadas como 0
            $chat->cha_unseen_messages = 0;

            $service = $serviceController->getServiceByChatId($chat->id);
            //Remove a tag de atendimento NOVO
            if(isset($service->ser_new_service) && $service->ser_new_service) {
                $service->ser_new_service = NULL;
                $service->save();
            }
        }
        $chat->save();

        //Pega a situação do atendimento em relação ao operador
        $serviceData = $this->serviceController->situationServiceOperator($chat->id, Auth::user()->id);
        $chat->setAttribute('statusService', $serviceData['situationService']);

        //Se houver mensagens
        if($messages)
        {   //Adiciona o atributo loadingSpinner como falso. Utilizado no momento do reenvio de alguma mensagem cujo envio falhou
            foreach($messages as $key => $message) {
                $messages[$key]['loadingSpinner'] = false;
            }

            $chat->setAttribute('chat', $messages);

            $images = [];
            $files = [];
            //Filtra apenas a imagens 
            $images = self::getMediaChatMessage($chat->id, 3);
            $files = self::getMediaChatMessage($chat->id, 5); 

            $chat->setAttribute('img', $images);
            $chat->setAttribute('files', $files);

        }
        else {
            $chat->setAttribute('chat', []);
        }
        //Dados que são apresentados no sidebar direito sobre o contato
        $contact = Contact::with('tags', 'colorAvatar', 'gender', 'blocked')->where('id', $request['contactId'])->first();
        
        $contact->setAttribute('fullName', $contact->con_name);
        $contact->setAttribute('role', 'Testando');
        $contact->setAttribute('about', 'Sobre mim');

        $contact->setAttribute('avatarColor', isset($contact['colorAvatar']->def_name)? $contact['colorAvatar']->def_name : 'primary');

        $initialsName = self::getInitialsNameContact($contact);
        $contact->setAttribute('initialsName', $initialsName);

        //Pega a última mensagem trocada
        $lastMessage = self::getLastMessage($chat->id);
        $hourLastMessage = isset($lastMessage->created_at) ? $lastMessage->created_at : null;
        //Pega o status do contato
        $contactStatus = self::getStatusContact($hourLastMessage);

        $contact->setAttribute('status', $contactStatus);

        $addressesContact = $addressController->getAddressesUser($request['contactId'], 2);
        $contact->setAttribute('addresses', $addressesContact);

        $userData = $this->userController->show(Auth::user()->id);
        //Traz os gêneros cadastrados
        $userData = json_encode($userData);
        $userData = json_decode($userData, true);
        $profileUserData  = $userData['original'];

        if($contact->con_birthday) {
            $contact->con_birthday = date("d-m-Y", strtotime($contact->con_birthday));
        }
        
        return response()->json([
            'chat'=> $chat,
            'contact'=> $contact,
            'userLogged'=> $userLogged,
            'profileUser'=> $profileUserData,
        ], 201);
    }

    public function generateProtocolNumber()
    {
        $dateTimeNow = Carbon::now(); 
        $protocolNumber = preg_replace( '/[^0-9]/', '', $dateTimeNow );
        //Gera um número aletório de 4 dígitos
        $digits = 2;
        $randomNumber = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);  
        $protocolNumber .= $randomNumber;    

        return $protocolNumber;
    }

    public function startService($chatId, $channelId=null)
    {
        $channelController = new ChannelController();
        $service = null;   
        
        //Traz a ação de transferência em que o operador será colocado como o atendente
        $action = Action::where('chat_id', $chatId)
                        ->whereNull('user_id')
                        ->orderBy('created_at', 'desc')
                        ->first();
        //Se o atendimento foi transferido
        if($action) {
            //Coloca o operador que deu start no atendimento como o titular do atendimento
            $action->user_id = Auth::user()->id;
        
            //Caso a transferência tenha sido realizada
            if($action->save()) {
                //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
                $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                                                ->join('users', 'man_users_departments.user_id', 'users.id')
                                                ->where('department_id', $action->department_id)
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
                $this->serviceController->updateServiceProgressEvent();

                //Chama o evento que atualiza a situação do atendimento
                $this->serviceController->updateSituationServiceOperatorEvent($chatId);
            }
        } //Caso o atendimento tenha sido iniciado pelo operador e não pelo contato (comunicação ativa)
        else {
            //Verifica se já não existe um atendimento em aberto
            $hasServiceActive = $this->serviceController->getServices($chatId, 1);
            Log::debug('tem serviço ativo');
            Log::debug($hasServiceActive);
            //Se não tem serviço ativo, cria um novo serviço com ação como comunicação ativa
            if($hasServiceActive == '[]') {
                $service = new Service();

                //$channelController = new ChannelController();
                //Traz o canal ativo mais antigo
                //$channel = $channelController->getFirstChannel(); 

                $service->chat_id = $chatId;
                $service->channel_id = $channelId; //Modificar para trazer qualquer canal ativo que não seja usado para mensagem em massa
                $service->type_status_service_id = 1;
                $service->ser_protocol_number = self::generateProtocolNumber();

                $channel = $channelController->getChannel($channelId);

                $userDepartment = UserDepartment::where('user_id', Auth::user()->id)
                                                ->first();

                if($service->save()) {
                    $action = new ActionController();
                    $request = new Request([
                        'serviceId'   => $service->id,
                        'chatId' => $chatId,
                        'typeActionId' => 5, //Cria uma ação do tipo Comunicação Ativa
                        'departmentId' => $userDepartment->department_id,
                        'userId' => Auth::user()->id,
                    ]);

                    $action->store($request);
                }
            }
        }
        
        return response()->json([
            'service' => $service,
            'channel' => isset($channel)? $channel : null, //A variável channel só existe se for comunicação ativa
        ], 201);
    }


    //Fecha o atendimento em andamento
    public function closeService(Request $params)
    {    
        //Log::debug('Parâmetros close service');
        //Log::debug($params);
        $statusService = 3;

        //Pega o atendimento em andamento
        $currentService = self::getCurrentServiceChat($params['chatId']);

        $chatbotController = new ChatbotController();
        //Se não for um atendimento associado a uma campanha
        if(!$currentService['campaign_id']) {
            $chatbotData = $chatbotController->getChatbotByChannel($currentService->channel_id);
        } //Se for atendimento associado a uma campanha
        else {
            $chatbotData = $chatbotController->getChatbotCampaign($currentService->campaign_id, $currentService->channel_id);
        }

        //Se existir algum chatbot associado ao atendimento
        if($chatbotData) {
            $blocEvaluation = $chatbotController->getBlocByTypeBloc($chatbotData->id, 4);

            //Se existir bloco de avaliação
            if($blocEvaluation) {
                //Coloca o atendimento em status de AVALIAÇÃO
                $statusService = 4;
            }
            else {
                //Coloca o atendimento como fechado
                $statusService = 3;
            }
        }
        
        
        //Coloca o atendimento com o status de avaliação
        Service::find($currentService->id)
                ->update([
                    'type_status_service_id' => $statusService,
                    'ser_dt_end_service' => Carbon::now(),
                    'user_id_end_service' => Auth::user()->id
                ]);
        
        //Caso o atendimento esteja PENDENTE ( tenha sido transferido para um departamento e nenhum operador ainda assumiu o mesmo)
        Action::where('service_id', $currentService->id)
                ->where('chat_id', $params['chatId'])
                ->whereNull('user_id') //Nenhum operador assumiu o atendimento
                ->whereNotNull('department_id')
                ->where('type_action_id', 1) //Tipo de ação seja TRANSFERÊNCIA
                ->delete();
        
        $chat = Chat::find($params['chatId']); 
        
        $chatbotController->autoAttendant($params['chatId'], $chat->contact_id, null, null, true, $currentService, null, $params['sendEvaluation'], $chatbotData);

        //Chama o evento que atualiza a tela com os atendimentos em progresso
        $this->serviceController->updateServiceProgressEvent();

        //Chama o evento que atualiza a situação do atendimento
        $this->serviceController->updateSituationServiceOperatorEvent($params['chatId']);
        
        
        return response()->json([], 201);
    }

    public function getStatusContact($dateHourLastMessage)
    {
        //Pega a data e hora atual
        $dateTimeNow = Carbon::now();
        $diffSeconds = $dateTimeNow->diffInSeconds($dateHourLastMessage);
        //Se a última mensagem tem menos de 24 horas
        if($diffSeconds < 86400)
        {
            return 'online';
        }
        else
        {
            return 'away';
        }
    }

    public function setColorAvatar($contact, $avatarColorArray)
    {
        $valueRandom = array_rand($avatarColorArray, 1)+1;

        Contact::find($contact->id)
                ->update([
                    'color_avatar_id' => $valueRandom 
                ]);

        return $valueRandom;
    }

    public function getLastMessage($chatId)
    {
        $lastMessage = ChatMessage::where('chat_id', $chatId)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
        return $lastMessage;
    }

    public function chatsAndContactsDetails($contacts)
    {
        $service = new ServiceController();
        $avatarColorArray = DefaultColor::pluck('def_name')->toArray();
        foreach ($contacts as $key => $contact) 
        {
            $chat = Chat::where('contact_id', $contact->id)->first();

            //Caso exista alguma conversa entre o contato e algum operador
            if($chat)
            {
                //Pega o atendimento em aberto
                $serviceOpen = Service::where('chat_id', $chat->id)
                                        ->where('type_status_service_id', 1)
                                        ->first();
                //Se existir algum atendimento em aberto
                if($serviceOpen) {
                    $contacts[$key]->setAttribute('service', $serviceOpen);
                }
                $lastMessage = self::getLastMessage($chat->id);
                $chat->setAttribute('lastMessage', $lastMessage);
                $hourLastMessage = isset($lastMessage->created_at) ? $lastMessage->created_at : null;
                //Pega o status do contato
                $contactStatus = self::getStatusContact($hourLastMessage);

                $contacts[$key]->setAttribute('status', $contactStatus);
                //Pega o código do país
                $countryCode = substr($contact->con_phone, 0, 2);
                //Se o país for o Brasil
                if($countryCode == '55') {
                    //Pega o código do estado
                    $stateCode = substr($contact->con_phone, 2, 2);
                    //Se houver o código do estado
                    if($stateCode) {
                        $state = DddState::with('state')
                                        ->where('ddd_number', $stateCode)
                                        ->first();
                        //Log::debug('state');
                        //Log::debug($state['state']);
                        if(isset($state['state'])) {
                            $contacts[$key]->setAttribute('state', $state['state']);
                        }
                    }
                }
            }
            else
            {   //Caso o contato não tenha mensagem trocada com algum operador
                $contacts[$key]->setAttribute('status', 'busy');
            }

            $contacts[$key]->setAttribute('chat', $chat);
            $contacts[$key]->setAttribute('fullName', $contact->con_name);
            
            $initialsName = self::getInitialsNameContact($contacts[$key]);
            $contacts[$key]->setAttribute('initialsName', $initialsName);

            $valueRandom = null;
            if($contact->color_avatar_id == null)
            {
                $valueRandom = self::setColorAvatar($contact, $avatarColorArray);
            } 
           
            $contacts[$key]->setAttribute('avatarColor', isset($valueRandom)? $avatarColorArray[$valueRandom-1] : $contact['colorAvatar']->def_name);
            $contacts[$key]->setAttribute('role', 'Testando');
            $contacts[$key]->setAttribute('about', 'Sobre mim');
            
            $contacts[$key]->setAttribute('contactId', $contact->id);

            //Traz os chats associados ao contato
            $chatsContact = self::getChatsContact($contact->id);
            //Extrai os Ids dos chats
            $chatsContactIds = $chatsContact->pluck('id')->toArray();
            //Traz os atendimentos que já foram fechados
            $servicesContact =  $service->getServices($chatsContactIds, 3);
            $servicesId = $servicesContact->pluck('id')->toArray();
            //Calcula a nota média dado pelo contato aos atendimentos realizados 
            $avgAvaluationServices =  $service->getAvgServicesEvaluations($servicesId);
            //Divide a nota por 2 para equiparar a 5 estrelas
            $contact->setAttribute('avg_rating_service', $avgAvaluationServices/2);
            //Quantidade atendimentos já prestados ao usuário
            $amountServices = $service->getCountServicesContact($chatsContactIds);
            $contact->setAttribute('amountServices', $amountServices);
        }
    }

    public function getCountActiveChats()
    {
        //Traz os setores onde o usuário está lotado
        $userDepartmentsId = UserDepartment::select('department_id')
                                        ->where('user_id', Auth::user()->id)
                                        ->where('use_status', 'A')
                                        ->pluck('department_id')
                                        ->toArray();

        $totalActiveContacts = Contact::with('tags', 'colorAvatar')
                            ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'con_contacts.status_id', 'color_avatar_id', 'con_avatar', 
                                    'cam_name', 'cam_description', 'cha_name', 'man_channels.cha_status', 'man_channels.cha_api_official')
                            ->join('cha_chats', 'cha_chats.contact_id', 'con_contacts.id')
                            ->join('cha_services', 'cha_chats.id', 'cha_services.chat_id')
                            ->join('man_channels', 'cha_services.channel_id', 'man_channels.id')
                            ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Campanha associada ao atendimento
                            ->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                            //->join('cha_messages', 'cha_messages.service_id', 'cha_services.id')
                            //Filtra pela última linha da tabela de ações (transferência) de um chat e de um determinado atendimento
                            ->where('cha_actions.id', function($query) {
                                $query->select('id')
                                    ->from('cha_actions')
                                    ->whereColumn('chat_id', 'cha_chats.id')
                                    ->whereColumn('service_id', 'cha_services.id')
                                    ->latest()
                                    ->limit(1);
                                })
                            ->whereIn('cha_actions.department_id', $userDepartmentsId) //Se a última transferência foi feita para o departamento do usuário logado
                            ->where('cha_actions.user_id', Auth::user()->id) //Se o usuário é o titular do atendimento
                            ->whereIn('cha_actions.type_action_id', [1, 5]) // Se o atendimento foi iniciado por uma transferência de atendimento ou por comunicação ativa
                            ->where('type_status_service_id', 1)
                            ->count();
        
        return $totalActiveContacts;
    }

    //Traz os chats ativos associados a um determinado usuário
    public function fetchActiveChats(Request $request, $searchData=false)
    {
        //Log::debug('fetchActiveChats');
        //Log::debug($request);
        $skip = null;
        
        //Se o usuário NÃO estiver pesquisando por algum atendimento específico
        if($request['q'] == '' || $request['q'] == NULL) {
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

        //Traz os setores onde o usuário está lotado
        $userDepartmentsId = UserDepartment::select('department_id')
                                        ->where('user_id', Auth::user()->id)
                                        ->where('use_status', 'A')
                                        ->pluck('department_id')
                                        ->toArray();
        
        //Traz os chats ativos
        $activeContacts = Contact::with('tags', 'colorAvatar')
                            ->join('cha_chats', 'cha_chats.contact_id', 'con_contacts.id')
                            ->join('cha_services', 'cha_chats.id', 'cha_services.chat_id')
                            ->leftJoin('con_contacts_tags', 'con_contacts.id', 'con_contacts_tags.contact_id')
                            ->leftJoin('man_tags', 'con_contacts_tags.tag_id', 'man_tags.id')
                            ->join('man_channels', 'cha_services.channel_id', 'man_channels.id')
                            ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Campanha associada ao atendimento
                            //->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                            ->select('con_contacts.id as id' , 'man_channels.id as channel_id', 'con_name', 'gender_id', 'con_contacts.status_id', 'color_avatar_id', 'con_avatar', 
                                    'cam_name', 'cam_description', 'cha_name', 'man_channels.cha_status', 'man_channels.cha_api_official', 'ser_new_service',
                                    DB::raw("COALESCE((SELECT created_at FROM cha_messages WHERE cha_messages.chat_id = cha_chats.id ORDER BY cha_messages.created_at DESC LIMIT 1), (SELECT updated_at FROM cha_actions WHERE cha_actions.chat_id = cha_chats.id ORDER BY cha_actions.updated_at DESC LIMIT 1)) AS latest_message"))
                            //->leftJoin('cha_messages', 'cha_messages.chat_id', 'cha_chats.id')
                            //Filtra pela última linha da tabela de ações (transferência) de um chat e de um determinado atendimento
                            /*->where('cha_actions.id', function($query) {
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
                            //Filtra pela última mensagem trocada com o contato
                            /*->where('cha_messages.id', function($query) {
                                $query->select('id')
                                    ->from('cha_messages')
                                    ->whereColumn('chat_id', 'cha_chats.id')
                                    //->whereColumn('service_id', 'cha_services.id')
                                    ->latest()
                                    ->limit(1);
                                })*/
                            ->whereIn('cha_actions.department_id', $userDepartmentsId) //Se a última transferência foi feita para o departamento do usuário logado
                            ->where('cha_actions.user_id', Auth::user()->id) //Se o usuário é o titular do atendimento
                            ->whereIn('cha_actions.type_action_id', [1, 5]) // Se o atendimento foi iniciado por uma transferência de atendimento ou por comunicação ativa
                            ->where('type_status_service_id', 1) //Onde o atendimento está em aberto
                            ->whereRaw('cha_actions.id = (
                                SELECT MAX(id) 
                                FROM cha_actions 
                                WHERE chat_id = cha_chats.id 
                                AND service_id = cha_services.id
                            )');
        //Se o usuário está filtrando
        if($request['q'] != '' && strlen($request['q']) > 3) {
            $activeContacts = $activeContacts->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('con_phone', 'like', '%'.trim($request['q']).'%')
                        //Verifica se a busca coincide com a tag pesquisada
                        ->orWhere('tag_name', 'like', '%'.trim($request['q']).'%');
            });
        }

        $total = $activeContacts->count();

        //Se for para pular algum contato
        if($skip) {
            $activeContacts = $activeContacts->skip($skip); //Quantidade de itens que serão desconsiderados por já terem sido carregados 
        }
        
        if(isset($amountPerClick)) {
            $activeContacts = $activeContacts->take($amountPerClick); //Quantidade de itens trazidos
        }
        //Ordena os contatos pelas conversas mais recentes
        $activeContacts = $activeContacts->groupBy('id' ,'con_name', 'gender_id', 'con_contacts.status_id', 'color_avatar_id', 'con_avatar', 
        'cam_name', 'cam_description', 'cha_name', 'man_channels.cha_status', 'man_channels.cha_api_official', 'latest_message')
                                        ->orderBy('latest_message', 'DESC')
                                        ->orderBy('cha_actions.updated_at', 'DESC')
                                        ->get();

        //Log::debug('activeContacts');
        //Log::debug($activeContacts);
        self::chatsAndContactsDetails($activeContacts);
        //Se não tem dado de pesquisa
        if($searchData == false) {
            return response()->json([
                'activeContacts' => $activeContacts,
                'totalActiveChats' => $total,
                'skip' => $skip,
            ], 201);
        }
        else {
            $activeContactsData['activeContacts'] = $activeContacts;
            $activeContactsData['totalActiveChats'] = $total;

            return $activeContactsData;
        }
    }

    //Traz o total de contatos pendentes
    public function getCountPendingChats()
    {
        //Traz os setores onde o usuário está lotado
        $userDepartmentsId = UserDepartment::select('department_id')
                                        ->where('user_id', Auth::user()->id)
                                        ->where('use_status', 'A')
                                        ->pluck('department_id')
                                        ->toArray();

        $totalPendingContacts = Contact::with('tags', 'colorAvatar')
                                        ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'con_contacts.status_id', 'color_avatar_id', 'con_avatar' , 
                                                'cam_name', 'cam_description', 'cha_name', 'man_channels.cha_status', 'man_channels.cha_api_official')
                                        ->join('cha_chats', 'cha_chats.contact_id', 'con_contacts.id')
                                        ->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                        ->join('cha_services', 'cha_chats.id', 'cha_services.chat_id')
                                        ->join('man_channels', 'cha_services.channel_id', 'man_channels.id')
                                        ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Campanha associada ao atendimento
                                        //Filtra pela última linha da tabela de ações (transferência) de um chat e de um determinado atendimento
                                        ->where('cha_actions.id', function($query) {
                                            $query->select('id')
                                                ->from('cha_actions')
                                                ->whereColumn('chat_id', 'cha_chats.id')
                                                ->whereColumn('service_id', 'cha_services.id')
                                                ->latest()
                                                ->limit(1);
                                            })
                                        ->whereIn('department_id', [$userDepartmentsId]) //Onde o chat esteja aguardando a captura de algum operador lotado no(s)  setor(es) em que o usuário esteja lotado
                                        ->whereNull('user_id') //Onde nenhum operador tenha capturado o atendimento
                                        ->count();
        
        return $totalPendingContacts;
    }

    //Traz os chats pendentes de um determinado departamento
    public function fetchPendingChats(Request $request, $searchData=false)
    {
        Log::debug('fetchPendingChats');
        Log::debug($request);
        $skip = null;
        
        //Se o usuário NÃO estiver pesquisando por algum atendimento específico
        if($request['q'] == '' || $request['q'] == NULL) {
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

        //Traz os setores onde o usuário está lotado
        $userDepartmentsId = UserDepartment::select('department_id')
                                        ->where('user_id', Auth::user()->id)
                                        ->where('use_status', 'A')
                                        ->pluck('department_id')
                                        ->toArray();

        $pendingContacts = Contact::with('tags', 'colorAvatar')
                                    ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'con_contacts.status_id', 'color_avatar_id', 'con_avatar' , 
                                            'cam_name', 'cam_description', 'cha_name', 'man_channels.cha_status', 'man_channels.cha_api_official')
                                    ->join('cha_chats', 'cha_chats.contact_id', 'con_contacts.id')
                                    //->join('cha_actions', 'cha_chats.id', 'cha_actions.chat_id')
                                    ->join('cha_services', 'cha_chats.id', 'cha_services.chat_id')
                                    ->leftJoin('con_contacts_tags', 'con_contacts.id', 'con_contacts_tags.contact_id')
                                    ->leftJoin('man_tags', 'con_contacts_tags.tag_id', 'man_tags.id')
                                    ->join('man_channels', 'cha_services.channel_id', 'man_channels.id')
                                    ->leftJoin('cam_campaigns', 'cha_services.campaign_id', 'cam_campaigns.id') //Campanha associada ao atendimento
                                    //Filtra pela última linha da tabela de ações (transferência) de um chat e de um determinado atendimento
                                    /*->where('cha_actions.id', function($query) {
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
                                    ->whereIn('cha_actions.department_id', [$userDepartmentsId]) //Onde o chat esteja aguardando a captura de algum operador lotado no(s)  setor(es) em que o usuário esteja lotado
                                    ->whereNull('cha_actions.user_id') //Onde nenhum operador tenha capturado o atendimento
                                    ->whereRaw('cha_actions.id = (
                                        SELECT MAX(id) 
                                        FROM cha_actions 
                                        WHERE chat_id = cha_chats.id 
                                        AND service_id = cha_services.id
                                    )');
        //Se o usuário está filtrando
        if($request['q'] != '' && strlen($request['q']) > 3) {
            $pendingContacts = $pendingContacts->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('con_phone', 'like', '%'.trim($request['q']).'%')
                        //Verifica se a busca coincide com a tag pesquisada
                        ->orWhere('tag_name', 'like', '%'.trim($request['q']).'%');
            });
        }

        $total = $pendingContacts->count();

        //Se for para pular algum contato
        if($skip) {
            $pendingContacts = $pendingContacts->skip($skip); //Quantidade de itens que serão desconsiderados por já terem sido carregados 
        }
        
        if(isset($amountPerClick)) {
            $pendingContacts = $pendingContacts->take($amountPerClick); //Quantidade de itens trazidos
        }
        $pendingContacts = $pendingContacts->groupBy('id' ,'con_name', 'gender_id', 'con_contacts.status_id', 
                                                    'color_avatar_id', 'con_avatar' , 'cam_name', 'cam_description', 'cha_name', 
                                                    'man_channels.cha_status', 'man_channels.cha_api_official')
                                            ->get();
        
        self::chatsAndContactsDetails($pendingContacts);

        //Se não tem dado de pesquisa
        if($searchData == false) {
            return response()->json([
                'pendingContacts' => $pendingContacts,
                'totalPendingChats' => $total,
                'skip' => $skip,
            ], 201);
        }
        else {
            $activeContactsData['pendingContacts'] = $pendingContacts;
            $activeContactsData['totalPendingChats'] = $total;

            return $activeContactsData;
        }
    }

    public function chatsAndContacts()
    {
        $extensionController = new ExtensionController();

        $requestChats = new Request([
            'offset'   => 0,
            'skip' => true,
        ]);
        
        //Traz os chats ativos
        $activeContacts = self::fetchActiveChats($requestChats, true);

        //Chats pendentes
        $pendingContacts = self::fetchPendingChats($requestChats, true);


        $userData = $this->userController->show(Auth::user()->id);
        //Traz os gêneros cadastrados
        $userData = json_encode($userData);
        $userData = json_decode($userData, true);
        $profileUserData  = $userData['original'];

        $baseUrlStorage = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC');

        //Log::debug('contatos ativos');
        //Log::debug($activeContacts);

        $userExtension = $extensionController->getUserLoggedExtension();

        return response()->json([
            'chatsContacts'=> $activeContacts['activeContacts'],
            'totalActiveChats'=> $activeContacts['totalActiveChats'],
            'pendingContacts'=> $pendingContacts['pendingContacts'],
            'totalPendingChats'=> $pendingContacts['totalPendingChats'],
            'profileUser'=> $profileUserData,
            'baseUrlStorage' => $baseUrlStorage,
            'userExtension' => $userExtension,
        ], 201);
    }

    public function getInitialsNameContact($contact)
    {
        $initialsName = "";
        if($contact->con_name != '') {
            //Remove emojis do nome, caso haja
            $contactNameWithoutEmoji = $this->utilsController->removeEmojis($contact->con_name);
            //Quebra o nome completo
            $nameDivided = explode(" ", $contactNameWithoutEmoji);
            
            //Para os dois primeiros nomes do contato
            foreach ($nameDivided as $key2 => $partName) {
                //Pega as duas primeiras iniciais
                if($key2 < 2 && $partName != '')
                {
                    $initialsName .= $partName[0];
                }
            }
        }
        
        return  mb_convert_encoding($initialsName, 'UTF-8', 'UTF-8');
    }


    //Traz o último atendimento para um determinado chat, se houver
    public function getCurrentServiceChat($chatId)
    {
        $service = Service::where('chat_id', $chatId)
                            ->where('type_status_service_id', 1) //Onde o atendimento está em aberto
                            ->orderBy('created_at', 'desc')
                            ->first();

        return $service;
    }

    //Traz o último atendimento para um determinado chat, se houver
    public function getServiceByStatus($chatId, $status)
    {
        $service = Service::where('chat_id', $chatId)
                        ->whereIn('type_status_service_id', $status) //Onde o atendimento está em aberto
                        ->orderBy('created_at', 'desc')
                        ->first();

        return $service;
    }

    public function getServiceEvaluation($chatId) 
    {
        $serviceEvaluation = Service::where('chat_id', $chatId)
                            ->where('type_status_service_id', 4) //Onde o atendimento está em avaliação
                            ->orderBy('created_at', 'desc')
                            ->first();

        return $serviceEvaluation;
    }

    public function storeMessage($chatId, $typeUserId, $senderId, $payloadMessage, $actionId, $apiName=null, $privateMessage='false', 
                                $channelData=null, $campaignId=null, $templateId=null, $quickMessageId=null, $typeFormatMessageId=null,
                                $typeOriginMessageId=null)
    {
        $channelController = new ChannelController();
        $communicatorController = new CommunicatorController();
        $chatbotController = new ChatbotController();
        $templateController = new TemplateController();
        $message = new ChatMessage();
        $service = null;
        $lastMessage = null;

        $dateTimeNow = Carbon::now();
        $message->chat_id = $chatId;
        $message->type_user_id = $typeUserId;
        $message->sender_id = $senderId;
        $message->campaign_id = $campaignId;
        $message->template_id = $templateId;
        $message->quick_message_id = $quickMessageId;
        $message->mes_phone_channel_received_message = isset($payloadMessage['mesPhoneChannelReceivedMessage'])? $payloadMessage['mesPhoneChannelReceivedMessage'] : null;
        $message->mes_phone_channel_sent_message = isset($payloadMessage['mesPhoneChannelSentMessage'])? $payloadMessage['mesPhoneChannelSentMessage'] : null;
        $message->type_origin_message_id = $typeOriginMessageId;
        $message->answered_message_id = isset($payloadMessage['answeredMessageId'])? $payloadMessage['answeredMessageId'] : NULL;
        $message->status_message_chat_id = 1; //Coloca a mensagem inicialmente com status enfileirada
        if($privateMessage == 'false') {
            $privateMessage = 0;
        }
        else {
            $privateMessage = 1;
        }
        $message->mes_private = $privateMessage;
        //$message->mes_private = $privateMessage == false ? 0 : 1;
        $message->action_id = $actionId;
        $serviceId = null;
        $service = self::getServiceByStatus($chatId, [1, 4]);
        $serviceEvaluation = null;
        $serviceEvaluation = self::getServiceEvaluation($chatId);

        //Caso exista um atendimento em ABERTO para o referido chat
        if(isset($service)) {
            if($service->type_status_service_id = 1) {
                $serviceId = $service->id;
            }
        }
        else {
            //Caso a mensagem tenha sido enviada pelo CONTATO ou pelo USUÁRIO EXTERNO
            //Faz a checagem para resolver situações em que a mensagem enviada pelo USUÁRIO EXTERNO é enviada no mesmo segundo que o CONTATO, evitando criar dois atendimentos para o mesmo usuário
            if(isset($payloadMessage['fromSystemUser']) && $payloadMessage['fromSystemUser']) {
                Log::debug('verificação de mensagem externa');
                //Aguarda 2 segundos
                sleep(2);
                $service = self::getCurrentServiceChat($chatId);

                //Caso exista um atendimento em ABERTO para o referido chat
                if(isset($service)) {
                    if($service->type_status_service_id = 1) {
                        $serviceId = $service->id;
                    }
                }
            }

            //Se o atendimento não está aberto, não está em fase de avaliação e a mensagem tenha sido enviada pelo CONTATO ou pelo USUÁRIO EXTERNO e NÃO tenha atendimento ABERTO
            if((!$serviceEvaluation && $typeUserId == 2 || $senderId == 3) && !$serviceId) {
                if($apiName == 'wppconnect') {
                    $channelNumber = mb_substr($channelData['sessionName'], 2);

                    $channel = Channel::where('cha_phone_number', $channelNumber)
                                    ->first();
                }
                else if($apiName == '360Dialog' || $apiName == 'gupshup' || $apiName == 'cloudApiWhatsapp' || $apiName == 'z-api' || $apiName == 'apizap' || $apiName == 'api-ehost' || $apiName == 'api-wa' || $apiName == 'unipix') {
                    //Traz o canal que o contato mandou mensagem
                    $channel = $channelController->getChannel($channelData['id']);
                }
                
                //Pega a última mensagem trocada (para poder pegar o id da campanha)
                $lastMessage = self::getLastMessage($chatId);

                $service = new Service();
                $service->chat_id = $chatId;
                $service->channel_id = $channel->id; //Canal associado ao atendimento
                $service->campaign_id = isset($lastMessage)? $lastMessage->campaign_id : null; //Campanha associada ao atendimento
                $service->type_status_service_id = 1; //Seta o status do atendimento como em aberto
                $service->ser_protocol_number = self::generateProtocolNumber(); 
                //Caso o serviço seja armazenado no banco de dados
                if($service->save()) {
                    $serviceId = $service->id;
                    //Se o atendimento estiver associado a uma campanha
                    if(isset($lastMessage) && $lastMessage->campaign_id) {
                        $mailingController = new MailingController();

                        //Marca que o contato retornou a mensagem da campanha
                        $mailingController->setContactReturn($lastMessage->campaign_id, $senderId);
                    }
                }
            }
        }

        //Se o cliente é proveniente de uma CAMPANHA e a mensagem tenha sido enviada pelo CONTATO
        if(isset($service->campaign_id) && $service->campaign_id && $typeUserId == 2) {
            //Cria uma ação de transferência para o departamento da campanha
            $actionController = new ActionController();
            $campaignController = new CampaignController();
            $hasAction = $actionController->getLastActionByService($service->id);
            //Log::debug('$hasAction');
            //Log::debug($hasAction);
            //Se o contato de campanha ainda NÃO está sendo atendido ou ainda NÃO foi transferido
            if(!$hasAction) {
                $campaign = $campaignController->show($service->campaign_id);

                //Verifica se existe algum chatbot de campanha associado a canal
                $isChatbot = $chatbotController->getChatbotCampaign($service->campaign_id, $service->channel_id);

                //Se NÃO houver chatbot de campanha associado ao número, transfere o atendimento para o departamento padrão
                if(!$isChatbot) {
                    Log::debug('$campaign');
                    Log::debug($campaign['settings'][0]['fair_distribution_id']);

                    $fairDistributionChannel = $this->serviceController->getFairDistributionChannel($campaign['settings'][0]['fair_distribution_id'], $service->channel_id);
                    $userChoosen = $this->serviceController->choiceFairDistributionUser($campaign['settings'][0]['fair_distribution_id']);
                    //Se o canal faz parte da distribuição igualitária de atendimentos e existem usuários cadastrados para o encaminhamento
                    if($fairDistributionChannel && $userChoosen) {
                        $departmentController = new DepartmentController();

                        //Traz o departamento onde o usuário está lotado
                        $userDepartment = $departmentController->fetchDepartmentsUser($userChoosen['user_id']);
                        
                        $requestActionData = new Request([
                            'serviceId'   => $service->id,
                            'chatId' => $chatId,
                            'typeActionId' => 1, //Cria uma ação do tipo TRANSFERÊNCIA
                            'departmentId' => $userDepartment[0]['id'],
                            'userId' => $userChoosen['user_id'],
                        ]);

                        $userFairDistribution = $this->serviceController->getFairDistribution($userChoosen->user_fair_distribution_id);
                        //Registra o encaminhamento do atendimento
                        $userFairDistribution->fai_total_forwarding = $userFairDistribution->fai_total_forwarding + 1;
                        $userFairDistribution->fai_dt_last_forwarding = now();
                        $userFairDistribution->save();
                    } //TRANSFERE PARA O DEPARTAMENTO PADRÃO SETADO NA CAMPANHA
                    else {
                        $requestActionData = new Request([
                            'serviceId'   => $service->id,
                            'chatId' => $chatId,
                            'typeActionId' => 1, //Cria uma ação do tipo Comunicação Ativa
                            'departmentId' => $campaign['settings'][0]['department_id'],
                            'userId' => null,
                        ]);
                    }

                    //Se a ação for armazenada
                    if($actionController->store($requestActionData)) {
                        //Se o atendimento NÃO foi transferido para um usuário específico
                        if(!$userChoosen) {
                            //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
                            $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                            ->join('users', 'man_users_departments.user_id', 'users.id')
                            ->where('department_id', $campaign['settings'][0]['department_id'])
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
                            $this->eventController->updateChats($userChoosen['user_id']);
                        }

                        //Chama o evento que atualiza a tela com os atendimentos em progresso
                        $this->serviceController->updateServiceProgressEvent();
                    }
                }
            }
        }

        if($apiName == '360Dialog' || $apiName == 'gupshup' || $apiName == 'cloudApiWhatsapp') {
            if($channelData) {
                //Traz o canal que o contato mandou mensagem
                $channel = $channelController->getChannel($channelData['id']);
            }
        }

        //Caso seja uma mensagem de texto
        if($payloadMessage['type'] == 'text') {
            $typeMessageChatId = null;
            //COMPARAÇÃO USADA PARA ENVIO SEQUENCIAL DE MENSANGENS NO CHATBOT
            //Se o formato da mensagem for texto
            if($typeFormatMessageId == null || $typeFormatMessageId == 1) {
                $message->type_message_chat_id = 1;
                $typeMessageChatId = 1;
            } //Se o formato da mensagem for áudio
            else if($typeFormatMessageId == 2) {
                $message->type_message_chat_id = 2;
                $typeMessageChatId = 2;
            } //Se o formato da mensagem for arquivo
            else if($typeFormatMessageId == 3) {
                $message->type_message_chat_id = 5;
                $typeMessageChatId = 5;
            }
            else if($typeFormatMessageId == 4) {
                $message->type_message_chat_id = 4;
                $typeMessageChatId = 4;
            }
            
            //Se o conteúdo da mensagem não veio
            if(isset($payloadMessage['payload']['waitingMessage']) && $payloadMessage['payload']['waitingMessage'] == true) {
                $message->mes_waiting_message = 1;
            }
            //Se uma mensagem recebida de algum contato, pega o id dessa mensagem, caso contrário pega o id da mensagem enviada (se não 
            //é mensagem recebida, é mensagem enviada)
            $message->api_message_id = isset($payloadMessage['id']) ? $payloadMessage['id'] : null;
            $message->mes_message = $payloadMessage['payload']['text'];
            $message->service_id = $serviceId;

            $newMessageData = array(
                'api_message_id' => $message->api_message_id,
                'type_message_chat_id' => $typeMessageChatId,
                'mes_message' => $payloadMessage['payload']['text'], 
                'created_at' => $dateTimeNow,
                'senderId' => $senderId,
                'type_user_id' => $typeUserId,
                'mes_private' => $privateMessage,
                'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                'mes_waiting_message' => isset($message->mes_waiting_message)? $message->mes_waiting_message : null, //Coloca como aguardando o conteúdo da mensagem
                'answered_message_id' => $message->answered_message_id,
                'mes_phone_channel_received_message' => isset($message->mes_phone_channel_received_message)? $message->mes_phone_channel_received_message : null,
                'mes_phone_channel_sent_message' => isset($message->mes_phone_channel_sent_message)? $message->mes_phone_channel_sent_message : null,
            );
        }
        else if($payloadMessage['type'] == 'file') {
            $message->type_message_chat_id = 5;
            $message->api_message_id = isset($payloadMessage['id']) ? $payloadMessage['id'] : null;
            $message->mes_media_original_name = $payloadMessage['payload']['name'];
            $message->mes_caption = isset($payloadMessage['payload']['caption'])? $payloadMessage['payload']['caption'] : null;
            $message->mes_url = isset($payloadMessage['payload']['url'])? $payloadMessage['payload']['url'] : null;
            $message->mes_content_type = $payloadMessage['payload']['contentType'];
            $message->service_id = $serviceId;

            if(isset($payloadMessage['payload']['name'])) {
                $dataSplit = explode(".", $payloadMessage['payload']['name']);
            }
            
            
            $extensionContent = '.'.$dataSplit[count($dataSplit)-1];
            //Formata a data/hora adicionando milissegundos
            $dateTimeNowFormatted = $dateTimeNow->format('Y-m-d H:i:s.u');
            //Deixa apenas os números no nome do arquivo
            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNowFormatted ).$extensionContent;

            $message->mes_content_name = $contentName;

            //Se for a API da WppConnect
            if($apiName == 'wppconnect') {
                $fileBase64 = base64_decode($payloadMessage['payload']['base64']);
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/files/', $fileBase64, $contentName, 'public');
            }
            else if($apiName == 'cloudApiWhatsapp') {
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                //Obtém os dados da mídia em base64
                $mediaData = $communicatorController->downloadMedia($channel, $media['url']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/files/'.$contentName, $mediaData->body());

                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/files/'.$message->mes_content_name);
                //Converte o arquivo em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $mediaData->body(), $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/files/', $fileBase64, $contentName, 'public');
                //Remove o arquivo localmente
                unlink($filePath);

                //Remove a mídia do servidor da 360Dialog
                //$communicatorController->deleteMedia($channel, $payloadMessage['payload']['mediaId']);

            }//Se for a API Zap
            else if($apiName == 'apizap' || $apiName == 'api-wa') {
                $typeMedia = explode('/', $payloadMessage['payload']['contentType']);
                $fileBase64 = 'data:application/' . $typeMedia[1] . ';base64,' .$payloadMessage['payload']['base64'];
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/files/', $fileBase64, $contentName, 'public');
            } //Se for a API eHost
            else if($apiName == 'api-ehost') {
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/files/', $payloadMessage['payload']['file'], $contentName, 'public');
            }
            else if($apiName == '360Dialog') {
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                //Salva o arquivo localmente
                Storage::disk('public')->put('chats/chat'.$chatId.'/files/'.$contentName, $media);
                //Pega o caminho do arquivo
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/files/'.$message->mes_content_name);
                //Converte o arquivo em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $media, $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/files/', $fileBase64, $contentName, 'public');
                //Remove o arquivo localmente
                unlink($filePath);

                //Remove a mídia do servidor da 360Dialog
                $communicatorController->deleteMedia($channel, $payloadMessage['payload']['mediaId']);

            }
            //Se for a API da GUPSHUP ou Z-API
            else {
                //Caso o contato tenha enviado o arquivo para o operador
                if(isset($payloadMessage['payload']['url'])) {
                    //Pega o conteúdo do arquivo
                    $response = Http::get($payloadMessage['payload']['url']);
                    $fileBase64 = $this->utilsController->convertToBase64($payloadMessage['payload']['url'], $response->body(), $message->type_message_chat_id);
                    //Salva no diretório
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/files/', $fileBase64, $contentName, 'public');
                    //Storage::disk('public')->put('chats/chat'.$chatId.'/files/'.$contentName, $response->body());
                }
                else {
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/files/', $payloadMessage['data']->file, $contentName, 'public');
                    //$payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/files/', $contentName);
                }
            }

            $newMessageData = array(
                'chat_id' => $chatId,
                'api_message_id' => $message->api_message_id,
                'type_message_chat_id' => 5, 
                'mes_content_type' => $payloadMessage['payload']['contentType'], 
                'mes_url' => isset($payloadMessage['payload']['url']) ? $payloadMessage['payload']['url'] : null, 
                'mes_content_name' => $contentName, 
                'mes_media_original_name' => $payloadMessage['payload']['name'], 
                'mes_caption' => isset($payloadMessage['payload']['caption'])? $payloadMessage['payload']['caption'] : null,
                'created_at' => $dateTimeNow,
                'senderId' => $senderId,
                'type_user_id' => $typeUserId,
                'mes_private' => $privateMessage,
                'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                'answered_message_id' => $message->answered_message_id,
                'mes_phone_channel_received_message' => isset($message->mes_phone_channel_received_message)? $message->mes_phone_channel_received_message : null,
                'mes_phone_channel_sent_message' => isset($message->mes_phone_channel_sent_message)? $message->mes_phone_channel_sent_message : null,
            );
        }
        else if($payloadMessage['type'] == 'image') {
            $message->type_message_chat_id = 3;
            $message->mes_content_type = $payloadMessage['payload']['contentType'];
            $message->mes_url = null;
            $message->service_id = $serviceId;
            $message->mes_caption = isset($payloadMessage['payload']['caption'])? $payloadMessage['payload']['caption'] : null;
            $message->api_message_id = isset($payloadMessage['id']) ? $payloadMessage['id'] : null;

            $dataSplit = explode("/", $payloadMessage['payload']['contentType']);
            
            $extensionContent = '.'.$dataSplit[1];
            //Formata a data/hora adicionando milissegundos
            $dateTimeNowFormatted = $dateTimeNow->format('Y-m-d H:i:s.u');
            //Deixa apenas os números no nome do arquivo
            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNowFormatted).$extensionContent;

            $message->mes_content_name = $contentName;
            //Se for a API da WppConnect
            if($apiName == 'wppconnect') {
                $image = base64_decode($payloadMessage['payload']['base64']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/images/'.$contentName, $image);
            } 
            else if($apiName == 'cloudApiWhatsapp') {
                //Obtém o link da imagem
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                //Obtém os dados da mídia em base64
                $mediaData = $communicatorController->downloadMedia($channel, $media['url']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/images/'.$contentName, $mediaData->body());
                //Pega o caminho da mídia
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/images/'.$message->mes_content_name);
                //Converte a mídia em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $mediaData->body(), $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/images/', $fileBase64, $contentName, 'public');
                //Remove a mídia localmente
                unlink($filePath);

                //Remove a mídia do servidor da 360Dialog
                //$communicatorController->deleteMedia($channel, $payloadMessage['payload']['mediaId']);
            }
            else if($apiName == 'apizap' || $apiName == 'api-wa') {
                $typeMedia = explode('/', $payloadMessage['payload']['contentType']);
                $fileBase64 = 'data:application/' . $typeMedia[1] . ';base64,' .$payloadMessage['payload']['base64'];
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/images/', $fileBase64, $contentName, 'public');
            } //Se for a API eHOST
            else if($apiName == 'api-ehost') {
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/images/', $payloadMessage['payload']['file'], $contentName, 'public');
            }
            else if($apiName == '360Dialog') {
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/images/'.$contentName, $media);
                //Pega o caminho do arquivo
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/images/'.$message->mes_content_name);
                //Converte o arquivo em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $media, $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/images/', $fileBase64, $contentName, 'public');
                //Remove o arquivo localmente
                unlink($filePath);

                //Remove a mídia do servidor da 360Dialog
                $communicatorController->deleteMedia($channel, $payloadMessage['payload']['mediaId']);
            }
            //Se for da GUPSHUP ou Z-API
            else {
                if(isset($payloadMessage['payload']['url'])) {
                    $message->mes_url = $payloadMessage['payload']['url'];
                }
                
                //Caso o contato tenha enviado a imagem para o operador
                if(isset($payloadMessage['payload']['url'])) {
                    //Pega o conteúdo do arquivo
                    $response = Http::get($payloadMessage['payload']['url']);
                    $fileBase64 = $this->utilsController->convertToBase64($payloadMessage['payload']['url'], $response->body(), $message->type_message_chat_id);
                    //Salva no diretório
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/images/', $fileBase64, $contentName, 'public');
                    //Storage::disk('public')->put('chats/chat'.$chatId.'/images/'.$contentName, $response->body());
                } //Caso o operador tenha enviado a mensagem para o contato
                else
                {   
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/images/', $payloadMessage['data']->file, $contentName, 'public');
                    //$payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/images/', $contentName);
                }
            }

            $newMessageData = array(
                'chat_id' => $chatId, 
                'api_message_id' => $message->api_message_id,
                'type_message_chat_id' => 3, 
                'mes_content_type' => $payloadMessage['payload']['contentType'], 
                'mes_content_name' => $contentName,
                'mes_caption' => isset($payloadMessage['payload']['caption'])? $payloadMessage['payload']['caption'] : null,
                'mes_url' => isset($payloadMessage['payload']['url']) ? $payloadMessage['payload']['url'] : null,  
                'created_at' => $dateTimeNow,
                'senderId' => $senderId,
                'type_user_id' => $typeUserId,
                'mes_private' => $privateMessage,
                'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                'answered_message_id' => $message->answered_message_id,
                'mes_phone_channel_received_message' => isset($message->mes_phone_channel_received_message)? $message->mes_phone_channel_received_message : null,
                'mes_phone_channel_sent_message' => isset($message->mes_phone_channel_sent_message)? $message->mes_phone_channel_sent_message : null,
            );
        }
        else if($payloadMessage['type'] == 'sticker') {
            $message->type_message_chat_id = 7;
            $message->mes_content_type = $payloadMessage['payload']['contentType'];
            $message->mes_url = null;
            $message->service_id = $serviceId;
            $message->api_message_id = isset($payloadMessage['id']) ? $payloadMessage['id'] : null;

            $dataSplit = explode("/", $payloadMessage['payload']['contentType']);
            
            $extensionContent = '.'.$dataSplit[1];
            //Deixa apenas os números no nome do arquivo
            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).$extensionContent;

            $message->mes_content_name = $contentName;
            //Se for a API da WppConnect
            if($apiName == 'wppconnect') {
                $image = base64_decode($payloadMessage['payload']['base64']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/stikers/'.$contentName, $image);
            }
            else if($apiName == 'cloudApiWhatsapp') {
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                //Obtém os dados da mídia em base64
                $mediaData = $communicatorController->downloadMedia($channel, $media['url']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/stikers/'.$contentName, $mediaData->body());
                //Pega o caminho do arquivo
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/stikers/'.$message->mes_content_name);
                //Converte o arquivo em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $mediaData->body(), $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/stikers/', $fileBase64, $contentName, 'public');
                //Remove o arquivo localmente
                unlink($filePath);
                
            }
            else if($apiName == 'apizap' || $apiName == 'api-wa') {
                $typeMedia = explode('/', $payloadMessage['payload']['contentType']);
                $fileBase64 = 'data:application/' . $typeMedia[1] . ';base64,' .$payloadMessage['payload']['base64'];
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/stikers/', $fileBase64, $contentName, 'public');
            } //Se for a API eHOST
            else if($apiName == 'api-ehost') {
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/stikers/', $payloadMessage['payload']['file'], $contentName, 'public');
            }
            else if($apiName == '360Dialog') {
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/stikers/'.$contentName, $media);
                //Pega o caminho do arquivo
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/stikers/'.$message->mes_content_name);
                //Converte o arquivo em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $media, $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/stikers/', $fileBase64, $contentName, 'public');
                //Remove o arquivo localmente
                unlink($filePath);

                //Remove a mídia do servidor da 360Dialog
                $communicatorController->deleteMedia($channel, $payloadMessage['payload']['mediaId']);
            }
            //Se for da GUPSHUP ou Z-API
            else {
                if(isset($payloadMessage['payload']['url'])) {
                    $message->mes_url = $payloadMessage['payload']['url'];
                }
                
                //Caso o contato tenha enviado a imagem para o operador
                if(isset($payloadMessage['payload']['url'])) {
                    //Pega o conteúdo do arquivo
                    $response = Http::get($payloadMessage['payload']['url']);
                    $fileBase64 = $this->utilsController->convertToBase64($payloadMessage['payload']['url'], $response->body(), $message->type_message_chat_id);
                    //Salva no diretório
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/stikers/', $fileBase64, $contentName, 'public');
                    //Storage::disk('public')->put('chats/chat'.$chatId.'/stikers/'.$contentName, $response->body());
                } //Caso o operador tenha enviado a mensagem para o contato
                else
                {
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/stikers/', $payloadMessage['data']->file, $contentName, 'public');
                    //$payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/stikers/', $contentName);
                }
            }

            $newMessageData = array(
                'chat_id' => $chatId, 
                'api_message_id' => $message->api_message_id,
                'type_message_chat_id' => 7, 
                'mes_content_type' => $payloadMessage['payload']['contentType'], 
                'mes_content_name' => $contentName, 
                'mes_url' => isset($payloadMessage['payload']['url']) ? $payloadMessage['payload']['url'] : null,  
                'created_at' => $dateTimeNow,
                'senderId' => $senderId,
                'type_user_id' => $typeUserId,
                'mes_private' => $privateMessage,
                'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                'answered_message_id' => $message->answered_message_id,
                'mes_phone_channel_received_message' => isset($message->mes_phone_channel_received_message)? $message->mes_phone_channel_received_message : null,
                'mes_phone_channel_sent_message' => isset($message->mes_phone_channel_sent_message)? $message->mes_phone_channel_sent_message : null,
            );
        }
        else if($payloadMessage['type'] == 'audio')
        {
            $message->type_message_chat_id = 2;
            $message->api_message_id = isset($payloadMessage['id']) ? $payloadMessage['id'] : null;
            $message->mes_url = isset($payloadMessage['payload']['url']) ? $payloadMessage['payload']['url'] : null;
            $message->mes_content_type = $payloadMessage['payload']['contentType'];
            $message->service_id = $serviceId;

            $dataSplit = explode(";", $payloadMessage['payload']['contentType']);
            $dataSplit = explode("/",  $dataSplit[0]);

            $isChannelOfficial = null;
            //Traz se o canal é oficial ou não
            if(isset($channelData['cha_api_official'])) {
                $isChannelOfficial = $channelData['cha_api_official'];
            }
            else if(isset($channel->cha_api_official)) {
                $isChannelOfficial = $channel->cha_api_official;
            }
            
            if($dataSplit[1] == 'mpeg') {
                //Se foi enviado pela API OFICIAL (360 Dialog)
                if($isChannelOfficial) {
                    $extensionContent = '.mpeg';
                }
                else {
                    $extensionContent = '.mp3';
                }
            }
            else {
                $extensionContent = '.'.$dataSplit[1];
            }
            
            //Deixa apenas os números no nome do arquivo
            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).$extensionContent;

            $message->mes_content_name = $contentName;

            //Se for a API da WppConnect
            if($apiName == 'wppconnect') {
                $image = base64_decode($payloadMessage['payload']['base64']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/audios/'.$contentName, $image);
            }
            else if($apiName == 'cloudApiWhatsapp') {
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                //Obtém os dados da mídia em base64
                $mediaData = $communicatorController->downloadMedia($channel, $media['url']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/audios/'.$contentName, $mediaData->body());
                //Pega o caminho do arquivo
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/audios/'.$message->mes_content_name);
                //Converte a mídia em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $mediaData, $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/audios/', $fileBase64, $contentName, 'public');
                //Remove a mídia localmente
                unlink($filePath);
            } //Se for a API ZAP ou API-WA
            else if($apiName == 'apizap' || $apiName == 'api-wa') {
                $typeMedia = explode('/', $payloadMessage['payload']['contentType']);
                $fileBase64 = 'data:application/' . $typeMedia[1] . ';base64,' .$payloadMessage['payload']['base64'];
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/audios/', $fileBase64, $contentName, 'public');
            }//Se for a API eHOST
            else if($apiName == 'api-ehost') {
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/audios/', $payloadMessage['payload']['file'], $contentName, 'public');
            }
            //Se for a API da 360Dialog
            else if($apiName == '360Dialog') {
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/audios/'.$contentName, $media);
                //Pega o caminho do arquivo
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/audios/'.$message->mes_content_name);
                //Converte o arquivo em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $media, $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/audios/', $fileBase64, $contentName, 'public');
                //Remove o arquivo localmente
                unlink($filePath);

                //Remove a mídia do servidor da 360Dialog
                $communicatorController->deleteMedia($channel, $payloadMessage['payload']['mediaId']);
            }
            //Se for da GUPSHUP ou Z-API
            else {
                //Caso o contato tenha enviado o áudio para o operador
                if(isset($payloadMessage['payload']['url']))
                {
                    //Pega o conteúdo do arquivo
                    $response = Http::get($payloadMessage['payload']['url']);
                    $fileBase64 = $this->utilsController->convertToBase64($payloadMessage['payload']['url'], $response->body(), $message->type_message_chat_id);
                    //Salva no diretório
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/audios/', $fileBase64, $contentName, 'public');
                    //Storage::disk('public')->put('chats/chat'.$chatId.'/audios/'.$contentName, $response->body());
                }//Caso o operador tenha enviado o áudio para o contato
                else
                {
                    if($channelData->cha_api_official == true) {
                        //se o canal está associado a API da GupShup
                        if($channelData->api_communication_id == 1) {
                            //Log::debug('entrou no processamento de audio da gupshup');

                            $payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/audios/', 'pending'.$contentName);
                            FFMpeg::fromDisk('public')
                                ->open('chats/chat'.$chatId.'/audios/'.'pending'.$contentName)
                                ->export()
                                //->toDisk('converted_songs')
                                ->inFormat(new \FFMpeg\Format\Audio\Mp3)
                                ->toDisk('spaces')
                                ->withVisibility('public')
                                ->save('public/chats/chat'.$chatId.'/audios/'.preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.mp3');
                            
                            //Troca o nome para o nome com a extensão correta
                            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.mp3';
                            $message->mes_content_name = $contentName;

                        } //Se for a Cloud API
                        else if($channelData->api_communication_id == 4) {
                            $payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/audios/', 'pending'.$contentName);

                            FFMpeg::fromDisk('public')
                                ->open('chats/chat'.$chatId.'/audios/'.'pending'.$contentName)
                                ->export()
                                //->toDisk('converted_songs')
                                ->inFormat(new \FFMpeg\Format\Audio\Mp3)
                                ->toDisk('local')
                                ->save('public/chats/chat'.$chatId.'/audios/'.preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.mp3');

                            $originalFilePath = storage_path('app/public/chats/chat'.$chatId.'/audios/'.preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.mp3');
                            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.ogg';
                            $message->mes_content_name = $contentName;
                            $finalFilePath = storage_path('app/public/chats/chat'.$chatId.'/audios/'.$contentName);
                            
                            $output = shell_exec('ffmpeg -i '.$originalFilePath.' -c:a libopus -b 19.1k -ac 1 -r 16k '.$finalFilePath);
                            $media = file_get_contents($finalFilePath);
                            $fileBase64 = $this->utilsController->convertToBase64($finalFilePath, $media, $message->type_message_chat_id);
                            Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/audios/', $fileBase64, $contentName, 'public');
                            /*FFMpeg::fromDisk('public')
                                ->open('chats/chat'.$chatId.'/audios/'.'pending'.$contentName)
                                ->export()
                                //->toDisk('converted_songs') 
                                ->inFormat(new \FFMpeg\Format\Video\X264('aac', 'libx264'))
                                ->save('chats/chat'.$chatId.'/audios/'.preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.aac');
                            */
                        }//Se a mensagem foi enviada pela API Oficial (360 Dialog)
                        else if($channelData->api_communication_id == 3) {
                            Log::debug('audio da 360');
                            $payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/audios/', 'pending'.$contentName);
                            FFMpeg::fromDisk('public')
                                ->open('chats/chat'.$chatId.'/audios/'.'pending'.$contentName)
                                ->export()
                                //->toDisk('converted_songs')
                                ->inFormat(new \FFMpeg\Format\Video\X264('aac', 'libx264'))
                                ->toDisk('spaces')
                                ->withVisibility('public')
                                ->save('public/chats/chat'.$chatId.'/audios/'.preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.aac');
                            
                            //Troca o nome para o nome com a extensão correta
                            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.aac';
                            $message->mes_content_name = $contentName;
                        }   
                    }
                    else {
                        //Log::debug('envio de áudio wppconnect');
                        //Se for o canal da Wppconnect, ZApi ou API WA
                        if($channelData->api_communication_id == 2 || $channelData->api_communication_id == 5 || $channelData->api_communication_id == 7) {
                            $payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/audios/', 'pending'.$contentName);
                            //Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/audios/', $payloadMessage['data']->file, 'pending'.$contentName, 'public');

                            FFMpeg::fromDisk('public')
                                ->open('chats/chat'.$chatId.'/audios/'.'pending'.$contentName)
                                ->export()
                                //->toDisk('converted_songs')
                                ->inFormat(new \FFMpeg\Format\Audio\Mp3)
                                ->toDisk('spaces')
                                ->withVisibility('public')
                                ->save('public/chats/chat'.$chatId.'/audios/'.$contentName);
                        } // Se for o canal da  API ZAP ou eHost
                        else if($channelData->api_communication_id == 6 || $channelData->api_communication_id == 8) {
                            $payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/audios/', 'pending'.$contentName);

                            FFMpeg::fromDisk('public')
                                ->open('chats/chat'.$chatId.'/audios/'.'pending'.$contentName)
                                ->export()
                                //->toDisk('converted_songs')
                                ->inFormat(new \FFMpeg\Format\Audio\Mp3)
                                ->toDisk('local')
                                ->save('public/chats/chat'.$chatId.'/audios/'.preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.mp3');

                            $originalFilePath = storage_path('app/public/chats/chat'.$chatId.'/audios/'.preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.mp3');
                            
                            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.ogg';
                            $message->mes_content_name = $contentName;
                            $finalFilePath = storage_path('app/public/chats/chat'.$chatId.'/audios/'.$contentName);
                            
                            $output = shell_exec('ffmpeg -i '.$originalFilePath.' -c:a libopus -b:a 128k '.$finalFilePath);
                            $media = file_get_contents($finalFilePath);
                            $fileBase64 = $this->utilsController->convertToBase64($finalFilePath, $media, $message->type_message_chat_id);
                            Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/audios/', $fileBase64, $contentName, 'public');
                        }
                    }
                }
            }

            $newMessageData = array(
                'chat_id' => $chatId, 
                'api_message_id' => $message->api_message_id,
                'type_message_chat_id' => 2, 
                'mes_content_type' => $payloadMessage['payload']['contentType'], 
                'mes_content_name' => $contentName, 
                'mes_url' => isset($payloadMessage['payload']['url']) ? $payloadMessage['payload']['url'] : null,  
                'created_at' => $dateTimeNow,
                'senderId' => $senderId,
                'type_user_id' => $typeUserId,
                'mes_private' => $privateMessage,
                'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                'answered_message_id' => $message->answered_message_id,
                'mes_phone_channel_received_message' => isset($message->mes_phone_channel_received_message)? $message->mes_phone_channel_received_message : null,
                'mes_phone_channel_sent_message' => isset($message->mes_phone_channel_sent_message)? $message->mes_phone_channel_sent_message : null,
            );
        }
        else if($payloadMessage['type'] == 'video')
        {
            $message->type_message_chat_id = 4;
            $message->api_message_id = isset($payloadMessage['id']) ? $payloadMessage['id'] : null;
            $message->mes_url = isset($payloadMessage['payload']['url']) ? $payloadMessage['payload']['url'] : null;
            $message->mes_content_type = $payloadMessage['payload']['contentType'];
            $message->service_id = $serviceId;
            $message->mes_caption = isset($payloadMessage['payload']['caption'])? $payloadMessage['payload']['caption'] : null;

            $dataSplit = explode("/", $payloadMessage['payload']['contentType']);
            
            $extensionContent = '.'.$dataSplit[1];

            //Deixa apenas os números no nome do arquivo
            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).$extensionContent;

            $message->mes_content_name = $contentName;
            if($apiName == 'wppconnect') {
                $image = base64_decode($payloadMessage['payload']['base64']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/videos/'.$contentName, $image);
            }
            else if($apiName == 'cloudApiWhatsapp') {
                //Obtém o link da imagem
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                //Obtém os dados da mídia em base64
                $mediaData = $communicatorController->downloadMedia($channel, $media['url']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/videos/'.$contentName, $mediaData->body());
                //Pega a mídia do arquivo
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/videos/'.$message->mes_content_name);
                //Converte a mídia em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $mediaData->body(), $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/videos/', $fileBase64, $contentName, 'public');
                //Remove a mídia localmente
                unlink($filePath);
            }
            else if($apiName == 'apizap' || $apiName == 'api-wa') {
                $typeMedia = explode('/', $payloadMessage['payload']['contentType']);
                $fileBase64 = 'data:application/' . $typeMedia[1] . ';base64,' .$payloadMessage['payload']['base64'];
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/videos/', $fileBase64, $contentName, 'public');
            } //Se for a API eHOST
            else if($apiName == 'api-ehost') {
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/videos/', $payloadMessage['payload']['file'], $contentName, 'public');
            }
            else if($apiName == '360Dialog') {
                $media = $communicatorController->getMedia($channel, $payloadMessage['payload']['mediaId']);
                Storage::disk('public')->put('chats/chat'.$chatId.'/videos/'.$contentName, $media);
                //Pega o caminho do arquivo
                $filePath = storage_path('app/public/chats/chat'.$message->chat_id.'/videos/'.$message->mes_content_name);
                //Converte o arquivo em base64
                $fileBase64 = $this->utilsController->convertToBase64($filePath, $media, $message->type_message_chat_id);
                //Salva no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/videos/', $fileBase64, $contentName, 'public');
                //Remove o arquivo localmente
                unlink($filePath);

                //Remove a mídia do servidor da 360Dialog
                $communicatorController->deleteMedia($channel, $payloadMessage['payload']['mediaId']);
            } //Se for da GUPSHUP ou Z-API
            else {
                //Caso o contato tenha enviado um vídeo para o operador
                if(isset($payloadMessage['payload']['url']))
                {
                    //Pega o conteúdo do arquivo
                    $response = Http::get($payloadMessage['payload']['url']);
                    $fileBase64 = $this->utilsController->convertToBase64($payloadMessage['payload']['url'], $response->body(), $message->type_message_chat_id);
                    //Salva no diretório
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/videos/', $fileBase64, $contentName, 'public');
                    //Storage::disk('public')->put('chats/chat'.$chatId.'/videos/'.$contentName, $response->body());
                }
                else
                {
                    Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatId.'/videos/', $payloadMessage['data']->file, $contentName, 'public');
                    //$payloadMessage['data']->file->storeAs('public/chats/chat'.$chatId.'/videos/', $contentName);
                }
            }

            $newMessageData = array(
                'chat_id' => $chatId, 
                'api_message_id' => $message->api_message_id,
                'type_message_chat_id' => 4, 
                'mes_content_type' => $payloadMessage['payload']['contentType'], 
                'mes_content_name' => $contentName,
                'mes_caption' => isset($payloadMessage['payload']['caption'])? $payloadMessage['payload']['caption'] : null,
                'mes_url' => isset($payloadMessage['payload']['url']) ? $payloadMessage['payload']['url'] : null,  
                'created_at' => $dateTimeNow,
                'senderId' => $senderId,
                'type_user_id' => $typeUserId,
                'mes_private' => $privateMessage,
                'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                'answered_message_id' => $message->answered_message_id,
                'mes_phone_channel_received_message' => isset($message->mes_phone_channel_received_message)? $message->mes_phone_channel_received_message : null,
                'mes_phone_channel_sent_message' => isset($message->mes_phone_channel_sent_message)? $message->mes_phone_channel_sent_message : null,
            );
        } //Se for um contato compartilhado
        else if($payloadMessage['type'] == 'vcard') {
            $message->type_message_chat_id = 6;
            //Se uma mensagem recebida de algum contato, pega o id dessa mensagem, caso contrário pega o id da mensagem enviada (se não 
            //é mensagem recebida, é mensagem enviada)
            $message->api_message_id = isset($payloadMessage['id']) ? $payloadMessage['id'] : null;
            $message->mes_contact_name = $payloadMessage['payload']['contactName'];
            $message->mes_contact_phone_number = $payloadMessage['payload']['contactPhoneNumber'];

            $newMessageData = array(
                'type_message_chat_id' => 6,
                'api_message_id' => $message->api_message_id,
                'mes_contact_name' => $payloadMessage['payload']['contactName'], 
                'mes_contact_phone_number' => $payloadMessage['payload']['contactPhoneNumber'], 
                'created_at' => $dateTimeNow,
                'senderId' => $senderId,
                'type_user_id' => $typeUserId,
                'mes_private' => $privateMessage,
                'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                'answered_message_id' => $message->answered_message_id,
                'mes_phone_channel_received_message' => isset($message->mes_phone_channel_received_message)? $message->mes_phone_channel_received_message : null,
                'mes_phone_channel_sent_message' => isset($message->mes_phone_channel_sent_message)? $message->mes_phone_channel_sent_message : null,
            );
        }
        else if($payloadMessage['type'] == 'location') {
            $message->type_message_chat_id = 8;
            //Se uma mensagem recebida de algum contato, pega o id dessa mensagem, caso contrário pega o id da mensagem enviada (se não 
            //é mensagem recebida, é mensagem enviada)
            $message->api_message_id = isset($payloadMessage['id']) ? $payloadMessage['id'] : null;
            $message->mes_lat = $payloadMessage['payload']['latitude'];
            $message->mes_long = $payloadMessage['payload']['longitude'];
            $message->service_id = $serviceId;

            $newMessageData = array(
                'type_message_chat_id' => 8,
                'api_message_id' => $message->api_message_id,
                'mes_lat' => $payloadMessage['payload']['latitude'],
                'mes_long' => $payloadMessage['payload']['longitude'],
                'created_at' => $dateTimeNow,
                'senderId' => $senderId,
                'type_user_id' => $typeUserId,
                'mes_private' => $privateMessage,
                'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                'answered_message_id' => $message->answered_message_id,
                'mes_phone_channel_received_message' => isset($message->mes_phone_channel_received_message)? $message->mes_phone_channel_received_message : null,
                'mes_phone_channel_sent_message' => isset($message->mes_phone_channel_sent_message)? $message->mes_phone_channel_sent_message : null,
            );
        }
        //Se a mensagem foi salva no banco de dados
        if($message->save()) {   
            //Pega o id da mensagem recém armazenada
            $newMessageData['id'] = $message->id;

            //Se a mensagem é uma RESPOSTA a outra mensagem
            if($message->answered_message_id) {
                $answeredMessage = self::getMessageByApiId($message->answered_message_id);

                if(isset($answeredMessage->quick_message_id) && $answeredMessage->quick_message_id) {
                    $parameters = self::getQuickMessageParameters($answeredMessage->quick_message_id);
                    $answeredMessage->setAttribute('parameters', $parameters);
                } //Se é um template
                else if(isset($answeredMessage->template_id) && $answeredMessage->template_id) {
                    $parameters = $templateController->getParametersTemplateRenamed($answeredMessage->template_id);
                    $answeredMessage->setAttribute('parameters', $parameters);
                }

                if($answeredMessage) {
                    $newMessageData['answeredMessage'] = $answeredMessage;
                }
            }

            //Caso a mensagem tenha sido enviada pelo CONTATO
            if($typeUserId == 2) { 
                //Event::dispatch(new sendMessage($newMessageData, 1));
                $pendingService = false;
                
                //Verifica se tem algum atendimento pendente (comunicação ativa)
                $actionCommunicationService = Action::where('service_id', $serviceId)
                                        ->where('chat_id', $chatId)
                                        ->where('type_action_id', 5)
                                        ->orderBy('created_at', 'desc')
                                        ->first();

                //Verifica se tem algum atendimento pendente
                $lastTransferService = Action::where('service_id', $serviceId)
                                        ->where('chat_id', $chatId)
                                        ->where('type_action_id', 1)
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                
                $pendingService = false;
                //Se existe alguma transferência do atendimento
                if($lastTransferService) {
                    //Se o atendimento ainda não foi capturado por algum operador
                    if($lastTransferService->user_id == null) {
                        $pendingService = true;     
                    }
                }

                //Traz todos os usuários que são GESTORES
                $usersManagers = $this->userController->getUsersByRoles([1, 3]);

                //Caso o atendimento não tenha sido capturado ou o atendidmento esteja em avaliação pelo contato e não tenha sido iniciado por iniciativa do operador (comunicação ativa)
                if((!$lastTransferService || $pendingService || $serviceEvaluation) && !$actionCommunicationService) {
                    //Pega a opção escolhida pelo contato durante a execução do chatbot
                    $actionKey = isset($payloadMessage['payload']['text'])? $payloadMessage['payload']['text'] : null;
                    
                    //Se o atendimento NÃO estiver pendente
                    if($pendingService == false) {
                        $chatbotData = null;

                        //Se NÃO for uma mensagem de AVALIAÇÃO DE ATENDIMENTO
                        if(!$serviceEvaluation) {
                            //Traz os dados do atendimento
                            $serviceData = $this->serviceController->getServiceById($serviceId);
                        }
                        else {
                            //Traz os dados do atendimento
                            $serviceData = $this->serviceController->getServiceById($serviceEvaluation['id']);
                        }
                        
                        if($serviceData) {
                            //Se NÃO for um atendimento associado a uma campanha e nem associado a um discador
                            if(!$serviceData['campaign_id'] && !$serviceData['dialer_fowarding_setting_id']) {
                                //Log::debug('não é de campanha');
                                $chatbotData = $chatbotController->getChatbotByChannel($serviceData['channel_id']);
                            } //Se for atendimento associado a uma campanha
                            else if($serviceData['campaign_id']) {
                                $chatbotData = $chatbotController->getChatbotCampaign($serviceData['campaign_id'], $serviceData['channel_id']);
                            }
                            else if($serviceData['dialer_fowarding_setting_id']) {
                                $chatbotData = $chatbotController->getChatbotFowardingSetting($serviceData['dialer_fowarding_setting_id']);
                            }
                        }
                        
                        $chatbotController->autoAttendant($chatId, $senderId, $actionKey, $newMessageData, false, $serviceEvaluation, $serviceId, true, $chatbotData);

                        //Se existir algum GESTOR
                        if($usersManagers) {
                            //Envia a mensagem para o GESTOR
                            foreach ($usersManagers as $userManager) {
                                $this->eventController->sendMessageChat($newMessageData, $userManager->id);
                            }
                        }
                    } //Se o atendimento estiver pendente (Aguardando algum operador capturar o atendimento)
                    else {
                        //Pega a última ação
                        $lastAction =  Action::where('chat_id', $chatId)
                                            ->orderBy('created_at', 'desc')
                                            ->first();
                        //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
                        $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                                                        ->join('users', 'man_users_departments.user_id', 'users.id')
                                                        ->where('department_id', $lastAction->department_id)
                                                        ->where('users.status', 'A') //Onde o status do usuário é ativo
                                                        ->where('use_status', 'A')
                                                        ->get();
                        //Caso exista algum usuário no departamento
                        if($usersSendEvent) {
                            //Envia a mensagem para os operadores lotados no referido departamento
                            foreach ($usersSendEvent as $userSendEvent) {
                                $this->eventController->sendMessageChat($newMessageData, $userSendEvent->user_id);
                            }
                        }
                    }
                }
                else {
                    //Pega a última ação
                    $lastAction =  Action::where('chat_id', $chatId)
                                        ->orderBy('created_at', 'desc')
                                        ->first();

                    //Caso algum operador já tenha capturado o atendimento 
                    if($lastAction->user_id) {
                        //Envia um evento em tempo real para o frontend
                        $this->eventController->sendMessageChat($newMessageData, $lastAction->user_id);

                        //Caso o OPERADOR também é GESTOR, retira o mesmo da lista dos que irão receber a notificação, já que o mesmo já recebeu a mesma
                        $usersManagers = $usersManagers->except($lastAction->user_id);
                    }
                    else {
                        //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
                        $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                                                        ->join('users', 'man_users_departments.user_id', 'users.id')
                                                        ->where('department_id', $lastAction->department_id)
                                                        ->where('users.status', 'A') //Onde o status do usuário é ativo
                                                        ->where('use_status', 'A')
                                                        ->get();

                        //Caso exista algum usuário no departamento
                        if($usersSendEvent) {
                            //Envia a mensagem para os operadores lotados no referido departamento
                            foreach ($usersSendEvent as $userSendEvent) {
                                $this->eventController->sendMessageChat($newMessageData, $userSendEvent->user_id);
                            }
                        }
                    }

                    //Caso exista algum usuário GESTOR
                    if($usersManagers) {
                        //Envia a mensagem para o GESTOR
                        foreach ($usersManagers as $userManager) {
                            $this->eventController->sendMessageChat($newMessageData, $userManager->id);
                        }
                    }
                }
            }
            else
            {   
                //Log::debug('id do senderId');
                //Log::debug($senderId); 
                //Se é uma mensagem enviada por um USUÁRIO EXTERNO (Via celular ou WhatsApp Web)
                if($senderId == 3) {
                    //Pega a última ação
                    $lastAction =  Action::where('chat_id', $chatId)
                                    ->orderBy('created_at', 'desc')
                                    ->first();

                    $chat = self::getChatById($chatId);
                    $newMessageData['contactId'] = $chat->contact_id;
                    
                    Log::debug('$lastAction');
                    Log::debug($lastAction);
                    //Caso algum operador já tenha capturado o atendimento 
                    if($lastAction->user_id) {
                        //Envia um evento em tempo real para o frontend
                        $this->eventController->sendMessageChat($newMessageData, $lastAction->user_id);
                    }

                    //Traz todos os usuários que são GESTORES
                    $usersManagers = $this->userController->getUsersByRoles([1, 3]);

                    //Caso exista algum usuário GESTOR
                    if($usersManagers) {
                        //Envia a mensagem para o GESTOR
                        foreach ($usersManagers as $userManager) {
                            $this->eventController->sendMessageChat($newMessageData, $userManager->id);
                        }
                    }
                }
                
                $message->setAttribute('newMessageData', $newMessageData);
                return $message;
            }
            
        }
    }

    public function sendMessage(Request $request)
    {   
        $channelController = New ChannelController();
        $templateController = New TemplateController();
        $utils = new UtilsController();

        Log::debug('request sendMessage');
        Log::debug($request);

        //Presenta se a mensagem foi enviada com sucesso ou não
        $sendSuccess = false;
        $contact = Contact::find($request->contactId);
        $chat = Chat::where('contact_id', $request->contactId)->first();
        $typeUserId = $request->typeUserId; //Operador ou Robô
        $privateMessage = $request->privateMessage; //Indica se a mensagem é privada ou não
        $destination = $contact->con_phone;
        $senderId = isset(Auth::user()->id)? Auth::user()->id : $request->senderId;
        $actionId = $request->actionId;
        $templateId = null;
        $quickMessageId = null;
        $quickMessageData = null;
        $messageDataFormatted = [];
        $typeFormatMessageId = null;

        //Se o template é proveniente do robô
        $templateData = $templateController->getTemplate($request['template_id']);
        if($templateData) {
            $templateData['tem_code'] = $templateData['language']['tem_code'];
            $request['message'] = $templateData['tem_body'];
        }
        else {
            if($request['quick_message_id']) {
                $quickMessageData = self::getQuickMessage($request['quick_message_id']);
            }
        }

        //Traz o último atendimento realizado para o contato
        $serviceOpen = Service::where('chat_id', $chat->id)
                                ->orderBy('created_at', 'desc')
                                ->first();
        //Pega o canal associado ao atendimento
        $channel = $channelController->getChannel($serviceOpen->channel_id); 

        //Caso a mensagem a ser enviada seja uma mídia
        if($request->file())
        {
            //Log::debug($request->file->getMimeType());
            $dataSplit = explode("/", $request->file->getMimeType());
            //Pega o tipo de arquivo de arquivo upado
            if($dataSplit[0] == 'application') {   
                $payloadMessage['type'] = 'file';
                $payloadMessage['payload']['name'] = $request->file->getClientOriginalName();
                $payloadMessage['payload']['contentType'] = $request->file->getMimeType();
            }
            else if($dataSplit[0] == 'image') {
                $payloadMessage['type'] = 'image';
                $payloadMessage['payload']['name'] = $request->file->getClientOriginalName();
                $payloadMessage['payload']['contentType'] = $request->file->getMimeType();
            }
            //Se for um áudio
            else if($dataSplit[1] == 'webm') {
                $payloadMessage['type'] = 'audio'; 
                $payloadMessage['payload']['contentType'] = 'audio/mpeg';
            }
            else {
                $payloadMessage['type'] = $dataSplit[0];
                $payloadMessage['payload']['contentType'] = $request->file->getMimeType();
            }
            $payloadMessage['data'] = $request;
            /*
            //Pega a extensão do arquivo
            $extensionContent = $request->file->getClientOriginalExtension();
            //Deixa apenas os números no nome do arquivo
            $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.'.$extensionContent;
            $request->file->storeAs('public/chats/chat'.$chat->id.'/images/', $contentName);
            */
        } //Se for uma mensagem TEMPLATE
        else if($request['templateData'] || $templateData) {
            //Se o template não for proveniente do chatbot, mas sim, enviado por um operador
            if(!$templateData) {
                $templateData = json_decode($request['templateData'], true);
                //Se houver parâmetros na mensagem rápida
                if(count($templateData['parameters']) > 0) {
                    foreach($templateData['parameters'] as $key => $parameter) {
                        $messageDataFormatted[$key]['quick_message_id'] = $parameter['template_id'];
                        $messageDataFormatted[$key]['type_parameter_id'] = $parameter['type_parameter_id'];
                        $messageDataFormatted[$key]['type_button_id'] = $parameter['type_button_id'];
                        $messageDataFormatted[$key]['content'] = $parameter['tem_content'];
                        $messageDataFormatted[$key]['url'] = $parameter['tem_url'];
                        $messageDataFormatted[$key]['phone_number'] = $parameter['tem_phone_number'];
                        $messageDataFormatted[$key]['media_name'] = $parameter['tem_media_name'];
                    }
                }
            }
            $templateId = $templateData['id'];
            $payloadMessage['type'] = "text";
            $templateDataChanged = self::replaceTemplateMessageTags($request->message, $contact, $senderId, $chat->id, null, $templateData);
            $payloadMessage['payload']['text'] = $templateDataChanged['messageChanged']; //Texto com as tags substituídas pelos respectivos valores
            $payloadMessage['payload']['text'] =  $utils->changeParagraphContent($payloadMessage['payload']['text']);
            $payloadMessage['payload']['text'] =  $utils->convertTextWhatsappFormat($payloadMessage['payload']['text']);
        }
        else //Se for uma mensagem de texto COMUM
        {   
            //Se for uma mensagem rápida
            if($request['quickMessageData'] || $quickMessageData) {
                if(!$quickMessageData) {
                    $quickMessageData = json_decode($request['quickMessageData'], true);
                    //Se houver parâmetros na mensagem rápida
                    if(count($quickMessageData['parameters']) > 0) {
                        foreach($quickMessageData['parameters'] as $key => $parameter) {
                            $messageDataFormatted[$key]['quick_message_id'] = $parameter['quick_message_id'];
                            $messageDataFormatted[$key]['type_parameter_id'] = $parameter['type_parameter_id'];
                            $messageDataFormatted[$key]['type_button_id'] = $parameter['type_button_id'];
                            $messageDataFormatted[$key]['content'] = $parameter['qui_content'];
                            $messageDataFormatted[$key]['url'] = $parameter['qui_url'];
                            $messageDataFormatted[$key]['phone_number'] = $parameter['qui_phone_number'];
                            $messageDataFormatted[$key]['media_name'] = $parameter['qui_media_name'];
                            $messageDataFormatted[$key]['media_original_name'] = $parameter['qui_media_original_name'];
                        }
                    }
                }
                $typeFormatMessageId = $quickMessageData['type_format_message_id'];
                $quickMessageId = $quickMessageData['id'];
                if(isset($quickMessageData['qui_content'])) {
                    $request['message'] = $quickMessageData['qui_content'];
                }
            }
            Log::debug('$typeFormatMessageId verificação de valor');
            Log::debug($typeFormatMessageId);
            $payloadMessage['type'] = "text";
            $payloadMessage['payload']['text'] = self::replaceQuickMessageTags($request->message, $contact, $senderId, $chat->id, null);
            $payloadMessage['payload']['text'] =  $utils->changeParagraphContent($payloadMessage['payload']['text']);
            $payloadMessage['payload']['text'] =  $utils->convertTextWhatsappFormat($payloadMessage['payload']['text']);
        }
        //Pega no número de telefone do canal que está enviando a mensagem
        $payloadMessage['mesPhoneChannelSentMessage'] = $channel['cha_phone_ddi'].$channel['cha_phone_number'];
        //Se for uma responsta do operador/gestor a uma mensagem específica
        $payloadMessage['answeredMessageId'] = $request['replyApiMessageId']? $request['replyApiMessageId'] : NULL;

        //Grava a mensagem no banco de dados
        $messageStored = self::storeMessage($chat->id, $typeUserId, $senderId, $payloadMessage, $actionId, null, $privateMessage, $channel, null, $templateId, $quickMessageId, $typeFormatMessageId);
        //Caso seja uma mensagem template
        if($messageStored['template_id']) {
            $messageStored['templateData'] = $templateData;
            //Valores das parâmetros (valores das variáveis, etc.)
            $messageStored['componentsData'] = $templateDataChanged['componentsData'];
        } //Se for uma mensagem
        else if($quickMessageId) {
            $messageStored['quickMessageData'] = $quickMessageData;
            $messageStored['quickMessageWithParameters'] = $quickMessageData['parameters']? true : false;
        }

        //Traz todos os usuários que são GESTORES
        $usersManagers = $this->userController->getUsersByRoles([1, 3]);

        //Caso não seja uma mensagem privada (do gestor para o operador)
        if($privateMessage == 'false') {
            //Envia a mensagem via API para o número de destino
            $response = $this->communicatorController->sendMessageApi($channel, $destination, $messageStored);
            //Log::debug('resposta da API que enviou a mensagem');
            //Log::debug($response);

            //Se a mensagem foi enviada, salva o id no payload
            if((isset($response['status']) && $response['status'] == 'success')) {
                //Atualiza a mensagem com o id da mensagem na API
                ChatMessage::find($messageStored->id)
                            ->update([
                                'api_message_id' => $response['message']['id']
                            ]);
                
                //Se for a API eHost
                if($channel->api_communication_id == 8) {
                    //Coloca o status como ENVIADO
                    self::setStatusMessage($messageStored['id'], 2);
                } //Se for a API WA
                /*else if($channel->api_communication_id == 7) {
                    //Coloca o status como ENTREGUE
                    self::setStatusMessage($messageStored['id'], 3);
                }*/
                
                $sendSuccess = true;
            } //Se o canal estiver desconectado no whatsapp
            else if(isset($response['status']) && $response['status'] == 'Disconnected') {
                $channelController = new ChannelController();
                //Dados do canal
                $channelData = new Request([
                    'channelId'   => $channel['id'],
                    'sessionName' => mb_substr($channel['cha_session_name'], 2),
                    'statusId' => 'I',
                ]);
                //Desabilita o canal 
                $channelController->updateStatusChannel($channelData);

                //Seta a mensagem como enviada com erro
                self::setStatusMessage($messageStored['id'], 4);
            } 
            else if(isset($response['status']) && $response['status'] == 'Template Unavailable') {
                Log::debug('entrou aqui template Unavailable');
                //Seta a mensagem como enviada com erro
                self::setStatusMessage($messageStored['id'], 4);
                
                $statusMessage = "Template não disponível no momento. Caso o template tenha acabado de ter sido aprovado, aguarde alguns instantes e tente novamente.";
                $this->eventController->statusMessage($statusMessage, true, Auth::user()->id);
            }
            //Se houve algum erro ao enviar a mensagem (seja por falta de internet ou erro na API)
            else if(isset($response['status']) && $response['status']) {
                //Seta a mensagem como enviada com erro
                self::setStatusMessage($messageStored['id'], 4);
            } //Se houve algum erro ao enviar uma mensagem template
        } //Se for uma mensagem privada (quem não vai ser enviada para o cliente e, portanto, não será utilizada a API do Whatsapp)
        else {
            //Marca a mensagem como enviada
            $sendSuccess = true;
        }

        //Log::debug('dados para realtime');
        //Log::debug($messageStored['newMessageData']);

        //Caso a mensagem tenha sido enviada por algum OPERADOR e enviada com SUCESSO
        if($typeUserId == 1 && $sendSuccess== true) {

            //Caso exista algum GESTOR do sistema
            if($usersManagers) {
                $messageData = $messageStored['newMessageData'];
                $messageData['contactId'] = $contact->id;
                //Envia a mensagem para o GESTOR
                foreach ($usersManagers as $userManager) {
                    $this->eventController->sendMessageChat($messageData, $userManager->id);
                }
            }
        } //Caso a mensagem tenha sido enviada pelo GESTOR e enviada com SUCESSO
        else if($typeUserId == 4 && $sendSuccess == true) {

            //Pega a última ação
            $lastAction =  Action::where('chat_id', $chat->id)
                                ->orderBy('created_at', 'desc')
                                ->first();

            $messageData = $messageStored['newMessageData'];
            $messageData['contactId'] = $contact->id;
            
            //Caso algum OPERADOR já tenha capturado o atendimento e esse operador não seja um GESTOR
            if(isset($lastAction->user_id) && !$usersManagers->contains('id', $lastAction->user_id)) {
                //Envia um evento em tempo real para o frontend para o operador que capturou
                $this->eventController->sendMessageChat($messageData, $lastAction->user_id);
            }
            /*else {
                $this->eventController->sendMessageChat($messageData, Auth::user()->id);
            }*/
        }
        else {
            //Se a mensagem foi enviada com sucesso
            if($sendSuccess == true) {
                //Pega a última ação
                $lastAction =  Action::where('chat_id', $chat->id)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
                //Caso o atendimento tenha alguma ação envolvida (NÃO esteja em autoatendimento)
                if($lastAction) {
                    $messageData = $messageStored['newMessageData'];
                    $messageData['contactId'] = $contact->id;

                    //Traz todos os usuários que fazem parte do departamento para onde o chat foi transferido
                    $usersSendEvent = UserDepartment::select('man_users_departments.user_id')
                                                    ->join('users', 'man_users_departments.user_id', 'users.id')
                                                    ->where('department_id', $lastAction->department_id)
                                                    ->where('users.status', 'A') //Onde o status do usuário é ativo
                                                    ->where('use_status', 'A')
                                                    ->get();

                    //Caso exista algum usuário no departamento
                    if($usersSendEvent) {
                        //Envia a mensagem para os operadores lotados no referido departamento
                        foreach ($usersSendEvent as $userSendEvent) {
                            $this->eventController->sendMessageChat($messageData, $userSendEvent->user_id);
                        }
                    }
                }
            }
        }

        $messageDataValue = $messageStored->newMessageData;

        Log::debug('$messageStored');
        Log::debug($messageStored);

        //Guarda a informação se a mensagem foi enviada ou não
        $messageDataValue['sendSuccess'] = $sendSuccess;
        $messageDataValue['service_id'] = $serviceOpen->id;
        $messageDataValue['chat_id'] = $chat->id;
        $messageDataValue['id'] = $messageStored->id; //Id da mensagem enviada
        $messageDataValue['type_user_id'] = $typeUserId;
        $messageDataValue['template_id'] = isset($messageStored['template_id'])? $messageStored['template_id'] : null;
        $messageDataValue['quick_message_id'] = $quickMessageId;
        $messageDataValue['sender_id'] = $senderId;
        if($sendSuccess == true) {
            //Se for a API eHost
            if($channel->api_communication_id == 8) {
                $messageDataValue['status_message_chat_id'] = 2; //Seta o status da mensagem como ENVIADO
            } //Se for a API-WA
            /*else if($channel->api_communication_id == 7) {
                $messageDataValue['status_message_chat_id'] = 2; //Seta o status da mensagem como ENTREGUE
            }*/ //Demais API's
            else {
                $messageDataValue['status_message_chat_id'] = 1; //Seta o status da mensagem como Enfileirado
            }
        }
        //$messageDataValue['status_message_chat_id'] = $sendSuccess == true && $channel->api_communication_id == 7? 3 : 1; //Se for a API WA, coloca como entregue, senão, coloca a mensagem inicialmente com status enfileirada
        $messageDataValue['loadingSpinner'] = false;
        $messageDataValue['parameters'] = $messageDataFormatted;
        $messageDataValue['api_message_id'] = isset($response['message']['id'])? $response['message']['id'] : NULL;
        
        
        return response()->json([
            'newMessageData'=> $messageDataValue
        ], 201);

    }

    public function resendMessage(Request $request)
    {
        $templateController = new TemplateController();
        Log::debug('dados de reenvio');
        Log::debug($request);

        $channelController = new ChannelController();
        $sendSuccess = false;
        $isTemplateMessage = false;
        //Traz os dados do atendimento para poder ter acesso ao id do canal utilizado no atendimento
        $service = $this->serviceController->getServiceById($request->messageData['serviceId']);
        //Traz os dados do canal
        $channel = $channelController->getChannel($service->channel_id);
        $chatData = Chat::find($request->messageData['chatId']);
        //Traz o contato que receberá a mensagem
        $contactDestination = $this->contactController->getContactById($chatData->contact_id);
        
        //Se NÃO for uma mensagem template
        if(!$request->messageData['templateId']) {
            //Monta o objeto conterá os dados da mensagem
            $messageData['type_message_chat_id'] = $request->messageData['typeMessageId'];
            $messageData['chat_id'] = $request->messageData['chatId'];
            $messageData['mes_content_name'] = isset($request->messageData['contentName'])? $request->messageData['contentName'] : null;
            $messageData['mes_message'] = isset($request->messageData['msg'])? $request->messageData['msg'] : null;
            //$messageData['senderId'] = 3;
            $messageData['created_at'] = $request->messageData['time'];
            $messageData['type_user_id'] = $request->messageData['typeUserId'];
            $messageData['mes_private'] = $request->messageData['privateMessage'];
            $messageData['template_id'] = $request->messageData['templateId'];
            $messageData['status_message_chat_id'] = 1; //Coloca a mensagem inicialmente com status enfileirada

            if($request->messageData['quickMessageId']) {
                $messageData['quick_message_id'] = $request->messageData['quickMessageId'];
                $messageData['quickMessageData'] = self::getQuickMessage($request->messageData['quickMessageId']);
                $messageData['quickMessageWithParameters'] = $messageData['quickMessageData']['parameters']? true : false;
            }
        }
        else {
            $isTemplateMessage = true;
            $templateData = $templateController->getTemplate($request->messageData['templateId']);
            $templateData['tem_code'] = $templateData['language']['tem_code'];
            //Log::debug('templateData');
            //Log::debug($templateData);
            $templateDataChanged = self::replaceTemplateMessageTags($request->messageData['msg'], $contactDestination, $request->messageData['senderId'], $request->messageData['chatId'], null, $templateData);

            $messageData['templateData'] = $templateData;
            //Valores das parâmetros (valores das variáveis, etc.)
            $messageData['componentsData'] = $templateDataChanged['componentsData'];
            $messageData['template_id'] = $request->messageData['templateId'];
        }

        $messageData = (object) $messageData;
        

        Log::debug('objeto enviado para template ou mensagem rápida');
        //Log::debug($messageData);

        //Tenta Enviar a mensagem
        $response = $this->communicatorController->sendMessageApi($channel, $contactDestination->con_phone, $messageData);

        //Log::debug('resposta do resend');
        //Log::debug($response);

        //Se a mensagem foi enviada, salva o id no payload
        if((isset($response['status']) && $response['status'] == 'success')) {
            
            ChatMessage::find($request->messageData['messageId'])
                        ->update([
                            'api_message_id' => $response['message']['id']
                        ]);
            
            $sendSuccess = true;
            self::setStatusMessage($request->messageData['messageId'], 2);
        } //Se o canal estiver desconectado no whatsapp
        else if(isset($response['status']) && $response['status'] == 'Disconnected') {
            $channelController = new ChannelController();
            //Dados do contato
            $channelData = new Request([
                'channelId'   => $channel['id'],
                'sessionName' => mb_substr($channel['cha_session_name'], 2),
                'statusId' => 'I',
            ]);
            //Desabilita o canal
            $channelController->updateStatusChannel($channelData);

            //Seta a mensagem como enviada com erro
            
        } //Se houve algum erro ao enviar a mensagem (seja por falta de internet ou erro na API)
        else if(isset($response['status']) && $response['status'] == 'error') {
            //Seta a mensagem como enviada com erro
            
        }
        else if(isset($response['status']) && $response['status'] == 'Template Unavailable') {
            Log::debug('entrou aqui template Unavailable');
            $statusMessage = "Template não disponível no momento. Caso o template tenha acabado de ter sido aprovado, aguarde alguns instantes e tente novamente.";
            $this->eventController->statusMessage($statusMessage, true, Auth::user()->id);
        }

        //Traz todos os usuários que são GESTORES
        $usersManagers = $this->userController->getUsersByRoles([1, 3]);

        //Caso a mensagem tenha sido enviada por algum OPERADOR e a mensagem tenha sido enviada com sucesso
        if($request->messageData['typeUserId'] == '1' && $sendSuccess) {
            //Caso exista algum GESTOR do sistema
            if($usersManagers) {
                $messageDataResend = (array) $messageData;
                Log::debug('entrou no envio real time');
                Log::debug($messageDataResend);
                $messageDataResend['contactId'] = $chatData->contact_id;


                //Envia a mensagem para o GESTOR
                foreach ($usersManagers as $userManager) {
                    $this->eventController->sendMessageChat($messageDataResend, $userManager->id);
                }
            }
        } //Caso a mensagem tenha sido enviada pelo GESTOR e a mensagem tenha sido enviada com sucesso
        else if($request->messageData['typeUserId'] == '4' && $sendSuccess) {

            //Pega a última ação
            $lastAction =  Action::where('chat_id', $request->messageData['chatId'])
                                ->orderBy('created_at', 'desc')
                                ->first();

            $messageDataResend = (array) $messageData;
            $messageDataResend['contactId'] = $chatData->contact_id;
            
            //Caso algum OPERADOR já tenha capturado o atendimento e esse operador não seja um GESTOR
            if(isset($lastAction->user_id) && !$usersManagers->contains('id', $lastAction->user_id)) {
                //Envia um evento em tempo real para o frontend para o operador que capturou
                $this->eventController->sendMessageChat($messageDataResend, $lastAction->user_id);
            }
            /*else {
                $this->eventController->sendMessageChat($messageData, Auth::user()->id);
            }*/
        }


        
        $resendMessageData = $request->messageData;
        $resendMessageData['sendSuccess'] = $sendSuccess;
        if($sendSuccess == true) {
            $resendMessageData['statusMessageChatId'] = 1;
        }
        else {
            $resendMessageData['statusMessageChatId'] = 4;
        }
        $resendMessageData['isTemplateMessage'] = $isTemplateMessage;

        return response()->json([
            'resendMessageData'=> $resendMessageData
        ], 201);
    }

    //Atualiza o status da mensagem
    public function setStatusMessage($messageId, $statusId)
    {
        $message = ChatMessage::find($messageId);
        $message->status_message_chat_id = $statusId;

        $message->save();
    }

    //Substitui as tags das mensagens rápidas pelos valores correspondentes
    public function replaceQuickMessageTags($message, $contact, $senderId, $chatId, $campaignId=null, $departmentId=null)
    {
        $messageChanged = "";
        $messageChanged = $message;
        $operator = User::find($senderId);
        $service = self::getCurrentServiceChat($chatId);
        if($contact) {
            $messageChanged = str_replace("%nome%", $contact->con_name, $messageChanged);    
            $messageChanged = str_replace("%cpf%", $contact->con_cpf, $messageChanged);    
            $messageChanged = str_replace("%cnpj%", $contact->con_cnpj, $messageChanged);    
        }
        if($operator) {
            $messageChanged = str_replace("%operador%", $operator->name, $messageChanged);    
            $messageChanged = str_replace("%link_usuario%", $operator->link, $messageChanged);    
        }
        if($service) {
            $messageChanged = str_replace("%protocolo%", $service->ser_protocol_number, $messageChanged);
        } //Se for uma mensagem para campanha
        if($campaignId) {
            $mailingController = new MailingController();
            //Traz os dados do contato no mailing
            $contactMailing = $mailingController->getMailingCampaignContact($campaignId, $contact->id);
            //Se existir alguma mensagem adicional
            if($contactMailing->mai_additional_data_message) {
                $messageChanged = str_replace("%dados_adicionais%", $contactMailing->mai_additional_data_message, $messageChanged);
            }
        }
        if($departmentId) {
            $departmentController = new DepartmentController();
            $department = $departmentController->getDepartmentById($departmentId);
            $messageChanged = str_replace("%departamento%", $department->dep_name, $messageChanged); 
        }
        //Saúda o contato de acordo com o horário do dia
        $messageChanged = str_replace("%saudacao%", self::getSaudacao(), $messageChanged);
        return $messageChanged;
    }
    
    //Substitui as tags do template pelos valores correspondentes e retorna os valores dos parâmetros
    public function replaceTemplateMessageTags($message, $contact, $senderId, $chatId, $campaignId, $templateData)
    {
        $templateDataChanged = null;
        $messageChanged = "";
        $messageChanged = $message;
        $operator = User::find($senderId);
        $service = self::getCurrentServiceChat($chatId);
        $componentsData = [];
        //Para cada parâmetro da mensagem template
        foreach($templateData['parameters'] as $key => $parameter) {
            //Se o parâmetro estiver no BODY
            if($parameter['location_parameter_id'] == 2) {
                //Se o parâmetro for uma variável
                if($parameter['type_parameter_id'] == 1) {
                    //Se a variável for o nome do contato
                    if($parameter['type_variable_id'] == 1) {
                        //Substitui a tag pelo valor correspondente 
                        $messageChanged = str_replace($parameter['tem_variable_tag'], $contact->con_name, $messageChanged);
                        $componentsData['body']['variables']['value'][$key] = $contact->con_name;
                    }
                    //Se a variável for o CPF do contato
                    else if($parameter['type_variable_id'] == 2) {
                        //Substitui a tag pelo valor correspondente 
                        $messageChanged = str_replace($parameter['tem_variable_tag'], isset($contact->con_cpf)? $contact->con_cpf : '', $messageChanged);
                        $componentsData['body']['variables']['value'][$key] = isset($contact->con_cpf)? $contact->con_cpf : '';
                    }
                    //Se a variável for o CNPJ do contato
                    else if($parameter['type_variable_id'] == 3) {
                        //Substitui a tag pelo valor correspondente 
                        $messageChanged = str_replace($parameter['tem_variable_tag'], isset($contact->con_cnpj)? $contact->con_cnpj : '', $messageChanged);
                        $componentsData['body']['variables']['value'][$key] = isset($contact->con_cnpj)? $contact->con_cnpj : '';
                    }
                    //Se a variável for o nome do usuário do sistema
                    else if($parameter['type_variable_id'] == 4) {
                        //Substitui a tag pelo valor correspondente 
                        $messageChanged = str_replace($parameter['tem_variable_tag'], $operator->name, $messageChanged);
                        $componentsData['body']['variables']['value'][$key] = $operator->name;
                    }
                    //Se a variável for uma saudação (bom dia, boa tarde ou boa noite)
                    else if($parameter['type_variable_id'] == 5) {
                        //Substitui a tag pelo valor correspondente 
                        $messageChanged = str_replace($parameter['tem_variable_tag'], self::getSaudacao(), $messageChanged);
                        $componentsData['body']['variables']['value'][$key] = self::getSaudacao();
                    }
                    //Se a variável for um número de protocolo
                    else if($parameter['type_variable_id'] == 6) {
                        //Substitui a tag pelo valor correspondente 
                        $messageChanged = str_replace($parameter['tem_variable_tag'], isset($service->ser_protocol_number)? $service->ser_protocol_number : '', $messageChanged);
                        $componentsData['body']['variables']['value'][$key] = isset($service->ser_protocol_number)? $service->ser_protocol_number : '';
                    }
                    //Se a variável for um número de protocolo
                    else if($parameter['type_variable_id'] == 7) {
                        if($campaignId) {
                            $mailingController = new MailingController();
                            //Traz os dados do contato no mailing
                            $contactMailing = $mailingController->getMailingCampaignContact($campaignId, $contact->id);
                            //Se existir alguma mensagem adicional
                            if($contactMailing->mai_additional_data_message) {
                                //Substitui a tag pelo valor correspondente 
                                $messageChanged = str_replace($parameter['tem_variable_tag'], isset($contactMailing->mai_additional_data_message)? $contactMailing->mai_additional_data_message : '', $messageChanged);
                                $componentsData['body']['variables']['value'][$key] = isset($contactMailing->mai_additional_data_message)? $contactMailing->mai_additional_data_message : '';
                            }
                        }
                    }
                }
            } 
        }
        $templateDataChanged['messageChanged'] = $messageChanged;
        $templateDataChanged['componentsData'] = $componentsData;
        
        return $templateDataChanged;
    }

    public function getSaudacao() 
    {
        date_default_timezone_set('America/Sao_Paulo');
        $hora = date('H');
        if($hora >= 6 && $hora <= 12) {
            return 'Bom dia';
        } 
        else if ($hora > 12 && $hora <= 18) {
            return 'Boa tarde';
        }
        else {
            return 'Boa noite';
        }
            
    }

    //Traz todas as mensagens rápidas ativas
    public function fetchQuickMessages(Request $request) 
    {
        Log::debug('fetchQuickMessages request');
        Log::debug($request);
        $campaignController = new CampaignController();

        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($request['currentPageQuickMessage']-1) * $request['perPageQuickMessage']);

        $quickMessages = QuickMessage::with('parameters', 'chatbots', 'campaigns')
                                    ->select('id', 'qui_title as title', 'qui_content as content', 'type_format_message_id', 'qui_list_name')
                                    ->where('qui_status', 'A');
        //Se não houver tipo de mensagem rápida ou a mensagem rápida for igual a mensagem de usuário
        if($request['typeQuickMessage'] == '' || $request['typeQuickMessage'] == 1) {
            $quickMessages = $quickMessages->where(function ($query) {
                //Onde o usuário que criou é nulo
                $query->whereNull('user_id')
                        //Verifica se a busca coincide com a tag pesquisada
                        ->orWhere('user_id', Auth::user()->id);
            });
        }
        
        if($request['typeQuickMessage'] != '') {
            $quickMessages = $quickMessages->where('type_quick_message_id', $request['typeQuickMessage']);
        } //Se for uma mensagem rápida de campanha
        if(isset($request['campaignId']) && $request['typeQuickMessage'] == 3) {
            //Traz os templates de uma campanha
            $quickMessagesCampaign = $campaignController->getCampaignMessages($request['campaignId']);
            //Extra o id desses templates
            $quickMessagesIdCampaign = $quickMessagesCampaign->pluck('quick_message_id')->toArray();
            //Log::debug('$quickMessagesIdCampaign');
            //Log::debug($quickMessagesIdCampaign);
            //Filtra os templates, excluindo os templates que já fazem parte dessa campanha
            $quickMessages = $quickMessages->whereNotIn('cha_quick_messages.id', $quickMessagesIdCampaign);
        }
        $totalQuickMessages = $quickMessages->count();
        $quickMessages = $quickMessages->orderBy('created_at', 'DESC')
                                        //Busca os registros de acordo com a paginação
                                        ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                        ->take($request['perPageQuickMessage']) //Quantidade de itens trazidos
                                        ->get();


        return response()->json([
            'quickMessages'=> $quickMessages,
            'totalQuickMessages'=> $totalQuickMessages,
        ], 201);

    }

    //Traz uma mensagem rápida pelo ID
    public function getQuickMessage($quickMessageId) {
        $quickMessage = QuickMessage::with('parameters')
                                    ->find($quickMessageId);

        return $quickMessage;
    }

    public function addQuickMessage(Request $request)
    {
        try {
            Log::debug('request addQuickMessage');
            Log::debug($request);

            $quickMessageData =  json_decode($request['messageData'], true);

            $newQuickMessage = new QuickMessage();

            $newQuickMessage->user_id = Auth::user()->id;
            $newQuickMessage->qui_title = $quickMessageData['title'];
            $newQuickMessage->qui_content = $quickMessageData['content'];
            $newQuickMessage->qui_list_name = isset($quickMessageData['listLabel'])? $quickMessageData['listLabel'] : NULL;
            $newQuickMessage->type_quick_message_id = $quickMessageData['typeQuickMessageId'];
            $newQuickMessage->type_format_message_id = $quickMessageData['typeFormatMessage']['id'];
            
            if($newQuickMessage->save()) {
                //Se foi realizado o upload de alguma mídia
                if($request->file()) {
                    //Marca o conteúdo do template como NÃO sendo somente texto
                    $onlyText = false;
                    $dateTimeNow = Carbon::now();
                    $extensionContent = '.'.$request->file->extension();
                    //Deixa apenas os números no nome do arquivo
                    $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).$extensionContent;
                    //$request->file->storeAs('public/quickMessages/quickMessage'.$newQuickMessage->id.'/header/', $contentName);
                    Storage::disk('spaces')->putFileAs('public/quickMessages/quickMessage'.$newQuickMessage->id.'/header/', $request->file, $contentName, 'public');

                    //Se for uma mensagem de TEXTO
                    if($quickMessageData['typeFormatMessage']['id'] == 1) { 
                        //Se for texto com VÍDEO
                        if($extensionContent == '.mp4') {
                            $parameterData['type_parameter_id'] = 5;    
                        } // Se for texto com IMAGEM
                        else {
                            $parameterData['type_parameter_id'] = 2;
                        }
                    }//Se for mensagem de ÁUDIO 
                    else if($quickMessageData['typeFormatMessage']['id'] == 2) {
                        $parameterData['type_parameter_id'] = 3;
                    } //Se for ARQUIVO
                    else if($quickMessageData['typeFormatMessage']['id'] == 3) {
                        $parameterData['type_parameter_id'] = 4;
                    } // Se for um vídeo
                    else if($quickMessageData['typeFormatMessage']['id'] == 4) {
                        $parameterData['type_parameter_id'] = 5;
                    }

                    $parameterData['quick_message_id'] = $newQuickMessage->id;
                    $parameterData['type_button_id'] = null;
                    $parameterData['qui_content'] = null;
                    $parameterData['qui_media_name'] = $contentName;
                    $parameterData['qui_media_original_name'] = $request->file->getClientOriginalName();
                    $parameterData['qui_positives_responses'] = isset($quickMessageData['qui_positives_responses'])? trim($quickMessageData['qui_positives_responses']) : null;
                    $parameterData['qui_negatives_responses'] = isset($quickMessageData['qui_negatives_responses'])? trim($quickMessageData['qui_negatives_responses']) : null;
                    //Salva o parâmetro da mensagem rápida
                    self::storeQuickMessageParameter($parameterData);
                }

                if(isset($quickMessageData['typeButton'])) {
                    //Se for botão de resposta rápida
                    if($quickMessageData['typeButton']['id'] == 1) {
                        //Se foi adicionado algum botão
                        if(count($quickMessageData['buttonLabel']) > 0) {
                            //Para cada botão
                            foreach($quickMessageData['buttonLabel'] AS $buttonLabel) {
                                $parameterData['quick_message_id'] = $newQuickMessage->id;
                                $parameterData['type_parameter_id'] = 1; //Botão
                                $parameterData['type_button_id'] = $quickMessageData['typeButton']['id'];
                                $parameterData['qui_content'] = $buttonLabel;
                                $parameterData['qui_media_name'] = null;
                                //Salva o parâmetro da mensagem rápida
                                self::storeQuickMessageParameter($parameterData);
                            }
                        }
                    }
                    //Se for uma mensagem de ação
                    else if($quickMessageData['typeButton']['id'] == 2) {
                        //Se foi adicionado algum botão
                        if(count($quickMessageData['buttonLabel']) > 0) {
                            foreach($quickMessageData['buttonLabel'] AS $key => $buttonLabel) {
                                $parameterData['quick_message_id'] = $newQuickMessage->id;
                                $parameterData['type_parameter_id'] = 1; //Botão
                                $parameterData['type_button_id'] = $quickMessageData['typeButton']['id'];
                                $parameterData['qui_content'] = $buttonLabel;
                                $parameterData['qui_media_name'] = null;
                                //Se for um botão de URL
                                if($quickMessageData['callActions'][$key]['id'] == 1) {
                                    $parameterData['qui_phone_number'] = null;
                                    $parameterData['qui_url'] = $quickMessageData['buttonUrl'];
                                } //Se for um telefoe
                                else {
                                    $parameterData['qui_url'] = null;
                                    $parameterData['qui_phone_number'] = preg_replace('/\D/', '', $quickMessageData['phoneNumber']);
                                }
                                //Salva o parâmetro da mensagem de ação
                                self::storeQuickMessageParameter($parameterData);
                            }
                        }
                    }
                    //Se for botão de LISTA
                    else if($quickMessageData['typeButton']['id'] == 3) {
                        //Se foi adicionado algum botão
                        if(count($quickMessageData['buttonLabel']) > 0) {
                            //Para cada botão
                            foreach($quickMessageData['buttonLabel'] AS $key => $buttonLabel) {
                                $parameterData['quick_message_id'] = $newQuickMessage->id;
                                $parameterData['type_parameter_id'] = 1; //Botão
                                $parameterData['type_button_id'] = $quickMessageData['typeButton']['id'];
                                $parameterData['qui_content'] = $buttonLabel;
                                $parameterData['qui_media_name'] = null;
                                $parameterData['qui_description'] = isset($quickMessageData['buttonDescription'][$key])? $quickMessageData['buttonDescription'][$key] : NULL;
                                //Salva o parâmetro da mensagem rápida
                                self::storeQuickMessageParameter($parameterData);
                            }
                        }
                    }
                }
            }

            return response()->json(
                ''
            , 201);
        } catch (e) {

        }
    }

    //Adiciona um parâmetro para a mensagem rápida
    public function storeQuickMessageParameter($parameterData)
    {
        $quickMessageParameter = new QuickMessageParameter();
        $quickMessageParameter->quick_message_id = $parameterData['quick_message_id'];
        $quickMessageParameter->type_parameter_id = $parameterData['type_parameter_id'];
        $quickMessageParameter->type_button_id = $parameterData['type_button_id'];
        $quickMessageParameter->qui_content = $parameterData['qui_content'];
        $quickMessageParameter->qui_description = isset($parameterData['qui_description'])? $parameterData['qui_description'] : NULL;
        $quickMessageParameter->qui_url = isset($parameterData['qui_url'])? $parameterData['qui_url'] : null;
        $quickMessageParameter->qui_phone_number = isset($parameterData['qui_phone_number'])? $parameterData['qui_phone_number'] : null;
        $quickMessageParameter->qui_media_name = isset($parameterData['qui_media_name'])? $parameterData['qui_media_name'] : null;
        $quickMessageParameter->qui_media_original_name = isset($parameterData['qui_media_original_name'])? $parameterData['qui_media_original_name'] : null;
        $quickMessageParameter->qui_positives_responses = isset($parameterData['qui_positives_responses'])? $parameterData['qui_positives_responses'] : null;
        $quickMessageParameter->qui_negatives_responses = isset($parameterData['qui_negatives_responses'])? $parameterData['qui_negatives_responses'] : null;
        $quickMessageParameter->save();
    }

    public function updateQuickMessage(Request $request, $id)
    {
        $quickMessage = json_encode($request->quickMessage);
        $quickMessage = json_decode($quickMessage, true);
        //Log::debug($quickMessage);
        $quickMessageUpdated = QuickMessage::find($id);

        $quickMessageUpdated->qui_title = $quickMessage['title'];
        $quickMessageUpdated->qui_content = $quickMessage['content'];
        $quickMessageUpdated->save();

        return response()->json(
            ''
        , 200);
    }

    public function removeQuickMessage($id)
    {
        //Deleta o bloco
        $quickMessage = QuickMessage::find($id);
        $quickMessage->qui_status = 'I';
        $quickMessage->save();

        return response()->json([], 200);
    }


    //Traz os chats associados a um contato
    public function getChatsContact($contactId)
    {
        $chats = Chat::where('contact_id', $contactId)
                        ->get();
        
        return $chats;
    }

    public function sendMessageCampaign($campaignMessageData)
    {
        $mailingController = new MailingController();
        $campaignController = new CampaignController();
        $channelController = new ChannelController();
        $templateController = new TemplateController();
        $utils = new UtilsController();
        $costController = new CostController();

        $apiName = null;

        //Log::debug('dados da mensagem de campanha');
        //Log::debug($campaignMessageData); 
        $contact = Contact::find($campaignMessageData['contactId']);

        $payloadMessage['type'] = "text";

        //Se for uma campanha de Whatsapp com canal NÃO OFICIAL ou se for uma campanha de SMS
        if(($campaignMessageData['channel'] && $campaignMessageData['channel']['cha_api_official'] == false) || $campaignMessageData['campaignTypeId'] == 2) {
            $campaignMessageData['mes_message'] = self::getTextSpinTax($campaignMessageData['mes_message']);
            
            $payloadMessage['payload']['text'] = self::getTextSpinTax($campaignMessageData['mes_message']);
            $payloadMessage['payload']['text'] = self::replaceQuickMessageTags($campaignMessageData['mes_message'], $contact, $campaignMessageData['senderId'], $campaignMessageData['chatId'], $campaignMessageData['campaignId']);
            $payloadMessage['payload']['text'] =  $utils->changeParagraphContent($payloadMessage['payload']['text']);
            $payloadMessage['payload']['text'] =  $utils->convertTextWhatsappFormat($payloadMessage['payload']['text']);

            /*$campaignData = new ChatMessage();
            $campaignData->type_message_chat_id = $campaignMessageData['type_message_chat_id'];
            $campaignData->mes_message = $payloadMessage['payload']['text'];*/
            $campaignData['mes_message'] = $payloadMessage['payload']['text'];
            $campaignData['quickMessageData'] = self::getQuickMessage($campaignMessageData['quickMessageId']);
            $campaignData['quickMessageWithParameters'] = $campaignData['quickMessageData']['parameters']? true : false;
            $campaignData = (object) $campaignData;
        } //Se for uma campanhade WhatsApp com canal OFICIAL
        else {
            $apiName = '360Dialog';
            $templateData = $templateController->getTemplate($campaignMessageData['messageId']);
            $templateData['tem_code'] = $templateData['language']['tem_code'];
            $templateDataChanged = self::replaceTemplateMessageTags($campaignMessageData['mes_message'], $contact, $campaignMessageData['senderId'], $campaignMessageData['chatId'], null, $templateData);

            $campaignData['templateData'] = $templateData;
            //Valores das parâmetros (valores das variáveis, etc.)
            $campaignData['componentsData'] = $templateDataChanged['componentsData'];
            $campaignData['template_id'] = $campaignMessageData['messageId'];
            $campaignData = (object) $campaignData;

            //Dados para serem gravados no banco de dados
            $payloadMessage['payload']['text'] = $templateDataChanged['messageChanged']; //Texto com as tags substituídas pelos respectivos valores
            $payloadMessage['payload']['text'] =  $utils->changeParagraphContent($payloadMessage['payload']['text']);
            $payloadMessage['payload']['text'] =  $utils->convertTextWhatsappFormat($payloadMessage['payload']['text']);
        }

        $destination = $contact->con_phone;
       
        //Log::debug('dados da campanha');
        //Log::debug($campaignMessageData);

        //Se for uma CAMPANHA DE WHATSAPP
        if($campaignMessageData['campaignTypeId'] == 1) {
            //Se for um canal associado a Z-API
            if($campaignMessageData['channel']['api_communication_id'] == 5) {
                $phoneExistsResponse = $this->communicatorController->phoneExists($campaignMessageData['channel'], $destination);
                //Se o número de telefone tem WhatsApp associado
                if($phoneExistsResponse['exists'] == true) {
                    //Envia a mensagem via API para o número de destino 
                    $response = $this->communicatorController->sendMessageApi($campaignMessageData['channel'], $destination, $campaignData);
                }//Se o número de telefone NÃO tem WhatsApp associado
                else {
                    $response['status'] = 'failed';
                }
            }
            else {
                
                if(is_object($campaignData)) {
                    $messageAux  = (array) $campaignData;
                    if(isset($messageAux['templateData'])) {
                        $campaignData = $messageAux;
                    }
                }
                
                //Envia a mensagem via API para o número de destino 
                $response = $this->communicatorController->sendMessageApi($campaignMessageData['channel'], $destination, $campaignData);    
            }
        } //Se for uma CAMPANHA DE SMS
        else if($campaignMessageData['campaignTypeId'] == 2) {
            $smsController = new SmsController();
            $response = $smsController->sendMessageApi($destination, $payloadMessage['payload']['text'], $campaignMessageData['mailingId']);
        }
        

        Log::debug('Resposta da API ao tentar enviar uma mensagem de campanha');
        //Log::debug($campaignMessageData);
        Log::debug($response);
        
        
        //Se a mensagem foi enviada com sucesso
        if($response['status'] == 'success') {
            $payloadMessage['id'] = $response['message']['id'];
            
            //Grava a mensagem no banco de dados
            $messageStored = self::storeMessage($campaignMessageData['chatId'], $campaignMessageData['typeUserId'], $campaignMessageData['senderId'], 
                                                $payloadMessage, $campaignMessageData['actionId'], $apiName, $campaignMessageData['privateMessage'], null, 
                                                $campaignMessageData['campaignId'], null, null, null);
            //Seta o status como ENVIADO
            $campaignMessageData['statusId'] = 2;

            //Se for uma campanha de WhatsApp
            if($campaignMessageData['campaignTypeId'] == 1) {
                //incrementa a quantidade de operações do canal em 1
                $campaignController->setIncrementOperationChannel($campaignMessageData['channelCampaign']['id']);
            }

            //Atualiza o mailing
            $mailingController->update($campaignMessageData);

            //Se a cobrança por envio de mensagem de campanha está habilitada
            if($campaignMessageData['isCharged'] == '1') {
                //Se for uma mensagem por WhatsApp
                if($campaignMessageData['campaignTypeId'] == 1) {

                    $typeCostId = 1;
                } //Se for uma mensagem por SMS
                else if($campaignMessageData['campaignTypeId'] == 2) {
                    $typeCostId = 2;
                }
                $costData = new Request([
                    'typeCostId'   => $typeCostId, //Custo de envio de mensagem pelo WhatsApp
                    'mailingId' => $campaignMessageData['mailingId'],
                ]);
    
                $costController->store($costData);
            }
        }
        else if($response['status'] == 'Disconnected') {
            //Log::debug('entrou aqui');
            $channelController = new ChannelController();
            //Dados do contato
            $channelData = new Request([
                'channelId'   => $campaignMessageData['channel']['id'],
                'sessionName' => mb_substr($campaignMessageData['channel']['cha_session_name'], 2),
                'statusId' => 'I',
            ]);
            //Desabilita o canal
            $channelController->updateStatusChannel($channelData);
        }
        else {
            //Seta o status como FALHA AO ENVIAR
            $campaignMessageData['statusId'] = 3;
            $campaignMessageData['messageId'] = null;
            $campaignMessageData['channel'] = null;

            //Atualiza o mailing
            $mailingController->update($campaignMessageData);
        }
    }

    //Seleciona um dos textos contidos em um ou mais spinTax
    public function getTextSpinTax($message)
    {   
        //Seleciona as spinText da mensagem e guarda em uma array
        preg_match_all( '/{{(.*?)}}/s' , $message, $match );
        

        //Caso tenha sido utlizado spinTax na mensagem
        if(isset($match[0])) {
            foreach($match[0] as $key => $spinText) {
                //Pega cada um dos textos de uma spinTax Ex: Pega o texto "Como vai" da spinTax {{Como vai|Olá, tudo bem?}}
                $spinTextArray = explode('|', $match[1][$key]);
                $textChosen = $spinTextArray[rand(0, (count($spinTextArray) -1))];
                //Substitui a spinTax pelo texto selecionado randomicamente
                $message = str_replace($spinText, $textChosen, $message);
            }
        }
        
        return $message;        
    }

    public function getLastMessageByTypeUserSender($chatId, $typeUserSender)
    {
        $lastMessage = ChatMessage::where('chat_id', $chatId)
                                    ->where('type_user_id', $typeUserSender)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
        return $lastMessage;
    }

    //Traz os tipos de parâmetros que uma mensagem rápida pode ter
    public function fetchQuickMessagesTypeParameters($statusId)
    {
        $typeParameters = QuickMessageTypeParameter::where('qui_status', $statusId)
                                                    ->get();
        
        return response()->json([
            'typeParameters' => $typeParameters
        ], 200);
    }

    //Traz a url pública do Storage
    public function getBaseUrlStorage()
    {
        $baseUrlStorage = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC');

        return response()->json([
            'baseUrlStorage' => $baseUrlStorage
        ], 200);
    }

    //Traz as observações em relação a um chat
    public function fetchChatObservations(Request $request)
    {
        //Log::debug('fetchChatObservations $request');
        //Log::debug($request);

        $amountPerClick = 2;
        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = ($request['offset'] * $amountPerClick);

        $chatObservations = ChatObservation::select('cha_chats_observations.id', 'cha_chats_observations.cha_observation', 'cha_services.ser_protocol_number',
                                                    'users.name', 'users.avatar', 'man_departments.dep_name', 'cha_chats_observations.updated_at')
                                            ->join('cha_chats', 'cha_chats_observations.chat_id', 'cha_chats.id')
                                            ->leftJoin('cha_services', 'cha_chats_observations.service_id', 'cha_services.id')
                                            ->join('users', 'cha_chats_observations.user_id', 'users.id')
                                            ->join('man_departments', 'cha_chats_observations.department_id', 'man_departments.id')
                                            ->where('cha_chats_observations.chat_id', $request['chatId'])
                                            ->where('cha_chats_observations.cha_status', 'A')
                                            ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                            ->take($amountPerClick) //Quantidade de itens trazidos
                                            ->orderBy('cha_chats_observations.id', 'DESC')
                                            ->get();

        //Log::debug('$chatObservations');                          
        //Log::debug($chatObservations);

        return response()->json([
            'chatObservations' => $chatObservations
        ], 200);
    }

    //Adiciona uma obervação a um chat
    public function addChatObservation(Request $request)
    {
        //Log::debug('addChatObservation request');
        //Log::debug($request);
        $departmentController = new DepartmentController();
        //Traz o departamento em que o usuário está lotado
        $departmentUser = $departmentController->fetchDepartmentsUser(Auth::user()->id);

        //Salva a observação
        $chatObservation = new ChatObservation();
        $chatObservation->chat_id = $request['chatId'];
        $chatObservation->user_id = Auth::user()->id;
        $chatObservation->department_id = $departmentUser[0]->id;
        $chatObservation->cha_observation = $request['observation'];
        $chatObservation->save();

        return response()->json([
            ''
        ], 200);
    }

    //Remove uma observação de um chat
    public function removeChatObservation($observationId)
    {
        $chatObservation = ChatObservation::find($observationId);
        $chatObservation->cha_status = 'I';
        $chatObservation->save();

        return response()->json([
            ''
        ], 200);
    }

    public function fetchTypeFormatMessages()
    {
        $typeFormatMessages = TypeFormatMessage::where('typ_status', 'A')
                                            ->get();
        
        return response()->json([
            'typeFormatMessages' => $typeFormatMessages
        ], 200);
    }

    /*
    function checkExtensionStatus($extensionData)
    {
        // Configuramos a conexao com o manager do Asterisk
        $options = array(
            'host' => '192.168.100.18',
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => 'tutorialclicktocall',
            'secret' => 'tutorialclicktocall',
            'connect_timeout' => 60000,
            'read_timeout' => 60000
        );

        // Instancia a conexao
        $conexao = new PamiClient($options);

        try {
            $extension = null;
            $conexao->open();
            $action10 = new DeviceStateListAction();
            $response = $conexao->send($action10);
            $eventsList = $response->getEvents();
            //Gera um array com a lista de ramais
            foreach($eventsList as $key => $eventList ) {
                $extensionsList[$key]['device'] = $eventList->getKey('Device');
                $extensionsList[$key]['state'] = $eventList->getKey('State');
            }

        foreach($extensionsList as $extensionOb) {
            //Se for o ramal associado ao usuário e o mesmo não está em uso
            if($extensionOb['device'] == 'SIP/'.$extensionData->name && $extensionOb['state'] == 'NOT_INUSE') {
                $extension = $extensionData->name;
            }
        }
        return $extension;

        } catch (\Exception $e) {
            $conexao->close();
            throw $e;
        }
    }*/

    //Armazena o valor de início de uma ligação (quando começa a tocar o telefone)
    /*public static $startTime;
    public static $uniqueId;
    
    public function callPhone(Request $request)
    {   //Define que uma ligação pode ter no máximo 1 hora
        ini_set('max_execution_time', 3600);
        
        Log::debug('callPhone request');
        Log::debug($request);
        $extensionController = new ExtensionController();
        $errorMessage = '';
        //ATENÇÃO: CORRIGIR O CÓDIGO ABAIXO PEGANDO O RAMAL ASSOCIADO AO CLIENTE (OU ASSOCIADO AO DEPARTAMENTO DELE)
        $extension = Extension::with('voip.setting')
                                ->where('host', 'dynamic')
                                ->first();
        
        try {
            //$extension = self::checkExtensionStatus($extensionData);
            //Se existir algum ramal para o usuário e o ramal está disponível
            if($extension && $extension->status == 0) {
                //Muda o status do canal para OCUPADO
                $extensionController->updateStatusExtension($extension->id, 1);
                // Configuramos a conexao com o manager do Asterisk
                $options = array(
                    'host' => env("HOST_ASTERISK"),
                    'scheme' => 'tcp://',
                    'port' => 5038,
                    'username' => 'tutorialclicktocall',
                    'secret' => 'tutorialclicktocall',
                    'connect_timeout' => 60000,
                    'read_timeout' => 60000
                );

                // Instancia a conexão
                $conexao = new PamiClient($options);
                $conexao->open();

                // Configura Originate
                $action = new OriginateAction('Local/'.$extension->name.'@from-pstn');
                $action->setContext('from-pstn');
                $action->setExtension($request['phoneNumber']);
                $action->setPriority('1');
                $action->setTimeout(0);
                $action->setVariable('USER', $extension['voip']['setting']->voi_user);
                $action->setAsync(true);

                //Envia
                $conexao->send($action);

                //Data e hora da ligação
                $callDataHour = Carbon::now();

                // Ouvir os eventos da chamada
                $conexao->registerEventListener(function (EventMessage $event) use ($conexao, $request, $extension, $callDataHour, $extensionController) {
                    //Log::debug('printr asterisk');
                    //Log::debug(print_r($event, true));
                    //Log::debug($event->getName());
                    if ($event->getName() === 'DeviceStateChange') {
                        //Log::debug(print_r($event, true));
                    }
                    if ($event->getName() === 'Newstate' && $event->getChannelStateDesc() === 'Ringing' && $event->getCallerIDNum() == $extension->name) {
                        //Só considera o primeiro evento Newstate
                        self::$startTime = Carbon::now();
                    }
                    if ($event->getName() === 'MixMonitorStart') {
                        // Obter o UNIQUEID da chamada (que também é o nome do arquivo de gravação do áudio)
                        self::$uniqueId = $event->getKey('Uniqueid');
                    }
                    ////Se a ligação foi finalizada
                    if ($event->getName() == 'Hangup') {
                        $endTime = Carbon::now();
                        $duration = self::$startTime->diffInSeconds($endTime);
                        if($event->getCallerIDNum() == $request['phoneNumber']) {
                            $callController = new CallController();
                            $contact = $this->contactController->getContactByPhoneNumber($request['phoneNumber']);
                            $chats = self::getChatsContact($contact->id);
                            //Traz o atendimento que está aberto referente a um contato
                            $service = $this->serviceController->getServiceByContactAndStatus($chats[0]->id, 1);

                            $callData = new Request([
                                'userId'   => Auth::user()->id,
                                'contactId' => $contact->id,
                                'serviceId' => isset($service)? $service->id : null,
                                'extensionId' => $extension->id, //Pegar o ramal dinamicamente
                                'calPhoneContact' => $request['phoneNumber'],
                                'calRecordName' => self::$uniqueId,
                                'calCallTime' => $duration,
                                'calCallMade' => 1, //Marca como uma ligação feita pelo usuário (e não recebida)
                                'calCallDate' => $callDataHour, //Data e hora que a ligação foi realizada
                            ]);

                            $callController->store($callData);

                            $recordPath = "projetos/audioteste.wav";
                            FFMpeg::fromDisk('c_drive')
                                ->open($recordPath)
                                ->export()
                                //->toDisk('converted_songs')
                                ->inFormat(new \FFMpeg\Format\Audio\Mp3)
                                ->toDisk('spaces')
                                ->withVisibility('public')
                                ->save('public/chats/chat'.$chats[0]->id.'/calls/'.self::$uniqueId.'.mp3');

                            $message = new ChatMessage();

                            $message->chat_id = $chats[0]->id;
                            $message->service_id = isset($service)? $service->id : null;
                            $message->type_user_id = 1; //Operador
                            $message->sender_id = Auth::user()->id;
                            $message->mes_content_name = self::$uniqueId.'.mp3';
                            $message->status_message_chat_id = 1; //Coloca a mensagem inicialmente com status enfileirada
                            $message->type_message_chat_id = 9; //Ligação
                            $message->mes_private = 0; //Não privada

                            if($message->save()) {
                                $newMessageData = array(
                                    'id' => $message->id, 
                                    'chat_id' => $chats[0]->id, 
                                    'type_message_chat_id' => 9,
                                    'mes_content_type' => null,
                                    'mes_content_name' => self::$uniqueId.'.mp3',
                                    'mes_url' =>  null,
                                    'created_at' => $callDataHour,
                                    'senderId' => Auth::user()->id,
                                    'type_user_id' => 1, //Operador
                                    'mes_private' => 0, //Não privada
                                    'status_message_chat_id' => 1, //Coloca a mensagem inicialmente com status enfileirada
                                );

                                $this->eventController->sendMessageChat($newMessageData, Auth::user()->id);

                                //Traz todos os usuários que são GESTORES
                                $usersManagers = $this->userController->getUsersByRoles([1, 3]);

                                //Se existir algum GESTOR
                                if($usersManagers) {
                                    //Envia a mensagem para o GESTOR
                                    foreach ($usersManagers as $userManager) {
                                        $this->eventController->sendMessageChat($newMessageData, $userManager->id);
                                    }
                                }
                            }    
                        }
                        //Altera o status do canal para disponível
                        $extensionController->updateStatusExtension($extension->id, 0);    
                        Log::debug('Ligação finalizada');
                        // Mandar o tchau para o asterisk
                        $action2 = new LogoffAction;
                        $conexao->send($action2);
                        // Fecha a conexão
                        $conexao->close();
                    }
                });

                $running = true;
                while ($running) {
                    $conexao->process();
                    //usleep(1000);
                }
            }
            else {
                    $errorMessage = 'O ramal se encontra indisponível';

                    Log::debug('caiu no erro');

                    return response()->json([
                        'errorMessage' => $errorMessage
                    ], 200);
            }
        } catch (\Exception $e) {
            $action2 = new LogoffAction;
            $conexao->send($action2);
            $conexao->close();
            //Altera o status do canal para disponível
            $extensionController->updateStatusExtension($extension->id, 0);
            throw $e;
        }
    }*/

    public function callPhone(Request $request)
    {
        Log::debug($request);
        $partPhone = substr($request['phoneNumber'], 2, null);
        Log::debug('$partPhone');
        Log::debug($partPhone);
        $phone = '0'.$partPhone;
        $callData = ['phone' => $phone, 'extension' => $request['extensionNumber']];

        $dialerInterfaceController = new DialerInterfaceController();
        $response = $dialerInterfaceController->callPhone($callData);

        $callController = new CallController();
        
        $contact = $this->contactController->getContactByPhoneNumber($request['phoneNumber']);
        $chats = self::getChatsContact($contact->id);
        //Traz o atendimento que está aberto referente a um contato
        $service = $this->serviceController->getServiceByContactAndStatus($chats[0]->id, 1);

        Log::debug('$response callphone');
        Log::debug($response);

        if(isset($response['data'][0]['id'])) {
            $callData = new Request([
                'callApiId'   => $response['data'][0]['id'],
                'userId'   => Auth::user()->id,
                'contactId' => $contact->id,
                'serviceId' => isset($service)? $service->id : null,
                'extensionId' => $request['extensionId'], //Pegar o ramal dinamicamente
                'calPhoneContact' => $request['phoneNumber'],
                'calCallMade' => 1, //Marca como uma ligação feita pelo usuário (e não recebida)
                'status' => 'I',
            ]);

            $callController->store($callData);
        }
    }

    //Traz uma mensagem pelo seu ID
    public function getMessageById($messageId)
    {
        $message = ChatMessage::where('api_message_id', $messageId)
                            ->first();

        return $message;
    }

    //Adiciona uma mensagem no chat
    public function addChatMessage($messageData)
    {
        $message = new ChatMessage();

        $message->chat_id = $messageData['chatId'];
        $message->service_id = isset($messageData['serviceId'])? $messageData['serviceId'] : null;
        $message->type_user_id = $messageData['typeUserId'];
        $message->sender_id = $messageData['senderId'];
        $message->campaign_id = $messageData['campaignId'];
        $message->quick_message_id = $messageData['quickMessageId'];
        $message->status_message_chat_id = $messageData['statusMessageChatId'];
        $message->type_message_chat_id = $messageData['typeMessageChatId'];
        $message->mes_private = $messageData['mesPrivate'];
        $message->save();
    }

    //Retorna o chat pelo seu ID
    public function getChatById($chatId)
    {
        $chat = Chat::find($chatId);

        return $chat;
    }

    //Traz o total de mensagens RECEBIDAS na plataforma durante um período
    public function getTotalMessagesReceived($days=null)
    {
        $totalMessagesReceived = ChatMessage::where('type_user_id', 2); // Onde o tipo de usuário é o contato
        //Se foi especificada a quantidade dias em que se deseja saber o volume de mensagens
        if($days) {
            $startDate = Carbon::now()->subDays($days)->startOfDay();
            $totalMessagesReceived = $totalMessagesReceived->whereBetween('created_at', [$startDate, Carbon::now()]);
        }
        $totalMessagesReceived = $totalMessagesReceived->count();

        return $totalMessagesReceived;
    }

    //Traz o total de mensagens ENVIADAS na plataforma durante um período
    public function getTotalMessagesSent($days=null)
    {
        $totalMessagesSent = ChatMessage::where('type_user_id', "!=", 2); // Onde a mensagem NÃO enviada pelo contato
        //Se foi especificada a quantidade dias em que se deseja saber o volume de mensagens
        if($days) {
            $startDate = Carbon::now()->subDays($days)->startOfDay();
            $totalMessagesSent = $totalMessagesSent->whereBetween('created_at', [$startDate, Carbon::now()]);
        }
        $totalMessagesSent = $totalMessagesSent->count();

        return $totalMessagesSent;
    }
}
