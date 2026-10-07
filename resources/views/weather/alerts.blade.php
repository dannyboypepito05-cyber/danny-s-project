<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Weather intelligence</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-950">Weather alerts</h1>
            </div>
            @include('weather.partials.location-selector', ['routeName' => 'weather.alerts'])
        </div>
    </x-slot>

    <div class="weather-workspace py-7 sm:py-10">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:px-8">
            @include('weather.partials.status')

            <section class="weather-panel rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Observed conditions · {{ $location->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">Alerts are calculated from the latest synchronized weather reading and administrator thresholds.</p>
                </div>

                <div class="mt-5 grid gap-3">
                    @forelse($alerts as $alert)
                        @php
                            $severityClasses = match ($alert->severity) {
                                'critical' => 'border-rose-300 bg-rose-50 text-rose-950',
                                'warning' => 'border-amber-300 bg-amber-50 text-amber-950',
                                'advisory' => 'border-orange-200 bg-orange-50 text-orange-950',
                                default => 'border-sky-200 bg-sky-50 text-sky-950',
                            };
                        @endphp
                        <article class="rounded-xl border p-4 {{ $severityClasses }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold">{{ $alert->title }}</h3>
                                <span class="rounded-full bg-white/70 px-2.5 py-1 text-xs font-semibold">{{ ucfirst($alert->severity) }} · {{ ucfirst($alert->status) }}</span>
                            </div>
                            <p class="mt-2 text-sm">{{ $alert->message }}</p>
                            <p class="mt-2 text-xs opacity-75">Started {{ $alert->started_at->timezone($location->timezone)->format('M j, Y · g:i A') }}
                                @if($alert->resolved_at)
                                    · Resolved {{ $alert->resolved_at->timezone($location->timezone)->format('M j, Y · g:i A') }}
                                @endif
                            </p>
                        </article>
                    @empty
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-8 text-center">
                            <p class="text-sm font-semibold text-emerald-900">No active or recent alerts</p>
                            <p class="mt-1 text-sm text-emerald-800">Alerts will appear when synchronized conditions meet configured thresholds.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-5">{{ $alerts->withQueryString()->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
