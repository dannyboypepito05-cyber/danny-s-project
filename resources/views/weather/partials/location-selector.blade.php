<form method="GET" action="{{ route($routeName) }}" class="flex flex-wrap items-end gap-3">
    <label class="grid gap-1 text-sm font-medium text-slate-700">
        Weather location
        <select name="location" onchange="this.form.submit()" class="min-h-10 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($locations as $weatherLocation)
                <option value="{{ $weatherLocation->id }}" @selected($weatherLocation->id === $location->id)>
                    {{ $weatherLocation->name }}{{ $weatherLocation->region ? ', '.$weatherLocation->region : '' }}
                </option>
            @endforeach
        </select>
    </label>
</form>
