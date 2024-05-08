<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FairDistributionChannel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_fair_distribution_channels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fair_distribution_id',
        'channel_id',
        'fai_status',
    ];

    public function channel()
    {
        return $this->hasOne(\App\Models\Management\Channel\Channel::class , 'id', 'channel_id'); 
    }
}
