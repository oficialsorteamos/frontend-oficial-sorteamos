<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DddState extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_ddd_states';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state_id',
        'ddd_number',
        'ddd_status',
    ];

    public function State()
    {
        return $this->belongsTo(\App\Models\System\StateCountry::class , 'state_id'); 
    }
}
