<section class="weather-dashboard-card weather-dashboard-card--{{ $weatherWidget['current']['scene'] ?? 'unavailable' }} relative isolate overflow-hidden rounded-3xl border border-white/60 shadow-xl shadow-slate-900/10" aria-label="Today's weather">
    <div class="weather-dashboard-glow" aria-hidden="true"></div>
    @if($weatherWidget['current'])
        <div class="weather-dashboard-cloud weather-dashboard-cloud--one" aria-hidden="true">☁</div>
        <div class="weather-dashboard-cloud weather-dashboard-cloud--two" aria-hidden="true">☁</div>
    @endif

    <div class="relative z-10 grid gap-6 p-5 sm:p-7 lg:grid-cols-[1fr_auto] lg:gap-8">
        <div class="grid content-start gap-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-indigo-700">
                        <span aria-hidden="true">☁</span> WeatherSphere Pro
                    </p>
                    <p class="mt-1 text-xs font-medium text-slate-600">Real-Time Weather Intelligence</p>
                    <h2 class="mt-1 text-lg font-semibold tracking-tight text-slate-950">
                        {{ $weatherWidget['location']?->name ?? 'Weather location unavailable' }}
                        @if($weatherWidget['location'])
                            <span class="font-normal text-slate-600">· {{ now($weatherWidget['location']->timezone)->format('l, M j') }}</span>
                        @endif
                    </h2>
                </div>

                @if($weatherWidget['location'])
                    <span data-weather-status data-state="{{ $weatherWidget['state'] }}"
                        data-message="{{ $weatherWidget['message'] }}"
                        data-status-url="{{ $weatherWidget['city_query'] ? route('weather.status', ['city' => $weatherWidget['city_query']]) : route('weather.status', ['location' => $weatherWidget['location']->id]) }}"
                        data-sync-url="{{ route('weather.sync') }}"
                        data-location-id="{{ $weatherWidget['location']->id ?? '' }}"
                        data-city="{{ $weatherWidget['city_query'] ?? '' }}"
                        data-refresh-interval="{{ $weatherWidget['refresh_interval'] }}"
                        data-has-reading="{{ $weatherWidget['current'] ? 'true' : 'false' }}"
                        class="weather-connectivity-badge weather-connectivity-badge--{{ $weatherWidget['state'] }}"
                        role="status" aria-live="polite">
                        <span class="weather-connectivity-dot" aria-hidden="true"></span>
                        <span data-weather-status-label>
                            {{ $weatherWidget['state'] === 'online' ? 'Online · Synced' : ($weatherWidget['state'] === 'stale' ? 'Offline · Saved forecast' : 'Weather unavailable') }}
                        </span>
                    </span>
                @endif
            </div>

            @if($weatherWidget['allow_search'])
                <form method="POST" action="{{ route('weather.search') }}" class="grid gap-2 sm:grid-cols-[1fr_auto]">
                    @csrf
                    <label class="sr-only" for="weather-city-search">Search city or location</label>
                    <input id="weather-city-search" name="city" type="search" value="{{ old('city', $weatherWidget['city_query'] ?? '') }}" maxlength="100" required placeholder="Search city, e.g. Manila or Davao City" class="min-h-12 w-full rounded-xl border border-white/80 bg-white/75 px-4 text-sm text-slate-900 shadow-sm placeholder:text-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-indigo-700 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">
                        <span aria-hidden="true">⌕</span> Search weather
                    </button>
                    @error('city')
                        <p class="text-sm font-medium text-rose-800 sm:col-span-2" role="alert">{{ $message }}</p>
                    @enderror
                </form>
            @endif

            @if($weatherWidget['search_error'])
                <div class="rounded-xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-900" role="alert">
                    <span class="font-semibold">Weather search:</span> {{ $weatherWidget['search_error'] }}
                </div>
            @endif

            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm font-medium text-emerald-900" role="status">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-900" role="alert">{{ session('error') }}</div>
            @endif

            @if($weatherWidget['current'])
                <div class="flex flex-wrap items-center gap-x-5 gap-y-3">
                    <div class="flex items-center gap-3">
                        @if($weatherWidget['current']['provider_icon_url'])
                            <img src="{{ $weatherWidget['current']['provider_icon_url'] }}" alt="{{ $weatherWidget['current']['condition'] }}" width="96" height="96" class="h-20 w-20 object-contain drop-shadow-sm sm:h-24 sm:w-24">
                        @else
                            <span class="text-6xl leading-none drop-shadow-sm sm:text-7xl" aria-hidden="true">{{ $weatherWidget['current']['icon'] }}</span>
                        @endif
                        <div>
                            <p class="text-5xl font-semibold leading-none tracking-[-0.06em] text-slate-950 sm:text-6xl">
                                {{ $weatherWidget['current']['temperature_display'] }}<span class="ml-1 align-top text-2xl font-medium tracking-normal">{{ $weatherWidget['current']['temperature_unit'] }}</span>
                            </p>
                            <p class="mt-2 text-sm font-semibold text-slate-700">{{ $weatherWidget['current']['condition'] }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 border-l border-slate-300/70 pl-4 text-sm text-slate-600">
                        @if($weatherWidget['today'] || $weatherWidget['current']['maximum_display'] !== null)
                            <p>H: <span class="font-semibold text-slate-800">{{ $weatherWidget['today']['maximum'] ?? $weatherWidget['current']['maximum_display'] }}{{ $weatherWidget['today']['temperature_unit'] ?? $weatherWidget['current']['temperature_unit'] }}</span></p>
                            <p>L: <span class="font-semibold text-slate-800">{{ $weatherWidget['today']['minimum'] ?? $weatherWidget['current']['minimum_display'] }}{{ $weatherWidget['today']['temperature_unit'] ?? $weatherWidget['current']['temperature_unit'] }}</span></p>
                        @endif
                        <p>Feels like <span class="font-semibold text-slate-800">{{ $weatherWidget['current']['feels_like_display'] }}{{ $weatherWidget['current']['temperature_unit'] }}</span></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4" aria-label="Today's weather details">
                    <div class="weather-dashboard-detail">
                        <span aria-hidden="true">💧</span>
                        <span><span class="block text-[11px] font-medium text-slate-500">Humidity</span><span class="text-sm font-semibold text-slate-800">{{ $weatherWidget['current']['humidity'] !== null ? $weatherWidget['current']['humidity'].'%' : '—' }}</span></span>
                    </div>
                    <div class="weather-dashboard-detail">
                        <span aria-hidden="true">🌬</span>
                        <span><span class="block text-[11px] font-medium text-slate-500">Wind</span><span class="text-sm font-semibold text-slate-800">{{ $weatherWidget['current']['wind_display'] !== null ? $weatherWidget['current']['wind_display'].' '.$weatherWidget['current']['wind_unit'] : '—' }}</span></span>
                    </div>
                    <div class="weather-dashboard-detail">
                        <span aria-hidden="true">☔</span>
                        <span><span class="block text-[11px] font-medium text-slate-500">Rain chance</span><span class="text-sm font-semibold text-slate-800">{{ $weatherWidget['current']['rain_probability'] !== null ? $weatherWidget['current']['rain_probability'].'%' : '—' }}</span></span>
                    </div>
                    <div class="weather-dashboard-detail">
                        <span aria-hidden="true">🧭</span>
                        <span><span class="block text-[11px] font-medium text-slate-500">Pressure</span><span class="text-sm font-semibold text-slate-800">{{ $weatherWidget['current']['pressure'] !== null ? $weatherWidget['current']['pressure'].' hPa' : '—' }}</span></span>
                    </div>
                </div>

                @if($weatherWidget['hourly'] !== [])
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <h3 class="text-xs font-bold uppercase tracking-[0.16em] text-slate-600">Next hours</h3>
                            @if($weatherWidget['last_synced_at'])
                                <p class="text-[11px] text-slate-500">
                                    Updated {{ $weatherWidget['last_synced_at']->timezone($weatherWidget['location']->timezone)->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-6" aria-label="Upcoming hourly forecast">
                            @foreach($weatherWidget['hourly'] as $hour)
                                <div class="weather-dashboard-hour">
                                    <p class="text-[11px] font-medium text-slate-500">{{ $loop->first ? 'Now' : $hour['time'] }}</p>
                                    @if($hour['provider_icon_url'])
                                        <img src="{{ $hour['provider_icon_url'] }}" alt="{{ $hour['condition'] }}" width="48" height="48" class="mx-auto h-10 w-10 object-contain">
                                    @else
                                        <p class="mt-1 text-xl" aria-hidden="true">{{ $hour['icon'] }}</p>
                                    @endif
                                    <p class="mt-1 text-sm font-semibold tabular-nums text-slate-900">{{ $hour['temperature'] }}{{ $hour['temperature_unit'] }}</p>
                                    @if($hour['precipitation_probability'] !== null)
                                        <p class="mt-1 text-[10px] text-sky-700">☔ {{ $hour['precipitation_probability'] }}%</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="rounded-2xl border border-slate-300/70 bg-white/50 p-5">
                    <p class="text-base font-semibold text-slate-900">Weather reading unavailable</p>
                    <p class="mt-1 max-w-xl text-sm leading-6 text-slate-600">{{ $weatherWidget['message'] }} A forecast will appear here after the first successful sync.</p>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-end gap-2 lg:min-w-40 lg:flex-col lg:items-stretch lg:justify-end">
            @if($weatherWidget['active_alerts'] > 0)
                <a href="{{ route('weather.alerts') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-amber-300/80 bg-amber-50/80 px-3 text-sm font-semibold text-amber-900 transition hover:bg-amber-100">
                    ⚠ {{ $weatherWidget['active_alerts'] }} active {{ \Illuminate\Support\Str::plural('alert', $weatherWidget['active_alerts']) }}
                </a>
            @endif
            <a href="{{ route('weather.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                Full forecast <span aria-hidden="true">↗</span>
            </a>
            @if($weatherWidget['location'])
                <form method="POST" action="{{ route('weather.sync') }}" class="lg:w-full">
                    @csrf
                    @if($weatherWidget['city_query'])
                        <input type="hidden" name="city" value="{{ $weatherWidget['city_query'] }}">
                    @else
                        <input type="hidden" name="location_id" value="{{ $weatherWidget['location']->id }}">
                    @endif
                    <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-slate-300/80 bg-white/50 px-4 text-sm font-semibold text-slate-700 transition hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                        ↻ Refresh live weather
                    </button>
                </form>
            @endif
            <div class="rounded-xl border border-white/60 bg-white/45 p-3 text-xs text-slate-600 backdrop-blur">
                <p class="font-bold uppercase tracking-[0.14em] text-slate-700">API connection</p>
                <p class="mt-1">OpenWeather · {{ $weatherWidget['state'] === 'online' ? 'Operational' : 'Unavailable or using saved data' }}</p>
                @if($weatherWidget['http_status'] !== null)
                    <p class="mt-1">HTTP {{ $weatherWidget['http_status'] }}
                        @if($weatherWidget['response_time_ms'] !== null)
                            · {{ $weatherWidget['response_time_ms'] }} ms
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    @if($weatherWidget['location'])
        <p class="relative z-10 border-t border-slate-300/50 px-5 py-3 text-xs text-slate-500 sm:px-7">
            Weather {{ $weatherWidget['state'] === 'stale' ? 'showing the last saved forecast' : 'provided by OpenWeather' }}
            @if($weatherWidget['last_synced_at'])
                <span aria-hidden="true">·</span>
                Last successful sync {{ $weatherWidget['last_synced_at']->timezone($weatherWidget['location']->timezone)->format('M j, g:i A') }}
            @endif
        </p>
    @endif
</section>
@if($weatherWidget['location'])
    @include('weather.partials.status', [
        'location' => $weatherWidget['location'],
        'state' => $weatherWidget['state'],
        'stateMessage' => $weatherWidget['message'],
        'lastSyncedAt' => $weatherWidget['last_synced_at'],
        'refreshInterval' => $weatherWidget['refresh_interval'],
        'compact' => true,
    ])
@endif
