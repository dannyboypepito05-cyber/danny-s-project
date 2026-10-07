<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Overview</p>
                <h2 class="mt-1 text-xl font-semibold leading-tight text-slate-900">Student dashboard</h2>
            </div>
            <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">Learning</span>
        </div>
    </x-slot>

    <div class="bg-slate-50 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-semibold text-cyan-700">DUCQUE · Academic catalog</p>
                <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Welcome Student!</h3>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Explore an institution-wide snapshot of active academic offerings. Enrollment, attendance, and grades are not yet tracked by this system.</p>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    @foreach($metrics as $metric)
                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4 transition duration-150 hover:border-slate-300 hover:bg-white">
                            <p class="text-sm font-medium text-slate-600">{{ $metric['label'] }}</p>
                            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $metric['value'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $metric['detail'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
            @include('weather.partials.widget')
        </div>
    </div>
</x-app-layout>
