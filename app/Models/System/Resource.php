<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_resources';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'res_name',
        'res_description',
        'res_status',
    ];

    //Um recurso pode estar associado a um ou muitos perfis
    public function roleResources()
    {
        return $this->hasMany(\App\Models\System\RoleResource::class , 'resource_id');
    }

    //Um recurso pode ter uma ou muitas permissões associadas
    public function permissions()
    {
        return $this->hasMany(\App\Models\System\Permission::class , 'resource_id');
    }
}
