<?php

namespace App\Models\Administration\Partner;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_partners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_partner_id',
        'payment_api_customer_id',
        'gender_id',
        'par_corporate_name',
        'par_url',
        'par_partnership_started',
        'par_cnpj',
        'par_cpf',
        'par_responsible_name',
        'par_birthday',
        'par_responsible_phone',
        'par_responsible_email',
        'par_finance_phone',
        'par_finance_email',
        'par_postal_code',
        'par_address',
        'par_address_number',
        'par_complement',
        'par_province',
        'par_city',
        'par_state',
        'par_country',
        'par_due_date',
    ];

    //Um parceiro tem um tipo
    public function typePartner()
    {
        return $this->hasOne(\App\Models\Administration\Partner\TypePartner::class , 'id', 'type_partner_id'); 
    }

    //Um parceiro pode ter uma comissão associada ao mesmo
    public function commission()
    {
        return $this->hasOne(\App\Models\Administration\Partner\PartnerCommission::class , 'partner_id', 'id'); 
    }

    //Uma empresa tem uma ou muitas taxas associadas
    public function fees()
    {
        return $this->hasMany(\App\Models\Administration\Partner\PartnerFee::class , 'partner_id')->orderBy('id', 'ASC'); 
    }
}
