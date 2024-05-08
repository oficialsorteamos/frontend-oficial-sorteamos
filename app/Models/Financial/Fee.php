<?php

namespace App\Models\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fin_fees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_fee_id',
        'fee_value',
        'fee_status',
    ];

    //Uma fatura tem um status
    public function typeFee()
    {
        return $this->belongsTo(\App\Models\Financial\TypeFee::class , 'type_fee_id'); 
    }
}
