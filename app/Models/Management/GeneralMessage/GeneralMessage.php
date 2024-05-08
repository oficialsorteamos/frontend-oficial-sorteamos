<?php

namespace App\Models\Management\GeneralMessage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'man_general_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_general_message_id',
        'gen_content',
        'gen_status',
    ];

    //Gênero do usuário (masculino, feminino, etc.) 
    public function typeGeneralMessage()
    {
        return $this->hasOne(\App\Models\Management\GeneralMessage\TypeGeneralMessage::class , 'id', 'type_general_message_id'); 
    }
}
