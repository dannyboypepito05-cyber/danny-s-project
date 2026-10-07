<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Weather intelligence</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">History & analytics</h1>
            </div>
            @include('weather.partials.location-selector', ['routeName' => 'weather.history'])
        </div>
    </x-slot>

    <div class="weather-workspace py-7 sm:py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:px-8">
            @include('weather.partials.status')

            <section class="weather-history-hero relative isolate overflow-hidden rounded-3xl p-6 text-white shadow-xl shadow-indigo-950/10 sm:p-9">
                <div class="weather-history-orb" aria-hidden="true"></div>
                <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-medium text-indigo-100">Local climate records</p>
                        <h2 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">Understand the patterns.</h2>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-indigo-100 sm:text-base">
                            Explore verified observations for {{ $location->name }}. Charts use synchronized readings only—no estimated history.
                        </p>
                        <p class="mt-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium text-indigo-50">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300" aria-hidden="true"></span>
                            {{ $location->timezone }} <span aria-hidden="true">·</span> {{ $samples->count() }} observations in this range
                        </p>
                    </div>
                    <a href="{{ route('weather.index', ['location' => $location->id]) }}" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 text-sm font-semibold text-white transition hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                        <span aria-hidden="true">←</span> Current conditions
                    </a>
                </div>
            </section>

            <section class="grid gap-3 sm:grid-cols-3" aria-label="Selected metric summary">
                <article class="weather-panel rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Selected measure</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $metricLabel }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $location->name }}</p>
                </article>
                <article class="weather-panel rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Period minimum</p>
                    <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $samples->isNotEmpty() ? $minimum : '—' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Across recorded observations</p>
                </article>
                <article class="weather-panel rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Period maximum</p>
                    <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $samples->isNotEmpty() ? $maximum : '—' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Across recorded observations</p>
                </article>
            </section>

            <section class="weather-panel overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex flex-col gap-5 border-b border-slate-100 p-5 sm:p-7 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-indigo-600">Observation trend</p>
                        <h2 class="mt-1 text-xl font-semibold tracking-tight text-slate-950">{{ $metricLabel }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Synced measurements for {{ $location->name }}</p>
                    </div>
                    <form method="GET" action="{{ route('weather.history') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                        <input type="hidden" name="location" value="{{ $location->id }}">
                        <label class="grid gap-1.5 text-xs font-semibold text-slate-600">
                            Time range
                            <select name="range" class="min-h-11 min-w-40 rounded-xl border-slate-300 bg-white text-sm font-medium text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(['24h' => 'Last 24 hours', '7d' => 'Last 7 days', '30d' => 'Last 30 days'] as $value => $label)
                                    <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-1.5 text-xs font-semibold text-slate-600">
                            Measure
                            <select name="metric" class="min-h-11 min-w-40 rounded-xl border-slate-300 bg-white text-sm font-medium text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(['temperature' => 'Temperature', 'humidity' => 'Humidity', 'rain' => 'Rainfall', 'wind' => 'Wind speed', 'pressure' => 'Pressure'] as $value => $label)
                                    <option value="{{ $value }}" @selected($metric === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Apply filters</button>
                    </form>
                </div>

                <div class="p-5 sm:p-7">
                    @if($samples->isNotEmpty())
                        <div class="overflow-x-auto rounded-2xl bg-slate-50/80 px-3 py-5 sm:px-5" role="img" aria-label="{{ $metricLabel }} chart for the selected period, ranging from {{ $minimum }} to {{ $maximum }}">
                            <svg viewBox="0 0 960 220" class="h-56 min-w-[640px] w-full" preserveAspectRatio="none" aria-hidden="true">
                                <defs>
                                    <linearGradient id="weather-history-line" x1="0" x2="1" y1="0" y2="0">
                                        <stop offset="0%" stop-color="#818cf8" />
                                        <stop offset="100%" stop-color="#4f46e5" />
                                    </linearGradient>
                                    <linearGradient id="weather-history-fill" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#6366f1" stop-opacity=".16" />
                                        <stop offset="100%" stop-color="#6366f1" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                                <path d="M10 20H950M10 100H950M10 180H950" fill="none" stroke="#e2e8f0" stroke-dasharray="4 7" stroke-width="1" />
                                @if($samples->count() > 1)
                                    <polygon points="0,200 {{ $points }} 940,200" fill="url(#weather-history-fill)" />
                                    <polyline points="{{ $points }}" fill="none" stroke="url(#weather-history-line)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                                @else
                                    <circle cx="470" cy="100" r="8" fill="#4f46e5" />
                                    <circle cx="470" cy="100" r="16" fill="#4f46e5" fill-opacity=".12" />
                                @endif
                            </svg>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs font-medium text-slate-500">
                            <span>{{ $samples->first()['label'] }}</span>
                            <span>{{ $samples->count() }} synchronized {{ \Illuminate\Support\Str::plural('observation', $samples->count()) }} <span class="px-1 text-slate-300">·</span> {{ $minimum }} – {{ $maximum }}</span>
                            <span>{{ $samples->last()['label'] }}</span>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-12 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-xl text-indigo-600" aria-hidden="true">↗</span>
                            <p class="mt-4 text-sm font-semibold text-slate-900">No observations in this range yet</p>
                            <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">History starts recording when weather synchronizes. Choose a longer range or return after the next update.</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="weather-panel overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-7">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Recorded observations</h2>
                        <p class="mt-1 text-sm text-slate-500">Local timestamps · {{ $location->timezone }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $samples->count() }} total</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-7">Local time</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 sm:px-7">{{ $metricLabel }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($samples->reverse() as $sample)
                                <tr class="transition hover:bg-indigo-50/40">
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-700 sm:px-7">{{ $sample['label'] }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-right text-sm font-semibold tabular-nums text-slate-900 sm:px-7">{{ $sample['value'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-5 py-8 text-center text-sm text-slate-500">No synchronized records yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
