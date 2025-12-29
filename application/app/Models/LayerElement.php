<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LayerElement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'layer_id',
        'element_type',
        'geometry',
        'payload',
        'state',
    ];

    protected $casts = [
        'geometry' => 'array',
        'payload' => 'array',
        'state' => 'array',
    ];

    public function layer()
    {
        return $this->belongsTo(MapLayer::class, 'layer_id');
    }
}
