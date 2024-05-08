<?php

namespace App\Models\Administration\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_notification_id',
        'not_title',
        'not_message',
        'not_status',
    ];

    //Uma notificação pode ter sido enviada para uma ou muitas empresas
    public function companies()
    {
        return $this->belongsToMany(\App\Models\Administration\Company\Company::class, 'adm_notifications_companies'); //Retorna todas as empresas que receberam uma notificação
    }

    //Uma notificação pode ter sido enviada para um ou mais tipos de usuários
    public function typeUsers()
    {
        return $this->belongsToMany(\App\Models\System\Role::class, 'adm_notifications_type_users'); //Retorna todos os tipos de usuários que receberam uma notificação
    }
}
