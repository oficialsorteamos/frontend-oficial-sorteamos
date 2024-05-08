<?php

namespace App\Models\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeSocialNetwork extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'con_type_social_networks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'typ_name',
        'typ_status',
    ];

    /*public function contacts()
    {
        return $this->hasMany(\App\Models\Contact\Contact::class , 'pipeline_id');
    }*/

}
