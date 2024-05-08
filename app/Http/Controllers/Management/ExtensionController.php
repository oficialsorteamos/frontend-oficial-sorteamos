<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Integration\VoipController;
use App\Http\Controllers\Utils\UtilsController;
use App\Models\Management\Call\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Management\Extension\Extension;
use App\Models\Management\Extension\ExtensionDialplan;
use App\Models\Management\Extension\ExtensionUser;
use Auth;

use function React\Promise\Stream\first;

class ExtensionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $skip = (($request['page']-1) * $request['perPage']);
        //Traz as ligações registradas pelo sistema
        $extensions = Extension::with('users')
                                ->where('ext_status', 'A');
        if($request['q'] != '') {
            $extensions = $extensions->where('users.id', $request['user']);
        }
        
        $extensions = $extensions->orderBy('voi_extensions.created_at', 'DESC');
        $total = $extensions->count();
        $extensions = $extensions->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();

        return response()->json([
            'extensions' => $extensions,
            'total' => $total,
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
            Log::debug('store extension');
            Log::debug($request);
            
            $extension = new Extension();
            $extension->name = $request['extensionData']['name'];
            $extension->description = $request['extensionData']['description'];

            //Caso o ramal tenha sido salvo
            if($extension->save()) {
                //Para cada usuário de sistema que irá compartilhar esse ramal
                foreach($request['extensionData']['users'] AS $user) {
                    $extensionUser = new ExtensionUser();
                    $extensionUser->extension_id = $extension->id;
                    $extensionUser->user_id = $user['id'];
                    $extensionUser->save();
                }
            }

            /*$isExtension = self::getExtensionByName($request->extensionData['name']);

            //Se NÃO existe ramal com esse nome
            if(!$isExtension) {
                //Se a empresa for a Mais Voip
                if($request->extensionData['voip']['id'] == 1) {
                    $isTrunk = self::getVoipTrunk($request->extensionData['voip']['id']);
                    //Se o tronco não foi cadastrado ainda
                    if(count($isTrunk) == 0) {
                        $host = env('HOST_MAISVOIP');
                        $voip = $voipController->getVoip($request->extensionData['voip']['id']);
                        $register = new Extension();
                        $register->voip_id = $request->extensionData['voip']['id'];
                        $register->name = 'register';
                        $register->secret = $voip['setting']->voi_secret;
                        $register->host = $host;
                        $register->type = 'peer';
                        $register->port = '5060';
                        $register->callbackextension = 's';
                        $register->defaultuser = $voip['setting']->voi_user;
                        $register->ext_status = 'A';
                        $register->save();

                        $trunk = new Extension();
                        $trunk->voip_id = $request->extensionData['voip']['id'];
                        $trunk->name = $voip['setting']->voi_user;
                        $trunk->context = 'from-pstn';
                        $trunk->secret = $voip['setting']->voi_secret;
                        $trunk->host = $host;
                        $trunk->fromdomain = $host;
                        $trunk->nat = 'force_rport,comedia';
                        $trunk->type = 'peer';
                        $trunk->qualify = 'no';
                        $trunk->disallow = 'all';
                        $trunk->allow = 'ulaw,alaw,g729';
                        $trunk->fromuser = $voip['setting']->voi_user;
                        $trunk->defaultuser = $voip['setting']->voi_user;
                        $trunk->ext_status = 'A';
                        $trunk->save();

                        //Cadastro dos planos de discagem
                        self::storeDefaultDialPlans($voip['setting']->voi_user);

                    }
                }

                $extension = new Extension();
                $extension->voip_id = $request->extensionData['voip']['id'];
                $extension->name = $request->extensionData['name'];
                $extension->description = $request->extensionData['description'];
                $extension->context = 'from-pstn';
                $extension->secret = $request->extensionData['secret'];
                $extension->host = 'dynamic';
                $extension->type = 'friend';
                $extension->defaultuser= $request->extensionData['name'];
                $extension->status = 0;
                $extension->ext_status= 'A';
                $extension->save();
            }
            else {
                $errorMessage = 'Não foi possível adicionar o ramal pois já existe um ramal com esse número';
            }*/

            
            return response()->json([
                //'errorMessage' => $errorMessage
            ], 200);

        } catch (e) {

        }
    }

    public function storeDefaultDialPlans($voipUser)
    {
        $dialPlansData[0] = new Request([
            'context'   => 'from-pstn',
            'exten' => ' _XXXX',
            'priority' => 1,
            'app' => 'Dial',
            'app_data' => 'SIP/${EXTEN},30,tT',
        ]);
        $dialPlansData[1] = new Request([
            'context'   => 'from-pstn',
            'exten' => '_55ZZ[2-9]X.',
            'priority' => 1,
            'app' => 'Set',
            'app_data' => 'MIXMONITOR_FILENAME=${UNIQUEID}',
        ]);
        $dialPlansData[2] = new Request([
            'context'   => 'from-pstn',
            'exten' => '_55ZZ[2-9]X.',
            'priority' => 'n',
            'app' => 'MixMonitor',
            'app_data' => '${MIXMONITOR_FILENAME}.wav, ab',
        ]);
        $dialPlansData[3] = new Request([
            'context'   => 'from-pstn',
            'exten' => '_55ZZ[2-9]X.',
            'priority' => 'n',
            'app' => 'Dial',
            'app_data' => 'SIP/'.$voipUser.'/${EXTEN},30,tT',
        ]);
        $dialPlansData[4] = new Request([
            'context'   => 'from-pstn',
            'exten' => '_55ZZ[2-9]X.',
            'priority' => 'n',
            'app' => 'StopMixMonitor',
            'app_data' => '',
        ]);
        $dialPlansData[5] = new Request([
            'context'   => 'from-pstn',
            'exten' => '_55ZZ[2-9]X.',
            'priority' => 'n',
            'app' => 'Hangup',
            'app_data' => '',
        ]);
        $dialPlansData[6] = new Request([
            'context'   => 'from-pstn',
            'exten' => '_XXXX',
            'priority' => 'n',
            'app' => 'Hangup',
            'app_data' => '',
        ]);

        //Salva os planos de discagens
        foreach($dialPlansData as $dialPlan) {
            self::storeExtensionDialPlan($dialPlan);
        }
        
    }

    //insere o plano de discagem (dados que originalmente ficariam no arquivo extensions.conf do Asterisk)
    public function storeExtensionDialPlan(Request $request)
    {
        $dialPlan = new ExtensionDialplan();
        $dialPlan->context = $request['context'];
        $dialPlan->exten = $request['exten'];
        $dialPlan->priority = $request['priority'];
        $dialPlan->app = $request['app'];
        $dialPlan->app_data = $request['app_data'];
        $dialPlan->save() ;
    }

    //Traz um ramal pelo seu nome
    public function getExtensionByName($extensionName)
    {
        $extension = Extension::where('name', $extensionName)
                                ->where('ext_status', 'A')
                                ->first();
        return $extension;
    }

    //Traz as configuração do tronco de uma empresa
    public function getVoipTrunk($voipId)
    {
        $extensionTrunk = Extension::where('voip_id', $voipId)
                                    ->where('host','!=', 'dynamic')
                                    ->get();

        return $extensionTrunk;
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

    //Traz o associação de ramal e usuário pelos seus respecitivos ID's
    public function getExtensionByExtensionAndUser($extensionId, $userId)
    {
        $extensionUser = ExtensionUser::where('extension_id', $extensionId)
                                        ->where('user_id', $userId)
                                        ->first();
        
        return $extensionUser;
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
        Log::debug('ramal $request');
        Log::debug($request);
        $extension = Extension::find($request['id']);

        $extension->name = $request['name'];
        $extension->description = $request['description'];

        if($extension->save()) {
            ExtensionUser::where('extension_id', $request['id'])
                        ->update([
                            'ext_status' => 'I'
                        ]);

            foreach($request['users'] AS $user) {
                $extensionUser = NULL;
                //Traz o ramal associado ao usuário, se houver
                $extensionUser = self::getExtensionByExtensionAndUser($extension->id, $user['id']);
                //Se ramal associado ao usuário já existir
                if($extensionUser) {
                    //Ativa o mesmo
                    $extensionUser->ext_status = 'A';
                    $extensionUser->save();
                } //Cria uma associação entre o ramal com o usuário
                else {
                    $extensionUser = new ExtensionUser();
                    $extensionUser->extension_id = $extension->id;
                    $extensionUser->user_id = $user['id'];
                    $extensionUser->save();
                }
            }
        }
        
        return response()->json([
            ''
        ], 200);

        /*try {
            $errorMessage = '';
            $extension = Extension::find($request['id']);

            $isExtension = self::getExtensionByName($request['name']);

            //Se o usuário alterou o número do ramal e esse número já está sendo utilizado
            if($extension->name != $request['name'] && $isExtension) {
                $errorMessage = 'Não foi possível atualizar o ramal pois já existe um ramal com o número informado';
            }
            else {
                $extension->voip_id = $request['voip']['id'];
                $extension->name = $request['name'];
                $extension->description = $request['description'];
                $extension->secret = $request['secret'];
                $extension->defaultuser= $request['name'];
                $extension->save();
            }
            
            return response()->json([
                'errorMessage' => $errorMessage
            ], 200);
        } catch (e) {

        }*/
       
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
            $extension = Extension::find($id);
            $extension->qualify = 'no'; //Desabilita o ramal no Asterisk
            $extension->status = '0';
            $extension->ext_status = 'I';
            $extension->save();
            
        } catch(e) {

        }
    }


    //Traz todos os ramais ativos cadastrados
    public function getExtensions()
    {
        $extensions = Extension::where('host', 'dynamic')
                                ->where('ext_status', 'A')
                                ->get();
        
        return response()->json([
            'extensions' => $extensions
        ], 200);
    }

    //Atualiza o status do canal. 0 = disponível; 1 = Ocupado
    public function updateStatusExtension($extensionId, $statusId)
    {
        $extension = Extension::where('id', $extensionId)
                                ->where('ext_status', 'A')
                                ->first();
        
        $extension->status = $statusId;
        $extension->save();
    }

    //Traz o ramal associado ao usuário
    public function getUserLoggedExtension()
    {
        $userExtension = Extension::select('voi_extensions.id', 'voi_extensions.name')
                                    ->join('voi_extensions_users', 'voi_extensions.id', 'voi_extensions_users.extension_id')
                                    ->where('voi_extensions_users.ext_status', 'A')
                                    ->where('voi_extensions.ext_status', 'A')
                                    ->where('voi_extensions_users.user_id', Auth::user()->id) //Onde o ramal está asociado ao usuário logado
                                    ->first();

        return $userExtension;
    }
}
