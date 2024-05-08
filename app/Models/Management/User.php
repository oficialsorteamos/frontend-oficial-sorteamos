<?php

namespace App\Models\Management;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'gender_id',
        'cpf',
        'birthday',
        'phone',
        'avatar',
        'link',
        'status',
        'situation_user_id',
        'audio_notification_chat',
        'date_deleted',
        'user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //Um usuário pode ter muitos departamentoa associados ao mesmo. Na função AS é passado o nome da tabela associativa
    public function departments()
    {
        return $this->belongsToMany(\App\Models\Management\Department::class, 'man_users_departments');
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Models\System\Role::class, 'sys_users_roles'); //Retorna todos os perfis associados ao usuário
    }

    //Um usuário pode ter um ou mais endereços
    public function addresses()
    {
        return $this->hasMany(\App\Models\System\Address::class , 'user_id')->where('type_user_id', 1); //Onde é um usuário do sistema 
    }

    //Gênero do usuário (masculino, feminino, etc.) 
    public function gender()
    {
        return $this->hasOne(\App\Models\System\Gender::class , 'id', 'gender_id'); 
    }

    //Verifica se usuário está logado 
    public function statusLogin()
    {
        return $this->belongsTo(\App\Models\System\PersonalAccessToken::class , 'id', 'tokenable_id'); 
    }
}
