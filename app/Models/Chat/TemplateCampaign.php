<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateCampaign extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cam_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'template_id',
        'user_id',
        'tem_status',
    ];

    public function template()
    {   //Um um template pode estar associado a uma ou muitas campanhas
        return $this->hasOne(\App\Models\Chat\TemplateMessage::class , 'id', 'template_id')->select('id' ,'tem_name', 'tem_body as body', 'tem_header as header', 'tem_footer as footer', 'status_id'); 
    }
}
