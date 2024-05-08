<?php

namespace App\Models\Management\Channel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelNotification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'man_channels_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_notification_id',
        'channel_id',
        'cha_content',
        'cha_status',
    ];

    //Um pagamento pode ter um cartão associado
    public function typeNotification()
    {
        return $this->belongsTo(\App\Models\Management\Channel\ChannelTypeNotification::class , 'type_notification_id'); 
    }
}
