<?php

namespace App\Models\Chatbot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bloc extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_chatbot_blocs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'chatbot_id',
        'template_id',
        'quick_message_id',
        'cha_send_option_error_message',
        'cha_title',
        'cha_content',
        'type_bloc_id',
    ];

    public function actions()
    {
        return $this->hasMany(\App\Models\Chatbot\BlocAction::class , 'main_bloc_id')->select('id', 'blo_key AS key', 'main_bloc_id', 'action_id', 'department_id', 'destination_bloc_id', 'fair_distribution_id', 'blo_free_key'); 
    }

    public function typeBloc()
    {
        return $this->belongsTo(\App\Models\Chatbot\TypeBloc::class , 'type_bloc_id'); 
    }

    //Um bloco pode ter um template
    public function template()
    {
        return $this->hasOne(\App\Models\Chat\TemplateMessage::class , 'id', 'template_id');
    }

    //Um bloco pode ter uma mensagem rápida
    public function quickMessage()
    {
        return $this->hasOne(\App\Models\Chat\QuickMessage::class , 'id', 'quick_message_id')->select('id', 'qui_title AS title', 'qui_content AS content', 'type_format_message_id');
    }

}
