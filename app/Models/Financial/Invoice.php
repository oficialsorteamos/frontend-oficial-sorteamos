<?php

namespace App\Models\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fin_invoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'api_payment_invoice_id',
        'inv_url_invoice',
        'inv_month_year',
        'inv_opening',
        'inv_closing',
        'inv_due',
        'status_id',
    ];

    //Uma fatura tem um status
    public function status()
    {
        return $this->belongsTo(\App\Models\Financial\StatusPayment::class , 'status_id'); 
    }

    //Um fatura pode ter uma ou muitas taxas associadas
    public function invoiceFees()
    {
        return $this->hasMany(\App\Models\Financial\InvoiceFee::class , 'invoice_id')->where('inv_status', 'A');
    }
}
