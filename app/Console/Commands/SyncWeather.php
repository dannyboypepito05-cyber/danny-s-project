<?php

namespace App\Console\Commands;

use App\Models\WeatherLocation;
use App\Services\WeatherDataService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-weather')]
#[Description('Synchronize cached weather and update location alerts.')]
class SyncWeather extends Command
{
    public function handle(WeatherDataService $weatherData): int
    {
        $failed = false;

        foreach (WeatherLocation::where('is_active', true)->get() as $location) {
            $result = $weatherData->weatherFor($location);

            if ($result['state'] === 'online') {
                $this->info('Weather is current for '.$location->name.'.');

                continue;
            }

            $this->error('Unable to synchronize '.$location->name.'.');
            $failed = true;
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
