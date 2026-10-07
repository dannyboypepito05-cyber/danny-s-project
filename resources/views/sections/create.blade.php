<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">Create Section</h2>
            <a href="{{ route('sections.index') }}" class="text-sm font-medium text-slate-300 hover:text-white">Back to sections</a>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-3xl rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-slate-950/30">
            <form method="POST" action="{{ route('sections.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="program_id" class="block text-sm font-medium text-slate-200">Program</label>
                    <select id="program_id" name="program_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                        <option value="">Select program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                        @endforeach
                    </select>
                    @error('program_id')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-200">Section Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    @error('name')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="year_level" class="block text-sm font-medium text-slate-200">Year Level</label>
                        <input id="year_level" name="year_level" type="text" value="{{ old('year_level') }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    </div>
                    <div>
                        <label for="academic_year" class="block text-sm font-medium text-slate-200">Academic Year</label>
                        <input id="academic_year" name="academic_year" type="text" value="{{ old('academic_year', '2026-2027') }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    </div>
                </div>

                <div>
                    <label for="semester" class="block text-sm font-medium text-slate-200">Semester</label>
                    <input id="semester" name="semester" type="text" value="{{ old('semester', '1st Semester') }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    @error('semester')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-slate-200">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none">
                        <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('sections.index') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800">Cancel</a>
                    <button type="submit" class="rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">Save Section</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
