<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-900">Program Management</h2>
            <a href="{{ route('programs.create') }}" class="rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">Add Program</a>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-200">{{ session('success') }}</div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/80 shadow-xl shadow-slate-950/30">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-800">
                        <thead class="bg-slate-950/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Program</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-sm text-slate-200">
                            @forelse($programs as $program)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('programs.show', $program) }}" class="font-semibold text-indigo-300 hover:text-indigo-200">{{ $program->name }}</a>
                                        <div class="text-xs text-slate-400">{{ $program->code }}</div>
                                    </td>
                                    <td class="px-6 py-4">{{ $program->department?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4"><span class="rounded-full border border-emerald-400/30 bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-200">{{ $program->status }}</span></td>
                                    <td class="px-6 py-4">
                                        <div class="flex gap-3">
                                            <a href="{{ route('programs.edit', $program) }}" class="text-indigo-300 hover:text-indigo-200">Edit</a>
                                            <form method="POST" action="{{ route('programs.destroy', $program) }}" onsubmit="return confirm('Delete this program?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-300 hover:text-rose-200">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-400">No programs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
