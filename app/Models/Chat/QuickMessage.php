<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_quick_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type_quick_message_id',
        'type_format_message_id',
        'qui_title',
        'qui_content',
        'qui_list_name',
        'qui_status',
    ];

    public function parameters()
    {   //Um serviço tem uma ou mais ações associadas
        return $this->hasMany(\App\Models\Chat\QuickMessageParameter::class , 'quick_message_id'); 
    }

    //Uma mensagem rápida pode estar associada a um ou muitos chatbots
    public function chatbots()
    {
        return $this->belongsToMany(\App\Models\Chatbot\Chatbot::class, 'cha_chatbot_blocs', 'quick_message_id', 'chatbot_id')
                                                            ->whereIn('cha_chatbots.cha_status', ['A', 'I']); //Onde o chatbot esteja ativo ou inativo
    }

    //Uma mensagem rápida pode pertencer a uma ou muitas campanhas
    public function campaigns()
    {
        return $this->belongsToMany(\App\Models\Campaign\Campaign::class , 'cam_messages', 'quick_message_id', 'campaign_id')->where('cam_messages.mes_status', 'A'); //Onde a mensagem não foi exluída da campanha 
    }


}
