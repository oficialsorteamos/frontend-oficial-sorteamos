<?php

namespace App\Models\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'con_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'con_name',
        'gender_id',
        'status_id',
        'color_avatar_id',
        'con_phone',
        'con_email',
        'con_birthday',
        'con_avatar',
        'con_cpf',
        'con_cnpj',
    ];

    //Um contato pode ter um ou muitos chats
    public function chats()
    {
        return $this->hasMany(\App\Models\Chat\Chat::class , 'contact_id'); 
    }
    
    //Cor do avatar
    public function colorAvatar()
    {
        return $this->belongsTo(\App\Models\System\DefaultColor::class , 'color_avatar_id'); 
    }

    //Gênero do contato (masculino, feminino, etc.) 
    public function gender()
    {
        return $this->hasOne(\App\Models\System\Gender::class , 'id', 'gender_id'); 
    }

    //Um contato pode ter uma ou mais tags
    public function tags()
    {
        return $this->belongsToMany(\App\Models\Management\Tag\Tag::class, 'con_contacts_tags', 'contact_id', 'tag_id');
    }

    //Um usuário pode estar bloqueado
    public function blocked()
    {
        return $this->belongsTo(\App\Models\Contact\Blocked::class , 'id', 'contact_id'); 
    }

}
