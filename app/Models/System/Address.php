<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type_user_id',
        'add_zip_code',
        'add_street',
        'add_district',
        'add_number',
        'add_address_complement',
        'add_city',
        'state_id',
        'country_id',
    ];

    //Um endereço pertence a um estado
    public function state()
    {
        return $this->belongsTo(\App\Models\System\StateCountry::class , 'state_id'); 
    }

    //Um endereço pertence a um país
    public function country()
    {
        return $this->belongsTo(\App\Models\System\Country::class , 'country_id'); 
    }
}
