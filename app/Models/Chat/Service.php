<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Service extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chat_id',
        'channel_id',
        'campaign_id',
        'type_status_service_id',
        'dialer_fowarding_setting_id',
        'ser_protocol_number',
        'ser_dt_end_service',
        'user_id_end_service',
        'ser_new_service',
    ];

    public function actions()
    {   //Um serviço tem uma ou mais ações associadas
        return $this->hasMany(\App\Models\Chat\Action::class , 'service_id'); 
    }

    //Um atendimento tem uma avaliação associada ao mesmo
    public function evaluation()
    {
        return $this->hasOne(\App\Models\Chat\ServiceEvaluation::class , 'service_id')->select(DB::raw('(ser_rating / 2) as ser_rating'), 'service_id'); 
    }

    //Um atendimento pode ter uma campanha associada ao mesmo
    public function campaign()
    {
        return $this->hasOne(\App\Models\Campaign\Campaign::class , 'id', 'campaign_id'); 
    }

    //Um atendimento pode ter um usuário que fechou um atendimento
    public function userEndService()
    {
        return $this->hasOne(\App\Models\Management\User::class , 'id', 'user_id_end_service'); 
    }

}
