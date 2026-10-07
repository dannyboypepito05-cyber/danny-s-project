<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeatherLocation extends Model
{
    protected $fillable = [
        'name',
        'region',
        'country',
        'latitude',
        'longitude',
        'timezone',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(WeatherSnapshot::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(WeatherAlert::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(WeatherSyncLog::class);
    }
}
