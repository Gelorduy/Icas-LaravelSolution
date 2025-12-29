<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function maps()
    {
        return $this->hasMany(Map::class);
    }
}
