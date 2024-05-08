<?php

namespace App\Models\Management\Channel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelPayment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'man_channels_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'channel_id',
        'api_payment_charge_id',
        'user_id',
        'payment_method_id',
        'card_id',
        'cha_value',
        'cha_status',
    ];

    //Um pagamento está associado a um método de pagamento
    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\Financial\PaymentMethod::class , 'payment_method_id'); 
    }

    //Um pagamento pode ter um cartão associado
    public function card()
    {
        return $this->belongsTo(\App\Models\Financial\Card::class , 'card_id'); 
    }

}
