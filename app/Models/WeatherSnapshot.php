<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherSnapshot extends Model
{
    protected $fillable = [
        'weather_location_id',
        'source',
        'current',
        'hourly',
        'daily',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'current' => 'array',
            'hourly' => 'array',
            'daily' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WeatherLocation::class, 'weather_location_id');
    }
}
