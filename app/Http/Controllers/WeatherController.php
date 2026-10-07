<?php

namespace App\Http\Controllers;

use App\Models\WeatherAlert;
use App\Models\WeatherLocation;
use App\Models\WeatherSnapshot;
use App\Models\WeatherSyncLog;
use App\Models\User;
use App\Services\WeatherDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeatherController extends Controller
{
    public function index(Request $request, WeatherDataService $weatherData): View
    {
        $location = $this->selectedLocation($request);
        $result = $weatherData->weatherFor($location);
        $settings = $weatherData->settings();
        $presented = $result['snapshot']
            ? $weatherData->present($result['snapshot'], $location, $settings)
            : null;

        return view('weather.index', [
            'location' => $location,
            'locations' => WeatherLocation::where('is_active', true)->orderBy('name')->get(),
            'weather' => $presented,
            'state' => $result['state'],
            'stateMessage' => $result['message'],
            'lastSyncedAt' => $result['snapshot']?->fetched_at,
            'refreshInterval' => $settings->refresh_interval_minutes,
            'alerts' => $location->alerts()->where('status', 'active')->latest('started_at')->get(),
        ]);
    }

    public function history(Request $request, WeatherDataService $weatherData): View
    {
        $validated = $request->validate([
            'range' => ['nullable', 'in:24h,7d,30d'],
            'metric' => ['nullable', 'in:temperature,humidity,rain,wind,pressure'],
        ]);
        $location = $this->selectedLocation($request);
        $result = $weatherData->weatherFor($location);
        $settings = $weatherData->settings();
        $range = $validated['range'] ?? '24h';
        $metric = $validated['metric'] ?? 'temperature';
        $start = match ($range) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subHours(24),
        };
        $snapshots = $location->snapshots()
            ->where('fetched_at', '>=', $start)
            ->orderBy('fetched_at')
            ->get(['id', 'current', 'fetched_at']);

        $samples = $snapshots->map(function (WeatherSnapshot $snapshot) use ($metric, $range, $settings, $location): array {
            $current = $snapshot->current;
            $value = match ($metric) {
                'humidity' => (float) ($current['relative_humidity_2m'] ?? 0),
                'rain' => (float) ($current['rain'] ?? $current['precipitation'] ?? 0),
                'wind' => $this->convertWind((float) ($current['wind_speed_10m'] ?? 0), $settings->wind_speed_unit),
                'pressure' => (float) ($current['pressure_msl'] ?? $current['surface_pressure'] ?? 0),
                default => $this->convertTemperature((float) ($current['temperature_2m'] ?? 0), $settings->temperature_unit),
            };

            return [
                'label' => $snapshot->fetched_at->timezone($location->timezone)->format($range === '24h' ? 'g A' : 'M j'),
                'value' => round($value, 1),
            ];
        })->values();

        if ($samples->count() > 120) {
            $step = (int) ceil($samples->count() / 120);
            $samples = $samples->filter(fn (array $sample, int $index): bool => $index % $step === 0)->values();
        }

        $values = $samples->pluck('value')->all();
        $minimum = $values === [] ? 0 : min($values);
        $maximum = $values === [] ? 1 : max($values);
        $spread = max(1, $maximum - $minimum);
        $lastIndex = max(1, $samples->count() - 1);
        $points = $samples->map(function (array $sample, int $index) use ($minimum, $spread, $lastIndex): string {
            $x = ($index / $lastIndex) * 940;
            $y = 180 - ((($sample['value'] - $minimum) / $spread) * 160);

            return round($x, 1).','.round($y, 1);
        })->implode(' ');

        return view('weather.history', [
            'location' => $location,
            'locations' => WeatherLocation::where('is_active', true)->orderBy('name')->get(),
            'state' => $result['state'],
            'stateMessage' => $result['message'],
            'lastSyncedAt' => $result['snapshot']?->fetched_at,
            'range' => $range,
            'metric' => $metric,
            'samples' => $samples,
            'points' => $points,
            'minimum' => $minimum,
            'maximum' => $maximum,
            'metricLabel' => match ($metric) {
                'humidity' => 'Relative humidity (%)',
                'rain' => 'Precipitation (mm)',
                'wind' => 'Wind speed ('.match ($settings->wind_speed_unit) {
                    'mph' => 'mph',
                    'ms' => 'm/s',
                    default => 'km/h',
                }.')',
                'pressure' => 'Pressure (hPa)',
                default => 'Temperature ('.($settings->temperature_unit === 'fahrenheit' ? '°F' : '°C').')',
            },
        ]);
    }

    public function alerts(Request $request, WeatherDataService $weatherData): View
    {
        $location = $this->selectedLocation($request);
        $result = $weatherData->weatherFor($location);
        $settings = $weatherData->settings();

        return view('weather.alerts', [
            'location' => $location,
            'locations' => WeatherLocation::where('is_active', true)->orderBy('name')->get(),
            'state' => $result['state'],
            'stateMessage' => $result['message'],
            'lastSyncedAt' => $result['snapshot']?->fetched_at,
            'refreshInterval' => $settings->refresh_interval_minutes,
            'alerts' => WeatherAlert::where('weather_location_id', $location->id)
                ->latest('started_at')
                ->paginate(20),
        ]);
    }

    public function settings(WeatherDataService $weatherData): View
    {
        return view('weather.settings', [
            'settings' => $weatherData->settings(),
            'locations' => WeatherLocation::withCount('snapshots')->orderByDesc('is_default')->orderBy('name')->get(),
            'syncLogs' => WeatherSyncLog::with('location')->latest('synced_at')->limit(10)->get(),
        ]);
    }

    public function updateSettings(Request $request, WeatherDataService $weatherData): RedirectResponse
    {
        $validated = $request->validate([
            'temperature_unit' => ['required', 'in:celsius,fahrenheit'],
            'wind_speed_unit' => ['required', 'in:kmh,mph,ms'],
            'refresh_interval_minutes' => ['required', 'integer', 'min:5', 'max:60'],
            'heavy_rain_threshold_mm' => ['required', 'numeric', 'min:1', 'max:500'],
            'high_temperature_threshold_c' => ['required', 'numeric', 'min:25', 'max:60'],
            'strong_wind_threshold_kmh' => ['required', 'numeric', 'min:10', 'max:300'],
        ]);

        $weatherData->settings()->update($validated);

        return back()->with('success', 'Weather settings updated.');
    }

    public function search(Request $request, WeatherDataService $weatherData): RedirectResponse
    {
        $validated = $request->validate([
            'city' => ['required', 'string', 'max:100'],
        ]);
        $city = trim($validated['city']);
        $result = $weatherData->weatherForCity($city);

        return redirect()->route('dashboards.administrator')
            ->with('weatherSearch', ['city' => $city, 'result' => $result]);
    }

    public function sync(Request $request, WeatherDataService $weatherData): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => ['nullable', 'integer', 'exists:weather_locations,id'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        if (isset($validated['city'])) {
            abort_unless($request->user()?->role === User::ROLE_ADMINISTRATOR, 403);
            $result = $weatherData->weatherForCity(trim($validated['city']));

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => $result['success'],
                    'state' => $result['state'],
                    'message' => $result['message'],
                    'last_synced_at' => $result['fetched_at']?->toIso8601String(),
                    'http_status' => $result['http_status'],
                    'response_time_ms' => $result['response_time_ms'],
                ], $result['success'] ? 200 : 503);
            }

            return back()
                ->with('weatherSearch', ['city' => trim($validated['city']), 'result' => $result])
                ->with($result['success'] ? 'success' : 'error', $result['message']);
        }

        $location = isset($validated['location_id'])
            ? WeatherLocation::where('is_active', true)->findOrFail($validated['location_id'])
            : WeatherLocation::where('is_default', true)->where('is_active', true)->firstOrFail();
        $result = $weatherData->synchronize($location);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['success'],
                'state' => $result['success'] ? 'online' : ($result['snapshot'] ? 'stale' : 'error'),
                'message' => $result['success']
                    ? 'Weather data synchronized successfully.'
                    : $result['message'],
                'last_synced_at' => $result['snapshot']?->fetched_at?->toIso8601String(),
                'http_status' => $result['http_status'],
                'response_time_ms' => $result['response_time_ms'],
            ], $result['success'] ? 200 : 503);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? 'Weather data synchronized successfully.'
                : $result['message'],
        );
    }

    public function status(Request $request, WeatherDataService $weatherData): JsonResponse
    {
        $validated = $request->validate([
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        if (isset($validated['city'])) {
            abort_unless($request->user()?->role === User::ROLE_ADMINISTRATOR, 403);
            $result = $weatherData->weatherForCity(trim($validated['city']));

            return response()->json([
                'state' => $result['state'],
                'message' => $result['message'],
                'last_synced_at' => $result['fetched_at']?->toIso8601String(),
                'http_status' => $result['http_status'],
                'response_time_ms' => $result['response_time_ms'],
            ], $result['success'] ? 200 : 503);
        }

        $location = $this->selectedLocation($request);
        $result = $weatherData->weatherFor($location);

        return response()->json([
            'state' => $result['state'],
            'message' => $result['message'],
            'last_synced_at' => $result['snapshot']?->fetched_at?->toIso8601String(),
            'http_status' => $result['http_status'],
            'response_time_ms' => $result['response_time_ms'],
        ]);
    }

    private function selectedLocation(Request $request): WeatherLocation
    {
        $validated = $request->validate([
            'location' => ['nullable', 'integer'],
        ]);

        if (isset($validated['location'])) {
            return WeatherLocation::where('is_active', true)->findOrFail($validated['location']);
        }

        return WeatherLocation::where('is_default', true)
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function convertTemperature(float $celsius, string $unit): float
    {
        return $unit === 'fahrenheit' ? ($celsius * 9 / 5) + 32 : $celsius;
    }

    private function convertWind(float $kilometersPerHour, string $unit): float
    {
        return match ($unit) {
            'mph' => $kilometersPerHour / 1.609344,
            'ms' => $kilometersPerHour / 3.6,
            default => $kilometersPerHour,
        };
    }
}
