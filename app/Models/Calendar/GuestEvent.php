<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestEvent extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cal_guests_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'contact_id',
    ];

    //Contato que é convidado na reunião
    public function contact()
    {
        return $this->hasOne(\App\Models\Contact\Contact::class, 'id', 'contact_id'); 
    }
}
