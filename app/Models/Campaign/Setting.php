<?php

namespace App\Models\Campaign;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cam_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'operation_frequency_id',
        'number_shot_frequency_id',
        'department_id',
        'fair_distribution_id',
        'set_status',
    ];

    //Uma configuração de campanha tem uma frenquência de operação 
    public function operatingFrequency()
    {
        return $this->hasOne(\App\Models\Campaign\OperatingFrequency::class , 'id', 'operation_frequency_id'); 
    }

    //Uma configuração de campanha tem uma frenquência de operação 
    public function numberShotFrequency()
    {
        return $this->hasOne(\App\Models\Campaign\NumberShotFrequency::class , 'id', 'number_shot_frequency_id'); 
    }

    //Uma configuração de campanha tem uma frenquência de operação 
    public function department()
    {
        return $this->hasOne(\App\Models\Management\Department::class , 'id', 'department_id'); 
    }

    //Uma configuração de campanha pode ter uma configuração de transferência igualitária
    public function fairDistribution()
    {
        return $this->hasOne(\App\Models\Chat\FairDistributionSetup::class , 'id', 'fair_distribution_id'); 
    }

}
