<?php

namespace App\Models\Campaign;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignBlacklist extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cam_blacklist';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'contact_id',
        'user_id',
    ];

    //Um registro na blacklist pode conter uma campanha ao qual o contato foi inserido na blacklist 
    public function campaign()
    {
        return $this->hasOne(\App\Models\Campaign\Campaign::class , 'id', 'campaign_id'); 
    }

    //Um registro na blacklist contém um contato associado 
    public function contact()
    {
        return $this->hasOne(\App\Models\Contact\Contact::class , 'id', 'contact_id'); 
    }

    //Um registro na blacklist contém um usuário de sistema associado 
    public function user()
    {
        return $this->hasOne(\App\Models\Management\User::class , 'id', 'user_id'); 
    }
}
