<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Utils\UtilsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\System\AddressController;
use App\Http\Controllers\System\DddStateController;
use App\Http\Controllers\System\StateCountryController;
use App\Models\Campaign\CampaignBlacklist;
use App\Models\Campaign\CampaignTagHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Auth;

use App\Models\Contact\Contact;
use App\Models\Chat\Chat;
use App\Models\Contact\Blocked;
use App\Models\Contact\ContactTag;
use App\Models\System\StateCountry;

class ContactController extends Controller
{
    private $dddStateController;
    private $chatController;

    public function __construct()
    {
        $this->dddStateController = new DddStateController();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $params)
    {
        $chat = new ChatController();
        
        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($params['page']-1) * $params['perPage']);
        
        

        $contacts = Contact::with('tags', 'colorAvatar', 'blocked')
                            ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'status_id', 'con_phone', 'con_avatar', 'color_avatar_id')
                            //Busca os contatos de acordo com a paginação
                            ->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                            ->take($params['perPage']); //Quantidade de itens trazidos
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($params['q'] != '') {
            //Verifica se a busca coincide com o nome de algum usuário
            $contacts = $contacts->where('con_name', 'like', '%'.trim($params['q']).'%');
            //Verifica se busca coincide com o telefone de algum usuário
            $contacts = $contacts->orWhere('con_phone', 'like', '%'.trim($params['q']).'%');
        }
        $contacts = $contacts->orderBy('con_contacts.created_at', 'DESC');
        if($params['q'] == '') {
            $contacts = $contacts->get();

            $total = Contact::count();
        }
        else {
            $contacts = $contacts->get();
            //Pega o total de contatos de acordo com a busca
            $total = self::getTotalContactsFiltered($params);
        }
        
        $chat->chatsAndContactsDetails($contacts);

        return response()->json([
            'contacts'=> $contacts,
            'total'=> $total,
        ], 201);
    }
    
    public function getTotalContactsFiltered($request)
    {
        $totalContacts = Contact::where('con_name', 'like', '%'.trim($request['q']).'%') //Verifica se a busca coincide com o nome de algum usuário
                            //Verifica se busca coincide com o telefone de algum usuário
                            ->orWhere('con_phone', 'like', '%'.trim($request['q']).'%')
                            ->count();
        
        return $totalContacts;
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
    //Grava o contato quando os dados vem do Whatsapp 
    public function store(Request $request)
    {
        try {
            //Log::debug('$request contact');
            //Log::debug($request);
            $chat = new ChatController();
            $utilsController = new UtilsController();
            //Só deixa os números 
            $phoneNumber = preg_replace( '/[^0-9]/', '', $request->phoneNumber);
            
            $contact = new Contact();
            $contact->con_name = $request->name? $request->name : $phoneNumber;
            $contact->con_phone = $phoneNumber;
            $contact->con_email = $request['email']? $request['email'] : null;
            $contact->gender_id = $request['gender']? $request['gender']['id'] : 3; //Se não foi selecionado gênero, coloca o mesmo como indefinido
            $contact->con_birthday = $request['birthday']? $request['birthday'] : null;
            $contact->save();

            //Verifica se já existe chat para este contato
            $chatContact = Chat::where('contact_id', $contact->id)->first();
            
            //Caso NÃO exista chat para esse contato
            if(!$chatContact) {
                //Cria um chat para o contato
                $chatContact =$chat->storeChat($contact->id);
            }

            //Se o usuário possui avatar (foto de perfil disponível)
            if(isset($request->avatarUrl)) {
                $contact = Contact::find($contact->id);
                //Pega o conteúdo da foto
                $response = Http::get($request->avatarUrl);
                $dateTimeNow = Carbon::now();
                $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.jpg';
                $filePath = storage_path('app/public/chats/chat'.$chatContact['id'].'/avatar/'.$contentName);
                //Salva no diretório
                //Storage::disk('public')->put('chats/chat'.$chatContact['id'].'/avatar/'.$contentName, $response->body());
                //Converte o avatar em base64
                $fileBase64 = $utilsController->convertToBase64($filePath, $response->body(), 3);
                //Salva o avatar no storage
                Storage::disk('spaces')->putFileAs('public/chats/chat'.$chatContact['id'].'/avatar/', $fileBase64, $contentName, 'public');
                $contact->con_avatar = 'public/chats/chat'.$chatContact['id'].'/avatar/'.$contentName;
                $contact->save();
            }

            return response()->json([
                'contact' => $contact,
                'chat' => $chatContact
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
        $address = new AddressController();
        $socialNetwork = new SocialNetworkController();

        $contact = Contact::with('tags', 'colorAvatar', 'gender')
                            ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'status_id', 'con_phone', 'con_email as email', 
                            'con_birthday as birthday', 'created_at', 'con_avatar', 'color_avatar_id')
                            ->where('id', $id)
                            ->get();
        
        $chat->chatsAndContactsDetails($contact);
        
        //Quantidade de endereços associados ao contato
        $amountAddresses = $address->countAddressesUser($contact[0]->id, 2);
        $contact[0]['amountAddresses'] = $amountAddresses;

        //Quantidade de redes sociais associadas ao contato
        $amountSocialNetworks = $socialNetwork->countSocialNetworksContact($contact[0]->id);
        $contact[0]['amountSocialNetworks'] = $amountSocialNetworks;

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
        try {
            Log::debug('update contact');
            Log::debug($request->contactData);
            $contact = Contact::find($id);
            $contact->con_name = $request->contactData['con_name'];
            $contact->gender_id = $request->contactData['gender']['id'];
            //$contact->tag_id = isset($request->contactData['tag']['id'])? $request->contactData['tag']['id'] : null;
            $contact->con_email = $request->contactData['email'];
            $contact->con_birthday = $request->contactData['birthday'];

            if($contact->save()) {
                //Se foi adicionada alguma tag
                if(isset($request->contactData['tags']) && !empty($request->contactData['tags'])) {
                    $tagData = new Request([
                        'tagData' => [
                            'id' => $request->contactData['id'],
                            'tags' => $request->contactData['tags'],
                        ],
                        'serviceData' => isset($request->contactData['service'])? $request->contactData['service'] : null, //Conjunto de mensagens que serão carregadas
                    ]);
                    //Atualiza as tags
                    self::updateTag($tagData);
                }
                else {
                    //Remove as tags associadas ao contato
                    self::removeTagsByContact($id);
                }
            }
            
            return response()->json(
                $request->contactData
            , 200);

        } catch (e) {

        }
    }

    //Remove as tags associadas a um contato
    public function removeTagsByContact($contactId)
    {
        //Remove todas as tags associadas ao contato no momento
        ContactTag::where('contact_id', $contactId)
                    ->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /*
        $contact = Contact::find($id);
        $contact->delete();

        return response()->json([
            ''
        ], 200);*/
    }

    public function uploadPhoto(Request $request)
    {
        try {
            if($request->file()) {
                
                $contact = Contact::find($request->contactId);

                $chatId = $request->chatId;
                $dateTimeNow = Carbon::now();
                $fileOriginalName = $request->file->getClientOriginalName();
                $nameDivide = explode('.', $fileOriginalName);
                $extensionContent = $nameDivide[1];
                $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.'.$extensionContent;
                //Salva no diretório
                $request->file->storeAs('public/chats/chat'.$chatId.'/avatar/', $contentName);
                //Storage::disk('public')->put('chats/chat'.$chatId.'/avatar/'.$contentName, $request->file());

                $contact->con_avatar = 'storage/chats/chat'.$chatId.'/avatar/'.$contentName;
                $contact->save();

                return response()->json(
                    ''
                , 200);
            }
        } catch(e) {

        }
    }

    //Retorna a quantidade total de contatos cadastrados
    public function getCountAllContacts()
    {
        $contactsAmounts = Contact::count();

        return $contactsAmounts;
    }

    //Retorna a quantidade total de contatos por gênero
    public function getCountContactsByGender($genderId)
    {
        $contactsGenderAmount = Contact::where('gender_id', $genderId)
                                        ->count();

        return $contactsGenderAmount;
    }

    //Retorna a quantidade de contatos por faixa etária
    public function getCountContactsByAgeGroup($arrayAgeGroup)
    {
        $arrayCountAgeGroup = [];
        foreach($arrayAgeGroup as $key => $ageGroup) {
            if(is_null($ageGroup['end_age'])) {
                $ageGroup['end_age'] = $ageGroup['start_age'];
            }
            
            $startAgeDate = Carbon::today()->subYears($ageGroup['start_age']);
            $endAgeDate = Carbon::today()->subYears($ageGroup['end_age'])->addYear()->subDay(); // plus 1 year minus a day    

            $countAgeGroup = Contact::whereBetween('con_birthday', [$endAgeDate, $startAgeDate])
                                    ->whereNotNull('con_birthday')
                                    ->count();
            $arrayCountAgeGroup[$key]['label'] = $ageGroup['label']; 
            $arrayCountAgeGroup[$key]['count'] = $countAgeGroup; 
        }

        //Log::debug('count age group');
        //Log::debug($arrayCountAgeGroup);
        return $arrayCountAgeGroup;   
    }
    
    public function getCountContactsPerStates()
    {
        $ddds = $this->dddStateController->getDddsState();
        $contactsPerState = collect([]);
        //Conta a quantidade de contatos em cada DDD
        foreach($ddds as $key => $ddd) {
            $countContactsStates['state'] = 'BR-'. $ddd['State']['sta_uf'];
            $countContactsStates['amountContacts'] = Contact::where('con_phone', 'LIKE', '55'.$ddd->ddd_number.'%')
                                                                ->count();
            
           $contactsPerState->push($countContactsStates);
        }
        //Agrupa somando a quantidade de contatos por estados 
        $contactsPerStateGrouped = $contactsPerState->groupBy('state')->map(function ($row) {
            return $row->sum('amountContacts');
        });

        //Log::debug('contatos por estados');
        //Log::debug($contactsPerStateGrouped);

        return $contactsPerStateGrouped;
    }

    public function updateTag(Request $request)
    {
        try {
            //Log::debug('tags update');
            //Log::debug($request);
            
            //Traz todas as tags associadas ao contato no momento
            $contactTags = ContactTag::where('contact_id', $request->tagData['id'])
                                    ->get();
            
            //Remove todas as tags associadas ao contato no momento
            ContactTag::where('contact_id', $request->tagData['id'])
                      ->delete();
            
            //Se o atendimento está associado a alguma campanha
            if(isset($request->serviceData['campaign_id'])) {
                //Remove as tags do usuário durante uma campanha
                CampaignTagHistory::where('campaign_id', $request->serviceData['campaign_id'])
                                ->where('contact_id', $request->tagData['id'])
                                ->delete();
            }
             
            //Para cada tag selecionada (atribuída ao contato)
            foreach($request->tagData['tags'] as $key => $tag) {
                $tagExist = [];

                $tagExist = $contactTags->where('tag_id', $tag['id']);

                //Log::debug('$tagExist new');
                //Log::debug($tagExist);

                //Se a tag a ser adicionada já existia anteriormente
                if($tagExist != '[]') {
                    $newContactTag = new ContactTag();
                    $newContactTag->contact_id = $request->tagData['id']; 
                    $newContactTag->tag_id = $tagExist[$key]['tag_id']; 
                    $newContactTag->user_id = $tagExist[$key]['user_id']; 
                    $newContactTag->campaign_id = $tagExist[$key]['campaign_id']; 
                    $newContactTag->save();
                }
                else {
                    $newContactTag = new ContactTag();
                    $newContactTag->contact_id = $request->tagData['id']; 
                    $newContactTag->tag_id = $tag['id'];
                    $newContactTag->user_id = Auth::user()->id;
                    $newContactTag->campaign_id = isset($request->serviceData['campaign_id'])? $request->serviceData['campaign_id'] : null;
                    $newContactTag->save();
                }

                //Se for uma tag associado a um usuário durante uma campanha, grava no histórico de tags de campanha
                if(isset($request->serviceData['campaign_id'])) {
                    $tagsCampaignExist = [];
                    $tagsCampaignExist = $contactTags->where('tag_id', $tag['id']);
                                                    //->where('campaign_id', $request->serviceData['campaign_id']);
                    
                    //Log::debug('tag exist');
                    //Log::debug($tagsCampaignExist);

                    //Se a tag a ser adicionada já existia anteriormente
                    if($tagsCampaignExist != '[]') {
                        $isTagsCampaing = $tagsCampaignExist->where('campaign_id', $request->serviceData['campaign_id']);

                        //Log::debug('isTagsCampaing');
                        //Log::debug($isTagsCampaing);

                        //Se for uma tag da campanha atual
                        if($isTagsCampaing != '[]') {
                            $newCampaignTag = new CampaignTagHistory();
                            $newCampaignTag->contact_id = $request->tagData['id']; 
                            $newCampaignTag->tag_id = $tagsCampaignExist[$key]['tag_id']; 
                            $newCampaignTag->user_id = $tagsCampaignExist[$key]['user_id']; 
                            $newCampaignTag->campaign_id = $tagsCampaignExist[$key]['campaign_id']; 
                            $newCampaignTag->save();
                        }
                        
                    } //Se a tag não estava associada anteriomente ao usuário 
                    else {
                        $newCampaignTag = new CampaignTagHistory();
                        $newCampaignTag->contact_id = $request->tagData['id']; 
                        $newCampaignTag->tag_id = $tag['id'];
                        $newCampaignTag->user_id = Auth::user()->id;
                        $newCampaignTag->campaign_id = $request->serviceData['campaign_id'];
                        $newCampaignTag->save();
                    }
                }
            }
            
            return response()->json([
                'tags' => $request->tagData['tags']
            ], 200);

        } catch (e) {

        }
    }

    public function fetchContactsChat(Request $params)
    {
        $chat = new ChatController();

        $contacts = Contact::with('tags', 'colorAvatar')
                            ->select('con_contacts.id as id' ,'con_name', 'gender_id', 'status_id', 'con_phone', 'con_avatar', 'color_avatar_id');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($params['q'] != '') {
            $contacts = $contacts->where(function ($query) use($params) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('con_contacts.con_name', 'like', '%'.trim($params['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('con_contacts.con_phone', 'like', '%'.trim($params['q']).'%');
            });
        }
        $contacts = $contacts->orderBy('con_contacts.created_at', 'DESC');
        $contacts = $contacts->limit(15);
        $contacts = $contacts->get();

        $chat->chatsAndContactsDetails($contacts);

        return response()->json([
            'contacts'=> $contacts,
        ], 201);
    }

    //Traz o contato através do número de telefone
    public function getContactByPhoneNumber($phoneNumber)
    {   
        $contact = null;
        $contact = Contact::where('con_phone', $phoneNumber)
                            ->first();
        
        return $contact;
    }

    public function storeContactMailing($contactData)
    {
        $newContact = new Contact();
        $newContact->con_phone = '55'.$contactData[0];
        //Deixa apenas os números do CPF/CNPJ 
        $identificationContact =  preg_replace('/[^0-9]/', '', $contactData[1]);
        //Se o usuário digitou algum CPF ou CNPJ
        if($identificationContact != null && $identificationContact != '') {
            //Se for um CPF (apenas números)
            if(strlen($identificationContact) == '11') {
                $newContact->con_cpf = $identificationContact; 
            }//Se for um CNPJ (apenas números)
            else if(strlen($identificationContact) == '14') {
                $newContact->con_cnpj = $identificationContact;
            }
        }
        //Se houver um nome, salva o mesmo removendo caracteres especiais e colocando a primeira letra de cada palavra como maiúscula, senão, salva o número de telefone como o nome
        //$newContact->con_name = isset($contactData[2])? ucwords(strtolower(preg_replace('/[^ \w]+/', '', $contactData[2]))) : '55'.$contactData[0];
        $newContact->con_name = isset($contactData[2])? ucwords(strtolower(mb_convert_encoding($contactData[2] , 'UTF-8' , 'ASCII'))) : '55'.$contactData[0];
        //Log::debug('nome contato mailing');
        //Log::debug($newContact->con_name);
        //Caso o contato seja do sexo masculino
        if(strtolower($contactData[3]) == 'masculino' || strtolower($contactData[3]) == 'm') {
            $newContact->gender_id = 1;
        } //Caso seja do sexo feminino
        else if(strtolower($contactData[3]) == 'feminino' || strtolower($contactData[3]) == 'f') {
            $newContact->gender_id = 2;
        }
        else {
            //Sexo indefinido
            $newContact->gender_id = 3;
        }
        //Se o contato possui uma data de nascimento
        if($contactData[4] != null && $contactData[4] != '' && strlen($contactData[4]) == 10 ) {
            $dateBirthday = explode( '/', $contactData[4] );
            //Se o contato possui uma data de nascimento com dia, mês e ano
            if(count($dateBirthday) == 3) {
                //Se o dia, mês e ano foram inseridos corretamente
                if(checkdate($dateBirthday[1], $dateBirthday[0], $dateBirthday[2]) == true) {
                    $newContact->con_birthday = Carbon::createFromFormat('d/m/Y', $contactData[4])->format('Y-m-d');
                }
            }
        }
        //Caso seja um e-mail válido
        if (filter_var($contactData[5], FILTER_VALIDATE_EMAIL)) {
            $newContact->con_email = $contactData[5];
        }
        //Escolhe o id da cor do avatar do contato aleatoriamente
        $newContact->color_avatar_id = rand(1, 6);
        //Caso o contato seja salvo
        if($newContact->save()) {
            $chat = new ChatController();
            //Cria um chat para o contato
            $chat->storeChat($newContact->id);
            //Caso o mailing tenha rua, bairro, CEP, cidade e UF
            if($contactData[6] != '' && $contactData[9] != '' && $contactData[10] != '' && $contactData[11] != '' && $contactData[12] != '') {
                $state = null;
                $stateController = new StateCountryController();
                //Busca o estado pela UF
                $state = $stateController->getStateByUf($contactData[12]);
                //Caso não tenha encontrado o estado pela UF, tenta pelo nome do mesmo
                if(!$state) {
                    $state = $stateController->getStateByName($contactData[12]);
                }
                $country['id'] = 1; //Brasil
                //Caso o estado tenha sido 
                if($state) {
                    $request = new Request([
                        'userId'   => $newContact->id,
                        'typeUserId' => 2,
                        'cep' => $contactData[10],
                        'street' => ucwords(strtolower($contactData[6])),
                        'district' => ucwords(strtolower($contactData[9])),
                        'number' => $contactData[7],
                        'address_complement' => ucwords(strtolower($contactData[8])),
                        'city' => ucwords(strtolower($contactData[11])),
                        'state' => $state,
                        'country' => $country, //Brasil
                    ]);

                    $addressController = new AddressController();
                    $addressController->store($request, true);
                }
            }   
        }  
        
        return $newContact;
    }

    //Traz os contatos que possui uma ou mais tags específicas
    public function getContactsByTags($tags)
    {
        $contactsTags = ContactTag::select('contact_id')
                                ->whereIn('tag_id', $tags)
                                ->distinct() //Traz o id do contato sem repetir o mesmo
                                ->get();
        
        Log::debug('contatos com tag espec.');
        Log::debug($contactsTags);

        return $contactsTags;
    }

    //Adiciona um contato na blacklist
    public function addContactBlacklistCampaign(Request $request)
    {
        $contactBlacklistCampaign = new CampaignBlacklist();
        $contactBlacklistCampaign->campaign_id = $request['campaignId'];
        $contactBlacklistCampaign->contact_id = $request['contactId'];
        $contactBlacklistCampaign->user_id = Auth::user()->id;

        $contactBlacklistCampaign->save();
    }

    //Traz o contato que está na blacklist pelo ID
    public function getContactBlacklistCampaign($contactId)
    {
        $contactBlacklist = CampaignBlacklist::where('contact_id', $contactId)->first();

        return $contactBlacklist;
    }

    //Adição de contato rápido na tela do operador
    public function addQuickContact(Request $request)
    {
        Log::debug('addQuickContact request');
        Log::debug($request);
        if(isset($request['con_name'])) {
            $request['name'] = $request['con_name'];
        }
        $phoneNumber = mb_substr($request['phoneNumber'], 1);
        //Verifica se já existe um contato com esse número
        $contactExist = self::getContactByPhoneNumber($phoneNumber);
        Log::debug('contato existe');
        Log::debug($contactExist);
        if($contactExist) {
            return response()->json([
                'message'=> 'Não foi possível adicionar o contato pois o mesmo já se encontra na base dados',
            ], 201);
        }
        else {
            //Se o usuário não digitou o nome do contato
            if($request['name'] == '') {
                //O nome do contato passa ser o número de telefone
                $request['name'] = $request['phoneNumber'];
            }
            //Salva o contato
            self::store($request);
        }
    }

    public function getContactById($id)
    {
        $contact = Contact::find($id);

        return $contact;
    }

    public function blockContact($contactId)
    {
        $blocked = new Blocked();
        $blocked->contact_id = $contactId;
        $blocked->user_id = Auth::user()->id;
        $blocked->save();

        return response()->json([
            'contactId'=> $contactId,
        ], 201);
    }

    public function unlockContact($contactId)
    {
        Blocked::where('contact_id', $contactId)->delete();

        return response()->json([
            'contactId'=> $contactId,
        ], 201);
    }

    public function updateContactGeneral(Request $request)
    {
        try {
            Log::debug('updateContactGeneral');
            Log::debug($request);
            $contact = Contact::find($request->contactData['id']);
            $contact->con_name = $request->contactData['con_name'];
            $contact->gender_id = $request->contactData['gender']['id'];
            //$contact->tag_id = isset($request->contactData['tag']['id'])? $request->contactData['tag']['id'] : null;
            $contact->con_email = $request->contactData['con_email'];
            $contact->con_birthday = isset($request->contactData['con_birthday'])? Carbon::createFromFormat('d/m/Y', $request->contactData['con_birthday'])->format('Y-m-d'): null;
            $contact->con_cpf = $request->contactData['legalPersonChecked'] == '0'? $request->contactData['con_cpf']: null;
            $contact->con_cnpj = $request->contactData['legalPersonChecked'] == '1'? $request->contactData['con_cnpj']: null;

            $contact->save();

            return response()->json([
                'contact' => $request->contactData
            ], 200);
            
        } catch (e) {

        }
    }

    //Verifica se o contato já possui uma determinada tag
    public function checkContactHasTag($contactId, $tagId)
    {
        $hastag = ContactTag::where('contact_id', $contactId)
                            ->where('tag_id', $tagId)
                            ->first();

        return $hastag;
    }

    //Associa uma tag em um contato
    public function addTagContact($contactId, $tagId)
    {

        $hastag = self::checkContactHasTag($contactId, $tagId);

        Log::debug('$hastag');
        Log::debug($hastag);

        //Se o contato ainda NÃO possui uma determinada tag
        if(!$hastag) {
            $contactTag = new ContactTag();

            $contactTag->contact_id = $contactId;
            $contactTag->tag_id = $tagId;
            $contactTag->user_id = 1; //Robô
            $contactTag->save();
        }
    }
}
