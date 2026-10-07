<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.16),_transparent_35%),linear-gradient(135deg,_#0f172a_0%,_#111827_50%,_#1e293b_100%)] px-4 py-10 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-5xl">
                <div class="mb-8 flex items-center justify-center">
                    <a href="/" class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-100 backdrop-blur-sm shadow-lg shadow-slate-900/30">
                        <x-application-logo class="h-8 w-8 fill-current text-indigo-300" />
                        <span class="tracking-[0.2em] text-xs uppercase">DUCQUE</span>
                    </a>
                </div>

                <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/8 shadow-2xl shadow-slate-950/40 backdrop-blur-xl sm:mx-auto sm:max-w-lg">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
