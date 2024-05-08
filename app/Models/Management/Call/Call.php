<?php

namespace App\Models\Management\Call;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'voi_calls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cal_call_api_id',
        'user_id',
        'contact_id',
        'service_id',
        'extension_id',
        'cal_phone_contact',
        'cal_record_name',
        'cal_call_time',
        'cal_call_made',
        'cal_call_date',
        'cal_has_record',
        'cal_status',
    ];

    //Uma ligação tem um usuário que ligou
    public function user()
    {
        return $this->belongsTo(\App\Models\Management\User::class , 'user_id');  
    }

    //Uma ligação tem um contato que recebeu a ligação
    public function contact()
    {
        return $this->belongsTo(\App\Models\Contact\Contact::class , 'contact_id');  
    }

    //Uma ligação pode ter sido efetuada durante um atendimento via chat
    public function service()
    {
        return $this->belongsTo(\App\Models\Chat\Service::class , 'service_id');  
    }

    //Uma ligação foi realizada via um ramal
    public function extension()
    {
        return $this->belongsTo(\App\Models\Management\Extension\Extension::class , 'extension_id');  
    }
}
