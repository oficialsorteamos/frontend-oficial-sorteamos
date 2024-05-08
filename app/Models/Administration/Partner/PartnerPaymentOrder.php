<?php

namespace App\Models\Administration\Partner;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerPaymentOrder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_partners_payments_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'par_value_order',
        'par_link_payment_receipt',
        'status_id',
    ];

    //Uma ordem de pagamento possui um status associado
    public function status()
    {
        return $this->hasOne(\App\Models\Administration\Partner\PartnerPaymentOrderStatus::class , 'id', 'status_id'); 
    }

    //Uma ordem de pagamento está associada a uma fatura
    public function invoice()
    {
        return $this->hasOne(\App\Models\Administration\Company\CompanyInvoice::class , 'id', 'invoice_id'); 
    }
}
