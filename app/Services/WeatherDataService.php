<?php

namespace App\Services;

use App\Models\WeatherLocation;
use App\Models\WeatherSetting;
use App\Models\WeatherSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

class WeatherDataService
{
    /**
     * @return array{snapshot: ?WeatherSnapshot, state: string, message: string, http_status: ?int, response_time_ms: ?int}
     */
    public function weatherFor(WeatherLocation $location): array
    {
        $snapshot = $location->snapshots()->latest('fetched_at')->first();
        $settings = $this->settings();
        $isStale = ! $snapshot
            || $snapshot->source !== 'OpenWeather'
            || $snapshot->fetched_at->lte(now()->subMinutes($settings->refresh_interval_minutes));

        if (! $isStale) {
            return [
                'snapshot' => $snapshot,
                'state' => 'online',
                'message' => 'Weather data is current.',
                'http_status' => $snapshot->current['api_http_status'] ?? null,
                'response_time_ms' => $snapshot->current['api_response_time_ms'] ?? null,
            ];
        }

        $sync = $this->synchronize($location);

        if ($sync['success']) {
            return [
                'snapshot' => $sync['snapshot'],
                'state' => 'online',
                'message' => 'Weather data synchronized successfully.',
                'http_status' => $sync['http_status'],
                'response_time_ms' => $sync['response_time_ms'],
            ];
        }

        return [
            'snapshot' => $snapshot,
            'state' => $snapshot ? 'stale' : 'error',
            'message' => $snapshot
                ? $sync['message'].' Showing the last synchronized weather.'
                : $sync['message'],
            'http_status' => $sync['http_status'],
            'response_time_ms' => $sync['response_time_ms'],
        ];
    }

    /**
     * Fetch live current conditions and forecast for an administrator city search without storing a new location.
     *
     * @return array{success: bool, state: string, message: string, weather: ?array, location: ?array, fetched_at: ?CarbonImmutable, http_status: ?int, response_time_ms: ?int}
     */
    public function weatherForCity(string $city): array
    {
        $result = $this->fetchWeather($this->qualifyCity($city), null);

        if (! $result['success']) {
            return [
                ...$result,
                'weather' => null,
                'location' => null,
                'fetched_at' => null,
            ];
        }

        $settings = $this->settings();
        $timeZone = $result['data']['location']['timezone'];

        return [
            ...$result,
            'weather' => $this->presentData($result['data'], $timeZone, $settings),
            'location' => $result['data']['location'],
            'fetched_at' => CarbonImmutable::now($timeZone),
        ];
    }

    /**
     * @return array{success: bool, snapshot: ?WeatherSnapshot, message: string, http_status: ?int, response_time_ms: ?int}
     */
    public function synchronize(WeatherLocation $location): array
    {
        $result = $this->fetchWeather(
            $this->qualifyCity($location->name),
            $location->timezone,
        );

        if (! $result['success']) {
            Log::warning('OpenWeather synchronization failed.', [
                'location_id' => $location->id,
                'http_status' => $result['http_status'],
                'failure_type' => $result['failure_type'] ?? 'provider',
            ]);
            $this->recordSync($location, 'failed', $result['message']);

            return [
                'success' => false,
                'snapshot' => $location->snapshots()->latest('fetched_at')->first(),
                'message' => $result['message'],
                'http_status' => $result['http_status'],
                'response_time_ms' => $result['response_time_ms'],
            ];
        }

        $snapshot = $location->snapshots()->create([
            'source' => 'OpenWeather',
            'current' => $result['data']['current'],
            'hourly' => $result['data']['hourly'],
            'daily' => $result['data']['daily'],
            'fetched_at' => now(),
        ]);

        $this->synchronizeAlerts($location, $result['data']['current'], $this->settings());
        $this->recordSync($location, 'success', 'OpenWeather data synchronized.');

        return [
            'success' => true,
            'snapshot' => $snapshot,
            'message' => $result['message'],
            'http_status' => $result['http_status'],
            'response_time_ms' => $result['response_time_ms'],
        ];
    }

    public function settings(): WeatherSetting
    {
        return WeatherSetting::query()->firstOrCreate([], [
            'temperature_unit' => 'celsius',
            'wind_speed_unit' => 'kmh',
            'refresh_interval_minutes' => config('services.openweather.refresh_interval_minutes', 15),
            'heavy_rain_threshold_mm' => 10,
            'high_temperature_threshold_c' => 35,
            'strong_wind_threshold_kmh' => 50,
        ]);
    }

    /**
     * @return array{current: array<string, mixed>, hourly: array<int, array<string, mixed>>, daily: array<int, array<string, mixed>>}
     */
    public function present(WeatherSnapshot $snapshot, WeatherLocation $location, WeatherSetting $settings): array
    {
        return $this->presentData([
            'current' => $snapshot->current,
            'hourly' => $snapshot->hourly,
            'daily' => $snapshot->daily,
        ], $location->timezone, $settings);
    }

    /**
     * @param  array{current: array<string, mixed>, hourly: array<string, mixed>, daily: array<string, mixed>}  $data
     * @return array{current: array<string, mixed>, hourly: array<int, array<string, mixed>>, daily: array<int, array<string, mixed>>}
     */
    private function presentData(array $data, string $timeZone, WeatherSetting $settings): array
    {
        $current = $data['current'];
        $hourlySource = $data['hourly'];
        $dailySource = $data['daily'];
        $temperature = fn (?float $value): ?float => $value === null
            ? null
            : ($settings->temperature_unit === 'fahrenheit' ? ($value * 9 / 5) + 32 : $value);
        $windSpeed = fn (?float $value): ?float => $value === null
            ? null
            : match ($settings->wind_speed_unit) {
                'mph' => $value / 1.609344,
                'ms' => $value / 3.6,
                default => $value,
            };
        $windLabel = match ($settings->wind_speed_unit) {
            'mph' => 'mph',
            'ms' => 'm/s',
            default => 'km/h',
        };
        $temperatureLabel = $settings->temperature_unit === 'fahrenheit' ? '°F' : '°C';
        $hourlyStart = $this->hourlyStartIndex($hourlySource['time'] ?? [], $timeZone);
        $hourlyCount = min(count($hourlySource['time'] ?? []), 24);
        $hourly = [];

        for ($offset = 0; $offset < $hourlyCount; $offset++) {
            $index = $hourlyStart + $offset;
            $time = $hourlySource['time'][$index] ?? null;

            if ($time === null) {
                break;
            }

            $condition = $this->conditionAt($hourlySource, $index);
            $hourly[] = [
                'time' => CarbonImmutable::parse($time, $timeZone)->format('g A'),
                'temperature' => round($temperature((float) ($hourlySource['temperature_2m'][$index] ?? 0)), 1),
                'temperature_unit' => $temperatureLabel,
                'precipitation_probability' => $hourlySource['precipitation_probability'][$index] ?? null,
                'condition' => $condition['label'],
                'icon' => $condition['icon'],
                'provider_icon_url' => $this->providerIconUrl($hourlySource['provider_icon_code'][$index] ?? null),
            ];
        }

        $daily = [];

        foreach (($dailySource['time'] ?? []) as $index => $date) {
            $condition = $this->conditionAt($dailySource, $index);
            $daily[] = [
                'date' => CarbonImmutable::parse($date, $timeZone)->format('D, M j'),
                'condition' => $condition['label'],
                'icon' => $condition['icon'],
                'maximum' => round($temperature((float) ($dailySource['temperature_2m_max'][$index] ?? 0)), 1),
                'minimum' => round($temperature((float) ($dailySource['temperature_2m_min'][$index] ?? 0)), 1),
                'temperature_unit' => $temperatureLabel,
                'rain_probability' => $dailySource['precipitation_probability_max'][$index] ?? null,
                'provider_icon_url' => $this->providerIconUrl($dailySource['provider_icon_code'][$index] ?? null),
            ];
        }

        $condition = $this->conditionAt($current);
        if (
            (float) $current['temperature_2m'] >= $settings->high_temperature_threshold_c
            && in_array($condition['scene'], ['clear', 'partly-cloudy', 'cloudy'], true)
        ) {
            $condition['scene'] = 'hot';
        }
        $currentHourlyIndex = $hourlyStart;
        $current['condition'] = $condition['label'];
        $current['icon'] = $condition['icon'];
        $current['scene'] = $condition['scene'];
        $current['temperature_display'] = round($temperature((float) $current['temperature_2m']), 1);
        $current['feels_like_display'] = round($temperature((float) ($current['apparent_temperature'] ?? $current['temperature_2m'])), 1);
        $current['minimum_display'] = isset($current['temperature_min'])
            ? round($temperature((float) $current['temperature_min']), 1)
            : null;
        $current['maximum_display'] = isset($current['temperature_max'])
            ? round($temperature((float) $current['temperature_max']), 1)
            : null;
        $current['temperature_unit'] = $temperatureLabel;
        $current['humidity'] = $current['relative_humidity_2m'] ?? null;
        $current['wind_display'] = isset($current['wind_speed_10m'])
            ? round($windSpeed((float) $current['wind_speed_10m']), 1)
            : null;
        $current['wind_unit'] = $windLabel;
        $current['wind_direction'] = isset($current['wind_direction_10m'])
            ? $this->windDirection((int) $current['wind_direction_10m'])
            : null;
        $pressure = $current['pressure_msl'] ?? $current['surface_pressure'] ?? null;
        $current['pressure'] = $pressure === null ? null : round((float) $pressure);
        $visibility = $current['visibility_meters'] ?? ($hourlySource['visibility'][$currentHourlyIndex] ?? null);
        $current['visibility_km'] = $visibility === null ? null : round((float) $visibility / 1000, 1);
        $uvIndex = $hourlySource['uv_index'][$currentHourlyIndex] ?? null;
        $current['uv_index'] = $uvIndex === null ? null : round((float) $uvIndex, 1);
        $current['uv_risk'] = $current['uv_index'] === null ? null : match (true) {
            $current['uv_index'] < 3 => 'Low',
            $current['uv_index'] < 6 => 'Moderate',
            $current['uv_index'] < 8 => 'High',
            $current['uv_index'] < 11 => 'Very high',
            default => 'Extreme',
        };
        $current['rain_probability'] = $hourlySource['precipitation_probability'][$currentHourlyIndex] ?? null;
        $current['sunrise'] = isset($current['sunrise_epoch'])
            ? CarbonImmutable::createFromTimestamp((int) $current['sunrise_epoch'], 'UTC')->setTimezone($timeZone)->format('g:i A')
            : (isset($dailySource['sunrise'][0]) ? CarbonImmutable::parse($dailySource['sunrise'][0], $timeZone)->format('g:i A') : null);
        $current['sunset'] = isset($current['sunset_epoch'])
            ? CarbonImmutable::createFromTimestamp((int) $current['sunset_epoch'], 'UTC')->setTimezone($timeZone)->format('g:i A')
            : (isset($dailySource['sunset'][0]) ? CarbonImmutable::parse($dailySource['sunset'][0], $timeZone)->format('g:i A') : null);
        $current['precipitation'] = isset($current['precipitation'])
            ? round((float) $current['precipitation'], 1)
            : null;
        $current['provider_icon_url'] = $this->providerIconUrl($current['provider_icon_code'] ?? null);

        return [
            'current' => $current,
            'hourly' => $hourly,
            'daily' => $daily,
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array{label: string, icon: string, scene: string}
     */
    private function conditionAt(array $source, ?int $index = null): array
    {
        $label = $index === null
            ? ($source['condition_label'] ?? null)
            : ($source['condition_label'][$index] ?? null);
        $icon = $index === null
            ? ($source['condition_icon'] ?? null)
            : ($source['condition_icon'][$index] ?? null);
        $scene = $index === null
            ? ($source['condition_scene'] ?? null)
            : ($source['condition_scene'][$index] ?? null);

        if (is_string($label) && is_string($icon) && is_string($scene)) {
            return ['label' => $label, 'icon' => $icon, 'scene' => $scene];
        }

        $weatherCode = $index === null
            ? ($source['weather_code'] ?? 0)
            : ($source['weather_code'][$index] ?? 0);

        return $this->condition((int) $weatherCode);
    }

    private function providerIconUrl(mixed $iconCode): ?string
    {
        if (! is_string($iconCode) || preg_match('/^\d{2}[dn]$/', $iconCode) !== 1) {
            return null;
        }

        return 'https://openweathermap.org/img/wn/'.$iconCode.'@2x.png';
    }

    /**
     * @return array{success: bool, state: string, message: string, data?: array<string, mixed>, http_status: ?int, response_time_ms: ?int, failure_type?: string}
     */
    private function fetchWeather(string $city, ?string $timeZone): array
    {
        $apiKey = trim((string) config('services.openweather.key'));

        if ($apiKey === '') {
            return [
                'success' => false,
                'state' => 'error',
                'message' => 'Weather service configuration error. Add OPENWEATHER_API_KEY to the server environment.',
                'http_status' => null,
                'response_time_ms' => null,
                'failure_type' => 'configuration',
            ];
        }

        $baseUrl = rtrim((string) config('services.openweather.base_url'), '/');
        $parameters = [
            'q' => $city,
            'appid' => $apiKey,
            'units' => 'metric',
        ];
        $startedAt = microtime(true);

        try {
            $currentResponse = Http::acceptJson()
                ->connectTimeout(3)
                ->timeout(8)
                ->retry(2, 300)
                ->get($baseUrl.'/weather', $parameters)
                ->throw();
            $currentPayload = $currentResponse->json();

            if (! is_array($currentPayload)) {
                throw new UnexpectedValueException('OpenWeather returned invalid JSON.');
            }

            $forecastPayload = [];

            try {
                $forecastResponse = Http::acceptJson()
                    ->connectTimeout(3)
                    ->timeout(8)
                    ->retry(2, 300)
                    ->get($baseUrl.'/forecast', $parameters)
                    ->throw();
                $forecast = $forecastResponse->json();

                if (! is_array($forecast) || ! is_array($forecast['list'] ?? null)) {
                    throw new UnexpectedValueException('OpenWeather returned an incomplete forecast payload.');
                }

                $forecastPayload = $forecast;
            } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
                Log::warning('OpenWeather forecast request failed; current conditions remain available.', [
                    'failure_type' => class_basename($exception),
                    'http_status' => $exception instanceof RequestException ? $exception->response->status() : null,
                ]);
            }

            $data = $this->normalizeWeather($currentPayload, $forecastPayload, $timeZone);
            $responseTime = (int) round((microtime(true) - $startedAt) * 1000);
            $data['current']['api_http_status'] = $currentResponse->status();
            $data['current']['api_response_time_ms'] = $responseTime;

            return [
                'success' => true,
                'state' => 'online',
                'message' => 'Live weather synchronized from OpenWeather.',
                'data' => $data,
                'http_status' => $currentResponse->status(),
                'response_time_ms' => $responseTime,
            ];
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            $httpStatus = $exception instanceof RequestException
                ? $exception->response->status()
                : null;
            $error = $this->providerErrorMessage($exception, $httpStatus);

            Log::warning('OpenWeather current conditions request failed.', [
                'failure_type' => class_basename($exception),
                'http_status' => $httpStatus,
            ]);

            return [
                ...$error,
                'response_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ];
        }
    }

    /**
     * Map OpenWeather's current and 3-hour forecast payloads into the stored weather representation.
     *
     * @param  array<string, mixed>  $currentPayload
     * @param  array<string, mixed>  $forecastPayload
     * @return array{location: array<string, mixed>, current: array<string, mixed>, hourly: array<string, mixed>, daily: array<string, mixed>}
     */
    private function normalizeWeather(array $currentPayload, array $forecastPayload, ?string $timeZone): array
    {
        $main = $currentPayload['main'] ?? null;
        $weather = $currentPayload['weather'][0] ?? null;

        if (
            ! is_array($main)
            || ! is_numeric($main['temp'] ?? null)
            || ! is_numeric($main['feels_like'] ?? null)
            || ! is_array($weather)
            || ! is_numeric($weather['id'] ?? null)
            || ! is_string($weather['main'] ?? null)
            || ! is_string($weather['description'] ?? null)
            || ! is_string($weather['icon'] ?? null)
            || ! is_string($currentPayload['name'] ?? null)
        ) {
            throw new UnexpectedValueException('OpenWeather returned an incomplete current-conditions payload.');
        }

        $timeZone ??= $this->offsetTimezone((int) ($currentPayload['timezone'] ?? 0));
        $condition = $this->openWeatherCondition(
            (int) $weather['id'],
            $weather['main'],
        );
        $wind = is_array($currentPayload['wind'] ?? null) ? $currentPayload['wind'] : [];
        $system = is_array($currentPayload['sys'] ?? null) ? $currentPayload['sys'] : [];
        $current = [
            'location_name' => $currentPayload['name'],
            'country' => $system['country'] ?? null,
            'latitude' => $currentPayload['coord']['lat'] ?? null,
            'longitude' => $currentPayload['coord']['lon'] ?? null,
            'temperature_2m' => (float) $main['temp'],
            'relative_humidity_2m' => isset($main['humidity']) ? (int) $main['humidity'] : null,
            'apparent_temperature' => (float) $main['feels_like'],
            'temperature_min' => isset($main['temp_min']) ? (float) $main['temp_min'] : null,
            'temperature_max' => isset($main['temp_max']) ? (float) $main['temp_max'] : null,
            'pressure_msl' => isset($main['pressure']) ? (int) $main['pressure'] : null,
            'visibility_meters' => isset($currentPayload['visibility']) ? (int) $currentPayload['visibility'] : null,
            'wind_speed_10m' => isset($wind['speed']) ? (float) $wind['speed'] * 3.6 : null,
            'wind_direction_10m' => isset($wind['deg']) ? (int) $wind['deg'] : null,
            'precipitation' => isset($currentPayload['rain']['1h'])
                ? (float) $currentPayload['rain']['1h']
                : (isset($currentPayload['rain']['3h']) ? (float) $currentPayload['rain']['3h'] : null),
            'condition_label' => $condition['label'],
            'condition_description' => ucfirst($weather['description']),
            'condition_icon' => $condition['icon'],
            'condition_scene' => $condition['scene'],
            'condition_group' => $weather['main'],
            'condition_id' => (int) $weather['id'],
            'provider_icon_code' => $weather['icon'],
            'sunrise_epoch' => isset($system['sunrise']) ? (int) $system['sunrise'] : null,
            'sunset_epoch' => isset($system['sunset']) ? (int) $system['sunset'] : null,
            'uv_index' => null,
        ];

        $hourly = [
            'time' => [],
            'temperature_2m' => [],
            'precipitation_probability' => [],
            'condition_label' => [],
            'condition_icon' => [],
            'condition_scene' => [],
            'provider_icon_code' => [],
        ];
        $days = [];

        foreach (($forecastPayload['list'] ?? []) as $forecastEntry) {
            if (! is_array($forecastEntry) || ! is_numeric($forecastEntry['dt'] ?? null)) {
                continue;
            }

            $forecastMain = is_array($forecastEntry['main'] ?? null) ? $forecastEntry['main'] : [];
            $forecastConditionData = $forecastEntry['weather'][0] ?? null;

            if (! is_numeric($forecastMain['temp'] ?? null) || ! is_array($forecastConditionData)) {
                continue;
            }

            $localTime = CarbonImmutable::createFromTimestampUTC((int) $forecastEntry['dt'])
                ->setTimezone($timeZone);
            $forecastCondition = $this->openWeatherCondition(
                (int) ($forecastConditionData['id'] ?? 0),
                (string) ($forecastConditionData['main'] ?? 'Unknown'),
            );
            $iconCode = is_string($forecastConditionData['icon'] ?? null)
                && preg_match('/^\d{2}[dn]$/', $forecastConditionData['icon']) === 1
                ? $forecastConditionData['icon']
                : null;

            $hourly['time'][] = $localTime->format('Y-m-d\TH:i');
            $hourly['temperature_2m'][] = (float) $forecastMain['temp'];
            $hourly['precipitation_probability'][] = isset($forecastEntry['pop'])
                ? (int) round((float) $forecastEntry['pop'] * 100)
                : null;
            $hourly['condition_label'][] = $forecastCondition['label'];
            $hourly['condition_icon'][] = $forecastCondition['icon'];
            $hourly['condition_scene'][] = $forecastCondition['scene'];
            $hourly['provider_icon_code'][] = $iconCode;

            $date = $localTime->format('Y-m-d');
            if (! isset($days[$date])) {
                $days[$date] = [
                    'time' => $date,
                    'temperature_2m_max' => (float) $forecastMain['temp'],
                    'temperature_2m_min' => (float) $forecastMain['temp'],
                    'condition_label' => $forecastCondition['label'],
                    'condition_icon' => $forecastCondition['icon'],
                    'condition_scene' => $forecastCondition['scene'],
                    'provider_icon_code' => $iconCode,
                    'precipitation_probability_max' => 0,
                    'precipitation_sum' => 0,
                ];
            }

            $days[$date]['temperature_2m_max'] = max($days[$date]['temperature_2m_max'], (float) $forecastMain['temp']);
            $days[$date]['temperature_2m_min'] = min($days[$date]['temperature_2m_min'], (float) $forecastMain['temp']);
            $days[$date]['precipitation_probability_max'] = max(
                $days[$date]['precipitation_probability_max'],
                (int) round((float) ($forecastEntry['pop'] ?? 0) * 100),
            );
            $days[$date]['precipitation_sum'] += (float) ($forecastEntry['rain']['3h'] ?? 0);
        }

        $daily = [
            'time' => [],
            'temperature_2m_max' => [],
            'temperature_2m_min' => [],
            'condition_label' => [],
            'condition_icon' => [],
            'condition_scene' => [],
            'provider_icon_code' => [],
            'precipitation_probability_max' => [],
            'precipitation_sum' => [],
        ];

        foreach ($days as $day) {
            foreach ($daily as $key => $_) {
                $daily[$key][] = $day[$key];
            }
        }

        return [
            'location' => [
                'name' => $currentPayload['name'],
                'country' => $system['country'] ?? null,
                'latitude' => $currentPayload['coord']['lat'] ?? null,
                'longitude' => $currentPayload['coord']['lon'] ?? null,
                'timezone' => $timeZone,
            ],
            'current' => $current,
            'hourly' => $hourly,
            'daily' => $daily,
        ];
    }

    /**
     * @return array{label: string, icon: string, scene: string}
     */
    private function openWeatherCondition(int $id, string $group): array
    {
        $normalizedGroup = strtolower($group);
        $scene = match (true) {
            $normalizedGroup === 'clear' => 'clear',
            $normalizedGroup === 'clouds' && $id <= 801 => 'partly-cloudy',
            $normalizedGroup === 'clouds' => 'cloudy',
            $normalizedGroup === 'drizzle', $normalizedGroup === 'rain' => 'rain',
            $normalizedGroup === 'thunderstorm', $normalizedGroup === 'squall', $normalizedGroup === 'tornado' => 'storm',
            $normalizedGroup === 'snow' => 'snow',
            in_array($normalizedGroup, ['mist', 'smoke', 'haze', 'dust', 'fog', 'sand', 'ash'], true) => 'fog',
            default => 'cloudy',
        };
        $icon = match ($scene) {
            'clear', 'hot' => '☀️',
            'partly-cloudy' => '⛅',
            'cloudy' => '☁️',
            'rain' => '🌧️',
            'storm' => '⛈️',
            'snow' => '❄️',
            'fog' => '🌫️',
            default => '🌤️',
        };

        return [
            'label' => match ($id) {
                800 => 'Clear sky',
                801 => 'Few clouds',
                802 => 'Scattered clouds',
                803 => 'Broken clouds',
                804 => 'Overcast clouds',
                default => ucwords($group),
            },
            'icon' => $icon,
            'scene' => $scene,
        ];
    }

    /**
     * @return array{success: false, state: string, message: string, http_status: ?int, failure_type: string}
     */
    private function providerErrorMessage(ConnectionException|RequestException|UnexpectedValueException $exception, ?int $status): array
    {
        if ($status === 401) {
            $message = 'Weather service configuration error. The API key is invalid or not active.';
            $failureType = 'configuration';
        } elseif ($status === 404) {
            $message = 'Location not found. Check the city name and try again.';
            $failureType = 'location';
        } elseif ($status === 429) {
            $message = 'Weather service is busy. Please wait before trying again.';
            $failureType = 'rate_limit';
        } elseif ($exception instanceof ConnectionException && str_contains(strtolower($exception->getMessage()), 'timed out')) {
            $message = 'The weather request timed out. Please try again.';
            $failureType = 'timeout';
        } elseif ($exception instanceof ConnectionException) {
            $message = 'Could not connect to the weather service. Please check the server connection.';
            $failureType = 'connection';
        } elseif ($exception instanceof UnexpectedValueException) {
            $message = 'Weather service returned an unexpected response. Please try again later.';
            $failureType = 'response';
        } else {
            $message = 'Weather service is temporarily unavailable. Please try again later.';
            $failureType = 'provider';
        }

        return [
            'success' => false,
            'state' => 'error',
            'message' => $message,
            'http_status' => $status,
            'failure_type' => $failureType,
        ];
    }

    private function qualifyCity(string $city): string
    {
        $city = trim($city);

        if (str_contains($city, ',')) {
            return $city;
        }

        return $city.','.config('services.openweather.default_country', 'PH');
    }

    private function offsetTimezone(int $offsetSeconds): string
    {
        $absoluteOffset = abs($offsetSeconds);
        $sign = $offsetSeconds < 0 ? '-' : '+';
        $hours = intdiv($absoluteOffset, 3600);
        $minutes = intdiv($absoluteOffset % 3600, 60);

        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }

    /**
     * @return array{label: string, icon: string, scene: string}
     */
    public function condition(int $code): array
    {
        return match (true) {
            $code === 0 => ['label' => 'Clear sky', 'icon' => '☀️', 'scene' => 'clear'],
            in_array($code, [1, 2], true) => ['label' => 'Partly cloudy', 'icon' => '⛅', 'scene' => 'partly-cloudy'],
            $code === 3 => ['label' => 'Overcast', 'icon' => '☁️', 'scene' => 'cloudy'],
            in_array($code, [45, 48], true) => ['label' => 'Fog', 'icon' => '🌫️', 'scene' => 'fog'],
            in_array($code, [51, 53, 55, 56, 57], true) => ['label' => 'Drizzle', 'icon' => '🌦️', 'scene' => 'rain'],
            in_array($code, [61, 63, 65, 66, 67, 80, 81, 82], true) => ['label' => 'Rain', 'icon' => '🌧️', 'scene' => 'rain'],
            in_array($code, [71, 73, 75, 77, 85, 86], true) => ['label' => 'Snow', 'icon' => '❄️', 'scene' => 'snow'],
            in_array($code, [95, 96, 99], true) => ['label' => 'Thunderstorm', 'icon' => '⛈️', 'scene' => 'storm'],
            default => ['label' => 'Variable conditions', 'icon' => '🌤️', 'scene' => 'cloudy'],
        };
    }

    /**
     * @param  array<int, string>  $times
     */
    private function hourlyStartIndex(array $times, string $timezone): int
    {
        $currentTime = now($timezone)->format('Y-m-d\TH:00');

        foreach ($times as $index => $time) {
            if ($time >= $currentTime) {
                return $index;
            }
        }

        return 0;
    }

    private function windDirection(int $degrees): string
    {
        return ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][(int) round($degrees / 45) % 8];
    }

    /**
     * @param  array<string, mixed>  $current
     */
    private function synchronizeAlerts(WeatherLocation $location, array $current, WeatherSetting $settings): void
    {
        $rain = (float) ($current['rain'] ?? $current['precipitation'] ?? 0);
        $temperature = (float) ($current['temperature_2m'] ?? 0);
        $windSpeed = (float) ($current['wind_speed_10m'] ?? 0);
        $isThunderstorm = ($current['condition_group'] ?? null) === 'Thunderstorm'
            || in_array((int) ($current['weather_code'] ?? 0), [95, 96, 99], true);
        $alerts = [];

        if ($isThunderstorm) {
            $alerts['thunderstorm'] = [
                'severity' => 'critical',
                'title' => 'Thunderstorm warning',
                'message' => 'Thunderstorm conditions are currently reported for '.$location->name.'.',
            ];
        }

        if ($rain >= $settings->heavy_rain_threshold_mm) {
            $alerts['heavy-rain'] = [
                'severity' => 'warning',
                'title' => 'Heavy rain alert',
                'message' => 'Heavy rainfall is currently reported for '.$location->name.'.',
            ];
        }

        if ($temperature >= $settings->high_temperature_threshold_c) {
            $alerts['high-temperature'] = [
                'severity' => 'advisory',
                'title' => 'Heat advisory',
                'message' => 'High temperatures are currently reported for '.$location->name.'.',
            ];
        }

        if ($windSpeed >= $settings->strong_wind_threshold_kmh) {
            $alerts['strong-wind'] = [
                'severity' => 'warning',
                'title' => 'Strong wind advisory',
                'message' => 'Strong winds are currently reported for '.$location->name.'.',
            ];
        }

        $activeAlerts = $location->alerts()->where('status', 'active')->get();

        foreach ($activeAlerts as $activeAlert) {
            if (! array_key_exists($activeAlert->alert_key, $alerts)) {
                $activeAlert->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                ]);
            }
        }

        foreach ($alerts as $key => $alert) {
            $activeAlert = $location->alerts()
                ->where('alert_key', $key)
                ->where('status', 'active')
                ->first();

            if ($activeAlert) {
                $activeAlert->update($alert);

                continue;
            }

            $location->alerts()->create([
                'alert_key' => $key,
                ...$alert,
                'status' => 'active',
                'started_at' => now(),
            ]);
        }
    }

    private function recordSync(WeatherLocation $location, string $status, string $message): void
    {
        $location->syncLogs()->create([
            'status' => $status,
            'message' => $message,
            'synced_at' => now(),
        ]);
    }
}
