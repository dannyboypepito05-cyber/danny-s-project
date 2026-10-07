<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-slate-500">Administrator · Weather locations</p>
            <h1 class="mt-1 text-xl font-semibold text-slate-950">{{ isset($location) ? 'Edit location' : 'Add weather location' }}</h1>
        </div>
    </x-slot>

    <div class="bg-slate-50 py-6 sm:py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ isset($location) ? route('weather-locations.update', $location) : route('weather-locations.store') }}" class="grid gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                @csrf
                @if(isset($location))
                    @method('PUT')
                @endif
                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                        <ul class="list-disc space-y-1 ps-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Location name
                        <input name="name" value="{{ old('name', $location->name ?? '') }}" required maxlength="120" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Region / province
                        <input name="region" value="{{ old('region', $location->region ?? '') }}" maxlength="120" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Country
                        <input name="country" value="{{ old('country', $location->country ?? 'Philippines') }}" required maxlength="120" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        IANA time zone
                        <input name="timezone" value="{{ old('timezone', $location->timezone ?? 'Asia/Manila') }}" required placeholder="Asia/Manila" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Latitude
                        <input type="number" name="latitude" value="{{ old('latitude', $location->latitude ?? '') }}" required min="-90" max="90" step="0.0000001" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="grid gap-1.5 text-sm font-medium text-slate-700">
                        Longitude
                        <input type="number" name="longitude" value="{{ old('longitude', $location->longitude ?? '') }}" required min="-180" max="180" step="0.0000001" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                </div>
                <div class="grid gap-3 rounded-xl bg-slate-50 p-4 sm:grid-cols-2">
                    <label class="flex items-center gap-3 text-sm text-slate-700">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $location->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Active in the location selector
                    </label>
                    <label class="flex items-center gap-3 text-sm text-slate-700">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" @checked((bool) old('is_default', $location->is_default ?? false)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Use as the default dashboard location
                    </label>
                </div>
                <p class="text-xs leading-5 text-slate-500">Coordinates determine the forecast area. Changing a location does not rewrite its previously stored weather history.</p>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex min-h-10 items-center rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">{{ isset($location) ? 'Save changes' : 'Create location' }}</button>
                    <a href="{{ route('weather-locations.index') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
