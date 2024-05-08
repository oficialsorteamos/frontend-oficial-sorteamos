<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\System\Address;
use App\Models\System\Resource;
use App\Models\System\Role;
use App\Models\System\UserRole;
use Auth;

class ResourceController extends Controller
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

    //Traz todos os módulos ativos
    public function getResources($roleId)
    {
        $permissionController = new PermissionController();

        $resources = Resource::with('permissions')
                            ->where('res_status', 'A')
                            ->orderBy('res_name', 'ASC')
                            ->get();

        //Para cada recurso
        foreach($resources AS $key => $resource) {

            foreach($resource['permissions'] AS $permission) {
                $permissionsRole = $permissionController->getPermissionsByRole($roleId);
                $hasPermission= null;
                $hasPermission = array_filter($permissionsRole, function ($permissionRole) use($permission) {
                    return ($permissionRole['id'] == $permission['id']);
                });

                if($hasPermission) {
                    $permission->setAttribute('hasPermission', true);
                }
                else {
                    $permission->setAttribute('hasPermission', false);
                }
            }
        }

        Log::debug('$resources permission');
        Log::debug($resources);

        return $resources;
    }
}
