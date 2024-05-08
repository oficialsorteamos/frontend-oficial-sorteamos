<?php

namespace App\Models\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialNetwork extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'con_social_networks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'type_social_network_id',
        'soc_address',
        'soc_status',
    ];

    public function typeSocialNetwork()
    {
        return $this->belongsTo(\App\Models\Contact\TypeSocialNetwork::class , 'type_social_network_id');
    }

}
