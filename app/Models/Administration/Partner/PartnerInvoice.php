<?php

namespace App\Models\Administration\Partner;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerInvoice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_partners_invoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'partner_id',
        'api_payment_invoice_id',
        'par_url_invoice',
        'par_month_year',
        'par_opening',
        'par_closing',
        'par_due',
        'status_id',
        'par_status',
    ];

    //Uma fatura tem um status
    public function status()
    {
        return $this->belongsTo(\App\Models\Financial\StatusPayment::class , 'status_id');
    }

    //Uma fatura tem um status
    public function partner()
    {
        return $this->hasOne(\App\Models\Administration\Partner\Partner::class , 'id', 'partner_id');
    }

    //Um fatura pode ter uma ou muitas taxas associadas
    public function invoiceFees()
    {
        return $this->hasMany(\App\Models\Administration\Partner\PartnerInvoiceFee::class , 'invoice_id')->where('par_status', 'A');
    }
}
