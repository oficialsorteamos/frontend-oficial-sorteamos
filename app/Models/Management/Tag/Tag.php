<?php

namespace App\Models\Management\Tag;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'man_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tag_name',
        'type_tag_id',
        'tag_description',
        'tag_color',
        'tag_status',
    ];

    //Um endereço pertence a um estado
    public function typeTag()
    {
        return $this->belongsTo(\App\Models\Management\Tag\TypeTag::class , 'type_tag_id'); 
    }

}
