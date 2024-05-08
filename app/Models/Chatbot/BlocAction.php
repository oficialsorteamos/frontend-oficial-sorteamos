<?php

namespace App\Models\Chatbot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlocAction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_bloc_actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'main_bloc_id',
        'action_id',
        'destination_bloc_id',
        'department_id',
        'fair_distribution_id',
        'blo_key',
        'blo_free_key',
    ];

    public function bloc()
    {
        return $this->belongsTo(\App\Models\Chatbot\Bloc::class , 'main_bloc_id'); 
    }
    public function typeAction()
    {
        return $this->belongsTo(\App\Models\Chatbot\TypeBlocAction::class , 'action_id'); 
    }
    public function department()
    {
        return $this->belongsTo(\App\Models\Management\Department::class , 'department_id'); 
    }
    public function destinationBloc()
    {
        return $this->belongsTo(\App\Models\Chatbot\Bloc::class , 'destination_bloc_id'); 
    }
    //Uma ação pode ter uma configuração de transferência igualitária encaminhada
    public function fairDistribution()
    {
        return $this->belongsTo(\App\Models\Chat\FairDistributionSetup::class , 'fair_distribution_id');
    }
}
