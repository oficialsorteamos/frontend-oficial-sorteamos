<?php

namespace App\Models\Administration\Instance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_instances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'api_communication_id',
        'ins_name',
        'ins_webhook',
        'ins_token',
        'ins_status_instance_id',
        'ins_status_connection_id',
        'ins_dt_created',
    ];

    //Uma instância tem um status
    public function status()
    {
        return $this->hasOne(\App\Models\Administration\Instance\InstanceStatus::class , 'id', 'ins_status_instance_id'); 
    }

    public function connectionStatus()
    {
        return $this->hasOne(\App\Models\Administration\Instance\InstanceConnectionStatus::class , 'id', 'ins_status_connection_id'); 
    }
}
