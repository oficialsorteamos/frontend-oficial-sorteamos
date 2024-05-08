<?php

namespace App\Models\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fin_costs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_cost_id',
        'mailing_id',
        'cos_value',
        'cos_status',
    ];

    //Um custo pode estar associado a um contato de um mailing
    public function contactMailing()
    {
        return $this->hasOne(\App\Models\Campaign\Mailing::class ,  'id', 'mailing_id'); 
    }

    //Um custo pode estar associado a um contato de um mailing
    public function typeCost()
    {
        return $this->hasOne(\App\Models\Financial\TypeCost::class ,  'id', 'type_cost_id'); 
    }
}
