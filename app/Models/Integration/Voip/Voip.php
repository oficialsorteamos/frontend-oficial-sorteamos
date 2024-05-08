<?php

namespace App\Models\Integration\Voip;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voip extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'int_voip';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'voi_name',
        'voi_description',
        'voi_status',
        
    ];

    public function setting()
    {
        return $this->hasOne(\App\Models\Integration\Voip\VoipSetting::class , 'voip_id'); 
    }    
}
