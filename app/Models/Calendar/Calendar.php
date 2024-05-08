<?php

namespace App\Models\Calendar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cal_calendar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'tag_id',
        'cal_title',
        'cal_description',
        'cal_url',
        'cal_event_start',
        'cal_event_end',
    ];

    //Um evento pode ter um ou muitos convidados
    public function guests()
    {
        return $this->hasMany(\App\Models\Calendar\GuestEvent::class , 'event_id')->join('con_contacts', 'cal_guests_events.contact_id', 'con_contacts.id'); 
    }

    //Tag do calendário (Apresentação, Agendamento, etc.) 
    public function tag()
    {
        return $this->hasOne(\App\Models\Management\Tag\Tag::class, 'id', 'tag_id'); 
    }
}
