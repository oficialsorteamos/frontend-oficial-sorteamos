<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Management\Call\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Management\Department;
use App\Models\Management\Tag\Tag;
use App\Models\Management\Tag\TypeTag;
use App\Models\Management\UserDepartment;

class CallController extends Controller
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

        $skip = (($request['page']-1) * $request['perPage']);
        //Traz as ligações registradas pelo sistema
        $calls = Call::select('voi_calls.id', 'cal_phone_contact', 'cal_record_name', 'cal_call_date', 'con_contacts.con_name',
                            'ser_protocol_number', 'users.name', 'cha_chats.id as chat_id', 'voi_extensions.name as extension_name')
                    ->join('con_contacts', 'voi_calls.contact_id', 'con_contacts.id')
                    ->join('cha_chats', 'con_contacts.id', 'cha_chats.contact_id')
                    ->join('users', 'voi_calls.user_id', 'users.id')
                    ->leftJoin('cha_services', 'voi_calls.service_id', 'cha_services.id')
                    ->join('voi_extensions', 'voi_calls.extension_id', 'voi_extensions.id')
                    ->where('cal_status', 'A');
        if($request['user'] != '') {
            $calls = $calls->where('users.id', $request['user']);
        }
        if($request['contact'] != '') {
            $contact = json_decode($request['contact'], true);
            $calls = $calls->where('con_contacts.id', $contact['id']);
        }
        if($request['extension'] != '') {
            $calls = $calls->where('voi_calls.extension_id', $request['extension']);
        }
        if($request['period']) {
            $periodDivided = explode('até', $request['period']);
            //Se foi selecionada apenas a data inicial
            if(count($periodDivided) == 1) {
                $calls = $calls->whereBetween('voi_calls.cal_call_date', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
            }//Se foi selecionada a data inicial e final
            else {
                $calls = $calls->whereBetween('voi_calls.cal_call_date', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
            }
        }
        $calls = $calls->orderBy('voi_calls.cal_call_date', 'DESC');
        $total = $calls->count();
        $calls = $calls->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();
        

        Log::debug('calls');
        Log::debug($calls);
        
        $urlBaseStorage = env('MIX_DIGITALOCEAN_SPACES_URL_PUBLIC');

        return response()->json([
            'calls' => $calls,
            'total' => $total,
            'urlBaseStorage' => $urlBaseStorage
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
        try{
            Log::debug('dados ligação');
            Log::debug($request);
            $newcall = new Call();
            $newcall->cal_call_api_id = $request['callApiId'];
            $newcall->user_id = $request['userId'];
            $newcall->contact_id = $request['contactId'];
            $newcall->service_id = $request['serviceId'];
            $newcall->extension_id = $request['extensionId'];
            $newcall->cal_phone_contact = $request['calPhoneContact'];
            $newcall->cal_record_name = $request['calRecordName'];
            $newcall->cal_call_time = $request['calCallTime'];
            $newcall->cal_call_made = $request['calCallMade'];
            $newcall->cal_call_date = $request['calCallDate'];
            $newcall->cal_status = $request['status'];
            $newcall->save();

            return response()->json([
                ''
            ], 200);

        } catch (e) {

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
            $tag = Tag::find($request->tagData['id']);

            $tag->tag_color = $request->tagData['tag_color'];
            $tag->tag_name = $request->tagData['tag_name'];
            $tag->tag_description = $request->tagData['tag_description'];
            $tag->type_tag_id = $request->tagData['type_tag']['id'];
            $tag->save();

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

    //Atualiza o status de processamento da ligação que ocorre via rotina
    public function updateCallProccessingStatus($callProccessData)
    {
        $call = Call::find($callProccessData['id']);
        $call->cal_record_name = $callProccessData['cal_record_name'];
        $call->cal_call_date = $callProccessData['cal_call_date'];
        $call->cal_has_record = $callProccessData['cal_has_record'];
        $call->cal_status = $callProccessData['cal_status'];
        $call->save();
    }
}
