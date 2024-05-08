<?php

namespace App\Models\Administration\Partner;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerCommission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'adm_partners_commissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'partner_id',
        'par_percentage_level_1',
        'par_initial_quantity_level_1',
        'par_final_quantity_level_1',
        'par_percentage_level_2',
        'par_initial_quantity_level_2',
        'par_final_quantity_level_2',
        'par_percentage_level_3',
        'par_initial_quantity_level_3',
        'par_final_quantity_level_3',
        'par_status',
    ];
}
