<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_id',
        'chat_id',
        'type_action_id',
        'department_id',
        'user_id',
        'department_id_sender',
        'user_id_sender',
    ];

    //Uma ação pertence a um chat
    public function chat()
    {
        return $this->belongsTo(\App\Models\Chat\Chat::class , 'chat_id'); 
    }

    //Uma ação pertence a um serviço
    public function service()
    {
        return $this->belongsTo(\App\Models\Chat\Service::class , 'service_id'); 
    }

    //Usuário (operador, gestor, etc.) que capturou ou recebeu o atendimento
    public function userReceive()
    {   //ID é o id do departamento e user_id é a chave estrangeira
        return $this->hasOne(\App\Models\Management\User::class , 'id', 'user_id'); 
    }

    //Usuário (operador, gestor, etc.) que transferiu o atendimento
    public function userSender()
    {   //ID é o id do departamento e user_id_sender é a chave estrangeira
        return $this->hasOne(\App\Models\Management\User::class , 'id', 'user_id_sender'); 
    }

    //Departamento que vai realizar o atendimento
    public function departmentReceive()
    {   //ID é o id do departamento e department_id é a chave estrangeira
        return $this->hasOne(\App\Models\Management\Department::class , 'id', 'department_id'); 
    }

    //Departamento que vai realizar o atendimento
    public function departmentSender()
    {   //ID é o id do departamento e department_id_sender é a chave estrangeira
        return $this->hasOne(\App\Models\Management\Department::class , 'id', 'department_id_sender'); 
    }



}
