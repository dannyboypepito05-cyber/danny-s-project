<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Weather intelligence</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-950">Current weather & forecast</h1>
            </div>
            @include('weather.partials.location-selector', ['routeName' => 'weather.index'])
        </div>
    </x-slot>

    <div class="weather-workspace py-7 sm:py-10">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:px-8">
            @include('weather.partials.status')

            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">{{ session('error') }}</div>
            @endif

            @if($weather)
                @php($current = $weather['current'])
                <section class="weather-scene weather-scene--{{ $current['scene'] }} relative overflow-hidden rounded-3xl border border-slate-200 shadow-sm" aria-label="{{ $location->name }} weather scene: {{ $current['condition'] }}">
                    <div class="weather-sun" aria-hidden="true"></div>
                    <div class="weather-cloud weather-cloud--one" aria-hidden="true">☁</div>
                    <div class="weather-cloud weather-cloud--two" aria-hidden="true">☁</div>
                    <div class="weather-rain" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                    <div class="weather-ground" aria-hidden="true"></div>
                    <div class="relative z-10 grid min-h-[300px] gap-6 p-6 sm:min-h-[340px] sm:grid-cols-[1.1fr_0.9fr] sm:items-center sm:p-10">
                        <div class="max-w-xl">
                            <p class="text-sm font-semibold text-slate-700">📍 {{ $location->name }}{{ $location->region ? ', '.$location->region : '' }}, {{ $location->country }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ now($location->timezone)->format('l, F j · g:i A') }}</p>
                            <div class="mt-7 flex items-start gap-3">
                                @if($current['provider_icon_url'])
                                    <img src="{{ $current['provider_icon_url'] }}" alt="{{ $current['condition'] }}" width="96" height="96" class="h-20 w-20 object-contain drop-shadow-sm sm:h-24 sm:w-24">
                                @else
                                    <span class="text-5xl drop-shadow-sm sm:text-6xl" aria-hidden="true">{{ $current['icon'] }}</span>
                                @endif
                                <div>
                                    <p class="text-5xl font-semibold tracking-tight text-slate-950 sm:text-6xl">{{ $current['temperature_display'] }}<span class="text-3xl">{{ $current['temperature_unit'] }}</span></p>
                                    <p class="mt-1 text-lg font-medium text-slate-800">{{ $current['condition'] }}</p>
                                    <p class="mt-1 text-sm text-slate-600">Feels like {{ $current['feels_like_display'] }}{{ $current['temperature_unit'] }}</p>
                                </div>
                            </div>
                            <p class="mt-5 inline-flex items-center rounded-full border border-white/70 bg-white/70 px-3 py-1 text-xs font-medium text-slate-700 backdrop-blur">
                                Weather scene updates from the latest synchronized conditions
                            </p>
                        </div>
                        <div class="relative flex min-h-36 items-end justify-center sm:min-h-52" aria-hidden="true">
                            <div class="weather-person">{{ in_array($current['scene'], ['rain', 'storm', 'snow'], true) ? '🧍🏻‍♀️' : '🧍🏻' }}</div>
                            @if(in_array($current['scene'], ['rain', 'storm'], true))
                                <span class="weather-umbrella">☂️</span>
                                <span class="absolute bottom-3 rounded-full bg-white/50 px-3 py-1 text-xs font-semibold text-slate-700">Rain gear</span>
                            @elseif($current['scene'] === 'hot')
                                <span class="absolute bottom-3 rounded-full bg-white/50 px-3 py-1 text-xs font-semibold text-slate-700">Hot conditions · stay hydrated</span>
                            @endif
                        </div>
                    </div>
                </section>

                @if($alerts->isNotEmpty())
                    <section class="grid gap-3" aria-label="Active weather alerts">
                        @foreach($alerts as $alert)
                            <a href="{{ route('weather.alerts', ['location' => $location->id]) }}" class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-950 transition hover:border-amber-300">
                                <span class="text-xl" aria-hidden="true">⚠</span>
                                <span>
                                    <span class="block text-sm font-semibold">{{ $alert->title }} · {{ ucfirst($alert->severity) }}</span>
                                    <span class="mt-1 block text-sm">{{ $alert->message }}</span>
                                </span>
                            </a>
                        @endforeach
                    </section>
                @endif

                <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4" aria-label="Current weather statistics">
                    @foreach([
                        ['Humidity', ($current['humidity'] ?? '—').'%', '💧'],
                        ['Wind', $current['wind_display'] !== null ? $current['wind_display'].' '.$current['wind_unit'].' · '.($current['wind_direction'] ?? '—') : '—', '🌬️'],
                        ['Pressure', $current['pressure'] !== null ? $current['pressure'].' hPa' : '—', '🧭'],
                        ['Visibility', $current['visibility_km'] !== null ? $current['visibility_km'].' km' : '—', '🌫️'],
                        ['Rain chance', $current['rain_probability'] !== null ? $current['rain_probability'].'%' : '—', '🌧️'],
                        ['UV index', $current['uv_index'] !== null ? $current['uv_index'].' · '.$current['uv_risk'] : 'Not provided', '☀️'],
                        ['Today · high / low', (($weather['daily'][0]['maximum'] ?? $current['maximum_display'] ?? '—').$current['temperature_unit'].' / '.($weather['daily'][0]['minimum'] ?? $current['minimum_display'] ?? '—').$current['temperature_unit']), '🌡️'],
                        ['Precipitation', $current['precipitation'] !== null ? $current['precipitation'].' mm' : '—', '💧'],
                    ] as [$label, $value, $icon])
                        <article class="weather-panel rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                            <p class="text-xs font-medium text-slate-500">{{ $icon }} {{ $label }}</p>
                            <p class="mt-2 text-xl font-semibold text-slate-950">{{ $value }}</p>
                        </article>
                    @endforeach
                </section>

                <section class="grid gap-5 xl:grid-cols-[1.4fr_1fr]">
                    <article class="weather-panel rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-base font-semibold text-slate-950">Next 24 hours</h2>
                                <p class="mt-1 text-sm text-slate-500">Forecast readings every three hours</p>
                            </div>
                            <a href="{{ route('weather.history', ['location' => $location->id]) }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">Weather history</a>
                        </div>
                        <div class="mt-5 flex gap-3 overflow-x-auto pb-2" aria-label="Hourly forecast">
                            @foreach($weather['hourly'] as $hour)
                                <div class="min-w-[88px] rounded-xl bg-slate-50 px-3 py-4 text-center">
                                    <p class="text-xs font-medium text-slate-500">{{ $loop->first ? 'NOW' : $hour['time'] }}</p>
                                    @if($hour['provider_icon_url'])
                                        <img src="{{ $hour['provider_icon_url'] }}" alt="{{ $hour['condition'] }}" width="48" height="48" class="mx-auto mt-2 h-10 w-10 object-contain">
                                    @else
                                        <p class="mt-3 text-2xl" aria-hidden="true">{{ $hour['icon'] }}</p>
                                    @endif
                                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $hour['temperature'] }}{{ $hour['temperature_unit'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $hour['precipitation_probability'] !== null ? $hour['precipitation_probability'].'% rain' : 'Rain chance unavailable' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="weather-panel rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
                        <div>
                            <h2 class="text-base font-semibold text-slate-950">5-day forecast</h2>
                            <p class="mt-1 text-sm text-slate-500">Daily conditions for {{ $location->name }} · OpenWeather forecast</p>
                        </div>
                        <div class="mt-4 divide-y divide-slate-100">
                            @foreach($weather['daily'] as $day)
                                <div class="flex items-center justify-between gap-3 py-3">
                                    <span class="min-w-20 text-sm font-medium text-slate-700">{{ $loop->first ? 'Today' : $day['date'] }}</span>
                                    @if($day['provider_icon_url'])
                                        <img src="{{ $day['provider_icon_url'] }}" alt="{{ $day['condition'] }}" width="48" height="48" class="h-10 w-10 object-contain">
                                    @else
                                        <span class="text-xl" aria-label="{{ $day['condition'] }}">{{ $day['icon'] }}</span>
                                    @endif
                                    <span class="text-xs text-slate-500">{{ $day['rain_probability'] !== null ? $day['rain_probability'].'% rain' : '—' }}</span>
                                    <span class="text-sm font-semibold text-slate-900">{{ $day['maximum'] }}{{ $day['temperature_unit'] }} <span class="font-normal text-slate-500">{{ $day['minimum'] }}{{ $day['temperature_unit'] }}</span></span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                </section>

                <section class="weather-panel flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Sunrise & sunset</h2>
                        <p class="mt-1 text-sm text-slate-500">Local time · {{ $location->timezone }}</p>
                    </div>
                    <div class="flex gap-6 text-sm">
                        <p><span class="block text-xs text-slate-500">Sunrise</span><span class="font-semibold text-slate-900">{{ $current['sunrise'] ?? '—' }}</span></p>
                        <p><span class="block text-xs text-slate-500">Sunset</span><span class="font-semibold text-slate-900">{{ $current['sunset'] ?? '—' }}</span></p>
                    </div>
                    <form method="POST" action="{{ route('weather.sync') }}">
                        @csrf
                        <input type="hidden" name="location_id" value="{{ $location->id }}">
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Sync now</button>
                    </form>
                </section>
            @else
                <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <span class="text-4xl" aria-hidden="true">🌦️</span>
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950">No weather reading is available</h2>
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">{{ $stateMessage }} Check the server connection and try again. The system will not substitute sample data for live conditions.</p>
                    </div>
                    <form method="POST" action="{{ route('weather.sync') }}" class="justify-self-start">
                        @csrf
                        <input type="hidden" name="location_id" value="{{ $location->id }}">
                        <button type="submit" class="inline-flex min-h-10 items-center rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">Retry synchronization</button>
                    </form>
                </section>
            @endif

            <p class="text-xs leading-5 text-slate-500">Weather data supplied by <a class="font-medium text-indigo-700 underline underline-offset-2" href="https://openweathermap.org/" target="_blank" rel="noopener noreferrer">OpenWeather</a>. Forecasts are informational and may change.</p>
        </div>
    </div>
</x-app-layout>
