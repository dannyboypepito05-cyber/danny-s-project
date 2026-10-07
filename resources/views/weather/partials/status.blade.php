@php
    $statusClasses = match ($state) {
        'online' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'stale' => 'border-amber-200 bg-amber-50 text-amber-800',
        default => 'border-rose-200 bg-rose-50 text-rose-800',
    };
    $statusLabel = match ($state) {
        'online' => 'Online · synchronized',
        'stale' => 'Offline · showing cached data',
        default => 'Weather unavailable',
    };
@endphp

@unless($compact ?? false)
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex min-w-0 items-center gap-3">
            <span data-weather-status data-state="{{ $state }}" data-message="{{ $stateMessage }}" data-status-url="{{ route('weather.status', ['location' => $location->id]) }}" data-sync-url="{{ route('weather.sync') }}" data-location-id="{{ $location->id }}" data-refresh-interval="{{ $refreshInterval ?? 15 }}" data-has-reading="{{ $lastSyncedAt ? 'true' : 'false' }}"
                class="inline-flex shrink-0 items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}"
                role="status" aria-live="polite">{{ $statusLabel }}</span>
            <p class="truncate text-sm text-slate-600" data-weather-message>{{ $stateMessage }}</p>
        </div>
        <p class="text-xs text-slate-500">
            @if($lastSyncedAt)
                Last synchronized <time datetime="{{ $lastSyncedAt->toIso8601String() }}">{{ $lastSyncedAt->timezone($location->timezone)->format('M j, Y · g:i A') }}</time>
            @else
                No cached reading available
            @endif
        </p>
    </div>
@endunless

@pushOnce('scripts')
    <script>
        (() => {
            const status = document.querySelector('[data-weather-status]');
            if (!status) return;

            const message = document.querySelector('[data-weather-message]');
            const label = status.querySelector('[data-weather-status-label]');
            const classes = {
                online: ['border-emerald-200', 'bg-emerald-50', 'text-emerald-800'],
                stale: ['border-amber-200', 'bg-amber-50', 'text-amber-800'],
                error: ['border-rose-200', 'bg-rose-50', 'text-rose-800'],
                offline: ['border-rose-200', 'bg-rose-50', 'text-rose-800'],
                syncing: ['border-blue-200', 'bg-blue-50', 'text-blue-800'],
            };

            const updateState = (state, text) => {
                Object.values(classes).flat().forEach(className => status.classList.remove(className));
                status.classList.add(...(classes[state] || classes.error));
                ['online', 'stale', 'offline', 'error', 'syncing'].forEach(stateName => {
                    status.classList.remove(`weather-connectivity-badge--${stateName}`);
                });
                if (status.classList.contains('weather-connectivity-badge')) {
                    status.classList.add(`weather-connectivity-badge--${state}`);
                }
                status.dataset.state = state;
                const statusText = {
                    online: label ? 'Online · Synced' : 'Online · synchronized',
                    stale: 'Offline · showing cached data',
                    error: 'Weather unavailable',
                    offline: status.dataset.hasReading === 'true' ? 'Offline · Saved forecast' : 'Offline · No saved data',
                    syncing: 'Synchronizing weather…',
                }[state] || 'Weather unavailable';
                if (label) {
                    label.textContent = statusText;
                } else {
                    status.textContent = statusText;
                }
                if (message) message.textContent = text;
            };

            const syncWhenOnline = async () => {
                if (!navigator.onLine) {
                    updateState('offline', 'No network connection. Any displayed reading is cached.');
                    return;
                }

                updateState('syncing', 'Checking the weather provider for a fresh reading.');

                try {
                    const body = new FormData();
                    if (status.dataset.city) {
                        body.append('city', status.dataset.city);
                    } else {
                        body.append('location_id', status.dataset.locationId);
                    }
                    const response = await fetch(status.dataset.syncUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body,
                    });

                    const result = await response.json();
                    if (response.ok && result.success) {
                        window.location.reload();
                        return;
                    }

                    updateState(result.state || 'error', result.message || 'Weather synchronization failed.');
                } catch {
                    updateState('offline', 'The weather service could not be reached. Cached data is preserved.');
                }
            };

            window.addEventListener('offline', () => updateState('offline', 'No network connection. Any displayed reading is cached.'));
            window.addEventListener('online', syncWhenOnline);

            const refreshMinutes = Math.max(5, Number(status.dataset.refreshInterval) || 15);
            window.setInterval(async () => {
                if (!navigator.onLine) {
                    updateState('offline', 'No network connection. Any displayed reading is cached.');
                    return;
                }

                try {
                    const response = await fetch(status.dataset.statusUrl, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const result = await response.json();
                    updateState(result.state || 'error', result.message || 'Weather synchronization failed.');
                } catch {
                    updateState('offline', 'Connection lost. Any displayed reading is cached.');
                }
            }, refreshMinutes * 60 * 1000);

            if (!navigator.onLine) {
                updateState('offline', 'No network connection. Any displayed reading is cached.');
            }
        })();
    </script>
@endPushOnce
