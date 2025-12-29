<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Map extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'floor_label',
        'sequence',
        'svg_asset_path',
        'source_dxf_path',
        'conversion_status',
        'conversion_notes',
        'canvas_config',
        'is_active',
    ];

    protected $casts = [
        'canvas_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function layers()
    {
        return $this->hasMany(MapLayer::class);
    }

    public function viewports()
    {
        return $this->hasMany(MapViewport::class);
    }

    public function rootViewport()
    {
        return $this->hasOne(MapViewport::class)->where('is_root', true);
    }
}
