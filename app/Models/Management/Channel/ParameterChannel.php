<?php

namespace App\Models\Management\Channel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParameterChannel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'man_parameters_channels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'channel_id',
        'type_parameter_channel_id',
        'par_value',
        'department_id',
        'fair_distribution_id',
        'par_status',
    ];

    //Um parâmetro tem um tipo
    public function typeParameterChannel()
    {
        return $this->belongsTo(\App\Models\Management\Channel\TypeParameterChannel::class , 'type_parameter_channel_id'); 
    }

    //Um parâmetro pode ter um departamento associado
    public function department()
    {
        return $this->belongsTo(\App\Models\Management\Department::class , 'department_id'); 
    }

    //Um parâmetro pode ter uma configuração de transferência igualitária encaminhada
    public function fairDistribution()
    {
        return $this->belongsTo(\App\Models\Chat\FairDistributionSetup::class , 'fair_distribution_id');
    }

}
