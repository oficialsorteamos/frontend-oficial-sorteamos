<?php

namespace App\Models\Management\Channel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'man_channels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_channel_id',
        'api_communication_id',
        'user_id_connection',
        'cha_api_official',
        'cha_name',
        'cha_description',
        'cha_phone_ddi',
        'cha_phone_number',
        'cha_company_name',
        'cha_company_email',
        'cha_company_site',
        'cha_company_address',
        'cha_status',
        'cha_session_name',
        'cha_session_token',
        'cha_app_id_api',
        'cha_channel_id_api',
        'cha_app_name_api',
        'cha_api_key',
        'whatsapp_business_account_id',
        'cha_due',
        'cha_subscription',
        'cha_automatic_subscription_renewal',
        'cha_trial',
    ];

    //Um canal tem um tipo
    public function typeChannel()
    {
        return $this->belongsTo(\App\Models\Management\Channel\TypeChannel::class , 'type_channel_id'); 
    }

    //Um canal está associado a uma API
    public function api()
    {
        return $this->belongsTo(\App\Models\System\ApiCommunication::class , 'api_communication_id'); 
    }

    //Um canal tem um ou muitos parâmetros
    public function parameters()
    {
        return $this->hasMany(\App\Models\Management\Channel\ParameterChannel::class , 'channel_id'); 
    }

    //Um canal pode ter uma ou muitas notificações
    public function notifications()
    {
        return $this->hasMany(\App\Models\Management\Channel\ChannelNotification::class , 'channel_id')->where('cha_status', 'A'); 
    }

    //Um canal tem uma ou muitas assinaturas
    public function subscription()
    {
        return $this->hasOne(\App\Models\Management\Channel\ChannelSubscription::class , 'channel_id')->where('cha_status', 'A');  
    }

}
