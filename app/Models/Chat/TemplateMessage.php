<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cha_templates_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tem_name',
        'category_id',
        'language_id',
        'template_id_api',
        'tem_body',
        'tem_header',
        'tem_footer',
        'tem_namespace',
        'status_id',
        'user_id',
        'channel_id',
    ];

    //Um template tem uma categoria
    public function category()
    {
        return $this->belongsTo(\App\Models\Chat\TemplateCategory::class , 'category_id'); 
    }

    //Um template tem um idioma
    public function language()
    {
        return $this->belongsTo(\App\Models\Chat\TemplateLanguage::class , 'language_id'); 
    }

    //Um template tem um canal associado
    public function channel()
    {
        return $this->belongsTo(\App\Models\Management\Channel\Channel::class , 'channel_id'); 
    }

    //Um template tem um status
    public function status()
    {
        return $this->belongsTo(\App\Models\Chat\TemplateStatus::class , 'status_id'); 
    }

    //Um template pode ter um ou muitos parâmetros
    public function parameters()
    {   
        return $this->hasMany(\App\Models\Chat\TemplateParameter::class , 'template_id')->leftJoin('cha_templates_messages_type_variables', 'cha_templates_messages_parameters.type_variable_id','cha_templates_messages_type_variables.id'); 
    }

    //Um template pode estar associado a uma ou muitas campanhas
    public function campaigns()
    {
        return $this->belongsToMany(\App\Models\Campaign\Campaign::class, 'cam_templates', 'template_id', 'campaign_id')
                                                            ->whereNotIn('cam_campaigns.status_id', [3, 5]) //Onde a campanha não esteja finalizada e nem removida
                                                            ->where('cam_templates.tem_status', 'A');
    }

    //Um template pode estar associado a um ou muitos chatbots
    public function chatbots()
    {
        return $this->belongsToMany(\App\Models\Chatbot\Chatbot::class, 'cha_chatbot_blocs', 'template_id', 'chatbot_id')
                                                            ->whereIn('cha_chatbots.cha_status', ['A', 'I']); //Onde o chatbot esteja ativo ou inativo
    }
}
