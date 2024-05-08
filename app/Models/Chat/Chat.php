<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_chats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'cha_unseen_messages',
        'cha_status',
    ];

    //Traz a última transferência de atendimento realizada 
    public function lastTransferService()
    {
        return $this->hasOne(\App\Models\Chat\Action::class , 'chat_id')->orderBy('id', 'desc')->where('type_action_id', 1)->latest(); 
    }

    //Um chat pertence a um contato
    public function contact()
    {
        return $this->belongsTo(\App\Models\Contact\Contact::class , 'contact_id'); 
    }

    //Um chat pode ter uma ou muitas mensagens
    public function messages()
    {
        return $this->hasMany(\App\Models\Chat\ChatMessage::class , 'chat_id'); 
    }

}
