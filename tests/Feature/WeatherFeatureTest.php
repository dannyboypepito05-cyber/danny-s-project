<?php

use App\Models\User;
use App\Models\WeatherLocation;
use App\Models\WeatherSetting;
use App\Models\WeatherSnapshot;
use App\Services\WeatherDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.openweather.key', 'test-openweather-key');
});

function weatherTacPayload(array $currentOverrides = []): array
{
    $hour = now('Asia/Manila')->startOfHour();

    return [
        'current' => array_merge([
            'temperature_2m' => 37.5,
            'relative_humidity_2m' => 78,
            'apparent_temperature' => 42,
            'is_day' => 1,
            'precipitation' => 12,
            'rain' => 12,
            'showers' => 0,
            'weather_code' => 95,
            'cloud_cover' => 100,
            'pressure_msl' => 1008,
            'surface_pressure' => 1005,
            'wind_speed_10m' => 55,
            'wind_direction_10m' => 180,
            'wind_gusts_10m' => 70,
        ], $currentOverrides),
        'hourly' => [
            'time' => [$hour->format('Y-m-d\TH:i'), $hour->addHours(3)->format('Y-m-d\TH:i')],
            'temperature_2m' => [37.5, 36.8],
            'relative_humidity_2m' => [78, 80],
            'apparent_temperature' => [42, 40],
            'precipitation_probability' => [90, 70],
            'precipitation' => [12, 4],
            'rain' => [12, 4],
            'weather_code' => [95, 61],
            'wind_speed_10m' => [55, 45],
            'wind_direction_10m' => [180, 185],
            'pressure_msl' => [1008, 1009],
            'uv_index' => [5, 3],
            'visibility' => [6000, 8000],
        ],
        'daily' => [
            'time' => [$hour->format('Y-m-d'), $hour->addDay()->format('Y-m-d')],
            'weather_code' => [95, 61],
            'temperature_2m_max' => [37.5, 34],
            'temperature_2m_min' => [25, 24],
            'sunrise' => [$hour->startOfDay()->addHours(6)->format('Y-m-d\TH:i'), $hour->addDay()->startOfDay()->addHours(6)->format('Y-m-d\TH:i')],
            'sunset' => [$hour->startOfDay()->addHours(18)->format('Y-m-d\TH:i'), $hour->addDay()->startOfDay()->addHours(18)->format('Y-m-d\TH:i')],
            'uv_index_max' => [8, 6],
            'precipitation_sum' => [12, 4],
            'precipitation_probability_max' => [90, 60],
        ],
    ];
}

function openWeatherCurrentPayload(array $currentOverrides = []): array
{
    $weather = weatherTacPayload($currentOverrides);
    $current = $weather['current'];
    [$weatherId, $condition, $description, $icon] = match ((int) $current['weather_code']) {
        0 => [800, 'Clear', 'clear sky', '01d'],
        1 => [801, 'Clouds', 'few clouds', '02d'],
        2 => [802, 'Clouds', 'scattered clouds', '03d'],
        95, 96, 99 => [200, 'Thunderstorm', 'thunderstorm with light rain', '11d'],
        default => [500, 'Rain', 'light rain', '10d'],
    };

    return [
        'name' => 'Doroluman',
        'coord' => ['lat' => 7.35388, 'lon' => 125.0894147],
        'sys' => [
            'country' => 'PH',
            'sunrise' => now('Asia/Manila')->startOfDay()->addHours(6)->utc()->timestamp,
            'sunset' => now('Asia/Manila')->startOfDay()->addHours(18)->utc()->timestamp,
        ],
        'timezone' => 28800,
        'main' => [
            'temp' => $current['temperature_2m'],
            'feels_like' => $current['apparent_temperature'],
            'temp_min' => 25,
            'temp_max' => $current['temperature_2m'],
            'pressure' => $current['pressure_msl'],
            'humidity' => $current['relative_humidity_2m'],
        ],
        'weather' => [[
            'id' => $weatherId,
            'main' => $condition,
            'description' => $description,
            'icon' => $icon,
        ]],
        'wind' => [
            'speed' => $current['wind_speed_10m'] / 3.6,
            'deg' => $current['wind_direction_10m'],
        ],
        'visibility' => 6000,
        'rain' => $current['rain'] > 0 ? ['1h' => $current['rain']] : [],
    ];
}

function openWeatherForecastPayload(array $currentOverrides = []): array
{
    $payload = weatherTacPayload($currentOverrides);
    $entries = [];

    foreach ($payload['hourly']['time'] as $index => $time) {
        [$weatherId, $condition, $description, $icon] = match ((int) ($payload['hourly']['weather_code'][$index] ?? 0)) {
            0 => [800, 'Clear', 'clear sky', '01d'],
            1 => [801, 'Clouds', 'few clouds', '02d'],
            2 => [802, 'Clouds', 'scattered clouds', '03d'],
            95, 96, 99 => [200, 'Thunderstorm', 'thunderstorm with light rain', '11d'],
            default => [500, 'Rain', 'light rain', '10d'],
        };
        $dateTime = \Carbon\CarbonImmutable::parse($time, 'Asia/Manila');
        $entry = [
            'dt' => $dateTime->utc()->timestamp,
            'main' => ['temp' => $payload['hourly']['temperature_2m'][$index]],
            'weather' => [[
                'id' => $weatherId,
                'main' => $condition,
                'description' => $description,
                'icon' => $icon,
            ]],
            'pop' => ($payload['hourly']['precipitation_probability'][$index] ?? 0) / 100,
        ];

        if (($payload['hourly']['rain'][$index] ?? 0) > 0) {
            $entry['rain'] = ['3h' => $payload['hourly']['rain'][$index]];
        }

        $entries[] = $entry;
    }

    $lastEntry = $entries[array_key_last($entries)];
    $lastEntry['dt'] = \Carbon\CarbonImmutable::createFromTimestampUTC($lastEntry['dt'])->addHours(3)->timestamp;
    $lastEntry['main']['temp'] = $payload['daily']['temperature_2m_min'][0];
    $entries[] = $lastEntry;

    return ['list' => $entries];
}

it('synchronizes live weather, displays forecasts, and records alerts', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(openWeatherCurrentPayload()),
        'api.openweathermap.org/data/2.5/forecast*' => Http::response(openWeatherForecastPayload()),
    ]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $location = WeatherLocation::where('is_default', true)->firstOrFail();

    $this->actingAs($student)
        ->postJson(route('weather.sync', absolute: false), ['location_id' => $location->id])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('state', 'online');

    $this->get(route('weather.index', absolute: false))
        ->assertOk()
        ->assertSee('Doroluman')
        ->assertSeeText('37.5°C')
        ->assertSeeText('55 km/h · S')
        ->assertSeeText('Not provided')
        ->assertSeeText('37.5°C / 25°C')
        ->assertSee('Thunderstorm')
        ->assertSee('Next 24 hours')
        ->assertSee('5-day forecast');

    expect(WeatherSnapshot::count())->toBe(1);
    expect($location->alerts()->where('status', 'active')->count())->toBe(4);
    expect($location->syncLogs()->where('status', 'success')->count())->toBe(1);

    Http::assertSent(fn ($request): bool => str_contains($request->url(), 'api.openweathermap.org/data/2.5/weather')
        && $request['q'] === 'Doroluman,PH'
        && $request['units'] === 'metric'
        && isset($request['appid']));
});

it('keeps current conditions available when the forecast endpoint is unavailable', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(openWeatherCurrentPayload()),
        'api.openweathermap.org/data/2.5/forecast*' => Http::failedConnection(),
    ]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->actingAs($student)
        ->get(route('weather.index', absolute: false))
        ->assertOk()
        ->assertSee('37.5°C')
        ->assertSee('Online · synchronized');

    expect(WeatherSnapshot::count())->toBe(1);
});

it('shows cached readings with a stale indicator when the weather provider is unreachable', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::failedConnection(),
    ]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $location = WeatherLocation::where('is_default', true)->firstOrFail();
    $payload = weatherTacPayload([
        'temperature_2m' => 27,
        'weather_code' => 2,
        'rain' => 0,
        'precipitation' => 0,
        'wind_speed_10m' => 10,
    ]);
    $snapshot = $location->snapshots()->create([
        'source' => 'Open-Meteo',
        ...$payload,
        'fetched_at' => now()->subHours(2),
    ]);

    $this->actingAs($student)
        ->get(route('weather.index', absolute: false))
        ->assertOk()
        ->assertSee('Offline · showing cached data')
        ->assertSeeText('27°C')
        ->assertSee('The provider is unavailable');

    expect($location->snapshots()->count())->toBe(1);
    expect($snapshot->fresh()->fetched_at->isBefore(now()->subHour()))->toBeTrue();
    expect($location->syncLogs()->where('status', 'failed')->count())->toBe(1);
});

it('does not substitute sample conditions when both provider and local cache are unavailable', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(['message' => 'unavailable'], 503),
    ]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->actingAs($student)
        ->get(route('weather.index', absolute: false))
        ->assertOk()
        ->assertSee('No weather reading is available')
        ->assertSee('no cached reading exists yet')
        ->assertDontSee('28°C');

    expect(WeatherSnapshot::count())->toBe(0);
});

it('restricts weather settings to administrators and persists validated preferences', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

    $this->actingAs($student)
        ->get(route('weather.settings', absolute: false))
        ->assertForbidden();

    $this->actingAs($administrator)
        ->put(route('weather.settings.update', absolute: false), [
            'temperature_unit' => 'fahrenheit',
            'wind_speed_unit' => 'mph',
            'refresh_interval_minutes' => 20,
            'heavy_rain_threshold_mm' => 15,
            'high_temperature_threshold_c' => 36,
            'strong_wind_threshold_kmh' => 55,
        ])
        ->assertRedirect();

    expect(WeatherSetting::firstOrFail()->temperature_unit)->toBe('fahrenheit')
        ->and(WeatherSetting::firstOrFail()->wind_speed_unit)->toBe('mph')
        ->and(WeatherSetting::firstOrFail()->refresh_interval_minutes)->toBe(20);
});

it('rejects invalid weather settings without changing persisted preferences', function () {
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
    $settings = app(WeatherDataService::class)->settings();

    $this->actingAs($administrator)
        ->put(route('weather.settings.update', absolute: false), [
            'temperature_unit' => 'kelvin',
            'wind_speed_unit' => 'mph',
            'refresh_interval_minutes' => 1,
            'heavy_rain_threshold_mm' => 15,
            'high_temperature_threshold_c' => 36,
            'strong_wind_threshold_kmh' => 55,
        ])
        ->assertSessionHasErrors(['temperature_unit', 'refresh_interval_minutes']);

    expect($settings->fresh()->temperature_unit)->toBe('celsius')
        ->and($settings->fresh()->refresh_interval_minutes)->toBe(15);
});

it('allows administrators to add locations and protects the default location from deletion', function () {
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
    $defaultLocation = WeatherLocation::where('is_default', true)->firstOrFail();

    $this->actingAs($administrator)
        ->post(route('weather-locations.store', absolute: false), [
            'name' => 'Manila',
            'region' => 'Metro Manila',
            'country' => 'Philippines',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'timezone' => 'Asia/Manila',
            'is_active' => '1',
            'is_default' => '0',
        ])
        ->assertRedirect(route('weather-locations.index', absolute: false));

    expect(WeatherLocation::where('name', 'Manila')->firstOrFail()->is_active)->toBeTrue();

    $this->actingAs($administrator)
        ->delete(route('weather-locations.destroy', $defaultLocation, absolute: false))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(WeatherLocation::find($defaultLocation->id))->not->toBeNull();
});

it('allows administrators to search live weather while preventing other roles from searching cities', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(openWeatherCurrentPayload()),
        'api.openweathermap.org/data/2.5/forecast*' => Http::response(openWeatherForecastPayload()),
    ]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

    $this->actingAs($student)
        ->post(route('weather.search', absolute: false), ['city' => 'Davao City'])
        ->assertForbidden();

    $this->actingAs($administrator)
        ->post(route('weather.search', absolute: false), ['city' => 'Davao City'])
        ->assertRedirect(route('dashboards.administrator', absolute: false))
        ->assertSessionHas('weatherSearch');

    $this->get(route('dashboards.administrator', absolute: false))
        ->assertOk()
        ->assertSee('Doroluman')
        ->assertSee('Search weather');

    Http::assertSent(fn ($request): bool => str_contains($request->url(), 'api.openweathermap.org/data/2.5/weather')
        && $request['q'] === 'Davao City,PH');
});

it('adds a live weather summary to the role dashboard', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(openWeatherCurrentPayload([
            'temperature_2m' => 29,
            'weather_code' => 1,
            'rain' => 0,
            'precipitation' => 0,
            'wind_speed_10m' => 10,
        ])),
        'api.openweathermap.org/data/2.5/forecast*' => Http::response(openWeatherForecastPayload([
            'temperature_2m' => 29,
            'weather_code' => 1,
            'rain' => 0,
            'precipitation' => 0,
            'wind_speed_10m' => 10,
        ])),
    ]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->actingAs($student)
        ->get(route('dashboards.student', absolute: false))
        ->assertOk()
        ->assertSee('Weather today')
        ->assertSee('Doroluman')
        ->assertSeeText('29°C')
        ->assertSeeText('37.5°C')
        ->assertSeeText('25°C')
        ->assertSee('Next hours')
        ->assertSee('Online · Synced')
        ->assertSee('Full forecast');
});

it('shows today and hourly cached weather on the dashboard when the provider is offline', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::failedConnection(),
    ]);
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $location = WeatherLocation::where('is_default', true)->firstOrFail();
    $location->snapshots()->create([
        'source' => 'Open-Meteo',
        ...weatherTacPayload([
            'temperature_2m' => 27,
            'weather_code' => 2,
            'rain' => 0,
            'precipitation' => 0,
            'wind_speed_10m' => 10,
        ]),
        'fetched_at' => now()->subHours(2),
    ]);

    $this->actingAs($student)
        ->get(route('dashboards.student', absolute: false))
        ->assertOk()
        ->assertSee('Offline · Saved forecast')
        ->assertSeeText('27°C')
        ->assertSeeText('37.5°C')
        ->assertSeeText('25°C')
        ->assertSee('Next hours')
        ->assertSee('showing the last saved forecast');
});

it('renders weather history for a selected range and metric', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $location = WeatherLocation::where('is_default', true)->firstOrFail();
    $snapshot = $location->snapshots()->create([
        'source' => 'Open-Meteo',
        ...weatherTacPayload(),
        'fetched_at' => now(),
    ]);

    $this->actingAs($student)
        ->get(route('weather.history', [
            'location' => $location->id,
            'range' => '7d',
            'metric' => 'humidity',
        ], absolute: false))
        ->assertOk()
        ->assertSee('History & analytics')
        ->assertSee('Relative humidity (%)')
        ->assertSee('Last 7 days')
        ->assertSeeText('78')
        ->assertSee($snapshot->fetched_at->timezone($location->timezone)->format('M j'));
});

it('applies configured units consistently to the current and forecast readings', function () {
    Http::preventStrayRequests();
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $location = WeatherLocation::where('is_default', true)->firstOrFail();
    $location->snapshots()->create([
        'source' => 'Open-Meteo',
        ...weatherTacPayload(),
        'fetched_at' => now(),
    ]);
    app(WeatherDataService::class)->settings()->update([
        'temperature_unit' => 'fahrenheit',
        'wind_speed_unit' => 'mph',
    ]);

    $this->actingAs($student)
        ->get(route('weather.index', absolute: false))
        ->assertOk()
        ->assertSeeText('99.5°F')
        ->assertSeeText('98.2°F')
        ->assertSeeText('77°F')
        ->assertSeeText('34.2 mph · S');
});
