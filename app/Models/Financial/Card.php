<?php

namespace App\Models\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fin_cards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_card_id',
        'car_main',
        'car_holder_name',
        'car_number',
        'car_due_month',
        'car_due_year',
        'car_token',
        'car_status',
    ];

    //Um cartão tem informações do titular associadas
    public function holderInfo()
    {
        return $this->hasOne(\App\Models\Financial\CardHolderInfo::class , 'card_id', 'id'); 
    }
}
