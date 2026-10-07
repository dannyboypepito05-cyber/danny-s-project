<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherSetting extends Model
{
    protected $fillable = [
        'temperature_unit',
        'wind_speed_unit',
        'refresh_interval_minutes',
        'heavy_rain_threshold_mm',
        'high_temperature_threshold_c',
        'strong_wind_threshold_kmh',
    ];

    protected function casts(): array
    {
        return [
            'refresh_interval_minutes' => 'integer',
            'heavy_rain_threshold_mm' => 'float',
            'high_temperature_threshold_c' => 'float',
            'strong_wind_threshold_kmh' => 'float',
        ];
    }
}
