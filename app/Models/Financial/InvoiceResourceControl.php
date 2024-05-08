<?php

namespace App\Models\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceResourceControl extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fin_invoices_resources_control';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_invoice_resource_id',
        'user_id',
        'channel_id',
        'inv_opening',
    ];

    //Um controle de recurso pode estar associado a um usuário
    public function user()
    {
        return $this->belongsTo(\App\Models\Management\User::class , 'user_id', 'id'); 
    }

    //Um controle de recurso pode estar associado a um usuário
    public function channel()
    {
        return $this->belongsTo(\App\Models\Management\Channel\Channel::class , 'channel_id', 'id'); 
    }
}
