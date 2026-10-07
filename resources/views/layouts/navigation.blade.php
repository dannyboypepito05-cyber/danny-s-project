@php
    $user = Auth::user();
    $navigationItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboards.*'],
    ];

    $navigationItems = array_merge($navigationItems, [
        ['label' => 'Weather', 'route' => 'weather.index', 'active' => 'weather.index'],
        ['label' => 'Weather history', 'route' => 'weather.history', 'active' => 'weather.history'],
        ['label' => 'Weather alerts', 'route' => 'weather.alerts', 'active' => 'weather.alerts'],
    ]);

    if ($user->role === \App\Models\User::ROLE_ADMINISTRATOR) {
        $navigationItems = array_merge($navigationItems, [
            ['label' => 'Weather settings', 'route' => 'weather.settings', 'active' => 'weather.settings*'],
            ['label' => 'Weather locations', 'route' => 'weather-locations.index', 'active' => 'weather-locations.*'],
            ['label' => 'Departments', 'route' => 'departments.index', 'active' => 'departments.*'],
            ['label' => 'Programs', 'route' => 'programs.index', 'active' => 'programs.*'],
            ['label' => 'Courses', 'route' => 'courses.index', 'active' => 'courses.*'],
            ['label' => 'Sections', 'route' => 'sections.index', 'active' => 'sections.*'],
            ['label' => 'Classrooms', 'route' => 'classrooms.index', 'active' => 'classrooms.*'],
            ['label' => 'Instructors', 'route' => 'instructors.index', 'active' => 'instructors.*'],
            ['label' => 'Users', 'route' => 'users.index', 'active' => 'users.*'],
        ]);
    }
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-800 bg-slate-950">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex min-h-16 items-center justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500 text-sm font-bold text-white">D</span>
                <span class="leading-tight">
                    <span class="block text-sm font-semibold tracking-[0.18em] text-white">DUCQUE</span>
                    <span class="hidden text-xs text-slate-400 sm:block">Academic workspace</span>
                </span>
            </a>

            <div class="hidden min-w-0 flex-1 items-center justify-center gap-1 overflow-x-auto px-3 md:flex">
                @foreach($navigationItems as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'shrink-0 rounded-lg px-3 py-2 text-sm font-medium transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400',
                           'bg-slate-800 text-white' => request()->routeIs($item['active']),
                           'text-slate-300 hover:bg-slate-900 hover:text-white' => ! request()->routeIs($item['active']),
                       ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="hidden shrink-0 items-center gap-3 md:flex">
                <button type="button" data-theme-toggle aria-label="Toggle color theme" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-700 text-slate-300 transition hover:bg-slate-800 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400">
                    <span aria-hidden="true">◐</span>
                </button>
                <span class="hidden text-right lg:block">
                    <span class="block max-w-40 truncate text-sm font-medium text-white">{{ $user->name }}</span>
                    <span class="block text-xs text-slate-400">{{ $user->role }}</span>
                </span>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" aria-label="Open account menu" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-800 text-sm font-semibold text-indigo-200 transition hover:bg-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-controls="mobile-navigation"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-300 transition hover:bg-slate-800 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400 md:hidden">
                <span class="sr-only">Toggle navigation</span>
                <svg x-show="!open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="open" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <div id="mobile-navigation" x-show="open" x-cloak x-transition class="border-t border-slate-800 py-3 md:hidden">
            <div class="grid gap-1">
                @foreach($navigationItems as $item)
                    <a href="{{ route($item['route']) }}"
                       @class([
                           'rounded-lg px-3 py-2.5 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400',
                           'bg-slate-800 text-white' => request()->routeIs($item['active']),
                           'text-slate-300 hover:bg-slate-900 hover:text-white' => ! request()->routeIs($item['active']),
                       ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-slate-800 px-3 pt-3">
                <span>
                    <span class="block text-sm font-medium text-white">{{ $user->name }}</span>
                    <span class="block text-xs text-slate-400">{{ $user->role }}</span>
                </span>
                <div class="flex items-center gap-3 text-sm">
                    <button type="button" data-theme-toggle aria-label="Toggle color theme" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 text-slate-300">◐</button>
                    <a href="{{ route('profile.edit') }}" class="text-slate-300 hover:text-white">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-300 hover:text-white">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
