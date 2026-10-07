<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\Program;
use App\Models\Section;
use App\Models\User;
use App\Models\WeatherLocation;
use App\Services\WeatherDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function administrator(Request $request, WeatherDataService $weatherData): View
    {
        $weatherSearch = $request->session()->get('weatherSearch');
        $searchCity = is_array($weatherSearch) ? ($weatherSearch['city'] ?? null) : null;
        $searchResult = is_array($weatherSearch) ? ($weatherSearch['result'] ?? null) : null;
        $averageInstructorQuality = Instructor::where('status', 'Active')->avg('quality_score');

        return view('dashboards.administrator', [
            'metrics' => [
                [
                    'label' => 'Student accounts',
                    'value' => User::where('role', User::ROLE_STUDENT)->count(),
                    'detail' => 'Registered student users',
                ],
                [
                    'label' => 'Classrooms',
                    'value' => Classroom::count(),
                    'detail' => 'Across all delivery modes',
                ],
                [
                    'label' => 'Active instructors',
                    'value' => Instructor::where('status', 'Active')->count(),
                    'detail' => 'Available instructor profiles',
                ],
                [
                    'label' => 'Instructor quality',
                    'value' => $averageInstructorQuality === null
                        ? '—'
                        : number_format((float) $averageInstructorQuality, 2).' / 5',
                    'detail' => 'Average for active instructors',
                ],
            ],
            'weatherWidget' => $this->weatherWidget($weatherData, $searchCity, $searchResult, true),
        ]);
    }

    public function staff(WeatherDataService $weatherData): View
    {
        return view('dashboards.staff', [
            'metrics' => [
                [
                    'label' => 'Active classrooms',
                    'value' => Classroom::where('status', 'Active')->count(),
                    'detail' => 'Currently active learning spaces',
                ],
                [
                    'label' => 'Active instructors',
                    'value' => Instructor::where('status', 'Active')->count(),
                    'detail' => 'Available instructor profiles',
                ],
                [
                    'label' => 'Active courses',
                    'value' => Course::where('status', 'Active')->count(),
                    'detail' => 'Courses in the academic catalog',
                ],
                [
                    'label' => 'Departments',
                    'value' => Department::count(),
                    'detail' => 'Academic departments',
                ],
            ],
            'weatherWidget' => $this->weatherWidget($weatherData),
        ]);
    }

    public function student(WeatherDataService $weatherData): View
    {
        return view('dashboards.student', [
            'metrics' => [
                [
                    'label' => 'Active courses',
                    'value' => Course::where('status', 'Active')->count(),
                    'detail' => 'In the institution-wide catalog',
                ],
                [
                    'label' => 'Active programs',
                    'value' => Program::where('status', 'Active')->count(),
                    'detail' => 'Available academic programs',
                ],
                [
                    'label' => 'Active sections',
                    'value' => Section::where('status', 'Active')->count(),
                    'detail' => 'Institution-wide section count',
                ],
            ],
            'weatherWidget' => $this->weatherWidget($weatherData),
        ]);
    }

    /**
     * @return array{location: ?WeatherLocation, state: string, message: string, last_synced_at: mixed, current: ?array, today: ?array, hourly: array<int, array<string, mixed>>, active_alerts: int, refresh_interval: int, http_status: ?int, response_time_ms: ?int, city_query: ?string, search_error: ?string, allow_search: bool}
     */
    private function weatherWidget(
        WeatherDataService $weatherData,
        ?string $city = null,
        ?array $searchResult = null,
        bool $allowSearch = false,
    ): array
    {
        $searchError = null;
        $settings = $weatherData->settings();

        if ($city !== null && $city !== '' && $searchResult !== null) {
            if ($searchResult['success'] && $searchResult['weather'] !== null && $searchResult['location'] !== null) {
                return $this->cityWeatherWidget($searchResult, $city, $settings->refresh_interval_minutes, $allowSearch);
            }

            $searchError = $searchResult['message'];
        }

        $location = WeatherLocation::where('is_default', true)
            ->where('is_active', true)
            ->first();

        if (! $location) {
            $defaultCity = (string) config('services.openweather.default_city', 'Manila');
            $defaultResult = $weatherData->weatherForCity($defaultCity);

            if ($defaultResult['success'] && $defaultResult['weather'] !== null && $defaultResult['location'] !== null) {
                return $this->cityWeatherWidget($defaultResult, $defaultCity, $settings->refresh_interval_minutes, $allowSearch, $searchError);
            }

            return [
                'location' => null,
                'state' => 'error',
                'message' => $defaultResult['message'],
                'last_synced_at' => null,
                'current' => null,
                'today' => null,
                'hourly' => [],
                'active_alerts' => 0,
                'refresh_interval' => 15,
                'http_status' => $defaultResult['http_status'],
                'response_time_ms' => $defaultResult['response_time_ms'],
                'city_query' => $defaultCity,
                'search_error' => $searchError,
                'allow_search' => $allowSearch,
            ];
        }

        $result = $weatherData->weatherFor($location);
        $presented = $result['snapshot']
            ? $weatherData->present($result['snapshot'], $location, $settings)
            : null;

        return [
            'location' => $location,
            'state' => $result['state'],
            'message' => $result['message'],
            'last_synced_at' => $result['snapshot']?->fetched_at,
            'current' => $presented['current'] ?? null,
            'today' => $presented['daily'][0] ?? null,
            'hourly' => array_slice($presented['hourly'] ?? [], 0, 6),
            'active_alerts' => $location->alerts()->where('status', 'active')->count(),
            'refresh_interval' => $settings->refresh_interval_minutes,
            'http_status' => $result['http_status'],
            'response_time_ms' => $result['response_time_ms'],
            'city_query' => $city,
            'search_error' => $searchError,
            'allow_search' => $allowSearch,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function cityWeatherWidget(
        array $result,
        string $cityQuery,
        int $refreshInterval,
        bool $allowSearch,
        ?string $searchError = null,
    ): array {
        $location = new WeatherLocation([
            'name' => $result['location']['name'],
            'country' => $result['location']['country'] ?? '',
            'latitude' => $result['location']['latitude'] ?? null,
            'longitude' => $result['location']['longitude'] ?? null,
            'timezone' => $result['location']['timezone'],
        ]);

        return [
            'location' => $location,
            'state' => $result['state'],
            'message' => $result['message'],
            'last_synced_at' => $result['fetched_at'],
            'current' => $result['weather']['current'],
            'today' => $result['weather']['daily'][0] ?? null,
            'hourly' => array_slice($result['weather']['hourly'], 0, 6),
            'active_alerts' => 0,
            'refresh_interval' => $refreshInterval,
            'http_status' => $result['http_status'],
            'response_time_ms' => $result['response_time_ms'],
            'city_query' => $cityQuery,
            'search_error' => $searchError,
            'allow_search' => $allowSearch,
        ];
    }
}
