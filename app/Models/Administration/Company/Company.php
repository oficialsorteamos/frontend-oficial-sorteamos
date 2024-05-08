<?php

namespace App\Models\Administration\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'partner_id',
        'gender_id',
        'com_name',
        'com_cnpj',
        'com_cpf',
        'com_responsible_name',
        'com_url',
        'com_birthday',
        'com_responsible_phone',
        'com_responsible_email',
        'com_finance_phone',
        'com_finance_email',
        'par_postal_code',
        'par_address',
        'par_address_number',
        'par_complement',
        'par_province',
        'par_city',
        'par_state',
        'par_country',
        'status_id',
    ];

    //Um parceiro tem um tipo
    public function partner()
    {
        return $this->hasOne(\App\Models\Administration\Partner\Partner::class , 'id', 'partner_id'); 
    }

    //Uma empresa tem um status
    public function status()
    {
        return $this->hasOne(\App\Models\Administration\Company\CompanyStatus::class , 'id', 'status_id'); 
    }

    //Uma empresa tem detalhes de informações
    public function details()
    {
        return $this->hasOne(\App\Models\Administration\Company\CompanyDetails::class , 'company_id', 'id'); 
    }

    //Uma empresa tem um ou muitos contratos
    public function contracts()
    {
        return $this->hasMany(\App\Models\Administration\Company\CompanyContract::class , 'company_id'); 
    }

    //Traz o último contrato
    public function lastContract()
    {
        return $this->hasOne(\App\Models\Administration\Company\CompanyContract::class , 'company_id', 'id')->latestOfMany('com_dt_end'); 
    }

    //Uma empresa tem um plano associado
    public function plan()
    {
        return $this->hasOne(\App\Models\Administration\Company\CompanyPlan::class , 'company_id', 'id'); 
    }

    //Uma empresa tem uma ou muitas taxas associadas
    public function fees()
    {
        return $this->hasMany(\App\Models\Administration\Company\CompanyFee::class , 'company_id'); 
    }

    //Uma empresa tem uma ou muitas cobranças associadas
    public function charges()
    {
        return $this->hasMany(\App\Models\Administration\Company\CompanyCharge::class , 'company_id'); 
    }
}
