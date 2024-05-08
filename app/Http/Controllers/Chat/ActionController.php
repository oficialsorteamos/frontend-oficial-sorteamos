<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Chat\Action;

class ActionController extends Controller
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
    public function store(Request $request)
    {
        try {
            
            $action = new Action();
            $action->service_id = $request['serviceId'];
            $action->chat_id = $request['chatId'];
            $action->type_action_id = $request['typeActionId'];
            $action->department_id = $request['departmentId'];
            $action->user_id = $request['userId'];
    
            $action->save();


            return response()->json([
                'action' => $action
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

        $contact = Contact::with('pipeline', 'colorAvatar')
                            ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_email as email', 
                            'con_birthday as birthday', 'created_at')
                            ->where('id', $id)
                            ->get();
        
        $chat->chatsAndContactsDetails($contact);
        
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

    //Retorna a última ação associada ao atendimento
    public function getLastActionByService($serviceId)
    {
        $action = Action::where('service_id', $serviceId)
                        ->orderBy('id', 'DESC')
                        ->first();
        
        return $action;
    }
}
