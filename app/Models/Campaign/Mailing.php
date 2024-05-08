<?php

namespace App\Models\Campaign;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mailing extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cam_mailings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'contact_id',
        'message_id',
        'template_id',
        'channel_id',
        'mai_dt_sending',
        'status_id',
        'mai_contact_returned',
        'mai_additional_data_message',
    ];

    //Um mailing pertence a uma campanha
    public function campaign()
    {
        return $this->belongsTo(\App\Models\Campaign\Campaign::class , 'campaign_id'); 
    }

    //Um mailing possui um contato
    public function contact()
    {
        return $this->belongsTo(\App\Models\Contact\Contact::class , 'contact_id'); 
    }

    //Um mailing pode possuir uma mensagem associada
    public function message()
    {
        return $this->belongsTo(\App\Models\Campaign\CampaignMessage::class , 'message_id'); 
    }

    //Um mailing pode possuir um template associado
    public function template()
    {
        return $this->belongsTo(\App\Models\Chat\TemplateMessage::class , 'template_id'); 
    }

    //Um mailing possui um canal associado
    public function channel()
    {
        return $this->belongsTo(\App\Models\Management\Channel\Channel::class , 'channel_id'); 
    }
}
