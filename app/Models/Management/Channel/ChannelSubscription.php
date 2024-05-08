<?php

namespace App\Models\Management\Channel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelSubscription extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'man_channels_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'channel_id',
        'card_id',
        'cha_observation',
        'cha_status',
    ];

    //Um canal está associado a um cartão
    public function card()
    {
        return $this->belongsTo(\App\Models\Financial\Card::class , 'card_id'); 
    }
}
