<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-slate-950 text-white antialiased">
        <div class="min-h-screen">
            <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 sm:px-8 lg:px-12">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/15 ring-1 ring-indigo-400/30">
                        <span class="text-lg font-black text-indigo-200">D</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-indigo-200">DUCQUE</p>
                    </div>
                </div>

                <nav class="hidden items-center gap-6 text-sm text-slate-300 md:flex">
                    <a href="#features" class="transition hover:text-white">Features</a>
                    <a href="#platform" class="transition hover:text-white">Platform</a>
                    <a href="#security" class="transition hover:text-white">Security</a>
                </nav>

                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="rounded-full border border-slate-700 bg-slate-900/60 px-4 py-2 text-sm font-medium text-white transition hover:border-slate-500">Login</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">Create account</a>
                </div>
            </header>

            <main>
                <section class="mx-auto max-w-7xl px-6 pb-10 pt-8 sm:px-8 lg:px-12 lg:pb-16">
                    <div class="grid items-center gap-10 lg:grid-cols-[1.2fr_0.8fr]">
                        <div>
                            <span class="inline-flex rounded-full border border-indigo-400/40 bg-indigo-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-indigo-200">Premium academic platform</span>
                            <h1 class="mt-6 text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">The secure command center for modern classrooms.</h1>
                            <p class="mt-6 max-w-2xl text-lg text-slate-300">
                                DUCQUE centralizes classroom operations, instructor quality, student engagement, and institutional intelligence in one secure, role-based system.
                            </p>
                            <div class="mt-8 flex flex-wrap gap-4">
                                <a href="{{ route('login') }}" class="rounded-full bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400">Access portal</a>
                                <a href="{{ route('register') }}" class="rounded-full border border-slate-700 bg-slate-900/60 px-6 py-3 text-sm font-semibold text-white transition hover:border-slate-500">Create account</a>
                            </div>
                            <div class="mt-8 flex flex-wrap gap-6 text-sm text-slate-300">
                                <span>Role-based access</span>
                                <span>Instructor analytics</span>
                                <span>Classroom insights</span>
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-slate-800 bg-slate-900/80 p-6 shadow-2xl shadow-indigo-950/40 backdrop-blur-sm">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl bg-indigo-500/12 p-4 ring-1 ring-indigo-400/20">
                                    <p class="text-sm text-indigo-200">Active classrooms</p>
                                    <p class="mt-3 text-3xl font-bold text-white">128</p>
                                </div>
                                <div class="rounded-2xl bg-emerald-500/12 p-4 ring-1 ring-emerald-400/20">
                                    <p class="text-sm text-emerald-200">Faculty</p>
                                    <p class="mt-3 text-3xl font-bold text-white">42</p>
                                </div>
                                <div class="rounded-2xl bg-amber-500/12 p-4 ring-1 ring-amber-400/20">
                                    <p class="text-sm text-amber-200">Students</p>
                                    <p class="mt-3 text-3xl font-bold text-white">3.4k</p>
                                </div>
                                <div class="rounded-2xl bg-cyan-500/12 p-4 ring-1 ring-cyan-400/20">
                                    <p class="text-sm text-cyan-200">Quality score</p>
                                    <p class="mt-3 text-3xl font-bold text-white">4.8/5</p>
                                </div>
                            </div>
                            <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950/80 p-4">
                                <div class="flex items-center justify-between text-sm text-slate-300">
                                    <span>Academic performance</span>
                                    <span class="text-emerald-300">+18.4%</span>
                                </div>
                                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-800">
                                    <div class="h-full w-[78%] rounded-full bg-gradient-to-r from-indigo-400 via-violet-400 to-emerald-400"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="mx-auto max-w-7xl px-6 pb-20 sm:px-8 lg:px-12">
                    <div class="mb-10 text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-200">Platform capabilities</p>
                        <h2 class="mt-4 text-3xl font-bold text-white">Built for institutional scale and clarity.</h2>
                    </div>
                    <div class="grid gap-6 md:grid-cols-3">
                        <article class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
                            <div class="mb-4 inline-flex rounded-2xl bg-indigo-500/12 p-3 text-indigo-300">01</div>
                            <h3 class="text-xl font-semibold text-white">Unified control</h3>
                            <p class="mt-3 text-slate-300">Coordinate instructors, classrooms, schedules, and accounts from a single administrative dashboard.</p>
                        </article>
                        <article class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
                            <div class="mb-4 inline-flex rounded-2xl bg-emerald-500/12 p-3 text-emerald-300">02</div>
                            <h3 class="text-xl font-semibold text-white">Quality tracking</h3>
                            <p class="mt-3 text-slate-300">Measure teaching quality through ratings, qualifications, and performance-based review workflows.</p>
                        </article>
                        <article class="rounded-3xl border border-slate-800 bg-slate-900/70 p-6">
                            <div class="mb-4 inline-flex rounded-2xl bg-violet-500/12 p-3 text-violet-300">03</div>
                            <h3 class="text-xl font-semibold text-white">Student visibility</h3>
                            <p class="mt-3 text-slate-300">Give students access to schedules, essential records, and announcements through a clear, role-aware experience.</p>
                        </article>
                    </div>
                </section>

                <section id="security" class="mx-auto max-w-7xl px-6 pb-20 sm:px-8 lg:px-12">
                    <div class="rounded-[2rem] border border-indigo-500/20 bg-gradient-to-br from-indigo-500/10 via-slate-900 to-slate-950 p-8 md:p-10">
                        <div class="grid gap-8 lg:grid-cols-[1fr_1fr] lg:items-center">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-200">Security-first design</p>
                                <h2 class="mt-4 text-3xl font-bold text-white">Built with accountability and protection in mind.</h2>
                                <p class="mt-4 text-slate-300">DUCQUE uses secure authentication patterns, role restrictions, and protected dashboards so only authorized users can access critical academic information.</p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-slate-700 bg-slate-900/60 p-4">
                                    <p class="text-sm text-slate-400">Access control</p>
                                    <p class="mt-2 text-2xl font-bold text-white">RBAC</p>
                                </div>
                                <div class="rounded-2xl border border-slate-700 bg-slate-900/60 p-4">
                                    <p class="text-sm text-slate-400">Audit visibility</p>
                                    <p class="mt-2 text-2xl font-bold text-white">Protected</p>
                                </div>
                                <div class="rounded-2xl border border-slate-700 bg-slate-900/60 p-4">
                                    <p class="text-sm text-slate-400">Session safety</p>
                                    <p class="mt-2 text-2xl font-bold text-white">Encrypted</p>
                                </div>
                                <div class="rounded-2xl border border-slate-700 bg-slate-900/60 p-4">
                                    <p class="text-sm text-slate-400">Operational clarity</p>
                                    <p class="mt-2 text-2xl font-bold text-white">Premium</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
