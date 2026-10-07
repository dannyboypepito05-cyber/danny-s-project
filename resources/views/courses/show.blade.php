<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">Course Details</h2>
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-slate-300 hover:text-white">Back to courses</a>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-3xl rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-slate-950/30">
            <div class="flex items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.22em] text-indigo-200">Course</p>
                    <h3 class="mt-2 text-3xl font-bold text-white">{{ $course->title }}</h3>
                </div>
                <span class="rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-200">{{ $course->status }}</span>
            </div>

            <dl class="mt-6 space-y-4 text-sm text-slate-300">
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-3">
                    <dt class="text-slate-400">Department</dt>
                    <dd class="font-medium text-slate-100">{{ $course->department?->name ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-3">
                    <dt class="text-slate-400">Course Code</dt>
                    <dd class="font-medium text-slate-100">{{ $course->code }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-3">
                    <dt class="text-slate-400">Units</dt>
                    <dd class="font-medium text-slate-100">{{ $course->units }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Description</dt>
                    <dd class="mt-2 text-slate-200">{{ $course->description ?: 'No description provided.' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>
