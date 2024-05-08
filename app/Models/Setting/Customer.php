<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'set_customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'com_name',
        'com_phone',
        'com_mobile_phone',
        'com_finance_phone',
        'com_finance_email',
        'com_responsible_name',
        'com_cpf',
        'com_cnpj',
        'com_postal_code',
        'com_address',
        'com_address_number',
        'com_complement',
        'com_province',
        'com_city',
        'com_state',
        'com_country',
        'whatsapp_business_account_id',
        'status_id',
    ];
}
