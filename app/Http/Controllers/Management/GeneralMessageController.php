<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Management\Call\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Management\Department;
use App\Models\Management\GeneralMessage\GeneralMessage;
use App\Models\Management\Tag\Tag;
use App\Models\Management\Tag\TypeTag;
use App\Models\Management\UserDepartment;

class GeneralMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        Log::debug('call request');
        Log::debug($request);

        $generalMessages = GeneralMessage::with('typeGeneralMessage')
                                        ->get();

        Log::debug('$generalMessages');
        Log::debug($generalMessages);
        return response()->json([
            'generalMessages' => $generalMessages,
            'total' => count($generalMessages),
        ], 200);
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
        //
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
            $generalMessage = GeneralMessage::find($request->generalMessageData['id']);

            $generalMessage->gen_content = $request->generalMessageData['gen_content'];
            $generalMessage->gen_status = $request->generalMessageData['gen_status'];
            $generalMessage->save();

            return response()->json([
                [] 
            ], 200);

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
        try {
            $tag = Tag::find($id);
            $tag->delete();
        } catch(e) {

        }
    }

    //Retorna uma mensagem geral pelo tipo
    public function getgeneralMessageByType($typeGeneralMessageId)
    {
        $generalMessage = GeneralMessage::where('type_general_message_id', $typeGeneralMessageId)
                                        ->first();
        return $generalMessage;
    }

    //Retorna o conteúdo da mensagem formatado
    public function generalMessageContentFormatted($generalMessageContent, $contact, $senderId, $chatId, $departmentId)
    {       
        $chatController = new ChatController();
        $utilsController = new UtilsController();

        $generalMessageContentFormatted = $chatController->replaceQuickMessageTags($generalMessageContent, $contact, $senderId, $chatId, null, $departmentId);
        $generalMessageContentFormatted =  $utilsController->changeParagraphContent($generalMessageContentFormatted);
        $generalMessageContentFormatted =  $utilsController->convertTextWhatsappFormat($generalMessageContentFormatted);

        return $generalMessageContentFormatted;
    }
}
