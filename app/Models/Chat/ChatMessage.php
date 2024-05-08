<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chat_id',
        'type_user_id',
        'sender_id',
        'action_id',
        'service_id',
        'campaign_id',
        'template_id',
        'quick_message_id',
        'type_origin_message_id',
        'api_message_id',
        'answered_message_id',
        'mes_message',
        'type_message_chat_id',
        'mes_phone_channel_received_message',
        'mes_phone_channel_sent_message',
        'mes_url',
        'mes_caption',
        'mes_media_original_name',
        'mes_content_name',
        'mes_content_type',
        'mes_contact_name',
        'mes_contact_phone_number',
        'mes_lat',
        'mes_long',
        'status_message_chat_id',
        'mes_private',
        'mes_waiting_message',
    ];

    //Usuário ou contato que enviou a mensagem
    public function userSender()
    {   //ID é o id do departamento e sender_id é a chave estrangeira
        return $this->hasOne(\App\Models\Management\User::class , 'id', 'sender_id'); 
    }

    //Uma mensagem pode estar associada a uma mensagem rápida
    public function quickMessage()
    {   //ID é o id do departamento e sender_id é a chave estrangeira
        return $this->hasOne(\App\Models\Chat\quickMessage::class , 'id', 'quick_message_id'); 
    }

    //Uma mensagem pode ser resposta de uma outra mensagem
    public function answeredMessage()
    {   //ID é o id do departamento e sender_id é a chave estrangeira
        return $this->hasOne(\App\Models\Chat\ChatMessage::class , 'answered_message_id', 'api_message_id'); 
    }

}
