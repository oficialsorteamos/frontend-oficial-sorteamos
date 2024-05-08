<?php

namespace App\Models\Management\Extension;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'voi_extensions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'voip_id',
        'name',
        'description',
        'context',
        'secret',
        'host',
        'fromdomain',
        'nat',
        'type',
        'callerid',
        'qualify',
        'rtptimeout',
        'username',
        'md5secret',
        'lastms',
        'regseconds',
        'useragent',
        'ipaddr',
        'port',
        'fullcontact',
        'regserver',
        'deny',
        'disallow',
        'allow',
        'insecure',
        'fromuser',
        'permit',
        'callbackextension',
        'dtmfmode',
        'ip',
        'status',
        'defaultuser',
        'call-limit',
        'ext_status',
    ];

    //Uma ligação foi realizada via um ramal
    public function voip()
    {
        return $this->belongsTo(\App\Models\Integration\Voip\Voip::class , 'voip_id');  
    }

    //Um ramal pode ter um ou muitos usuários associados
    public function users()
    {
        return $this->belongsToMany(\App\Models\Management\User::class, 'voi_extensions_users', 'extension_id', 'user_id')->where('voi_extensions_users.ext_status', 'A');
    }

    //Uma campanha pode ter um ou muitos canais
    
}
