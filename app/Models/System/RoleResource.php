<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_roles_resources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id',
        'resource_id',
    ];

    

    //Retorna as permissões cadastradas para um conjunto role/permissão específico
    public function rolesResourcesPermissions()
    {
        return $this->hasMany(\App\Models\System\RoleResourcePermission::class, 'role_resource_id'); 
    }

}
