<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherAlert extends Model
{
    protected $fillable = [
        'weather_location_id',
        'alert_key',
        'severity',
        'title',
        'message',
        'status',
        'started_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WeatherLocation::class, 'weather_location_id');
    }
}
