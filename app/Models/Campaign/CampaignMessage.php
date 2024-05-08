<?php

namespace App\Models\Campaign;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cam_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'quick_message_id',
        'user_id',
        'mes_content',
        'mes_status',
    ];

    //Uma mensagem pode pertencer a uma ou muitas campanhas
    public function campaigns()
    {
        return $this->belongsTo(\App\Models\Campaign\Campaign::class , 'campaign_id')->where('cam_campaigns.status_id', '!=', 5); //Onde a campanha não foi excluída 
    }
    //Uma mensagem pode conter uma mensagem rápida
    public function quickMessage()
    {
        return $this->belongsTo(\App\Models\Chat\QuickMessage::class , 'quick_message_id')->select('id' ,'qui_content as content', 'qui_title', 'type_format_message_id');
    }
}
