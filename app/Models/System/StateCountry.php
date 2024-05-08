<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StateCountry extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_states_country';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_id',
        'country_region_id',
        'sta_name',
        'sta_uf',
        'sta_status',
    ];

    public function ddds()
    {
        return $this->hasMany(\App\Models\System\DddState::class , 'state_id'); 
    }
}
