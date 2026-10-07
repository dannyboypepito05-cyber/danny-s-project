<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">Program Details</h2>
            <a href="{{ route('programs.index') }}" class="text-sm font-medium text-slate-300 hover:text-white">Back to programs</a>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-3xl rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-slate-950/30">
            <div class="flex items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.22em] text-indigo-200">Program</p>
                    <h3 class="mt-2 text-3xl font-bold text-white">{{ $program->name }}</h3>
                </div>
                <span class="rounded-full border border-emerald-400/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-200">{{ $program->status }}</span>
            </div>

            <dl class="mt-6 space-y-4 text-sm text-slate-300">
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-3">
                    <dt class="text-slate-400">Department</dt>
                    <dd class="font-medium text-slate-100">{{ $program->department?->name ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-3">
                    <dt class="text-slate-400">Program Code</dt>
                    <dd class="font-medium text-slate-100">{{ $program->code }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Description</dt>
                    <dd class="mt-2 text-slate-200">{{ $program->description ?: 'No description provided.' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>
