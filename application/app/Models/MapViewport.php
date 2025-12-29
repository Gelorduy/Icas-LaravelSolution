<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapViewport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'map_id',
        'name',
        'slug',
        'is_root',
        'bounds',
        'default_zoom',
        'default_pan',
        'layer_overrides',
        'refresh_interval',
        'notes',
    ];

    protected $casts = [
        'is_root' => 'boolean',
        'bounds' => 'array',
        'default_zoom' => 'float',
        'default_pan' => 'array',
        'layer_overrides' => 'array',
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function history()
    {
        return $this->hasMany(ViewportHistory::class, 'viewport_id');
    }
}
