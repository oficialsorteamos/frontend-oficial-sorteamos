<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'man_departments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dep_name',
        'dep_description',
        'dep_status',
    ];

    public function actions()
    {
        return $this->hasMany(\App\Models\Chatbot\BlocAction::class , 'department_id'); 
    }

    //Um departamento pode estar associado a muitos usuários
    public function users()
    {
        return $this->belongsToMany(\App\Models\Management\User::class);
    }
}
