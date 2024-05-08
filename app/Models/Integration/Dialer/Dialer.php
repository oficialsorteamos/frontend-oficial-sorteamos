<?php

namespace App\Models\Integration\Dialer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dialer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'int_dialers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dia_name',
        'dia_description',
        'dia_status',
        
    ];

    public function fowardingSettings()
    {
        return $this->hasMany(\App\Models\Integration\Dialer\FowardingSetting::class , 'dialer_id');
    }    
}
