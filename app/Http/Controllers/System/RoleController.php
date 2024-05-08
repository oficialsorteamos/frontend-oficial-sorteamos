<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\System\Address;
use App\Models\System\Permission;
use App\Models\System\Role;
use App\Models\System\UserRole;
use Auth;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            //Traz o perfil do usuário logado
            $roleUser = self::getRoleByUserId(Auth::user()->id);
            //Se for um Administrador
            if($roleUser->role_id == 3) {
                $roles = Role::where('rol_status', 'A')
                        ->get();
            }
            else {
                //Não traz o perfil de administrador e nem do white label
                $roles = Role::whereNotIn('id', [3, 4])
                        ->where('rol_status', 'A')
                        ->get();
            }
            
        
            return response()->json([
                'roles' => $roles    
            ], 201);
        } catch (e) {

        }
        
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
    public function update(Request $request, $id)
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
        
    }

    //Insere um perfil para um usuário
    public function addRoleUser($userId, $roleId)
    {
        $userRole = new UserRole();
        $userRole->user_id = $userId;
        $userRole->role_id = $roleId;
        $userRole->save();
    }

    //Atualiza os perfis em que o usuário faz parte
    public function updateRoleUser($userId, $roles)
    {   
        try {
            //Remove o departamento onde o usuário estava lotado atualmente
            UserRole::where('user_id', $userId)
            ->delete();

            foreach($roles as $role) {
                $newUserRole = new UserRole();
                $newUserRole->user_id = $userId;
                $newUserRole->role_id = $role['id'];
                $newUserRole->save();
            }
            
        } catch(e) {

        }
    }

    public function getRoleByUserId($userId)
    {
        $roleUser = UserRole::where('user_id', $userId)->first();

        return $roleUser;
    }

    public function fetchRoles(Request $request)
    {
        //Quantidade de itens que serão 'pulados', ou seja, que serão desconsiderados por já terem sido carregados
        $skip = (($request['page']-1) * $request['perPage']);

        $roles = Role::with('resources');
        /*if($request['q'] != '') {
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
        }*/
        $roles = $roles->whereNotIn('sys_roles.id', [3]); //Não trazer o perfil de administrador
        $total = $roles->count();
                        //Busca os contatos de acordo com a paginação
        $roles = $roles->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                        ->take($request['perPage']) //Quantidade de itens trazidos
                        ->get();

        //Log::debug('roles');
        //Log::debug($roles);

        return response()->json([
            'roles' => $roles,
            'total' => $total
        ], 200);

    }
}
