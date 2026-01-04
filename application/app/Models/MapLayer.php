<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MapLayer represents a drawable layer on a map that can contain various elements.
 * 
 * Supports layer relationships:
 * - parent_layer_id: Creates a parent-child hierarchy for layer control
 * - element_types: Array of element types this layer contains (svg_icon, text, marker, etc.)
 * - related_layers: Array of layer IDs that this layer depends on or controls
 * 
 * Example use cases:
 * - A "Fire Alarms" layer with svg_icon elements can have a related "Fire Alarm Labels" layer with text elements
 * - Turning on/off the parent layer can automatically control visibility of child layers
 * - Related layers allow bidirectional dependencies between different content types
 */
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
        'parent_layer_id',
        'element_types',
        'related_layers',
    ];

    protected $casts = [
        'default_visible' => 'boolean',
        'style_preset' => 'array',
        'data_source' => 'array',
        'element_types' => 'array',
        'related_layers' => 'array',
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function elements()
    {
        return $this->hasMany(LayerElement::class, 'layer_id');
    }

    public function parent()
    {
        return $this->belongsTo(MapLayer::class, 'parent_layer_id');
    }

    public function children()
    {
        return $this->hasMany(MapLayer::class, 'parent_layer_id');
    }

    public function relatedLayers()
    {
        if (empty($this->related_layers)) {
            return collect([]);
        }
        return MapLayer::whereIn('id', $this->related_layers)->get();
    }
}
