<?php

namespace App\Models\Chatbot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chatbot extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_chatbots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cha_name',
        'type_chatbot_id',
        'cha_description',
        'cha_only_official_channel',
        'cha_status',
    ];

    //Um chatbot pode ter um ou muitos canais
    public function channels()
    {
        return $this->belongsToMany(\App\Models\Management\Channel\Channel::class, 'cha_chatbots_channels', 'chatbot_id', 'channel_id')->where('cha_chatbots_channels.cha_status', 'A');
    }

    //Um chatbot tem um tipo
    public function typeChatbot()
    {
        return $this->hasOne(\App\Models\Chatbot\TypeChatbot::class , 'id', 'type_chatbot_id');
    }
}
