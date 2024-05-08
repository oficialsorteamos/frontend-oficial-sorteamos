<?php

namespace App\Models\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fin_credits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'api_payment_credit_id',
        'card_id',
        'payment_method_id',
        'user_id',
        'url_external_checkout',
        'cre_due',
        'cre_value',
        'status_id',
    ];

    //Um crédito tem um status
    public function status()
    {
        return $this->belongsTo(\App\Models\Financial\StatusPayment::class , 'status_id'); 
    }

    //Um crédito tem um método de pagamento associado
    public function paymentMethod()
    {
        return $this->hasOne(\App\Models\Financial\PaymentMethod::class ,  'id', 'payment_method_id'); 
    }

    //Um crédito pode ter um cartão associado
    public function card()
    {
        return $this->hasOne(\App\Models\Financial\Card::class ,  'id', 'card_id'); 
    }
}
