<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleResourcePermission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_roles_resources_permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_resource_id',
        'permission_id',
    ];

    //Um recurso tem uma ou muitas permissões associadas
    public function permissions()
    {
        return $this->hasMany(\App\Models\System\Permission::class, 'id', 'permission_id'); //Retorna todos os usuários associados ao perfil
    }

}
