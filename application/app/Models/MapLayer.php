<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapLayer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'map_id',
        'key',
        'display_name',
        'layer_type',
        'z_index',
        'default_visible',
        'style_preset',
        'data_source',
    ];

    protected $casts = [
        'default_visible' => 'boolean',
        'style_preset' => 'array',
        'data_source' => 'array',
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function elements()
    {
        return $this->hasMany(LayerElement::class, 'layer_id');
    }
}
