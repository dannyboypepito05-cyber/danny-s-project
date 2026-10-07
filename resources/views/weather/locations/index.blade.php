<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Administrator</p>
                <h1 class="mt-1 text-xl font-semibold text-slate-950">Weather locations</h1>
            </div>
            <a href="{{ route('weather-locations.create') }}" class="inline-flex min-h-10 items-center rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">Add location</a>
        </div>
    </x-slot>

    <div class="bg-slate-50 py-6 sm:py-8">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">{{ session('error') }}</div>
            @endif
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach(['Location', 'Coordinates', 'Timezone', 'Status', 'Records', 'Actions'] as $heading)
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($locations as $location)
                                <tr>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('weather-locations.show', $location) }}" class="font-semibold text-slate-900 hover:text-indigo-700">{{ $location->name }}</a>
                                        <p class="mt-1 text-xs text-slate-500">{{ $location->region ? $location->region.', ' : '' }}{{ $location->country }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ number_format($location->latitude, 5) }}, {{ number_format($location->longitude, 5) }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $location->timezone }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @if($location->is_default)<span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">Default</span>@endif
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $location->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $location->is_active ? 'Active' : 'Inactive' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600">{{ $location->snapshots_count }} readings · {{ $location->alerts_count }} alerts</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3 text-sm">
                                            <a href="{{ route('weather-locations.edit', $location) }}" class="font-medium text-indigo-700 hover:text-indigo-900">Edit</a>
                                            <form method="POST" action="{{ route('weather-locations.destroy', $location) }}" onsubmit="return confirm('Delete this location? Locations with saved weather data cannot be deleted.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-rose-700 hover:text-rose-900">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No weather locations configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
