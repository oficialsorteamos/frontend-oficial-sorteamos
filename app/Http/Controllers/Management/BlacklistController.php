<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Campaign\CampaignBlacklist;
use App\Models\Contact\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;


class BlacklistController extends Controller
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
        //Para cada contato a ser inserido na blacklist
        foreach($request->blacklistData['contact'] as $contact) {
            $contactBlacklist = new CampaignBlacklist();
            $contactBlacklist->contact_id = $contact['id']; //Contato que está sendo inserido na blacklist
            $contactBlacklist->user_id = Auth::user()->id; //Usuário que está inserindo o contato na blacklist
            $contactBlacklist->save();
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
            $contactBlacklist = CampaignBlacklist::find($id);
            $contactBlacklist->delete();
        } catch(e) {

        }
    }


    //Traz todos os contatos associados a blacklist
    public function fetchBlacklist(Request $request)
    {
        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($request['page']-1) * $request['perPage']);

        $contactsBlacklist = CampaignBlacklist::select('cam_blacklist.id', 'con_name', 'name', 'cam_blacklist.created_at')
                                                ->join('con_contacts', 'cam_blacklist.contact_id', 'con_contacts.id')
                                                ->leftJoin('cam_campaigns', 'cam_blacklist.campaign_id', 'cam_campaigns.id')
                                                ->join('users', 'cam_blacklist.user_id', 'users.id')
                                                //Busca os contatos de acordo com a paginação
                                                ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                                                ->take($request['perPage']); //Quantidade de itens trazidos
        
        if($request['q'] != '') {
            $contactsBlacklist = $contactsBlacklist->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_contacts.con_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('users.name', 'like', '%'.trim($request['q']).'%');
            });

            $contactsBlacklist = $contactsBlacklist->orderBy('cam_blacklist.created_at', 'DESC');
            $contactsBlacklist = $contactsBlacklist->get();
            $total = self::getTotalBlacklistFiltered($request);
        }
        else {
            $contactsBlacklist = $contactsBlacklist->orderBy('cam_blacklist.created_at', 'DESC');
            $contactsBlacklist = $contactsBlacklist->get();
            
            //Pega o total de créditos de acordo com a busca
            $total = CampaignBlacklist::count();
        }

        return response()->json([
            'contactsBlacklist' => $contactsBlacklist,
            'total' => $total
        ], 200);
    }

    public function getTotalBlacklistFiltered($request)
    {
        $contactsBlacklist = CampaignBlacklist::select('cam_blacklist.id', 'con_name', 'name', 'cam_blacklist.created_at')
                                                ->join('con_contacts', 'cam_blacklist.contact_id', 'con_contacts.id')
                                                ->leftJoin('cam_campaigns', 'cam_blacklist.campaign_id', 'cam_campaigns.id')
                                                ->join('users', 'cam_blacklist.user_id', 'users.id');
        
        
        $contactsBlacklist = $contactsBlacklist->where(function ($query) use($request) {
            //Verifica se a busca coincide com o nome de algum usuário
            $query->where('con_contacts.con_name', 'like', '%'.trim($request['q']).'%')
                    //Verifica se busca coincide com o telefone de algum usuário
                    ->orWhere('users.name', 'like', '%'.trim($request['q']).'%');
        });

        $contactsBlacklist = $contactsBlacklist->count();

        return $contactsBlacklist;
    }

    public function fetchContacts(Request $params)
    {
        $chat = new ChatController();

        $contactsIdBlacklist = CampaignBlacklist::get()->pluck('contact_id')->toArray();
        //Log::debug('$contactsIdBlacklist');
        //Log::debug($contactsIdBlacklist);

        $contacts = Contact::with('tags', 'colorAvatar')
                            ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'status_id', 'con_phone', 'con_avatar', 'color_avatar_id')
                            ->whereNotIn('con_contacts.id', $contactsIdBlacklist); //Onde não seja um contato que já esteja na blacklist
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($params['q'] != '') {
            $contacts = $contacts->where(function ($query) use($params) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_contacts.con_name', 'like', '%'.trim($params['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('con_contacts.con_phone', 'like', '%'.trim($params['q']).'%');
            });
        }
        $contacts = $contacts->orderBy('con_contacts.created_at', 'DESC')
                            ->limit(15)
                            ->get();

        $chat->chatsAndContactsDetails($contacts);

        return response()->json([
            'contacts'=> $contacts,
        ], 201);
    }
}
