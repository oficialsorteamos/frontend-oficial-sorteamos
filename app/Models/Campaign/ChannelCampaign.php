<?php

namespace App\Models\Campaign;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelCampaign extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cam_channels_campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'channel_id',
        'cha_total_operations',
        'cha_status',
    ];

    //Uma campanha pode conter um canal associado a um chatbot 
    public function channel()
    {
        return $this->hasOne(\App\Models\Management\Channel\Channel::class , 'id', 'channel_id'); 
    }

}
