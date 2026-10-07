<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-100">Create Course</h2>
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-slate-300 hover:text-white">Back to courses</a>
        </div>
    </x-slot>

    <div class="bg-slate-950 py-8">
        <div class="mx-auto max-w-3xl rounded-2xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl shadow-slate-950/30">
            <form method="POST" action="{{ route('courses.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="department_id" class="block text-sm font-medium text-slate-200">Department</label>
                    <select id="department_id" name="department_id" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                        <option value="">Select department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-slate-200">Course Code</label>
                    <input id="code" name="code" type="text" value="{{ old('code') }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    @error('code')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-slate-200">Course Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    @error('title')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="units" class="block text-sm font-medium text-slate-200">Units</label>
                    <input id="units" name="units" type="number" min="1" max="12" value="{{ old('units', 3) }}" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none" required>
                    @error('units')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-slate-200">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none">
                        <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-200">Description</label>
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-slate-100 focus:border-indigo-500 focus:outline-none">{{ old('description') }}</textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('courses.index') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800">Cancel</a>
                    <button type="submit" class="rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">Save Course</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
