<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use App\Models\Management\User;
use App\Notifications\NewChat;
use Illuminate\Http\Request;
use Auth;
use Notification;
use Illuminate\Support\Facades\Log;

class WebPushController extends Controller
{

    public function __construct(){
        $this->middleware('auth:sanctum');
    }

    /**
     * Store the PushSubscription.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) 
    {   
        $this->validate($request, [
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);

        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];
        $user = Auth::user();

        $user->updatePushSubscription($endpoint, $key, $token);
        
        return response()->json(['success' => true],200);
    }

    /**
     * Send Push Notifications.
     * 
     * @return \Illuminate\Http\Response
     */
    //Envia uma notificação WebPush comunicando os usuários que existe um novo contato aguardando atendimento
    public function sendWebPushNewChat($userId) {
        Notification::send($userId, new NewChat);
    }
    
}