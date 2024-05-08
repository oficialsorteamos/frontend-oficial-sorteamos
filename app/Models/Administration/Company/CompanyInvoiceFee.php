<?php

namespace App\Models\Administration\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInvoiceFee extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_companies_invoices_fees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'invoice_fee_id',
        'type_fee_id',
        'user_id',
        'channel_id',
        'com_unit_value_fee',
        'com_total_value_fee',
        'com_status',
    ];
}
