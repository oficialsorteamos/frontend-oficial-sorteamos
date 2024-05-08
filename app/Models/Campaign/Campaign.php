<?php

namespace App\Models\Campaign;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cam_campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_type_id',
        'campaign_id_api',
        'cam_name',
        'cam_description',
        'status_id',
    ];

    //Uma campanha pode ter um ou muitos canais
    public function channels()
    {
        return $this->belongsToMany(\App\Models\Management\Channel\Channel::class, 'cam_channels_campaigns', 'campaign_id', 'channel_id')->where('cam_channels_campaigns.cha_status', 'A');
    }

    //Uma campanha tem uma ou muitas configurações
    public function settings()
    {   //Um serviço tem uma ou mais ações associadas
        return $this->hasMany(\App\Models\Campaign\Setting::class , 'campaign_id'); 
    }

    //Uma campanha tem um ou mais horários de operação
    public function operatingHours()
    {
        return $this->hasMany(\App\Models\Campaign\CampaignOperatingHour::class , 'campaign_id'); 
    }

    //Uma campanha tem uma ou muitas mensagens
    public function messages()
    {   //Um serviço tem uma ou mais ações associadas
        return $this->hasMany(\App\Models\Campaign\CampaignMessage::class , 'campaign_id')->where('mes_status', 'A'); 
    }
    //Uma campanha pode ter uma ou muitas mensagens templates
    public function templateMessages()
    {   //Um serviço tem uma ou mais ações associadas
        return $this->hasMany(\App\Models\Chat\TemplateCampaign::class , 'campaign_id')->where('tem_status', 'A'); 
    }

    //Um registro na blacklist pode conter uma campanha ao qual o contato foi inserido na blacklist 
    public function typeCampaign()
    {
        return $this->hasOne(\App\Models\Campaign\CampaignType::class , 'id', 'campaign_type_id'); 
    }

    //Uma campanha tem uma ou muitas tags
    /*public function tags()
    {
        return $this->belongsToMany(\App\Models\Management\Tag\Tag::class, 'cam_tags_history', 'campaign_id', 'tag_id'); 
    }*/
}
