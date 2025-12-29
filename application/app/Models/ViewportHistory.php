<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'viewport_id',
        'user_id',
        'entered_at',
        'duration',
        'context',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'context' => 'array',
    ];

    public function viewport()
    {
        return $this->belongsTo(MapViewport::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
