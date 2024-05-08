<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Calendar\Calendar;
use App\Models\Calendar\GuestEvent;
use App\Models\Contact\Contact;
use Carbon\Carbon;
use Log;
use Auth;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::debug('dados de evento');
        Log::debug($request->tags);
        //Se o atributo tag não existir
        if(!isset($request->tags)) {
            $request->tags = [];
        }
        $events = Calendar::with('guests', 'tag')
                            ->select('id', 'cal_title AS title', 'cal_event_start AS start', 'cal_event_end AS end', 'cal_url AS url', 
                                    'cal_description AS description', 'tag_id')
                            ->whereIn('tag_id', $request->tags)
                            ->get();
        
        

        Log::debug("Eventos: ".$events);

        //$events = array(['id' => 1, 'end' => '2021-06-15 13:00', 'allDay' => false, 'start' => '2021-06-15 12:00', 'title' => 'Teste da agenda', 'url' => '']);

        return response()->json(
            $events
        , 201);
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
        Log::debug('event data');
        Log::debug($request->event);

        $newEvent = new Calendar();
        
        $newEvent->user_id = Auth::check()? Auth::user()->id : 1;
        $newEvent->tag_id = $request->event['extendedProps']['tag']['id'];
        $newEvent->cal_title = $request->event['title'];
        $newEvent->cal_description = $request->event['extendedProps']['description'];
        $newEvent->cal_url = isset($request->event['url']) ? $request->event['url'] : '';
        $newEvent->cal_event_start = $request->event['start'];
        $newEvent->cal_event_end = $request->event['end'];
        
        if($newEvent->save()) {

            //Se a marcação da reunião estiver sendo realizada a partir da tela de chat
            if(isset($request->event['contactId'])) {
                $guestEvent = new GuestEvent();
                $guestEvent->event_id = $newEvent->id;
                $guestEvent->contact_id = $request->event['contactId'];
                $guestEvent->save();
            }

            //Para cada convidado (contato) do evento
            foreach($request->event['extendedProps']['guests'] as $guest) {
                $guestEvent = new GuestEvent();
                $guestEvent->event_id = $newEvent->id;
                $guestEvent->contact_id = $guest['id'];
                $guestEvent->save();
            }
        }

        return response()->json(
            $newEvent
        , 201);
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
    public function update(Request $request, $id)
    {   
        $eventUpdated = Calendar::find($id);

        $eventUpdated->user_id = auth()->user()->id;
        $eventUpdated->tag_id = $request->event['extendedProps']['tag']['id'];
        $eventUpdated->cal_title = $request->event['title'];
        $eventUpdated->cal_description = $request->event['extendedProps']['description'];
        $eventUpdated->cal_url = isset($request->event['url']) ? $request->event['url'] : '';
        $eventUpdated->cal_event_start = $request->event['start'];
        $eventUpdated->cal_event_end = $request->event['end'];
        
        if($eventUpdated->save()) {
            //Deleta todos os convidados do evento atualizado
            GuestEvent::where('event_id', $eventUpdated->id)
                        ->delete();

            //Para cada convidado (contato) do evento
            foreach($request->event['extendedProps']['guests'] as $guest) {
                $guestEvent = new GuestEvent();
                $guestEvent->event_id = $eventUpdated->id;
                $guestEvent->contact_id = $guest['id'];
                $guestEvent->save();
            }
        }

        return response()->json([
            'event' => $request->event 
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   //Deleta o evento
        Calendar::find($id)->delete();

        return response()->json([], 200);
    }

    public function fetchContacts()
    {
        $contacts = Contact::all();
        
        Log::debug("contacts Calendar");

        return response()->json([
            'contacts' => $contacts 
        ], 200);
    }
}
