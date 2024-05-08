<?php

namespace App\Models\Chatbot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeBloc extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_type_blocs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'typ_description',
        'typ_status',
    ];

    public function blocs()
    {
        return $this->hasMany(\App\Models\Chatbot\Bloc::class , 'type_bloc_id'); 
    }

}
