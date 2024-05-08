<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rol_name',
        'rol_description',
        'rol_status',
    ];


    public function users()
    {
        return $this->belongsToMany(\App\Models\Management\User::class, 'sys_users_roles'); //Retorna todos os usuários associados ao perfil
    }

    //Um perfil pode ter um ou mais recursos
    public function resources()
    {
        return $this->belongsToMany(\App\Models\System\Resource::class, 'sys_roles_resources');
    }
}
