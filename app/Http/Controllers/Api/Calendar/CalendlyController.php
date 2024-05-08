<?php

namespace App\Http\Controllers\Api\Calendar;

use App\Http\Controllers\Api\Kanban\KanbanInterfaceController;
use App\Http\Controllers\Calendar\CalendarController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Controller;
use App\Models\Chat\Service;
use App\Models\Contact\Contact;
use App\Models\Management\Channel\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use FFMpeg;
use Carbon\Carbon;



class CalendlyController extends Controller
{
    
    public function webhookCalendly(Request $request)
    {
        Log::debug('CALENDLY request');
        Log::debug($request);

        $contactController = new ContactController();
        $chatController = new ChatController();
        $serviceController = new ServiceController();
        $calendarController = new CalendarController();
        $kanbanInterfaceController = new KanbanInterfaceController();

        //Se o contato adicionou um novo contato no Calendly
        if($request['event'] == 'invitee.created') {
            //Se o contato forneceu o telefone
            if($request['payload']['questions_and_answers'][0]['question'] == 'Telefone') {
                $phoneNumber = preg_replace( '/[^0-9]/', '', $request['payload']['questions_and_answers'][0]['answer']);

                $contact = $contactController->getContactByPhoneNumber($phoneNumber);
                //Se NÃO existir um contato com o número de telefone correspondente
                if(!$contact) {
                    //Dados do contato
                    $contactData = new Request([
                        'name'   => $request['payload']['name'],
                        'phoneNumber' => $phoneNumber,
                    ]);
                    //Salva o contato no banco de dados
                    $contact = $contactController->store($contactData);
                    $contactData = json_encode($contact);
                    $contactData = json_decode($contactData, true);
                    //Pega os dados do novo contato
                    $contact = $contactData['original']['contact'];
                    $chat = $contactData['original']['chat'];
                }
                else {
                    $chat = $chatController->getChatsContact($contact->id);
                }

                $chatId = isset($chat[0]->id)? $chat[0]->id : $chat['id'];

                //Traz o atendimento que está aberto referente a um contato, se houver
                $service = $serviceController->getServiceByContactAndStatus($chatId, 1);
                
                //Se existe um atendimento aberto
                if(!$service) {

                    $channel = self::randomChannelCalendly();


                    $service = new Service();
                    $service->chat_id = $chatId;
                    $service->channel_id = $channel[0]->id;
                    $service->type_status_service_id = 1;
                    $service->ser_protocol_number = $chatController->generateProtocolNumber();
                    $service->save();
                }

                $requestTransferService = new Request([
                    'transferData' => [
                        'chatId'   => $chatId,
                        'department' => [
                            'id' => 3 //Transfere para o departamento comercial
                        ],
                    ]
                ]);

                //Transfere o atendimento
                $serviceController->transferService($requestTransferService, true);

                Log::debug('$contact->id');
                Log::debug($contact->id);
                //Associa a tag "Agendou apresentação ao contato"
                $contactController->addTagContact($contact->id, 13);

                $calendarData = new Request([
                    "event" =>[
                        "title" => "Apresentação do ChatX",
                        "start" => $request['payload']['scheduled_event']['start_time'],
                        "end" => Carbon::parse($request['payload']['scheduled_event']['start_time'])->addHour(),
                        //"contactId" => $contact->id,
                        "extendedProps" => [
                            "tag" => [
                                "id" => 42,
                            ],
                            "guests" => [$contact],
                            "description" => "Apresentação de sistema com o cliente ".$request['payload']['name']
                        ]
                    ]
                ]);

                //Cria um evento no calendário do ChatX
                $calendarController->store($calendarData);

                $cardData['name'] = $request['payload']['name'].' - '. Carbon::parse($request['payload']['scheduled_event']['start_time'])->format('d/m/Y');
                $cardData['description'] = "**Nome**: ".$request['payload']['name']. "\n **Telefone:** ".$phoneNumber."\n **Data da Apresentação**: ".Carbon::parse($request['payload']['scheduled_event']['start_time'])->format('d/m/Y H:i');

                //Adiciona card no Trello
                $kanbanInterfaceController->createCard($cardData);
                
                //Envia a mensagem de confirmação do agendamento ao cliente
                $messageData = new Request([
                    'contactId'   => $contact['id'],
                    'typeUserId' => 3, //Robô
                    'privateMessage' => 'false',
                    'senderId' => 1, //Robô
                    'actionId' => null,
                    'message' => "Seu *agendamento de apresentação do sistema ChatX* foi realizado com sucesso.\n \n Nos vemos no dia e horário marcado. Tenha um excelente dia!",
                    //'template_id' => $fowardingSetting[0]->template_id,
                ]);
                //Envia a mensagem para o cliente
                $chatController->sendMessage($messageData);
                
            }
        }
    }

    //Sorteia um canal NÃO OFICIAL para enviar mensagem de confirmação de agendamento
    public function randomChannelCalendly()
    {   
        $channel = Channel::where('cha_api_official', 0)
                                        ->where('cha_status', 'A')
                                        ->get()
                                        ->random(1)//Traz uma mensagem aleatória
                                        ->values();

        return $channel;
    }
}
