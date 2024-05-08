<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FairDistributionSetup extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_fair_distributions_setup';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fai_name',
        'fai_description',
        'fai_main',
        'fai_status',
    ];

    //Um configuração de distribuição igualitária pode ter um ou muitos canais
    public function channels()
    {
        return $this->belongsToMany(\App\Models\Management\Channel\Channel::class, 'cha_fair_distribution_channels', 'fair_distribution_id', 'channel_id')->where('cha_fair_distribution_channels.fai_status', 'A'); 
    }
    
    //Um configuração de distribuição igualitária pode ter um ou muitos usuários
    public function users()
    {
        return $this->belongsToMany(\App\Models\Management\User::class, 'cha_fair_distributions', 'fair_distribution_id', 'user_id')->where('cha_fair_distributions.fai_status', 'A');
    }

    public function actions()
    {
        return $this->hasMany(\App\Models\Chatbot\BlocAction::class , 'fair_distribution_id'); 
    }
}
