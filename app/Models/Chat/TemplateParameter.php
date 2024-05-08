<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateParameter extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_templates_messages_parameters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'template_id',
        'parameter_id',
        'type_parameter_id',
        'location_parameter_id',
        'type_button_id',
        'type_variable_id',
        'tem_url',
        'tem_phone_number',
        'tem_content',
        'tem_variable_tag',
        'tem_media_name',
    ];

    //Um template tem um status
    public function typeVariable()
    {
        return $this->belongsTo(\App\Models\Chat\TemplateTypeVariable::class , 'type_variable_id'); 
    }
}
