<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Overview</p>
                <h2 class="mt-1 text-xl font-semibold leading-tight text-slate-900">Administrator dashboard</h2>
            </div>
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Administrator</span>
        </div>
    </x-slot>

    <div class="bg-slate-50 py-8 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-sm font-semibold text-indigo-700">DUCQUE · Academic operations</p>
                <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Welcome Administrator!</h3>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">A live overview of users, classrooms, and instructor quality across your academic workspace.</p>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
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

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h4 class="text-base font-semibold text-slate-900">Quick access</h4>
                        <p class="mt-1 text-sm text-slate-500">Go directly to a management workspace.</p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 sm:mt-0">
                        <a href="{{ route('users.index') }}" class="inline-flex min-h-10 items-center rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Manage users</a>
                        <a href="{{ route('instructors.index') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Instructors</a>
                        <a href="{{ route('classrooms.index') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Classrooms</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
