<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Administrator · Weather locations</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-950">{{ $location->name }}</h1>
            </div>
            <a href="{{ route('weather-locations.edit', $location) }}" class="inline-flex min-h-10 items-center rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">Edit location</a>
        </div>
    </x-slot>

    <div class="bg-slate-50 py-6 sm:py-8">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 sm:p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Region</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $location->region ? $location->region.', ' : '' }}{{ $location->country }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Coordinates</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $location->latitude }}, {{ $location->longitude }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Time zone</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $location->timezone }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $location->is_default ? 'Default · ' : '' }}{{ $location->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                @if($snapshot)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Last stored reading</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $snapshot->current['temperature_2m'] }}°C · Weather code {{ $snapshot->current['weather_code'] }} · {{ $snapshot->fetched_at->timezone($location->timezone)->format('M j, Y · g:i A') }}</p>
                    </div>
                @endif
            </section>
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4 sm:px-6"><h2 class="text-base font-semibold text-slate-950">Recent synchronization attempts</h2></div>
                <div class="divide-y divide-slate-100">
                    @forelse($syncLogs as $log)
                        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 text-sm sm:px-6">
                            <span class="{{ $log->status === 'success' ? 'text-emerald-700' : 'text-rose-700' }} font-semibold">{{ ucfirst($log->status) }}</span>
                            <span class="text-slate-600">{{ $log->message }}</span>
                            <time class="text-slate-500">{{ $log->synced_at->timezone($location->timezone)->format('M j, Y · g:i A') }}</time>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">No synchronization attempts recorded.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
