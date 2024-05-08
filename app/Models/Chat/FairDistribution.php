<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FairDistribution extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_fair_distributions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fair_distribution_id',
        'user_id',
        'fai_total_forwarding',
        'fai_dt_last_forwarding',
        'fai_status',
    ];

    public function user()
    {
        return $this->hasOne(\App\Models\Management\User::class , 'id', 'user_id'); 
    }
}
