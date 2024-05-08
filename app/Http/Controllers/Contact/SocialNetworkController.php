<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Contact\TypeSocialNetwork;
use App\Models\Contact\SocialNetwork;

class SocialNetworkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    {   //Salva a rede social
        try {
            $newSocialNetwork = new SocialNetwork();
            $newSocialNetwork->contact_id = $request['userId'];
            $newSocialNetwork->type_social_network_id = $request->typeSocialNetwork['id'];
            $newSocialNetwork->soc_address = $request['url'];
            
            $newSocialNetwork->save();

            return response()->json(
                ''    
            , 201);
        }
        catch(e) {

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Deleta o bloco
        SocialNetwork::find($id)->delete();

        return response()->json([], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            Log::debug('Dados para update');
            Log::debug($request);
            $socialNetwork = SocialNetwork::find($id);

            $socialNetwork->type_social_network_id = $request->typeSocialNetwork['id'];
            $socialNetwork->soc_address = $request['url'];
            $socialNetwork->save();

            return response()->json(
                []
            , 200);
        } catch(e) {

        }
        
    }

    //Traz todos os fluxos da pipeline
    public function fetchTypeSocialNetworks() 
    {
        $typeSocialNetworks = TypeSocialNetwork::where('typ_status', 'A')
                                            ->get();
        
        return response()->json(
            $typeSocialNetworks
        , 201);

    }

    public function fetchSocialNetworksContact(Request $request)
    {
        try {

            //Traz as redes sociais cadastradas de um contato
            $socialNetworks = SocialNetwork::with('typeSocialNetwork')
                                        ->select('id', 'type_social_network_id', 'soc_address as url')
                                        ->where('contact_id', $request['userId'])
                                        ->get();
            
            Log::debug('Redes Sociais');
            Log::debug($socialNetworks);
            return response()->json(
                $socialNetworks    
            , 201);
        } catch(e) {

        }
    }

    //Quantidade de redes sociais cadastradas para um determinado contato
    public function countSocialNetworksContact($contactId)
    {
        $amountSocialNetworks = SocialNetwork::where('contact_id', $contactId)
                                        ->count();
        
        return $amountSocialNetworks;
    }
}
