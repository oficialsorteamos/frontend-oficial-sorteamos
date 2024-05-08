<?php

namespace App\Models\Integration\Dialer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FowardingSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'int_dialers_fowarding_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dialer_id',
        'channel_id',
        'chatbot_id',
        'department_id',
        'fair_distribution_id',
        'send_message',
        'dia_message',
        'template_id',
        'dia_status',
    ];
    
    //Um encaminhamento possui um canal
    public function channel()
    {
        return $this->belongsTo(\App\Models\Management\Channel\Channel::class , 'channel_id'); 
    }

    //Um encaminhamento pode possuir um chatbot
    public function chatbot()
    {
        return $this->belongsTo(\App\Models\Chatbot\Chatbot::class , 'chatbot_id'); 
    }

    //Um encaminhamento possui um departamento
    public function department()
    {
        return $this->belongsTo(\App\Models\Management\Department::class , 'department_id'); 
    }

    //Um encaminhamento pode possuir um template
    public function template()
    {
        return $this->belongsTo(\App\Models\Chat\TemplateMessage::class , 'template_id')->select('id', 'tem_body AS body'); 
    }

    //Um encaminhamento possui uma distribuição igualitária
    public function fairDistribution()
    {
        return $this->belongsTo(\App\Models\Chat\FairDistributionSetup::class , 'fair_distribution_id'); 
    }
}
