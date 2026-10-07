<?php

namespace App\Http\Controllers;

use App\Models\WeatherLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WeatherLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('weather.locations.index', [
            'locations' => WeatherLocation::withCount(['snapshots', 'alerts'])
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('weather.locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($validated): void {
            if ($validated['is_default']) {
                WeatherLocation::query()->update(['is_default' => false]);
            }

            WeatherLocation::create($validated);
        });

        return redirect()->route('weather-locations.index')->with('success', 'Weather location created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WeatherLocation $weatherLocation): View
    {
        return view('weather.locations.show', [
            'location' => $weatherLocation,
            'snapshot' => $weatherLocation->snapshots()->latest('fetched_at')->first(),
            'syncLogs' => $weatherLocation->syncLogs()->latest('synced_at')->limit(10)->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WeatherLocation $weatherLocation): View
    {
        return view('weather.locations.edit', ['location' => $weatherLocation]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WeatherLocation $weatherLocation): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($weatherLocation, $validated): void {
            if ($validated['is_default']) {
                WeatherLocation::where('id', '!=', $weatherLocation->id)->update(['is_default' => false]);
            } elseif ($weatherLocation->is_default) {
                $validated['is_default'] = true;
            }

            $weatherLocation->update($validated);
        });

        return redirect()->route('weather-locations.index')->with('success', 'Weather location updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WeatherLocation $weatherLocation): RedirectResponse
    {
        if ($weatherLocation->is_default) {
            return back()->with('error', 'The default location cannot be deleted.');
        }

        if ($weatherLocation->snapshots()->exists() || $weatherLocation->alerts()->exists()) {
            return back()->with('error', 'This location has weather history. Deactivate it instead of deleting it.');
        }

        $weatherLocation->delete();

        return redirect()->route('weather-locations.index')->with('success', 'Weather location deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'timezone' => ['required', 'timezone'],
            'is_active' => ['required', 'boolean'],
            'is_default' => ['required', 'boolean'],
        ]);

        if ($validated['is_default'] && ! $validated['is_active']) {
            throw ValidationException::withMessages([
                'is_active' => 'The default weather location must remain active.',
            ]);
        }

        return $validated;
    }
}
