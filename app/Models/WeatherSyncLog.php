<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherSyncLog extends Model
{
    protected $fillable = [
        'weather_location_id',
        'status',
        'message',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WeatherLocation::class, 'weather_location_id');
    }
}
