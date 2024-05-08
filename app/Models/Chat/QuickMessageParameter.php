<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickMessageParameter extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_quick_messages_parameters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quick_message_id',
        'type_parameter_id',
        'type_button_id',
        'qui_content',
        'qui_description',
        'qui_url',
        'qui_phone_number',
        'qui_media_name',
        'qui_media_original_name',
        'qui_positives_responses',
        'qui_negatives_responses',
        'qui_status',
    ];
}
