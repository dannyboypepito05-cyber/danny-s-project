<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Administrator</p>
            <h1 class="mt-1 text-xl font-semibold text-slate-950">Weather system settings</h1>
        </div>
    </x-slot>

    <div class="weather-workspace py-7 sm:py-10">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                    <p class="font-semibold">Please correct the following settings.</p>
                    <ul class="mt-2 list-disc space-y-1 ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <section class="weather-panel rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Display & alert thresholds</h2>
                    <p class="mt-1 text-sm text-slate-500">Live conditions are provided by OpenWeather. Keep OPENWEATHER_API_KEY in the server environment; it is never sent to the browser.</p>
                </div>
                <form method="POST" action="{{ route('weather.settings.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    @method('PUT')
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Temperature display
                        <select name="temperature_unit" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="celsius" @selected(old('temperature_unit', $settings->temperature_unit) === 'celsius')>Celsius</option>
                            <option value="fahrenheit" @selected(old('temperature_unit', $settings->temperature_unit) === 'fahrenheit')>Fahrenheit</option>
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Wind speed display
                        <select name="wind_speed_unit" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="kmh" @selected(old('wind_speed_unit', $settings->wind_speed_unit) === 'kmh')>Kilometres per hour</option>
                            <option value="mph" @selected(old('wind_speed_unit', $settings->wind_speed_unit) === 'mph')>Miles per hour</option>
                            <option value="ms" @selected(old('wind_speed_unit', $settings->wind_speed_unit) === 'ms')>Metres per second</option>
                        </select>
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Refresh interval (minutes)
                        <input type="number" name="refresh_interval_minutes" min="5" max="60" value="{{ old('refresh_interval_minutes', $settings->refresh_interval_minutes) }}" required class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Heavy rain alert (mm/hour)
                        <input type="number" name="heavy_rain_threshold_mm" min="1" max="500" step="0.1" value="{{ old('heavy_rain_threshold_mm', $settings->heavy_rain_threshold_mm) }}" required class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Heat advisory (°C)
                        <input type="number" name="high_temperature_threshold_c" min="25" max="60" step="0.1" value="{{ old('high_temperature_threshold_c', $settings->high_temperature_threshold_c) }}" required class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Strong wind alert (km/h)
                        <input type="number" name="strong_wind_threshold_kmh" min="10" max="300" step="0.1" value="{{ old('strong_wind_threshold_kmh', $settings->strong_wind_threshold_kmh) }}" required class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <div class="sm:col-span-2 xl:col-span-3">
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Save settings</button>
                    </div>
                </form>
            </section>

            <section class="weather-panel rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Weather locations</h2>
                        <p class="mt-1 text-sm text-slate-500">Manage active locations and choose the default dashboard location.</p>
                    </div>
                    <a href="{{ route('weather-locations.index') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Manage locations</a>
                </div>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach($locations as $location)
                        <div class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                            <span class="font-medium text-slate-800">{{ $location->name }}{{ $location->region ? ', '.$location->region : '' }}</span>
                            <span class="text-slate-500">{{ $location->is_default ? 'Default · ' : '' }}{{ $location->is_active ? 'Active' : 'Inactive' }} · {{ $location->snapshots_count }} stored observations</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="weather-panel overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-950">Recent synchronization log</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50"><tr>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Location</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Message</th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($syncLogs as $log)
                                <tr>
                                    <td class="px-5 py-3 text-sm text-slate-700">{{ $log->location?->name ?? 'Deleted location' }}</td>
                                    <td class="px-5 py-3 text-sm font-semibold {{ $log->status === 'success' ? 'text-emerald-700' : 'text-rose-700' }}">{{ ucfirst($log->status) }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-600">{{ $log->message }}</td>
                                    <td class="px-5 py-3 text-sm text-slate-500">{{ $log->synced_at->format('M j, g:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">No synchronization attempts have been recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
