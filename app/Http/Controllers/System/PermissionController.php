<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\System\Permission;
use App\Models\System\RoleResource;
use App\Models\System\RoleResourcePermission;
use Auth;

class PermissionController extends Controller
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

    //Traz as permissões associadas a um perfil
    public function getPermissionsByRole($roleId)
    {
        $permissions = Permission::select('sys_permissions.id')
                                ->join('sys_roles_resources_permissions', 'sys_permissions.id', 'sys_roles_resources_permissions.permission_id')
                                ->join('sys_roles_resources', 'sys_roles_resources_permissions.role_resource_id', 'sys_roles_resources.id')
                                ->where('sys_roles_resources.role_id', $roleId)
                                ->get()
                                ->toArray();

        return $permissions;
    }

    //Atualiza uma permissão em um perfil
    public function updatePermissionRole(Request $request)
    {
        Log::debug('updatePermissionRole');
        Log::debug($request);
        $roleResource = null;
        $roleResource = RoleResource::where('role_id', $request['roleId'])
                                    ->where('resource_id', $request['permissionData']['resource_id'])
                                    ->first();

        //Se a permissão ESTÁ associada ao perfil
        if($request['permissionData']['hasPermission']) {
            

            RoleResourcePermission::where('role_resource_id', $roleResource['id'])
                                    ->where('permission_id', $request['permissionData']['id']) 
                                    ->delete();
        }// Se a permissão NÃO ESTÁ associado ao perfil
        else {
            if(!$roleResource) {
                $roleResource = new RoleResource();
                $roleResource->role_id = $request['roleId'];
                $roleResource->resource_id = $request['permissionData']['resource_id'];
                $roleResource->save();
            }

            $roleResourcePermission = new RoleResourcePermission();
            $roleResourcePermission->role_resource_id = $roleResource['id'];
            $roleResourcePermission->permission_id = $request['permissionData']['id'];
            $roleResourcePermission->save();
        }
    }
}
