<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\EventController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Management\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Notification\Notification;
use App\Notifications\SendUserNotification;
use Carbon\Carbon;

use Auth;

class NotificationController extends Controller
{
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
    public function fetchNotifications(Request $request)
    {   
        try {
            //Log::debug('chamou função notificação');
            //Quantidade de itens exibidos a cada click
            $amountPerClick = 4;
            //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
            $skip = ($request['offset'] * $amountPerClick);
            //Log::debug('skip');
            //Log::debug($skip); 
            
            $user = Auth::user();
            
            //Traz as notificações associadas ao usuário logado
            $notifications = $user->notifications()
                                ->orderBy('created_at', 'desc')
                                ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                ->take($amountPerClick) //Quantidade de itens trazidos
                                ->get();
            
            foreach ($notifications as $notification) {

                //Calcula há quanto tempo o atendimento foi iniciado
                $timeDiff = Carbon::parse($notification->created_at)->diffForHumans(Carbon::now());
                $notification->setAttribute('timeDiff', $timeDiff);
            }
            
            //Log::debug('Notificações');
            //Log::debug($notifications);

            return response()->json(
                $notifications
            , 201);
        } catch(e) {

        }
        
    }

    public function webhookNotification(Request $request)
    {
        $eventController = new EventController();
        $userController = new UserController();
        $utilsController = new UtilsController();

        Log::debug('webhookNotification request');
        Log::debug($request);

        $typeNotification = "system-message";

        //Extrai o ID dos perfis de usuários que receberão as mensagens
        $typeUsersId = array_column($request['typeUsers'], 'id');

        $users = $userController->getUsersByRoles($typeUsersId);

        //Log::debug('webhookNotification $users');
        //Log::debug($users); 
        //Para cada usuário
        $message = $utilsController->changeParagraphContent($request['message']);
        //Remove os elementos HTML para exibir no toast notification
        $messageToast = $utilsController->changeParagraphContentTemplate($request['message']);
        foreach($users as $user) {
            //Salva a notificação no banco ded dados
            $user->notify(new SendUserNotification($request['title'], $message, null, $typeNotification, 'Devsky Soluções em Tecnologia'));
            //Envia um evento em tempo real para o frontend
            $eventController->sendAlertNotification($messageToast, $user['id'], 'Devsky Soluções em Tecnologia');
        }

        return response()->json([
            'status' => 'success'
        ]
        , 200);
    }
}