<?php

namespace App\Models\Administration\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInvoice extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_companies_invoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'invoice_id',
        'api_payment_invoice_id',
        'com_url_invoice',
        'com_month_year',
        'com_opening',
        'com_closing',
        'com_due',
        'status_id',
        'com_status',
    ];

    //Um fatura pode ter uma ou muitas taxas associadas
    public function invoiceFees()
    {
        return $this->hasMany(\App\Models\Administration\Company\CompanyInvoiceFee::class , 'invoice_id')->where('com_status', 'A');
    }

    //Uma fatura pertence a uma empresa
    public function company()
    {
        return $this->hasOne(\App\Models\Administration\Company\Company::class , 'id', 'company_id'); 
    }
}
