<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Financial\InvoiceController;
use App\Http\Controllers\Setting\PlanController;
use App\Http\Controllers\System\AddressController;
use App\Http\Controllers\System\RoleController;
use App\Http\Controllers\Utils\UtilsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Auth;
use Carbon\Carbon;

use App\Models\Management\User;
use App\Models\System\Role;
use App\Models\System\TypeUser;
use App\Notifications\SendUserNotification;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private $eventController;

    public function __construct()
    {
        $this->eventController = new EventController();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::where('status', 'A')
                    ->whereIn('id', '!=', [1, 3]) //Onde não seja o BOT e nem o Usuário Externo
                    ->get();
        
        return response()->json([
            'users' => $users 
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
        try {
            $invoiceController = new InvoiceController();
            Log::debug('dados usuário');
            Log::debug($request);

            $hasEmail = self::getUserByEmail($request['email']);

            Log::debug('$hasEmail');
            Log::debug($hasEmail);

            //Caso o e-mail não tenha sido usado em outro cadastro ativo
            if(!$hasEmail) {
                //Salva um novo usuário
                $user = new User();
                $user->name = $request['name'];
                $user->username = $request['username'];
                $user->email = $request['email'];
                $user->password = bcrypt($request['password']);
                $user->gender_id = $request['gender']['id'];
                $user->cpf = $request['cpf'];
                //$user->birthday = $request['birthday'];
                $user->birthday = Carbon::createFromFormat('d/m/Y', $request['birthday'])->format('Y-m-d');
                $user->phone = preg_replace('/[^0-9]/', '', $request['phoneNumber']);
                $user->user_id = Auth::user()->id;
                
                if($user->save()) {
                    $addressController = new AddressController();
                    $roleController = new RoleController();
                    $departmentController = new DepartmentController();

                    //Pega o id do usuário recém criado
                    $request->merge([
                        'userId' => $user->id,
                        'typeUserId' => 1 //Seta o tipo de usuário como operador (usuário do sistema)
                    ]);
                    
                    //Salva o endereço do usuário
                    $addressController->store($request);
                    //Associa um departamento ao usuário
                    $roleController->addRoleUser($user->id, $request['role']['id']);
                    //Associa um departamento ao usuário
                    $departmentController->addDepartmentUser($user->id, $request['department']['id']);

                    //Registra o recurso, caso necessário
                    $invoiceController->storeInvoiceResourceControl(1, $user);
                }  
            }
            else {
                return response()->json([
                        'error' => 'O e-mail já está sendo utilizado em outro cadastro de usuário. Favor usar um e-mail diferente.'
                ], 200);
            }
            
                
        } catch(e) {

        }
    }

    //Traz o usuário pelo seu e-mail
    public function getUserByEmail($email)
    {
        $user = User::where('email', $email)
                    ->where('status', 'A')
                    ->first();
        
        return $user;
    }

    //Traz um usuário pelo seu ID
    public function getUserById($userId)
    {   
        $user = User::with('roles')->find($userId);

        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $serviceController = new ServiceController();
        $user = User::with('departments', 'roles', 'addresses' ,'addresses.state', 'addresses.country', 'gender')
                    ->where('id', $id)
                    ->first();
        
        //Log::debug('detalhes do usuário');
        //Log::debug($user);

        $countServicesUser = $serviceController->getCountServicesUser($user->id);
        $user->setAttribute('amountService', $countServicesUser);
        
        //Traz os id's  dos atendimentos já realizados pelo usuário
        $servicesIdUser = $serviceController->getServicesIdUser($user->id);
        //Se o usuário realizou algum atendimento
        if($servicesIdUser->isnotEmpty()) {
            //Traz o nota média dos atendimentos realizados pelo usuário
            $servicesAvg = $serviceController->getAvgServicesEvaluations($servicesIdUser);
            //Média das notas do atendimento realizado pelo usuário (Divide por 2 para readequar ao conceito de 5 estrelas)
            $user->setAttribute('rating', number_format($servicesAvg/2, 2));
        } else {
            $user->setAttribute('rating', '-');
        }
        
        return response()->json(
            $user
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
    public function update(Request $request)
    {
        try {
            $user = User::find($request->userData['id']);
            
            Log::debug('dados para atualização do usuário');
            Log::debug($request);
            $user->name = $request->userData['name'];
            $user->phone = preg_replace('/[^0-9]/', '', $request->userData['phoneNumber']);
            $user->email = $request->userData['email'];
            $user->birthday = Carbon::createFromFormat('d/m/Y', $request->userData['birthday'])->format('Y-m-d');
            $user->gender_id = $request->userData['gender']['id'];
            $user->cpf = $request->userData['cpf'];
            $user->link = $request->userData['link'];
            $user->save();

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
            $user = User::find($id);
            $user->date_deleted = now();
            $user->status = 'I';
            $user->save();

            return response()->json([
                [] 
            ], 200);
        } catch(e) {

        }
    }

    public function fetchUsers(Request $params)
    {
        $utils = new UtilsController();
        $invoiceController = new InvoiceController();

        $users = User::with('departments', 'roles')
                    //->whereIn('status', ['A', 'I']);
                    ->where('status', 'A');
                            //->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_avatar');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($params['q'] != '') {
            //Verifica se a busca coincide com o nome de algum usuário
            $users = $users->where('name', 'like', '%'.trim($params['q']).'%');
            //Verifica se busca coincide com o telefone de algum usuário
            //$users = $users->orWhere('dep_description', 'like', '%'.trim($params['q']).'%');
        }
        $users = $users->whereNotIn('users.id', [1, 2, 3]); //Retira o BOT, o ADMIN da lista e o USUÁRIO EXTERNO
        $users = $users->orderBy('users.status');
        $users = $users->orderBy('users.created_at', 'DESC');
        $users = $users->get();

        Log::debug('usuários');
        Log::debug($users);

        $totalCurrentUserQuota = $invoiceController->getCurrentQuotaResource(1);

        return response()->json([
            'departments'=> $utils->paginateArray($users->toArray(), $params['perPage'], $params['page']),
            'total'=> count($users),
            'totalCurrentUserQuota'=> $totalCurrentUserQuota,
        ], 201);
    }

    //Traz todos os usuários
    public function getUsers()
    {
        $users = User::all();

        return response()->json([
            "users" => $users
        ], 201);
    }

    //Traz todos os usuários, exceto o usuário logado e o robô
    public function getUsersWithoutLogged()
    {
        $userIdLogged = Auth::user()->id;
        $usersException = [1, $userIdLogged];
        $users = User::whereNotIn('id', $usersException)
                    ->get();

        return response()->json([
            "users" => $users
        ], 201);
    }

    //Traz os usuários de um determinado departamento
    public function getUsersDepartment($departmentId)
    {
        $users = User::select('users.id', 'users.name', 'users.email')
                    ->join('man_users_departments', 'users.id', 'man_users_departments.user_id')
                    ->join('man_departments', 'man_users_departments.department_id', 'man_departments.id')
                    ->where('man_departments.id', $departmentId)
                    ->get();

        return response()->json([
            "users" => $users
        ], 201);
    }

    //Traz os usuários de um determinado departamento
    public function getUsersDepartmentTransfer($departmentId, $object = false)
    {
        $users = User::select('users.id', 'users.name', 'users.email')
                    ->join('man_users_departments', 'users.id', 'man_users_departments.user_id')
                    ->join('man_departments', 'man_users_departments.department_id', 'man_departments.id')
                    ->where('man_departments.id', $departmentId)
                    ->where('users.id', '!=', Auth::user()->id) //Só não traz o usuário que está com o atendimento (já que não faz sentido ele transferir o atendimento para ele mesmo)
                    ->where('users.status', 'A')
                    ->get();
        
        if($object == false) {
            return response()->json([
                "users" => $users
            ], 201);
        }
        else {
            return $users;
        }
    }

    public function getUserRole($userId)
    {
        $userRole = User::with('roles')
                        ->where('id', $userId)
                        ->first();

        return $userRole;
    }

    public function getUser($userId)
    {
        $user = User::with('statusLogin')->where('id', $userId)->first();

        return $user;
    }

    public function getUserPermissions($userId)
    {
        $abilities = collect([]);

        //Traz as permissões associadas ao conjunto role/resource específico que um usuário possui
        $userabilities = User::join('sys_users_roles', 'users.id', 'sys_users_roles.user_id')
                            ->join('sys_roles_resources', 'sys_users_roles.role_id', 'sys_roles_resources.role_id')
                            ->join('sys_roles_resources_permissions', 'sys_roles_resources.id', 'sys_roles_resources_permissions.role_resource_id')
                            ->join('sys_permissions', 'sys_roles_resources_permissions.permission_id', 'sys_permissions.id')
                            ->where('users.id', $userId)
                            ->get();
        
        //Cria o array com as permissões associadas ao usuário com seus respectivos recursos 
        foreach($userabilities as $userPermissions) {
            $abilities->push(['action' => $userPermissions->per_name, 'subject' => $userPermissions->res_name]);
        }
        
        return $abilities;
    }

    //Atualiza os dados de acesso e detalhes da conta do usuário
    public function updateAccessDetailAccount(Request $request)
    {
        try {
            $departmentController = new DepartmentController();
            $roleController = new RoleController();
            $user = User::find($request->userData['id']);
            //Salva o username do usuário
            $user->username = $request->userData['username'];
            $user->save();
            
            if(isset($request->userData['departments'])) {
                //Atualiza o departamento do usuário
                $departmentController->updateUserDepartment($request->userData['id'], $request->userData['departments']);
            }
            
            if(isset($request->userData['roles'])) {
                //Atualiza o perfil do usuário
                $roleController->updateRoleUser($request->userData['id'], $request->userData['roles']);
            }

            //Caso a senha tenha sido digitada
            if(isset($request->userData['confirm_password'])) {
                //Caso a atualização da senha seja feita pelo próprio usuário, será solicitada a senha atual
                if(isset($request->userData['old_password'])) {
                    //Caso a senha confira com a senha atual
                    if(Hash::check($request->userData['old_password'], $user->password)) {
                        //Atualiza a senha
                        self::updateUserPassword($request->userData['id'], $request->userData['confirm_password']);
                    }
                    else {
                        return response()->json([
                            'error' => true
                        ], 201);
                    }
                } //Se a senha do usuário for alterada pelo gestor
                else {
                    //Atualiza a senha
                    self::updateUserPassword($request->userData['id'], $request->userData['confirm_password']);
                }
            }
        } catch (e) {

        }
    }

    //Atualiza a senha do usuário
    public function updateUserPassword($userId, $password)
    {
        $user = User::find($userId);
        $user->password = bcrypt($password);
        $user->save();

        return response()->json([
            
        ], 201);

    }

    public function updateUserAddress(Request $request)
    {
        try {
            Log::debug('dados endereço');
            Log::debug($request);
            $addressController = new AddressController();
            //Atualiza o endereço
            $addressController->update($request, $request['id']);

        } catch(e) {

        }
    }

    //Retorna a quantidade total de usuários por situação (Online, Offline, etc.)
    public function getCountUsersBySituation($situationId)
    {
        $usersSituationAmounts = User::where('situation_user_id', $situationId)
                                    ->whereNotIn('id', [1, 2, 3]) //Tira o robô, o ADMIN da conta, e o USUÁRIO EXTERNO
                                    ->count();

        return $usersSituationAmounts;
    }

    //Retorna a quantidade total de usuários por status (Ativo, Inativo, etc.)
    public function getCountUsersByStatus($status)
    {
        $usersStatusAmounts = User::where('status', $status)
                                    ->whereNotIn('id', [1, 2, 3]) //Retira o robô, o ADMIN da conta e o USUÁRIO EXTERNO
                                    ->count();

        return $usersStatusAmounts;
    }

    //Retorna os usuários por status (Ativo, Inativo, etc.)
    public function getUsersByStatus($status)
    {
        $usersStatus = User::where('status', $status)
                                    ->whereNotIn('id', [1, 2, 3]) //Retira o robô, o ADMIN da conta e o USUÁRIO EXTERNO
                                    ->get();

        return $usersStatus;
    }

    //Traz usuários de acordo com o seu perfil
    public function getUsersByRole($roleId)
    {   
        $usersRole = User::with('roles');
        //Filtra por um perfil específico
        $usersRole = $usersRole->whereHas('roles', function($q) use($roleId)
        {
            $q->where('sys_roles.id', $roleId);	
        });
        $usersRole = $usersRole->where('status', 'A');
        $usersRole = $usersRole->get();

        return $usersRole;
    }

    //Dados do usuário (operador)
    public function getProfileUser()
    {
        $user = User::with('roles')
                    ->where('id', Auth::user()->id)
                    ->first();

        Log::debug('profile user');
        Log::debug($user);
        return response()->json([
            'profileUser'=> $user,
        ], 201);
    }

    public function updateUserSituation(Request $params)
    {
        try {
            Log::debug('dados atualização canal');
            Log::debug($params);
            $user = User::find($params['userId']);

            $user->situation_user_id = $params['situationId'];
            $user->save();

            return response()->json(
                $user 
            , 200);
            
        } catch (e) {

        }
    }

    public function uploadPhoto(Request $request)
    {
        try {
            if($request->file()) {
                $utilsController = new UtilsController();
                $user = User::find($request->userId);

                $dateTimeNow = Carbon::now();
                $fileOriginalName = $request->file->getClientOriginalName();
                $nameDivide = explode('.', $fileOriginalName);
                $extensionContent = $nameDivide[1];
                $contentName = preg_replace( '/[^0-9]/', '', $dateTimeNow ).'.'.$extensionContent;
                //Salva no diretório
                //$request->file->storeAs('public/users/'.$request->userId.'/avatar/', $contentName);
                //Salva o avatar no storage
                Storage::disk('spaces')->putFileAs('public/users/'.$request->userId.'/avatar/', $request->file, $contentName, 'public');
                $user->avatar = 'public/users/'.$request->userId.'/avatar/'.$contentName;
                $user->save();

                return response()->json(
                    ''
                , 200);
            }
        } catch(e) {

        }
    }

    //Atualiza as configurações referentes as notificações recebidas pelo usuário
    public function updateUserNotification(Request $request)
    {
        try {
            Log::debug('atualiza as notificações');
            Log::debug($request);
            //Caso a notificação esteja habilitada
            if($request->userData['audio_notification_chat'] == true || $request->userData['audio_notification_chat'] == 1) {
                $notificationChat = 1;
            }
            else {
                $notificationChat = 0;
            }

            $user = User::find($request->userData['id']);
            $user->audio_notification_chat = $notificationChat;
            $user->save();

            return response()->json(
                ''
            , 200);

        } catch (e) {

        }
    }

    public function sendAlertNotification(Request $request)
    {
        try {
            Log::debug('send alert data');
            Log::debug($request);
            $typeNotification = "user-message";
            //Caso o alerta seja enviado para usuários de um ou mais departamentos
            if(isset($request->alertData['departments']) && $request->alertData['departments']) {
                //Para cada departamento
                foreach($request->alertData['departments'] as $department) {
                    //Traz os usuários do departamento
                    $usersDepartment = self::getUsersDepartmentTransfer($department['id'], true);

                    //Notification::sendNow($userT, new SendUserNotification('Olá'));
                    if($usersDepartment) {
                        //Para cada usuário
                        foreach($usersDepartment as $user) {
                            //Salva a notificação no banco ded dados
                            $title = "Mensagem Recebida";
                            $user->notify(new SendUserNotification($title, $request->alertData['message'], Auth::user()->avatar, $typeNotification, $request->alertData['name']));
                            //Envia um evento em tempo real para o frontend
                            $this->eventController->sendAlertNotification($request->alertData['message'], $user['id'], $request->alertData['name']);
                        }
                    }
                }    
            }
            else {
                //Se algum(ns) usuário(s) específico(s) foi(ram) selecionado(s)
                if(isset($request->alertData['users']) && $request->alertData['users']) {
                    //Para cada usuário
                    foreach($request->alertData['users'] as $userData) {
                        //Salva a notificação no banco ded dados
                        $title = "Mensagem Recebida";
                        $user = User::find($userData['id']);
                        $user->notify(new SendUserNotification($title, $request->alertData['message'], Auth::user()->avatar, $typeNotification, $request->alertData['name']));
                        //Envia um evento em tempo real para o frontend
                        $this->eventController->sendAlertNotification($request->alertData['message'], $userData['id'], $request->alertData['name']);
                    }
                }
            }
        } catch(e) {

        }
    }

    //Traz as notificações do usuário
    public function fetchUserNotification($userId)
    {
        $user = User::find($userId);

        return response()->json([
            'notifications' => $user->notifications()->limit(10)->get(),
            'totalUnreadNotifications' => count($user->unreadNotifications)
        ], 200);
    }

    //Marca as notificações de um usuário como lidas
    public function markNotificationAsRead($userId)
    {
        $user = User::find($userId);
        //Marca as notificações como lidas
        $user->unreadNotifications->markAsRead();

        return response()->json([
            
        ], 200);
    }

    public function getOperators()
    {
        $users = User::with('roles');
        //Filtra por um perfil específico
        $users = $users->whereHas('roles', function($q)
        {
            $q->where('sys_roles.id', 2);	
        });

        $users = $users->where('id', '!=', 1) //Onde não é o BOT
                        ->where('status', 'A')
                        ->get(); 
        
        return response()->json([
            'users' => $users 
        ], 200);
    }

    public function getTotalUsersEnabled()
    {
        $totalUsers = User::whereNotIn('id', [1, 2, 3]) //Desconsidera o usuário ROBÔ, o ADMIN e o USUÁRIO EXTERNO
                        ->orderBy('id', 'ASC')
                        ->get();

        return $totalUsers;
    }

    //Traz os tipos de usuários de sistema
    public function getSystemUsers()
    {
        $systemUsers = Role::whereIn('id', [1, 2])
                                ->get();

        return response()->json([
            'systemUsers' => $systemUsers 
        ], 200);
    }

    //Traz usuários de acordo com um ou mais perfis
    public function getUsersByRoles($roleIds)
    {   
        $users = User::with('roles');
        //Filtra por um ou mais perfis
        $users = $users->whereHas('roles', function($q) use($roleIds)
        {
            $q->whereIn('sys_roles.id', $roleIds);	
        });
        $users = $users->where('status', 'A')
                                ->get();

        return $users;
    }

}
